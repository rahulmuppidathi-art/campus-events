<?php
/**
 * CAMPUS EVENTS - Admin Sidebar Navigation
 */
?>
<aside class="admin-sidebar">
    <div class="admin-sidebar-brand">
        <div class="brand-icon bg-primary" style="width: 32px; height: 32px; font-size: 1rem;">
            <i class="bi bi-shield-check"></i>
        </div>
        <div class="d-flex flex-column">
            <span class="fs-6 fw-bold text-white"><?= htmlspecialchars(APP_NAME) ?></span>
            <span style="font-size: 0.65rem; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em;">Admin Portal</span>
        </div>
    </div>

    <div class="admin-nav">
        <div class="text-uppercase small px-3 mb-2 fw-bold text-white-50" style="font-size: 0.7rem; letter-spacing: 0.05em;">
            Main Menu
        </div>
        
        <ul class="list-unstyled mb-4">
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>admin/index.php" class="admin-nav-link <?= ($adminPage === 'index.php') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>admin/events.php" class="admin-nav-link <?= (in_array($adminPage, ['events.php', 'event-edit.php'])) ? 'active' : '' ?>">
                    <i class="bi bi-calendar-event"></i>
                    <span>Manage Events</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>admin/event-create.php" class="admin-nav-link <?= ($adminPage === 'event-create.php') ? 'active' : '' ?>">
                    <i class="bi bi-calendar-plus"></i>
                    <span>Create Event</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>admin/registrations.php" class="admin-nav-link <?= ($adminPage === 'registrations.php') ? 'active' : '' ?>">
                    <i class="bi bi-card-checklist"></i>
                    <span>Registrations &amp; Passes</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>admin/students.php" class="admin-nav-link <?= ($adminPage === 'students.php') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>Registered Students</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>admin/messages.php" class="admin-nav-link <?= ($adminPage === 'messages.php') ? 'active' : '' ?>">
                    <i class="bi bi-chat-left-text"></i>
                    <span>Inquiries &amp; Support</span>
                </a>
            </li>
        </ul>

        <div class="text-uppercase small px-3 mb-2 fw-bold text-white-50" style="font-size: 0.7rem; letter-spacing: 0.05em;">
            System
        </div>
        <ul class="list-unstyled">
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>setup.php" class="admin-nav-link">
                    <i class="bi bi-database-gear"></i>
                    <span>DB Setup &amp; Reset</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?= BASE_URL ?>logout.php" class="admin-nav-link text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Sign Out</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="p-3 border-top border-secondary-subtle mt-auto small text-white-50" style="font-size: 0.75rem;">
        <div>Logged in as: <strong class="text-white"><?= htmlspecialchars($currentUser['name']) ?></strong></div>
        <div>Leader: <span class="text-white"><?= htmlspecialchars(TEAM_LEADER) ?></span></div>
    </div>
</aside>

