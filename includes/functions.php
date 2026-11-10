<?php
/**
 * CAMPUS EVENTS - Helper Functions Library
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize string input against XSS
 */
function sanitize(?string $data): string {
    if ($data === null) return '';
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF Token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output CSRF Hidden Input Tag
 */
function csrf_field(): string {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Validate CSRF Token
 */
function csrf_validate(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set Flash Message
 * Types: 'success', 'danger', 'warning', 'info'
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and Clear Flash Message
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if user is logged in
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user details
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }

    static $currentUser = null;
    if ($currentUser !== null) {
        return $currentUser;
    }

    $db = getDB();
    if (!$db) return null;

    $stmt = $db->prepare("SELECT id, name, email, role, roll_no, phone, department, year_of_study, avatar, status FROM users WHERE id = :id AND status = 'active' LIMIT 1");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
    return $currentUser ?: null;
}

/**
 * Check if current user is an administrator
 */
function is_admin(): bool {
    $user = current_user();
    return $user && ($user['role'] === 'admin');
}

/**
 * Enforce authentication for protected student routes
 */
function require_login(string $redirectUrl = ''): void {
    if (!is_logged_in()) {
        $target = $redirectUrl ?: $_SERVER['REQUEST_URI'] ?? 'index.php';
        set_flash('warning', 'Please sign in to access this page or register for events.');
        header('Location: ' . BASE_URL . 'login.php?redirect=' . urlencode($target));
        exit;
    }
}

/**
 * Enforce administrator authentication
 */
function require_admin(): void {
    if (!is_logged_in() || !is_admin()) {
        set_flash('danger', 'Access denied. Administrator privileges required.');
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

/**
 * Format Date nicely (e.g. Nov 20, 2026)
 */
function format_date(?string $date, string $format = 'M d, Y'): string {
    if (!$date) return 'TBA';
    try {
        $dt = new DateTime($date);
        return $dt->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Format Time nicely (e.g. 09:30 AM)
 */
function format_time(?string $time, string $format = 'g:i A'): string {
    if (!$time) return 'TBA';
    try {
        $dt = new DateTime($time);
        return $dt->format($format);
    } catch (Exception $e) {
        return $time;
    }
}

/**
 * Generate Unique Event Registration Code (e.g., REG-2026-X8B9Q)
 */
function generate_registration_code(string $prefix = 'REG'): string {
    $year = date('Y');
    $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 5));
    return "{$prefix}-{$year}-{$random}";
}

/**
 * Generate Slug from string
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'event-' . time());
}

/**
 * Render Status Badges
 */
function get_status_badge(string $status): string {
    $status = strtolower($status);
    return match ($status) {
        'published', 'confirmed', 'active' => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">Confirmed / Active</span>',
        'draft' => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1">Draft</span>',
        'completed', 'attended' => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">Completed / Attended</span>',
        'cancelled', 'inactive' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">Cancelled</span>',
        default => '<span class="badge bg-light text-dark px-2.5 py-1">' . htmlspecialchars(ucfirst($status)) . '</span>'
    };
}

/**
 * File upload handler with validation
 */
function handle_image_upload(array $file, string $targetDir): ?string {
    if (!isset($file['error']) || is_array($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        throw new RuntimeException('Exceeded filesize limit (max 5MB).');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp'
    ];

    $ext = array_search($mime, $allowedMimes, true);
    if ($ext === false) {
        throw new RuntimeException('Invalid file format. Allowed formats: JPG, PNG, WEBP.');
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filename = sprintf('event_%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(4)), $ext);
    $targetPath = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return $filename;
}

