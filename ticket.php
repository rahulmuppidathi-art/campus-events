<?php
/**
 * CAMPUS EVENTS - Digital Event Pass / E-Ticket
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$currentUser = current_user();

$regCode = sanitize($_GET['code'] ?? '');
if (empty($regCode)) {
    header('Location: ' . BASE_URL . 'my-registrations.php');
    exit;
}

$db = getDB();
if (!$db) {
    die("Database connection offline.");
}

// Fetch registration details with user and event info
$stmt = $db->prepare("
    SELECT r.*, 
           u.name AS student_name, u.email AS student_email, u.roll_no AS student_roll_no, 
           u.department AS student_dept, u.year_of_study,
           e.title AS event_title, e.event_date, e.start_time, e.end_time, e.venue, e.coordinator_name, e.coordinator_phone,
           c.name AS category_name
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
    JOIN categories c ON e.category_id = c.id
    WHERE r.registration_code = :code
    LIMIT 1
");
$stmt->execute(['code' => $regCode]);
$ticket = $stmt->fetch();

// Authorization: Only the registered student or an administrator can view this ticket
if (!$ticket || ($ticket['user_id'] != $currentUser['id'] && !is_admin())) {
    set_flash('danger', 'Access denied. You do not have permission to view this ticket.');
    header('Location: ' . BASE_URL . 'my-registrations.php');
    exit;
}

$pageTitle = 'Digital Event Pass - ' . $ticket['registration_code'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center max-w-700 mx-auto mb-4 d-print-none">
        <a href="<?= BASE_URL ?>my-registrations.php" class="btn btn-outline-navy btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Registrations
        </a>
        <button id="printTicketBtn" class="btn btn-primary btn-sm">
            <i class="bi bi-printer me-1"></i> Print / Save as PDF
        </button>
    </div>

    <!-- Ticket Wrapper Container -->
    <div class="ticket-wrapper">
        <!-- Ticket Top Banner -->
        <div class="ticket-header">
            <div>
                <span class="badge bg-white text-dark fw-bold mb-1 font-monospace">OFFICIAL ENTRY PASS</span>
                <h4 class="fw-bold mb-0 text-white"><?= htmlspecialchars(APP_NAME) ?></h4>
                <div class="text-white-50 small"><?= htmlspecialchars(APP_TAGLINE) ?></div>
            </div>
            <div class="text-end">
                <span class="badge bg-primary px-3 py-2 fs-6 font-monospace">
                    <?= htmlspecialchars($ticket['registration_code']) ?>
                </span>
                <div class="text-white-50 small mt-1">Status: <?= strtoupper($ticket['status']) ?></div>
            </div>
        </div>

        <!-- Ticket Body Details -->
        <div class="ticket-body">
            <div class="row g-4 align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-primary-subtle text-primary fw-semibold mb-2">
                        <?= htmlspecialchars($ticket['category_name']) ?>
                    </span>
                    <h3 class="fw-bold text-dark mb-3"><?= htmlspecialchars($ticket['event_title']) ?></h3>

                    <div class="row g-3 small">
                        <div class="col-6">
                            <span class="text-muted d-block">Event Date:</span>
                            <strong class="text-dark fs-6"><?= format_date($ticket['event_date']) ?></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Time:</span>
                            <strong class="text-dark fs-6"><?= format_time($ticket['start_time']) ?></strong>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block">Venue:</span>
                            <strong class="text-dark"><?= htmlspecialchars($ticket['venue']) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Simulated High-Precision QR Code -->
                <div class="col-md-4 text-center">
                    <div class="ticket-qr-box">
                        <svg viewBox="0 0 100 100" width="100" height="100">
                            <!-- QR Code Frame -->
                            <rect width="100" height="100" fill="#FFFFFF"/>
                            <!-- Corner 1 -->
                            <rect x="5" y="5" width="30" height="30" fill="#111827"/>
                            <rect x="10" y="10" width="20" height="20" fill="#FFFFFF"/>
                            <rect x="15" y="15" width="10" height="10" fill="#111827"/>
                            <!-- Corner 2 -->
                            <rect x="65" y="5" width="30" height="30" fill="#111827"/>
                            <rect x="70" y="10" width="20" height="20" fill="#FFFFFF"/>
                            <rect x="75" y="15" width="10" height="10" fill="#111827"/>
                            <!-- Corner 3 -->
                            <rect x="5" y="65" width="30" height="30" fill="#111827"/>
                            <rect x="10" y="70" width="20" height="20" fill="#FFFFFF"/>
                            <rect x="15" y="75" width="10" height="10" fill="#111827"/>
                            <!-- Random Pattern Pixels -->
                            <rect x="42" y="10" width="8" height="8" fill="#111827"/>
                            <rect x="45" y="25" width="8" height="8" fill="#111827"/>
                            <rect x="25" y="45" width="8" height="8" fill="#111827"/>
                            <rect x="40" y="42" width="16" height="16" fill="#2563EB"/>
                            <rect x="65" y="45" width="8" height="8" fill="#111827"/>
                            <rect x="45" y="65" width="8" height="8" fill="#111827"/>
                            <rect x="65" y="65" width="12" height="12" fill="#111827"/>
                            <rect x="80" y="80" width="10" height="10" fill="#111827"/>
                        </svg>
                    </div>
                    <span class="font-monospace small text-muted mt-2 d-block" style="font-size: 0.75rem;">
                        SCAN AT ENTRANCE
                    </span>
                </div>
            </div>

            <!-- Student Attendee Information Block -->
            <div class="mt-4 pt-3 border-top">
                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 0.05em;">Attendee Verification Information</h6>
                <div class="row g-3 small bg-light p-3 rounded-3 border">
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Attendee Name:</span>
                        <strong class="text-dark"><?= htmlspecialchars($ticket['student_name']) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Roll Number / ID:</span>
                        <strong class="text-dark font-monospace"><?= htmlspecialchars($ticket['student_roll_no'] ?: 'N/A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Department:</span>
                        <span class="text-dark"><?= htmlspecialchars($ticket['student_dept'] ?: 'General') ?> (<?= htmlspecialchars($ticket['year_of_study'] ?: '') ?>)</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Registration Date:</span>
                        <span class="text-dark"><?= format_date($ticket['registered_at'], 'M d, Y h:i A') ?></span>
                    </div>
                </div>
            </div>

            <!-- Pass Security Footer -->
            <div class="mt-4 pt-2 text-center text-muted small border-top" style="font-size: 0.75rem;">
                <p class="mb-0">
                    <i class="bi bi-shield-lock-fill text-success me-1"></i>
                    This digital e-pass is issued by Campus Events Central Portal and is non-transferable. Present college photo ID at check-in.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

