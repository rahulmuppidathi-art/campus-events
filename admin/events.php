<?php
/**
 * CAMPUS EVENTS - Admin Event List & Management (CRUD)
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

$adminTitle = 'Manage Events | Campus Events Admin';
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();
if (!$db) {
    die("Database connection offline.");
}

$search = sanitize($_GET['search'] ?? '');
$categoryFilter = sanitize($_GET['category'] ?? '');
$statusFilter = sanitize($_GET['status'] ?? '');

// Fetch Categories for dropdown
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Dynamic query
$sql = "
    SELECT e.*, c.name AS category_name, c.color AS category_color,
           (e.total_seats - e.available_seats) AS booked_seats 
    FROM events e 
    JOIN categories c ON e.category_id = c.id 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (e.title LIKE :search OR e.venue LIKE :search_venue)";
    $params['search'] = "%{$search}%";
    $params['search_venue'] = "%{$search}%";
}

if (!empty($categoryFilter) && $categoryFilter !== 'all') {
    $sql .= " AND c.slug = :cat_slug";
    $params['cat_slug'] = $categoryFilter;
}

if (!empty($statusFilter) && $statusFilter !== 'all') {
    $sql .= " AND e.status = :status";
    $params['status'] = $statusFilter;
}

$sql .= " ORDER BY e.event_date DESC, e.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$eventsList = $stmt->fetchAll();
?>

<!-- Header & Quick Actions -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Manage College Events</h1>
        <p class="text-muted small mb-0">Create, edit, publish, monitor seats and delete collegiate events.</p>
    </div>
    <a href="<?= BASE_URL ?>admin/event-create.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Create New Event
    </a>
</div>

<!-- Filters Bar -->
<div class="card border rounded-3 p-3 bg-white shadow-sm mb-4">
    <form method="GET" action="events.php" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search event title or venue..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select form-select-sm">
                <option value="all">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($categoryFilter === $cat['slug']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="all">All Statuses</option>
                <option value="published" <?= ($statusFilter === 'published') ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= ($statusFilter === 'draft') ? 'selected' : '' ?>>Draft</option>
                <option value="completed" <?= ($statusFilter === 'completed') ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= ($statusFilter === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-navy btn-sm w-100">Filter</button>
            <a href="events.php" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

<!-- Events Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h5 class="admin-card-title">All Events (<?= count($eventsList) ?>)</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 70px;">Poster</th>
                    <th>Event Title &amp; Category</th>
                    <th>Date &amp; Time</th>
                    <th>Venue</th>
                    <th>Bookings</th>
                    <th>Status</th>
                    <th class="text-end" style="min-width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($eventsList)): ?>
                    <?php foreach ($eventsList as $event): ?>
                        <tr>
                            <td><span class="text-muted small">#<?= $event['id'] ?></span></td>
                            <td>
                                <img src="<?= BASE_URL . htmlspecialchars($event['banner_image'] ?: 'assets/images/events/default.jpg') ?>" 
                                     class="rounded-2 border" 
                                     width="54" height="38" 
                                     style="object-fit: cover;" 
                                     alt="">
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-0.5">
                                    <a href="<?= BASE_URL ?>event-details.php?id=<?= $event['id'] ?>" target="_blank" class="text-dark text-decoration-none">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </a>
                                    <?php if ($event['is_featured']): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis ms-1" style="font-size: 0.65rem;">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.7rem;">
                                    <?= htmlspecialchars($event['category_name']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= format_date($event['event_date']) ?></div>
                                <div class="text-muted small" style="font-size: 0.75rem;"><?= format_time($event['start_time']) ?></div>
                            </td>
                            <td>
                                <span class="small text-truncate d-inline-block" style="max-width: 140px;" title="<?= htmlspecialchars($event['venue']) ?>">
                                    <?= htmlspecialchars($event['venue']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">
                                    <?= $event['booked_seats'] ?> / <?= $event['total_seats'] ?>
                                </div>
                                <div class="progress mt-1" style="height: 4px; width: 80px;">
                                    <?php $p = ($event['total_seats'] > 0) ? round(($event['booked_seats'] / $event['total_seats']) * 100) : 0; ?>
                                    <div class="progress-bar bg-primary" style="width: <?= $p ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <?php if ($event['status'] === 'published'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Published</span>
                                <?php elseif ($event['status'] === 'draft'): ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Draft</span>
                                <?php elseif ($event['status'] === 'completed'): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Completed</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>admin/registrations.php?event_id=<?= $event['id'] ?>" class="btn btn-outline-secondary" title="View Registrations">
                                        <i class="bi bi-people"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>admin/event-edit.php?id=<?= $event['id'] ?>" class="btn btn-outline-primary" title="Edit Event">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="event-delete.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete \'<?= htmlspecialchars(addslashes($event['title'])) ?>\'? All related registrations will also be deleted.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete Event">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted small">
                            No events found matching your filter criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>

