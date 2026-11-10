<?php
/**
 * CAMPUS EVENTS - Admin Dashboard Main
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

$adminTitle = 'Admin Command Center | Campus Events';
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();
if (!$db) {
    die("Database connection offline. Please check MySQL in XAMPP.");
}

// 1. Fetch KPI Metrics
$totalEvents = 0;
$publishedEvents = 0;
$totalRegistrations = 0;
$totalStudents = 0;
$unreadMessages = 0;
$totalSeatsOffered = 0;
$totalSeatsBooked = 0;

try {
    $totalEvents = (int) $db->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $publishedEvents = (int) $db->query("SELECT COUNT(*) FROM events WHERE status = 'published'")->fetchColumn();
    $totalRegistrations = (int) $db->query("SELECT COUNT(*) FROM registrations WHERE status = 'confirmed' OR status = 'attended'")->fetchColumn();
    $totalStudents = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $unreadMessages = (int) $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();

    // Capacity metrics
    $seatsData = $db->query("SELECT SUM(total_seats) AS total_cap, SUM(available_seats) AS avail_cap FROM events WHERE status = 'published'")->fetch();
    $totalSeatsOffered = (int) ($seatsData['total_cap'] ?? 0);
    $availSeats = (int) ($seatsData['avail_cap'] ?? 0);
    $totalSeatsBooked = max(0, $totalSeatsOffered - $availSeats);
} catch (PDOException $e) {
    // Graceful fallback
}

$utilizationRate = ($totalSeatsOffered > 0) ? round(($totalSeatsBooked / $totalSeatsOffered) * 100) : 0;

// 2. Fetch Recent Registrations
$recentRegistrations = [];
try {
    $stmt = $db->query("
        SELECT r.*, u.name AS student_name, u.email AS student_email, u.roll_no, 
               e.title AS event_title, e.event_date 
        FROM registrations r 
        JOIN users u ON r.user_id = u.id 
        JOIN events e ON r.event_id = e.id 
        ORDER BY r.registered_at DESC 
        LIMIT 6
    ");
    $recentRegistrations = $stmt->fetchAll();
} catch (PDOException $e) {}

// 3. Fetch Upcoming Events for quick status
$upcomingEvents = [];
try {
    $stmt = $db->query("
        SELECT e.*, c.name AS category_name 
        FROM events e 
        JOIN categories c ON e.category_id = c.id 
        WHERE e.status = 'published' AND e.event_date >= CURRENT_DATE() 
        ORDER BY e.event_date ASC 
        LIMIT 4
    ");
    $upcomingEvents = $stmt->fetchAll();
} catch (PDOException $e) {}
?>

<!-- Dashboard Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Administrator Dashboard</h1>
        <p class="text-muted small mb-0">Overview of collegiate events, student participation, and live registrations.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>admin/event-create.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Add New Event
        </a>
        <a href="<?= BASE_URL ?>admin/registrations.php" class="btn btn-outline-navy btn-sm">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> View Registrations
        </a>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Total Events</div>
                <div class="kpi-value"><?= number_format($totalEvents) ?></div>
                <div class="small text-muted mt-1">
                    <span class="text-success fw-bold"><?= $publishedEvents ?></span> published active
                </div>
            </div>
            <div class="kpi-icon bg-primary-subtle text-primary">
                <i class="bi bi-calendar4-event"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Active Passes</div>
                <div class="kpi-value"><?= number_format($totalRegistrations) ?></div>
                <div class="small text-muted mt-1">
                    Confirmed student bookings
                </div>
            </div>
            <div class="kpi-icon bg-success-subtle text-success">
                <i class="bi bi-ticket-perforated"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Registered Students</div>
                <div class="kpi-value"><?= number_format($totalStudents) ?></div>
                <div class="small text-muted mt-1">
                    University student accounts
                </div>
            </div>
            <div class="kpi-icon bg-info-subtle text-info">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="kpi-card">
            <div>
                <div class="kpi-title">Seat Occupancy</div>
                <div class="kpi-value"><?= $utilizationRate ?>%</div>
                <div class="small text-muted mt-1">
                    <?= number_format($totalSeatsBooked) ?> / <?= number_format($totalSeatsOffered) ?> filled
                </div>
            </div>
            <div class="kpi-icon bg-warning-subtle text-warning">
                <i class="bi bi-pie-chart-fill"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Registrations Feed (Left 7 Cols) -->
    <div class="col-lg-7">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Student Registrations</h5>
                <a href="<?= BASE_URL ?>admin/registrations.php" class="small fw-semibold text-decoration-none">
                    View All (<?= $totalRegistrations ?>) &rarr;
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Student &amp; Roll No</th>
                            <th>Event Registered</th>
                            <th>Code</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentRegistrations)): ?>
                            <?php foreach ($recentRegistrations as $reg): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($reg['student_name']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($reg['roll_no'] ?: $reg['student_email']) ?></div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 180px;">
                                            <?= htmlspecialchars($reg['event_title']) ?>
                                        </div>
                                        <div class="small text-muted"><?= format_date($reg['event_date']) ?></div>
                                    </td>
                                    <td>
                                        <code class="small"><?= htmlspecialchars($reg['registration_code']) ?></code>
                                    </td>
                                    <td>
                                        <?php if ($reg['status'] === 'confirmed'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Confirmed</span>
                                        <?php elseif ($reg['status'] === 'attended'): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Attended</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">No registrations recorded yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upcoming Events & Quick System Status (Right 5 Cols) -->
    <div class="col-lg-5">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-calendar-check me-2 text-primary"></i>Upcoming Events Status</h5>
                <a href="<?= BASE_URL ?>admin/events.php" class="small fw-semibold text-decoration-none">Manage &rarr;</a>
            </div>
            <div class="p-3">
                <?php if (!empty($upcomingEvents)): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($upcomingEvents as $evt): 
                            $booked = $evt['total_seats'] - $evt['available_seats'];
                            $pct = ($evt['total_seats'] > 0) ? round(($booked / $evt['total_seats']) * 100) : 0;
                        ?>
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="fw-bold mb-0 text-dark small text-truncate" style="max-width: 220px;">
                                        <?= htmlspecialchars($evt['title']) ?>
                                    </h6>
                                    <span class="badge bg-primary-subtle text-primary font-monospace" style="font-size: 0.7rem;">
                                        <?= htmlspecialchars($evt['category_name']) ?>
                                    </span>
                                </div>
                                <div class="small text-muted mb-2">
                                    <i class="bi bi-calendar3 me-1"></i> <?= format_date($evt['event_date']) ?> • <?= htmlspecialchars($evt['venue']) ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                                    <span><?= $booked ?> / <?= $evt['total_seats'] ?> Booked</span>
                                    <span><?= $evt['available_seats'] ?> Available</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar <?= ($pct >= 90) ? 'bg-danger' : 'bg-primary' ?>" style="width: <?= $pct ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted small text-center my-3">No upcoming events scheduled.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick System Status Card -->
        <div class="card border rounded-3 p-3 bg-white shadow-sm">
            <h6 class="fw-bold text-dark small mb-3"><i class="bi bi-info-circle me-1 text-primary"></i>System &amp; Database Information</h6>
            <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-1.5">
                <li class="d-flex justify-content-between">
                    <span>Database:</span>
                    <strong class="text-dark font-monospace"><?= DB_NAME ?></strong>
                </li>
                <li class="d-flex justify-content-between">
                    <span>PHP Version:</span>
                    <strong class="text-dark"><?= phpversion() ?></strong>
                </li>
                <li class="d-flex justify-content-between">
                    <span>Server Software:</span>
                    <span class="text-dark"><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') ?></span>
                </li>
                <li class="d-flex justify-content-between">
                    <span>Unread Inquiries:</span>
                    <strong class="<?= ($unreadMessages > 0) ? 'text-danger' : 'text-success' ?>">
                        <a href="<?= BASE_URL ?>admin/messages.php" class="text-decoration-none"><?= $unreadMessages ?> unread</a>
                    </strong>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>

