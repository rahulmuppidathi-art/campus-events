<?php
/**
 * CAMPUS EVENTS - Admin Header Component
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();
$currentUser = current_user();
$adminPage = basename($_SERVER['PHP_SELF']);
$adminTitle = $adminTitle ?? 'Admin Dashboard | ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminTitle) ?></title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Admin Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- Sidebar Component -->
    <?php require_once __DIR__ . '/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="admin-main">
        <!-- Admin Top Navigation Bar -->
        <header class="admin-header">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-outline-secondary btn-sm d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="fw-bold text-dark fs-6 d-none d-sm-block">
                    University Event Administration
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="<?= BASE_URL ?>index.php" class="btn btn-outline-navy btn-sm" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i> View Website
                </a>

                <div class="dropdown">
                    <button class="btn btn-light btn-sm border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="badge bg-danger text-white rounded-circle p-1">
                            <i class="bi bi-shield-fill"></i>
                        </span>
                        <span class="fw-semibold small"><?= htmlspecialchars($currentUser['name']) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                        <li class="px-3 py-1.5 border-bottom small">
                            <div class="fw-bold"><?= htmlspecialchars($currentUser['name']) ?></div>
                            <div class="text-muted"><?= htmlspecialchars($currentUser['email']) ?></div>
                        </li>
                        <li><a class="dropdown-item py-2 small" href="<?= BASE_URL ?>profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 small text-danger" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Admin Content Container -->
        <div class="admin-content">
            <!-- Flash Message in Admin -->
            <?php 
            $flash = get_flash();
            if ($flash): 
            ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show d-flex align-items-center shadow-sm mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($flash['message']) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

