<?php
/**
 * CAMPUS EVENTS - Event Details & Registration Handler
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$eventId) {
    header('Location: ' . BASE_URL . 'events.php');
    exit;
}

$db = getDB();
if (!$db) {
    die("Database connection failed. Please ensure MySQL is running in XAMPP.");
}

// Fetch Event Details
$stmt = $db->prepare("
    SELECT e.*, c.name AS category_name, c.color AS category_color, c.slug AS category_slug 
    FROM events e 
    JOIN categories c ON e.category_id = c.id 
    WHERE e.id = :id AND e.status = 'published'
    LIMIT 1
");
$stmt->execute(['id' => $eventId]);
$event = $stmt->fetch();

if (!$event) {
    set_flash('danger', 'The requested event does not exist or has been archived.');
    header('Location: ' . BASE_URL . 'events.php');
    exit;
}

$currentUser = current_user();
$isRegistered = false;
$registrationDetails = null;

// Check if current user is already registered for this event
if ($currentUser) {
    $checkStmt = $db->prepare("SELECT * FROM registrations WHERE event_id = :event_id AND user_id = :user_id LIMIT 1");
    $checkStmt->execute([
        'event_id' => $eventId,
        'user_id' => $currentUser['id']
    ]);
    $registrationDetails = $checkStmt->fetch();
    if ($registrationDetails && $registrationDetails['status'] === 'confirmed') {
        $isRegistered = true;
    }
}

// Handle Event Registration Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    require_login(BASE_URL . 'event-details.php?id=' . $eventId);

    if (!csrf_validate()) {
        set_flash('danger', 'Security validation token mismatch. Please try again.');
        header('Location: ' . BASE_URL . 'event-details.php?id=' . $eventId);
        exit;
    }

    $userId = $currentUser['id'];
    $notes = sanitize($_POST['notes'] ?? '');

    // Check deadlines and seat capacity
    $today = date('Y-m-d');
    if ($event['registration_deadline'] < $today) {
        set_flash('danger', 'Registration for this event closed on ' . format_date($event['registration_deadline']) . '.');
        header('Location: ' . BASE_URL . 'event-details.php?id=' . $eventId);
        exit;
    }

    if ($event['available_seats'] <= 0) {
        set_flash('danger', 'Sorry, all available seats for this event are fully booked.');
        header('Location: ' . BASE_URL . 'event-details.php?id=' . $eventId);
        exit;
    }

    // Check existing registration
    if ($isRegistered) {
        set_flash('info', 'You are already registered for this event! You can view your pass below.');
        header('Location: ' . BASE_URL . 'ticket.php?code=' . urlencode($registrationDetails['registration_code']));
        exit;
    }

    try {
        // Begin transaction
        $db->beginTransaction();

        $regCode = generate_registration_code('REG');

        // Check if registration was previously cancelled, if so update it, otherwise insert new
        if ($registrationDetails && $registrationDetails['status'] === 'cancelled') {
            $regStmt = $db->prepare("
                UPDATE registrations 
                SET registration_code = :code, status = 'confirmed', registered_at = NOW(), notes = :notes 
                WHERE id = :id
            ");
            $regStmt->execute([
                'code' => $regCode,
                'notes' => $notes,
                'id' => $registrationDetails['id']
            ]);
        } else {
            $regStmt = $db->prepare("
                INSERT INTO registrations (event_id, user_id, registration_code, status, notes) 
                VALUES (:event_id, :user_id, :code, 'confirmed', :notes)
            ");
            $regStmt->execute([
                'event_id' => $eventId,
                'user_id' => $userId,
                'code' => $regCode,
                'notes' => $notes
            ]);
        }

        // Decrement available seats safely
        $seatStmt = $db->prepare("UPDATE events SET available_seats = available_seats - 1 WHERE id = :id AND available_seats > 0");
        $seatStmt->execute(['id' => $eventId]);

        $db->commit();

        set_flash('success', "Congratulations! You have successfully registered for '{$event['title']}'. Your Digital E-Pass is ready.");
        header('Location: ' . BASE_URL . 'ticket.php?code=' . urlencode($regCode));
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        set_flash('danger', 'Registration could not be completed: ' . $e->getMessage());
        header('Location: ' . BASE_URL . 'event-details.php?id=' . $eventId);
        exit;
    }
}

// Fetch Related Events in same category
$relStmt = $db->prepare("
    SELECT id, title, event_date, venue, banner_image 
    FROM events 
    WHERE category_id = :cat_id AND id != :id AND status = 'published' 
    LIMIT 3
");
$relStmt->execute([
    'cat_id' => $event['category_id'],
    'id' => $eventId
]);
$relatedEvents = $relStmt->fetchAll();

$pageTitle = $event['title'] . ' | Campus Events';
require_once __DIR__ . '/includes/header.php';

$deadlinePassed = (strtotime($event['registration_deadline']) < strtotime(date('Y-m-d')));
$isSoldOut = ($event['available_seats'] <= 0);
?>

<!-- Event Header Banner -->
<div class="event-header-banner">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2 text-white-50 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>events.php" class="text-white-50">Events</a></li>
                <li class="breadcrumb-item text-white active" aria-current="page"><?= htmlspecialchars($event['category_name']) ?></li>
            </ol>
        </nav>
        
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="badge bg-primary text-white px-3 py-1.5 fw-semibold font-monospace">
                <?= htmlspecialchars($event['category_name']) ?>
            </span>
            <?php if ($isRegistered): ?>
                <span class="badge bg-success px-3 py-1.5"><i class="bi bi-check-circle-fill me-1"></i> Registered</span>
            <?php elseif ($isSoldOut): ?>
                <span class="badge bg-danger px-3 py-1.5"><i class="bi bi-x-circle me-1"></i> Fully Booked</span>
            <?php elseif ($deadlinePassed): ?>
                <span class="badge bg-secondary px-3 py-1.5">Registration Closed</span>
            <?php else: ?>
                <span class="badge bg-success-subtle text-success border px-3 py-1.5">Open for Registration</span>
            <?php endif; ?>
        </div>

        <h1 class="display-6 fw-bold mb-3"><?= htmlspecialchars($event['title']) ?></h1>
        
        <div class="d-flex flex-wrap align-items-center gap-4 text-white-50 small">
            <div><i class="bi bi-calendar3 me-1.5 text-primary"></i> <?= format_date($event['event_date'], 'l, F d, Y') ?></div>
            <div><i class="bi bi-clock me-1.5 text-primary"></i> <?= format_time($event['start_time']) ?> <?= !empty($event['end_time']) ? '– ' . format_time($event['end_time']) : '' ?></div>
            <div><i class="bi bi-geo-alt me-1.5 text-primary"></i> <?= htmlspecialchars($event['venue']) ?></div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <!-- Left / Main Column: Details, Poster, Content -->
        <div class="col-lg-8">
            <!-- Event Banner Image -->
            <div class="card border rounded-4 overflow-hidden mb-4 shadow-sm">
                <img src="<?= BASE_URL . htmlspecialchars($event['banner_image'] ?: 'assets/images/events/default.jpg') ?>" 
                     class="img-fluid w-100" 
                     alt="<?= htmlspecialchars($event['title']) ?>"
                     style="max-height: 420px; object-fit: cover;">
            </div>

            <!-- About The Event -->
            <div class="card border rounded-4 p-4 mb-4 shadow-sm bg-white">
                <h3 class="h4 fw-bold mb-3">About This Event</h3>
                <div class="lead fs-6 text-muted mb-4">
                    <?= htmlspecialchars($event['short_description']) ?>
                </div>

                <hr class="my-4">

                <h4 class="h5 fw-bold mb-3">Full Details &amp; Schedule</h4>
                <div class="event-full-description" style="white-space: pre-line; line-height: 1.8; color: #334155;">
                    <?= htmlspecialchars($event['description']) ?>
                </div>

                <div class="bg-light p-3.5 rounded-3 mt-4 border">
                    <h5 class="fs-6 fw-bold mb-2"><i class="bi bi-shield-exclamation text-warning me-2"></i>Event Guidelines &amp; Rules</h5>
                    <ul class="mb-0 small text-muted ps-3">
                        <li>All attendees must present a valid college ID card along with their digital registration pass.</li>
                        <li>Please arrive at the venue at least 15 minutes prior to the scheduled start time.</li>
                        <li>Recording or streaming without organizer consent is strictly prohibited.</li>
                    </ul>
                </div>
            </div>

            <!-- Coordinator Information -->
            <div class="card border rounded-4 p-4 shadow-sm bg-white mb-4">
                <h4 class="h5 fw-bold mb-3"><i class="bi bi-person-lines-fill text-primary me-2"></i>Event Faculty &amp; Student Coordinator</h4>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="text-muted small">Lead Coordinator</div>
                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($event['coordinator_name']) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="text-muted small">Official Email</div>
                            <div class="fw-bold text-dark fs-6">
                                <a href="mailto:<?= htmlspecialchars($event['coordinator_email']) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($event['coordinator_email']) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Registration Sidebar -->
        <div class="col-lg-4">
            <div class="event-details-sidebar">
                <h4 class="h5 fw-bold mb-3 text-dark">Event Registration</h4>

                <!-- Price & Seat Information -->
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <div>
                        <div class="text-muted small">Fee / Price</div>
                        <div class="h4 fw-bold text-primary mb-0">
                            <?= ($event['price'] > 0) ? '$' . number_format($event['price'], 2) : 'Free Admission' ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Seats Left</div>
                        <div class="h5 fw-bold text-dark mb-0">
                            <?= $event['available_seats'] ?> <span class="fs-6 text-muted fw-normal">/ <?= $event['total_seats'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- Seat Capacity Progress Bar -->
                <?php 
                $bookedSeats = $event['total_seats'] - $event['available_seats'];
                $percentage = ($event['total_seats'] > 0) ? round(($bookedSeats / $event['total_seats']) * 100) : 0;
                ?>
                <div class="mb-4">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Capacity Booked</span>
                        <span><?= $percentage ?>%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentage ?>%" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <!-- Registration Actions -->
                <?php if ($isRegistered): ?>
                    <div class="alert alert-success d-flex align-items-center mb-4">
                        <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                        <div>
                            <strong>You're Registered!</strong>
                            <div class="small">Pass Code: <code><?= htmlspecialchars($registrationDetails['registration_code']) ?></code></div>
                        </div>
                    </div>

                    <a href="<?= BASE_URL ?>ticket.php?code=<?= urlencode($registrationDetails['registration_code']) ?>" class="btn btn-navy w-100 py-2.5 mb-2 fw-semibold">
                        <i class="bi bi-qr-code me-2"></i> View &amp; Print E-Pass
                    </a>
                    <a href="<?= BASE_URL ?>my-registrations.php" class="btn btn-outline-secondary w-100 py-2 btn-sm">
                        Manage My Registrations
                    </a>

                <?php elseif ($isSoldOut): ?>
                    <div class="alert alert-danger mb-4">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>
                        <strong>Event Full!</strong> All seats for this event are currently booked.
                    </div>
                    <button class="btn btn-secondary w-100 py-2.5 disabled" disabled>Sold Out</button>

                <?php elseif ($deadlinePassed): ?>
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-calendar-x-fill me-2"></i>
                        <strong>Deadline Passed!</strong> Registrations closed on <?= format_date($event['registration_deadline']) ?>.
                    </div>
                    <button class="btn btn-secondary w-100 py-2.5 disabled" disabled>Registration Closed</button>

                <?php elseif (!is_logged_in()): ?>
                    <div class="bg-light p-3 rounded mb-4 text-center">
                        <i class="bi bi-lock-fill text-muted fs-3 mb-2 d-block"></i>
                        <p class="small text-muted mb-3">Sign in with your university student account to register for this event.</p>
                        <a href="<?= BASE_URL ?>login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary w-100 py-2 fw-semibold mb-2">
                            Sign In to Register
                        </a>
                        <a href="<?= BASE_URL ?>register.php" class="small text-decoration-none">New student? Create account</a>
                    </div>

                <?php else: ?>
                    <!-- Registration Form for Logged In Students -->
                    <form method="POST" action="event-details.php?id=<?= $event['id'] ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="register">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Registering as</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($currentUser['name']) ?> (<?= htmlspecialchars($currentUser['roll_no'] ?: 'Student') ?>)" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label small fw-semibold text-muted">Dietary or Special Requests (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control form-control-sm" rows="2" placeholder="e.g., Team name, dietary preferences..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold shadow-sm">
                            <i class="bi bi-check2-circle me-1.5"></i> Confirm Registration
                        </button>
                    </form>
                <?php endif; ?>

                <!-- Event Snapshot Info -->
                <div class="mt-4 pt-3 border-top text-muted small">
                    <div class="d-flex justify-content-between py-1.5">
                        <span>Registration Deadline:</span>
                        <strong class="text-dark"><?= format_date($event['registration_deadline']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between py-1.5">
                        <span>Audience / Level:</span>
                        <strong class="text-dark">All Departments</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1.5">
                        <span>E-Certificate:</span>
                        <strong class="text-success">Provided</strong>
                    </div>
                </div>
            </div>

            <!-- Related Events Sidebar Widget -->
            <?php if (!empty($relatedEvents)): ?>
                <div class="card border rounded-4 p-4 mt-4 shadow-sm bg-white">
                    <h5 class="fw-bold fs-6 mb-3">More <?= htmlspecialchars($event['category_name']) ?></h5>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($relatedEvents as $rel): ?>
                            <a href="<?= BASE_URL ?>event-details.php?id=<?= $rel['id'] ?>" class="text-decoration-none text-dark d-flex align-items-center gap-3 group">
                                <img src="<?= BASE_URL . htmlspecialchars($rel['banner_image'] ?: 'assets/images/events/default.jpg') ?>" class="rounded-2" width="60" height="45" style="object-fit: cover;" alt="">
                                <div class="overflow-hidden">
                                    <h6 class="small fw-semibold mb-0 text-truncate"><?= htmlspecialchars($rel['title']) ?></h6>
                                    <span class="text-muted" style="font-size: 0.75rem;"><?= format_date($rel['event_date']) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

