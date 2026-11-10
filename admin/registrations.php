<?php
/**
 * CAMPUS EVENTS - Admin Registrations & Attendance Roster
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();
$db = getDB();
if (!$db) {
    die("Database connection offline.");
}

// 1. Handle Status Change Actions (e.g. Mark Attended, Cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (csrf_validate()) {
        $regId = filter_input(INPUT_POST, 'reg_id', FILTER_VALIDATE_INT);
        $newStatus = sanitize($_POST['new_status'] ?? '');

        if ($regId && in_array($newStatus, ['confirmed', 'attended', 'cancelled'])) {
            try {
                $updStmt = $db->prepare("UPDATE registrations SET status = :status, attended_at = :att_at WHERE id = :id");
                $attAt = ($newStatus === 'attended') ? date('Y-m-d H:i:s') : null;
                $updStmt->execute([
                    'status' => $newStatus,
                    'att_at' => $attAt,
                    'id' => $regId
                ]);
                set_flash('success', "Registration #{$regId} status changed to " . ucfirst($newStatus) . ".");
            } catch (PDOException $e) {
                set_flash('danger', 'Update failed: ' . $e->getMessage());
            }
        }
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// 2. Filter & Search Parameters
$search = sanitize($_GET['search'] ?? '');
$eventIdFilter = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
$statusFilter = sanitize($_GET['status'] ?? '');
$exportCsv = isset($_GET['export']) && $_GET['export'] === 'csv';

// Fetch all events for filter dropdown
$eventsDropdown = $db->query("SELECT id, title, event_date FROM events ORDER BY event_date DESC")->fetchAll();

// Build Query
$sql = "
    SELECT r.*, 
           u.name AS student_name, u.email AS student_email, u.roll_no, u.department, u.year_of_study, u.phone AS student_phone,
           e.title AS event_title, e.event_date, e.start_time, e.venue 
    FROM registrations r 
    JOIN users u ON r.user_id = u.id 
    JOIN events e ON r.event_id = e.id 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.name LIKE :search OR u.email LIKE :search_email OR u.roll_no LIKE :search_roll OR r.registration_code LIKE :search_code)";
    $params['search'] = "%{$search}%";
    $params['search_email'] = "%{$search}%";
    $params['search_roll'] = "%{$search}%";
    $params['search_code'] = "%{$search}%";
}

if ($eventIdFilter) {
    $sql .= " AND r.event_id = :event_id";
    $params['event_id'] = $eventIdFilter;
}

if (!empty($statusFilter) && $statusFilter !== 'all') {
    $sql .= " AND r.status = :status";
    $params['status'] = $statusFilter;
}

$sql .= " ORDER BY r.registered_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$registrations = $stmt->fetchAll();

// Handle CSV Export
if ($exportCsv) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=event_registrations_' . date('Y-m-d_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Reg ID', 'Pass Code', 'Event Title', 'Event Date', 'Student Name', 'Roll Number', 'Email', 'Department', 'Year', 'Phone', 'Booking Date', 'Status', 'Notes']);

    foreach ($registrations as $row) {
        fputcsv($output, [
            $row['id'],
            $row['registration_code'],
            $row['event_title'],
            $row['event_date'],
            $row['student_name'],
            $row['roll_no'],
            $row['student_email'],
            $row['department'],
            $row['year_of_study'],
            $row['student_phone'],
            $row['registered_at'],
            $row['status'],
            $row['notes']
        ]);
    }
    fclose($output);
    exit;
}

$adminTitle = 'Registrations & Attendance | Campus Events Admin';
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Event Registrations &amp; Attendee Roster</h1>
        <p class="text-muted small mb-0">Search registered students, verify entry passcodes, and log campus attendance.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print();" class="btn btn-outline-secondary btn-sm d-print-none">
            <i class="bi bi-printer me-1"></i> Print Attendance Sheet
        </button>
        <a href="<?= BASE_URL ?>admin/registrations.php?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn btn-navy btn-sm d-print-none">
            <i class="bi bi-file-earmark-excel me-1"></i> Export to CSV
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card border rounded-3 p-3 bg-white shadow-sm mb-4 d-print-none">
    <form method="GET" action="registrations.php" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Student name, roll no, or REG-XXXX..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-4">
            <select name="event_id" class="form-select form-select-sm">
                <option value="">All Events Roster</option>
                <?php foreach ($eventsDropdown as $ev): ?>
                    <option value="<?= $ev['id'] ?>" <?= ($eventIdFilter == $ev['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ev['title']) ?> (<?= format_date($ev['event_date']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="all">All Statuses</option>
                <option value="confirmed" <?= ($statusFilter === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                <option value="attended" <?= ($statusFilter === 'attended') ? 'selected' : '' ?>>Attended</option>
                <option value="cancelled" <?= ($statusFilter === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            <a href="registrations.php" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

<!-- Registrations Roster Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h5 class="admin-card-title">Registration Entries (<?= count($registrations) ?>)</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th>Pass Code</th>
                    <th>Student Attendee</th>
                    <th>Department &amp; Roll No</th>
                    <th>Event Details</th>
                    <th>Registered At</th>
                    <th>Status</th>
                    <th class="text-end d-print-none">Attendance Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($registrations)): ?>
                    <?php foreach ($registrations as $r): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>ticket.php?code=<?= urlencode($r['registration_code']) ?>" target="_blank" class="font-monospace fw-bold small text-primary text-decoration-none">
                                    <i class="bi bi-qr-code me-1"></i><?= htmlspecialchars($r['registration_code']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($r['student_name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($r['student_email']) ?></div>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= htmlspecialchars($r['department'] ?: 'General') ?></div>
                                <div class="small text-muted font-monospace"><?= htmlspecialchars($r['roll_no'] ?: 'N/A') ?> (<?= htmlspecialchars($r['year_of_study'] ?: '') ?>)</div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-truncate" style="max-width: 170px;">
                                    <?= htmlspecialchars($r['event_title']) ?>
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <?= format_date($r['event_date']) ?> • <?= htmlspecialchars($r['venue']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="small text-muted"><?= format_date($r['registered_at'], 'M d, Y h:i A') ?></span>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'confirmed'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Confirmed</span>
                                <?php elseif ($r['status'] === 'attended'): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Attended</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end d-print-none">
                                <form method="POST" action="registrations.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : '' ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="reg_id" value="<?= $r['id'] ?>">

                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end small">
                                            <li>
                                                <button type="submit" name="new_status" value="attended" class="dropdown-item text-primary">
                                                    <i class="bi bi-check2-circle me-1"></i> Mark Attended
                                                </button>
                                            </li>
                                            <li>
                                                <button type="submit" name="new_status" value="confirmed" class="dropdown-item text-success">
                                                    <i class="bi bi-ticket me-1"></i> Set Confirmed
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="submit" name="new_status" value="cancelled" class="dropdown-item text-danger">
                                                    <i class="bi bi-x-circle me-1"></i> Cancel Pass
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted small">
                            No registration entries found matching the filter query.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>

