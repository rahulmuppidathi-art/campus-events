<?php
/**
 * CAMPUS EVENTS - Student Registrations Dashboard
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
    die("Database is offline. Please start MySQL in XAMPP.");
}

// Handle Registration Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    if (!csrf_validate()) {
        set_flash('danger', 'Security validation mismatch. Please try again.');
        header('Location: ' . BASE_URL . 'my-registrations.php');
        exit;
    }

    $regId = filter_input(INPUT_POST, 'registration_id', FILTER_VALIDATE_INT);
    if ($regId) {
        try {
            $db->beginTransaction();

            // Verify registration belongs to current user and is confirmed
            $checkStmt = $db->prepare("
                SELECT r.*, e.id AS event_id, e.title AS event_title, e.event_date 
                FROM registrations r 
                JOIN events e ON r.event_id = e.id 
                WHERE r.id = :id AND r.user_id = :user_id AND r.status = 'confirmed' 
                LIMIT 1
            ");
            $checkStmt->execute([
                'id' => $regId,
                'user_id' => $currentUser['id']
            ]);
            $reg = $checkStmt->fetch();

            if ($reg) {
                // Update registration status to cancelled
                $updStmt = $db->prepare("UPDATE registrations SET status = 'cancelled' WHERE id = :id");
                $updStmt->execute(['id' => $regId]);

                // Increment available seat back
                $seatStmt = $db->prepare("UPDATE events SET available_seats = available_seats + 1 WHERE id = :id");
                $seatStmt->execute(['id' => $reg['event_id']]);

                $db->commit();
                set_flash('success', "Registration for '{$reg['event_title']}' has been cancelled. Seat released.");
            } else {
                $db->rollBack();
                set_flash('danger', 'Registration not found or already cancelled.');
            }
        } catch (Exception $e) {
            $db->rollBack();
            set_flash('danger', 'Cancellation failed: ' . $e->getMessage());
        }
    }
    header('Location: ' . BASE_URL . 'my-registrations.php');
    exit;
}

// Fetch all registrations for current user
$stmt = $db->prepare("
    SELECT r.*, e.title, e.event_date, e.start_time, e.venue, e.banner_image, e.registration_deadline,
           c.name AS category_name, c.color AS category_color 
    FROM registrations r 
    JOIN events e ON r.event_id = e.id 
    JOIN categories c ON e.category_id = c.id 
    WHERE r.user_id = :user_id 
    ORDER BY r.registered_at DESC
");
$stmt->execute(['user_id' => $currentUser['id']]);
$registrations = $stmt->fetchAll();

$pageTitle = 'My Registrations | Campus Events';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="bg-white border-bottom py-4 mb-4">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">My Registrations</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold mb-1">My Event Passes &amp; Registrations</h1>
                <p class="text-muted small mb-0">Manage your active bookings, view digital entry tickets, and track attendance.</p>
            </div>
            <a href="<?= BASE_URL ?>events.php" class="btn btn-primary btn-sm">
                <i class="bi bi-search me-1"></i> Browse More Events
            </a>
        </div>
    </div>
</div>

<div class="container pb-5">
    <?php if (!empty($registrations)): ?>
        <div class="row g-4">
            <?php foreach ($registrations as $reg): 
                $isPast = (strtotime($reg['event_date']) < strtotime(date('Y-m-d')));
            ?>
                <div class="col-lg-6">
                    <div class="card border rounded-4 shadow-sm h-100 overflow-hidden bg-white">
                        <div class="row g-0 h-100">
                            <!-- Card Image -->
                            <div class="col-sm-4 bg-light position-relative">
                                <img src="<?= BASE_URL . htmlspecialchars($reg['banner_image'] ?: 'assets/images/events/default.jpg') ?>" 
                                     class="img-fluid h-100 w-100" 
                                     style="object-fit: cover; min-height: 160px;" 
                                     alt="<?= htmlspecialchars($reg['title']) ?>">
                                <span class="position-absolute top-0 start-0 m-2 badge bg-dark text-white font-monospace small" style="font-size: 0.7rem;">
                                    <?= htmlspecialchars($reg['category_name']) ?>
                                </span>
                            </div>

                            <!-- Card Content -->
                            <div class="col-sm-8 p-3.5 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="font-monospace small text-muted">#<?= htmlspecialchars($reg['registration_code']) ?></span>
                                    <?php if ($reg['status'] === 'confirmed'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Confirmed</span>
                                    <?php elseif ($reg['status'] === 'attended'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Attended</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Cancelled</span>
                                    <?php endif; ?>
                                </div>

                                <h5 class="fw-bold mb-2 fs-6">
                                    <a href="<?= BASE_URL ?>event-details.php?id=<?= $reg['event_id'] ?>" class="text-dark text-decoration-none">
                                        <?= htmlspecialchars($reg['title']) ?>
                                    </a>
                                </h5>

                                <div class="small text-muted mb-3 d-flex flex-column gap-1">
                                    <div><i class="bi bi-calendar3 me-1 text-primary"></i> <?= format_date($reg['event_date']) ?> at <?= format_time($reg['start_time']) ?></div>
                                    <div><i class="bi bi-geo-alt me-1 text-primary"></i> <span class="text-truncate"><?= htmlspecialchars($reg['venue']) ?></span></div>
                                    <div><i class="bi bi-clock-history me-1 text-muted"></i> Booked: <?= format_date($reg['registered_at'], 'M d, Y h:i A') ?></div>
                                </div>

                                <div class="mt-auto pt-2 border-top d-flex gap-2 justify-content-between align-items-center">
                                    <?php if ($reg['status'] === 'confirmed'): ?>
                                        <a href="<?= BASE_URL ?>ticket.php?code=<?= urlencode($reg['registration_code']) ?>" class="btn btn-navy btn-sm px-3">
                                            <i class="bi bi-qr-code me-1"></i> View E-Pass
                                        </a>

                                        <?php if (!$isPast): ?>
                                            <form method="POST" action="my-registrations.php" onsubmit="return confirm('Are you sure you want to cancel this registration? Your seat will be released.');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="registration_id" value="<?= $reg['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    Cancel Pass
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="small text-muted fst-italic">Pass inactive</span>
                                        <a href="<?= BASE_URL ?>event-details.php?id=<?= $reg['event_id'] ?>" class="btn btn-outline-secondary btn-sm">
                                            View Event
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card border text-center py-5 shadow-sm rounded-4 bg-white">
            <div class="card-body py-5">
                <div class="display-3 text-muted mb-3">
                    <i class="bi bi-ticket-detailed"></i>
                </div>
                <h4 class="fw-bold text-dark">No Registrations Found</h4>
                <p class="text-muted max-w-500 mx-auto mb-4">
                    You haven't registered for any college events yet. Explore upcoming hackathons, sports tournaments, and workshops!
                </p>
                <a href="<?= BASE_URL ?>events.php" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-compass me-1"></i> Discover Events
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

