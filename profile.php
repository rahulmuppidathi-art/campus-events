<?php
/**
 * CAMPUS EVENTS - Student & User Profile Management
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$currentUser = current_user();
$db = getDB();

if (!$db) {
    die("Database is offline.");
}

$errors = [];
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $errors[] = 'Security validation token mismatch.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $name = sanitize($_POST['name'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');
            $rollNo = sanitize($_POST['roll_no'] ?? '');
            $department = sanitize($_POST['department'] ?? '');
            $yearOfStudy = sanitize($_POST['year_of_study'] ?? '');

            if (empty($name)) {
                $errors[] = 'Name cannot be empty.';
            } else {
                $updStmt = $db->prepare("
                    UPDATE users 
                    SET name = :name, phone = :phone, roll_no = :roll_no, department = :department, year_of_study = :year_of_study 
                    WHERE id = :id
                ");
                $updStmt->execute([
                    'name' => $name,
                    'phone' => $phone,
                    'roll_no' => $rollNo,
                    'department' => $department,
                    'year_of_study' => $yearOfStudy,
                    'id' => $currentUser['id']
                ]);

                $_SESSION['user_name'] = $name;
                set_flash('success', 'Profile details updated successfully!');
                header('Location: ' . BASE_URL . 'profile.php');
                exit;
            }
        } elseif ($action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Fetch user password hash
            $pStmt = $db->prepare("SELECT password FROM users WHERE id = :id");
            $pStmt->execute(['id' => $currentUser['id']]);
            $userData = $pStmt->fetch();

            if (!password_verify($currentPassword, $userData['password'])) {
                $errors[] = 'Current password entered is incorrect.';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'New passwords do not match.';
            } else {
                $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                $updPass = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
                $updPass->execute([
                    'password' => $newHash,
                    'id' => $currentUser['id']
                ]);

                set_flash('success', 'Your password has been changed successfully!');
                header('Location: ' . BASE_URL . 'profile.php');
                exit;
            }
        }
    }
}

// Re-fetch current user data to reflect recent changes
$currentUser = current_user();

$pageTitle = 'Profile Settings | Campus Events';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">Account &amp; Profile</h1>
                    <p class="text-muted small mb-0">Manage your personal university registration credentials</p>
                </div>
                <span class="badge bg-<?= ($currentUser['role'] === 'admin') ? 'danger' : 'primary' ?> px-3 py-2 text-uppercase font-monospace">
                    <?= htmlspecialchars($currentUser['role']) ?>
                </span>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger small mb-4">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Personal Information Card -->
            <div class="card border rounded-4 shadow-sm p-4 mb-4 bg-white">
                <h4 class="h5 fw-bold mb-3"><i class="bi bi-person-circle text-primary me-2"></i>Personal Details</h4>
                <form method="POST" action="profile.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_profile">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($currentUser['name']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Email Address (Read-only)</label>
                            <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($currentUser['email']) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Roll Number / ID</label>
                            <input type="text" name="roll_no" class="form-control" value="<?= htmlspecialchars($currentUser['roll_no'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Academic Department</label>
                            <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($currentUser['department'] ?? '') ?>" placeholder="e.g. Computer Science">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Year of Study</label>
                            <input type="text" name="year_of_study" class="form-control" value="<?= htmlspecialchars($currentUser['year_of_study'] ?? '') ?>" placeholder="e.g. 3rd Year">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            Save Profile Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change Card -->
            <div class="card border rounded-4 shadow-sm p-4 bg-white">
                <h4 class="h5 fw-bold mb-3"><i class="bi bi-shield-lock text-primary me-2"></i>Change Password</h4>
                <form method="POST" action="profile.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">New Password (Min. 6 chars)</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-outline-navy px-4 fw-semibold">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

