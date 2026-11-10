<?php
/**
 * CAMPUS EVENTS - Authentication / Login
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, redirect
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: ' . BASE_URL . 'admin/index.php');
    } else {
        header('Location: ' . BASE_URL . 'my-registrations.php');
    }
    exit;
}

$redirect = sanitize($_GET['redirect'] ?? '');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $errors[] = 'Security validation failed. Please try again.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $errors[] = 'Please provide both your email address and password.';
        } else {
            $db = getDB();
            if (!$db) {
                $errors[] = 'Database is currently offline. Please ensure MySQL is running.';
            } else {
                $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    if ($user['status'] !== 'active') {
                        $errors[] = 'Your account has been deactivated. Please contact the administrator.';
                    } else {
                        // Login successful!
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];

                        set_flash('success', "Welcome back, {$user['name']}!");

                        if (!empty($redirect) && str_starts_with($redirect, '/')) {
                            header('Location: ' . $redirect);
                        } elseif ($user['role'] === 'admin') {
                            header('Location: ' . BASE_URL . 'admin/index.php');
                        } else {
                            header('Location: ' . BASE_URL . 'my-registrations.php');
                        }
                        exit;
                    }
                } else {
                    $errors[] = 'Invalid email address or password. Please verify your credentials.';
                }
            }
        }
    }
}

$pageTitle = 'Sign In | Campus Events';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="card border shadow-lg rounded-4 overflow-hidden bg-white">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="brand-icon mx-auto mb-3" style="width: 48px; height: 48px; font-size: 1.4rem;">
                            <i class="bi bi-person-lock"></i>
                        </div>
                        <h2 class="h4 fw-bold text-dark">Sign In to Campus Events</h2>
                        <p class="text-muted small">Access your event registrations and university portal</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger p-2.5 small mb-3">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Demo Evaluation Credentials Box -->
                    <div class="alert alert-light border small mb-4">
                        <div class="fw-semibold text-dark mb-1"><i class="bi bi-key-fill text-primary me-1"></i> Quick Demo Logins:</div>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-danger w-50 py-1" onclick="fillCredentials('admin@campusevents.edu', 'Admin@123')">
                                <i class="bi bi-shield-lock me-1"></i> Admin Demo
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary w-50 py-1" onclick="fillCredentials('patrick@student.edu', 'Student@123')">
                                <i class="bi bi-person-fill me-1"></i> Student Demo
                            </button>
                        </div>
                    </div>

                    <form method="POST" action="login.php<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="loginEmail" class="form-label small fw-semibold text-muted">College Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" id="loginEmail" class="form-control border-start-0" placeholder="e.g. patrick@student.edu" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="loginPassword" class="form-label small fw-semibold text-muted">Password</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" id="loginPassword" class="form-control border-start-0" placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold shadow-sm mb-3">
                            Sign In to Portal
                        </button>
                    </form>

                    <div class="text-center pt-3 border-top text-muted small">
                        Don't have an account yet? 
                        <a href="<?= BASE_URL ?>register.php" class="fw-semibold text-decoration-none">Create Student Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

