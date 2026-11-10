<?php
/**
 * CAMPUS EVENTS - Student Registration
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . 'my-registrations.php');
    exit;
}

$errors = [];
$name = '';
$email = '';
$rollNo = '';
$phone = '';
$department = '';
$yearOfStudy = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $errors[] = 'CSRF validation token mismatch. Please try again.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $rollNo = sanitize($_POST['roll_no'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $department = sanitize($_POST['department'] ?? '');
        $yearOfStudy = sanitize($_POST['year_of_study'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($name)) $errors[] = 'Full Name is required.';
        if (!$email) $errors[] = 'A valid college email address is required.';
        if (empty($rollNo)) $errors[] = 'Student Roll Number / University ID is required.';
        if (empty($department)) $errors[] = 'Please select your department.';
        if (empty($yearOfStudy)) $errors[] = 'Please select your academic year.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters long.';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            $db = getDB();
            if (!$db) {
                $errors[] = 'Database is currently offline. Please ensure MySQL is started.';
            } else {
                // Check if email already registered
                $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
                $stmt->execute(['email' => $email]);
                if ($stmt->fetch()) {
                    $errors[] = 'An account with this email address already exists. Please sign in instead.';
                } else {
                    // Create student account
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $insertStmt = $db->prepare("
                        INSERT INTO users (name, email, password, role, roll_no, phone, department, year_of_study, status) 
                        VALUES (:name, :email, :password, 'student', :roll_no, :phone, :department, :year_of_study, 'active')
                    ");
                    $inserted = $insertStmt->execute([
                        'name' => $name,
                        'email' => $email,
                        'password' => $hashedPassword,
                        'roll_no' => $rollNo,
                        'phone' => $phone,
                        'department' => $department,
                        'year_of_study' => $yearOfStudy
                    ]);

                    if ($inserted) {
                        $newUserId = $db->lastInsertId();

                        // Auto-login new user
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $newUserId;
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['user_role'] = 'student';

                        set_flash('success', "Welcome to Campus Events, {$name}! Your student account has been created.");
                        header('Location: ' . BASE_URL . 'events.php');
                        exit;
                    } else {
                        $errors[] = 'Failed to create student account. Please try again.';
                    }
                }
            }
        }
    }
}

$pageTitle = 'Create Student Account | Campus Events';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card border shadow-lg rounded-4 overflow-hidden bg-white">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="brand-icon mx-auto mb-3" style="width: 48px; height: 48px; font-size: 1.4rem;">
                            <i class="bi bi-mortarboard"></i>
                        </div>
                        <h2 class="h4 fw-bold text-dark">Create Student Account</h2>
                        <p class="text-muted small">Join your campus event network to participate in competitions & workshops</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger p-2.5 small mb-4">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="register.php" id="registerForm">
                        <?= csrf_field() ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label small fw-semibold text-muted">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Patrick Anderson" value="<?= htmlspecialchars($name) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-semibold text-muted">College Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="e.g. patrick@student.edu" value="<?= htmlspecialchars($email) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="roll_no" class="form-label small fw-semibold text-muted">Student Roll No / ID <span class="text-danger">*</span></label>
                                <input type="text" name="roll_no" id="roll_no" class="form-control" placeholder="e.g. CS2023-042" value="<?= htmlspecialchars($rollNo) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label small fw-semibold text-muted">Phone Number</label>
                                <input type="tel" name="phone" id="phone" class="form-control" placeholder="e.g. +1 (555) 012-3456" value="<?= htmlspecialchars($phone) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="department" class="form-label small fw-semibold text-muted">Academic Department <span class="text-danger">*</span></label>
                                <select name="department" id="department" class="form-select" required>
                                    <option value="">Choose Department...</option>
                                    <option value="Computer Science & Engineering" <?= ($department === 'Computer Science & Engineering') ? 'selected' : '' ?>>Computer Science &amp; Engineering</option>
                                    <option value="Electronics & Communication" <?= ($department === 'Electronics & Communication') ? 'selected' : '' ?>>Electronics &amp; Communication</option>
                                    <option value="Mechanical Engineering" <?= ($department === 'Mechanical Engineering') ? 'selected' : '' ?>>Mechanical Engineering</option>
                                    <option value="Civil Engineering" <?= ($department === 'Civil Engineering') ? 'selected' : '' ?>>Civil Engineering</option>
                                    <option value="Information Technology" <?= ($department === 'Information Technology') ? 'selected' : '' ?>>Information Technology</option>
                                    <option value="Business Administration" <?= ($department === 'Business Administration') ? 'selected' : '' ?>>Business Administration</option>
                                    <option value="Biotechnology" <?= ($department === 'Biotechnology') ? 'selected' : '' ?>>Biotechnology</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="year_of_study" class="form-label small fw-semibold text-muted">Year of Study <span class="text-danger">*</span></label>
                                <select name="year_of_study" id="year_of_study" class="form-select" required>
                                    <option value="">Choose Year...</option>
                                    <option value="1st Year" <?= ($yearOfStudy === '1st Year') ? 'selected' : '' ?>>1st Year (Freshman)</option>
                                    <option value="2nd Year" <?= ($yearOfStudy === '2nd Year') ? 'selected' : '' ?>>2nd Year (Sophomore)</option>
                                    <option value="3rd Year" <?= ($yearOfStudy === '3rd Year') ? 'selected' : '' ?>>3rd Year (Junior)</option>
                                    <option value="4th Year" <?= ($yearOfStudy === '4th Year') ? 'selected' : '' ?>>4th Year (Senior)</option>
                                    <option value="Post Graduate" <?= ($yearOfStudy === 'Post Graduate') ? 'selected' : '' ?>>Post Graduate</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label small fw-semibold text-muted">Password (Min. 6 chars) <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                            </div>

                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label small fw-semibold text-muted">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold shadow-sm">
                                Complete Student Registration
                            </button>
                        </div>
                    </form>

                    <div class="text-center pt-4 border-top mt-4 text-muted small">
                        Already have an account? 
                        <a href="<?= BASE_URL ?>login.php" class="fw-semibold text-decoration-none">Sign In Here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

