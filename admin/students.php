<?php
/**
 * CAMPUS EVENTS - Admin Student Directory
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

// Handle status toggle (Activate / Deactivate student)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (csrf_validate()) {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $newStatus = sanitize($_POST['new_status'] ?? '');
        if ($userId && in_array($newStatus, ['active', 'inactive'])) {
            $stmt = $db->prepare("UPDATE users SET status = :status WHERE id = :id AND role = 'student'");
            $stmt->execute(['status' => $newStatus, 'id' => $userId]);
            set_flash('success', "Student account status updated to " . ucfirst($newStatus) . ".");
        }
    }
    header('Location: ' . BASE_URL . 'admin/students.php');
    exit;
}

$search = sanitize($_GET['search'] ?? '');

$sql = "
    SELECT u.*, COUNT(r.id) AS total_registrations 
    FROM users u 
    LEFT JOIN registrations r ON u.id = r.user_id AND r.status = 'confirmed' 
    WHERE u.role = 'student'
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.name LIKE :search OR u.email LIKE :search_email OR u.roll_no LIKE :search_roll OR u.department LIKE :search_dept)";
    $params['search'] = "%{$search}%";
    $params['search_email'] = "%{$search}%";
    $params['search_roll'] = "%{$search}%";
    $params['search_dept'] = "%{$search}%";
}

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$adminTitle = 'Registered Students Directory | Campus Events Admin';
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Registered Student Directory</h1>
        <p class="text-muted small mb-0">List of student accounts, departmental affiliations, and participation stats.</p>
    </div>
    <div class="text-muted small">
        Total Students: <strong class="text-dark"><?= count($students) ?></strong>
    </div>
</div>

<!-- Search Bar -->
<div class="card border rounded-3 p-3 bg-white shadow-sm mb-4">
    <form method="GET" action="students.php" class="row g-2 align-items-center">
        <div class="col-md-10">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, email, roll number, or department..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">Search</button>
            <a href="students.php" class="btn btn-outline-secondary btn-sm" title="Clear">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

<!-- Students Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h5 class="admin-card-title">Enrolled Students</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Student Name &amp; Email</th>
                    <th>Roll Number</th>
                    <th>Department &amp; Year</th>
                    <th>Phone</th>
                    <th>Active Bookings</th>
                    <th>Status</th>
                    <th class="text-end">Account Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $stu): ?>
                        <tr>
                            <td><span class="text-muted small">#<?= $stu['id'] ?></span></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($stu['name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($stu['email']) ?></div>
                            </td>
                            <td>
                                <code class="small fw-semibold"><?= htmlspecialchars($stu['roll_no'] ?: 'N/A') ?></code>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= htmlspecialchars($stu['department'] ?: 'Undeclared') ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($stu['year_of_study'] ?: '') ?></div>
                            </td>
                            <td>
                                <span class="small text-muted"><?= htmlspecialchars($stu['phone'] ?: 'N/A') ?></span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                                    <?= (int) $stu['total_registrations'] ?> events
                                </span>
                            </td>
                            <td>
                                <?php if ($stu['status'] === 'active'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="students.php" class="d-inline" onsubmit="return confirm('Change status for this student?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="user_id" value="<?= $stu['id'] ?>">
                                    <?php if ($stu['status'] === 'active'): ?>
                                        <input type="hidden" name="new_status" value="inactive">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Deactivate">
                                            Suspend
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="new_status" value="active">
                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Activate">
                                            Reactivate
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted small">
                            No students found matching your search.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>

