<?php
/**
 * CAMPUS EVENTS - Navigation Bar
 */
?>
<nav class="navbar navbar-expand-lg navbar-campus">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand navbar-brand-logo" href="<?= BASE_URL ?>index.php">
            <div class="brand-icon">
                <i class="bi bi-calendar4-event"></i>
            </div>
            <div>
                <div><?= htmlspecialchars(APP_NAME) ?></div>
                <div style="font-size: 0.65rem; color: #64748B; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;">
                    <?= htmlspecialchars(APP_TAGLINE) ?>
                </div>
            </div>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#campusNavbar" aria-controls="campusNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="campusNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'events.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>events.php">
                        <i class="bi bi-calendar-event me-1"></i> Events
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'about.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>about.php">
                        <i class="bi bi-info-circle me-1"></i> About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'contact.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>contact.php">
                        <i class="bi bi-envelope me-1"></i> Contact
                    </a>
                </li>
            </ul>

            <!-- Right Side Auth Navigation -->
            <div class="d-flex align-items-center gap-2">
                <?php if (is_logged_in() && $currentUser): ?>
                    <a href="<?= BASE_URL ?>my-registrations.php" class="btn btn-outline-navy btn-sm d-none d-md-inline-flex align-items-center">
                        <i class="bi bi-ticket-perforated me-1 text-primary"></i> My Registrations
                    </a>

                    <div class="dropdown">
                        <button class="btn btn-outline-navy btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="badge bg-primary rounded-circle p-1">
                                <i class="bi bi-person-fill text-white"></i>
                            </span>
                            <span class="fw-semibold text-truncate" style="max-width: 140px;"><?= htmlspecialchars($currentUser['name']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2" aria-labelledby="userMenuDropdown">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($currentUser['name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($currentUser['email']) ?></div>
                                <span class="badge bg-<?= ($currentUser['role'] === 'admin') ? 'danger' : 'secondary' ?> mt-1">
                                    <?= ucfirst($currentUser['role']) ?>
                                </span>
                            </li>
                            <?php if (is_admin()): ?>
                                <li>
                                    <a class="dropdown-item py-2 text-danger fw-semibold" href="<?= BASE_URL ?>admin/index.php">
                                        <i class="bi bi-speedometer2 me-2"></i> Admin Dashboard
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item py-2" href="<?= BASE_URL ?>my-registrations.php">
                                    <i class="bi bi-ticket-perforated me-2"></i> My Registrations
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="<?= BASE_URL ?>profile.php">
                                    <i class="bi bi-person me-2"></i> Profile Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-navy btn-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </a>
                    <a href="<?= BASE_URL ?>register.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

