<?php
/**
 * CAMPUS EVENTS - Global Page Header
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? APP_NAME . ' | ' . APP_TAGLINE;
$currentPage = basename($_SERVER['PHP_SELF']);
$currentUser = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/navbar.php'; ?>

<!-- Flash Messages Container -->
<div class="container flash-container">
    <?php 
    $flash = get_flash();
    if ($flash): 
    ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($flash['message']) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php 
    // Check if database is connected; if not, show gentle alert with setup link
    $db = getDB();
    if (!$db):
    ?>
        <div class="alert alert-warning border-warning d-flex align-items-center justify-content-between p-3 my-3 shadow-sm rounded-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-database-exclamation fs-3 text-warning me-3"></i>
                <div>
                    <strong>Database Notice:</strong> MySQL server connection not detected on localhost:3306.
                    <div class="small text-muted">Make sure Apache & MySQL are started in the XAMPP Control Panel. Once running, click setup to initialize tables.</div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>setup.php" class="btn btn-sm btn-navy text-nowrap ms-3">
                <i class="bi bi-gear-fill me-1"></i> Run DB Setup
            </a>
        </div>
    <?php endif; ?>
</div>

<main>

