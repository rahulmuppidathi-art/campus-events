<?php
/**
 * CAMPUS EVENTS - Events Directory Page
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

$pageTitle = 'Campus Events | Discover Upcoming University Events';
require_once __DIR__ . '/includes/header.php';

// Retrieve filter and search parameters
$search = sanitize($_GET['search'] ?? '');
$categorySlug = sanitize($_GET['category'] ?? '');
$dateFilter = sanitize($_GET['date_filter'] ?? '');
$sortBy = sanitize($_GET['sort'] ?? 'date_asc');

$events = [];
$categories = [];

$db = getDB();
if ($db) {
    try {
        // Fetch all categories for filter dropdown/pills
        $stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $stmt->fetchAll();

        // Build dynamic SQL query for published events
        $sql = "
            SELECT e.*, c.name AS category_name, c.color AS category_color, c.slug AS category_slug 
            FROM events e 
            JOIN categories c ON e.category_id = c.id 
            WHERE e.status = 'published'
        ";
        $params = [];

        // 1. Search by title or keyword
        if (!empty($search)) {
            $sql .= " AND (e.title LIKE :search OR e.short_description LIKE :search_desc OR e.venue LIKE :search_venue)";
            $params['search'] = "%{$search}%";
            $params['search_desc'] = "%{$search}%";
            $params['search_venue'] = "%{$search}%";
        }

        // 2. Filter by Category slug
        if (!empty($categorySlug) && $categorySlug !== 'all') {
            $sql .= " AND c.slug = :category_slug";
            $params['category_slug'] = $categorySlug;
        }

        // 3. Filter by Date
        $today = date('Y-m-d');
        if ($dateFilter === 'today') {
            $sql .= " AND e.event_date = :filter_date";
            $params['filter_date'] = $today;
        } elseif ($dateFilter === 'upcoming') {
            $sql .= " AND e.event_date >= :filter_date";
            $params['filter_date'] = $today;
        } elseif ($dateFilter === 'month') {
            $monthEnd = date('Y-m-t');
            $sql .= " AND e.event_date BETWEEN :filter_date_start AND :filter_date_end";
            $params['filter_date_start'] = $today;
            $params['filter_date_end'] = $monthEnd;
        } elseif ($dateFilter === 'past') {
            $sql .= " AND e.event_date < :filter_date";
            $params['filter_date'] = $today;
        }

        // 4. Sort order
        switch ($sortBy) {
            case 'date_desc':
                $sql .= " ORDER BY e.event_date DESC, e.start_time DESC";
                break;
            case 'seats_desc':
                $sql .= " ORDER BY e.available_seats DESC";
                break;
            case 'newest':
                $sql .= " ORDER BY e.created_at DESC";
                break;
            case 'date_asc':
            default:
                $sql .= " ORDER BY e.event_date ASC, e.start_time ASC";
                break;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll();

    } catch (PDOException $e) {
        // Fallback gracefully
    }
}
?>

<!-- Page Header Banner -->
<div class="bg-white border-bottom py-4 mb-4">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Events Directory</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold mb-1">Campus Events Directory</h1>
                <p class="text-muted small mb-0">Browse and register for officially sanctioned university events.</p>
            </div>
            <div class="text-muted small">
                Showing <strong class="text-dark"><?= count($events) ?></strong> published events
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <!-- Filter and Search Bar -->
    <div class="filter-bar">
        <form method="GET" action="events.php" class="row g-3 align-items-end">
            <!-- Search Keyword -->
            <div class="col-lg-4 col-md-6">
                <label for="search" class="form-label small fw-semibold text-muted">Search Events</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" id="liveEventSearch" class="form-control border-start-0" placeholder="Title, topic or venue..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="col-lg-3 col-md-6">
                <label for="category" class="form-label small fw-semibold text-muted">Category</label>
                <select name="category" id="category" class="form-select">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($categorySlug === $cat['slug']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date Filter -->
            <div class="col-lg-2 col-md-4">
                <label for="date_filter" class="form-label small fw-semibold text-muted">Date Horizon</label>
                <select name="date_filter" id="date_filter" class="form-select">
                    <option value="" <?= ($dateFilter === '') ? 'selected' : '' ?>>All Dates</option>
                    <option value="upcoming" <?= ($dateFilter === 'upcoming') ? 'selected' : '' ?>>All Upcoming</option>
                    <option value="today" <?= ($dateFilter === 'today') ? 'selected' : '' ?>>Happening Today</option>
                    <option value="month" <?= ($dateFilter === 'month') ? 'selected' : '' ?>>This Month</option>
                    <option value="past" <?= ($dateFilter === 'past') ? 'selected' : '' ?>>Past Events</option>
                </select>
            </div>

            <!-- Sort By -->
            <div class="col-lg-2 col-md-4">
                <label for="sort" class="form-label small fw-semibold text-muted">Sort By</label>
                <select name="sort" id="sort" class="form-select">
                    <option value="date_asc" <?= ($sortBy === 'date_asc') ? 'selected' : '' ?>>Date: Earliest First</option>
                    <option value="date_desc" <?= ($sortBy === 'date_desc') ? 'selected' : '' ?>>Date: Latest First</option>
                    <option value="seats_desc" <?= ($sortBy === 'seats_desc') ? 'selected' : '' ?>>Seats Available</option>
                    <option value="newest" <?= ($sortBy === 'newest') ? 'selected' : '' ?>>Recently Added</option>
                </select>
            </div>

            <!-- Submit & Reset Buttons -->
            <div class="col-lg-1 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="<?= BASE_URL ?>events.php" class="btn btn-outline-secondary" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>

        <!-- Category Quick Badges -->
        <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
            <span class="small fw-semibold text-muted align-self-center me-1">Quick Select:</span>
            <a href="<?= BASE_URL ?>events.php" class="badge rounded-pill <?= empty($categorySlug) || $categorySlug === 'all' ? 'bg-navy text-white' : 'bg-light text-dark border' ?> px-3 py-2 text-decoration-none">
                All
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>events.php?category=<?= urlencode($cat['slug']) ?>" class="badge rounded-pill <?= ($categorySlug === $cat['slug']) ? 'bg-primary text-white' : 'bg-light text-dark border' ?> px-3 py-2 text-decoration-none">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Events Grid -->
    <?php if (!empty($events)): ?>
        <div class="row g-4" id="eventsGrid">
            <?php foreach ($events as $event): 
                $isClosed = (strtotime($event['registration_deadline']) < strtotime(date('Y-m-d'))) || ($event['available_seats'] <= 0);
            ?>
                <div class="col-lg-4 col-md-6 event-search-item" 
                     data-title="<?= strtolower(htmlspecialchars($event['title'])) ?>"
                     data-category="<?= strtolower(htmlspecialchars($event['category_name'])) ?>"
                     data-venue="<?= strtolower(htmlspecialchars($event['venue'])) ?>">
                    <div class="event-card">
                        <!-- Poster Image with Badges -->
                        <div class="event-card-img-wrapper">
                            <img src="<?= BASE_URL . htmlspecialchars($event['banner_image'] ?: 'assets/images/events/default.jpg') ?>" 
                                 class="event-card-img" 
                                 alt="<?= htmlspecialchars($event['title']) ?>"
                                 loading="lazy">
                            <span class="event-category-badge">
                                <?= htmlspecialchars($event['category_name']) ?>
                            </span>
                            <?php if ($event['available_seats'] <= 0): ?>
                                <span class="event-status-badge badge bg-danger">House Full</span>
                            <?php elseif ($isClosed): ?>
                                <span class="event-status-badge badge bg-secondary">Closed</span>
                            <?php elseif ($event['is_featured']): ?>
                                <span class="event-status-badge badge bg-primary">Featured</span>
                            <?php endif; ?>
                        </div>

                        <!-- Card Body -->
                        <div class="event-card-body">
                            <h5 class="event-card-title">
                                <a href="<?= BASE_URL ?>event-details.php?id=<?= $event['id'] ?>">
                                    <?= htmlspecialchars($event['title']) ?>
                                </a>
                            </h5>
                            
                            <p class="event-card-description">
                                <?= htmlspecialchars($event['short_description']) ?>
                            </p>

                            <ul class="event-meta-list">
                                <li>
                                    <i class="bi bi-calendar3"></i>
                                    <span><strong><?= format_date($event['event_date']) ?></strong></span>
                                </li>
                                <li>
                                    <i class="bi bi-clock"></i>
                                    <span><?= format_time($event['start_time']) ?> <?= !empty($event['end_time']) ? '– ' . format_time($event['end_time']) : '' ?></span>
                                </li>
                                <li>
                                    <i class="bi bi-geo-alt"></i>
                                    <span class="text-truncate"><?= htmlspecialchars($event['venue']) ?></span>
                                </li>
                                <li>
                                    <i class="bi bi-hourglass-split"></i>
                                    <span class="text-muted">Deadline: <?= format_date($event['registration_deadline']) ?></span>
                                </li>
                            </ul>

                            <div class="event-card-footer">
                                <div>
                                    <span class="seats-badge <?= ($event['available_seats'] < 10) ? 'text-danger fw-bold' : '' ?>">
                                        <i class="bi bi-person-check me-1"></i> <?= $event['available_seats'] ?> / <?= $event['total_seats'] ?> seats
                                    </span>
                                </div>
                                <a href="<?= BASE_URL ?>event-details.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-navy">
                                    View Details <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State Message -->
        <div class="card border text-center py-5 shadow-sm rounded-4">
            <div class="card-body py-5">
                <div class="display-3 text-muted mb-3">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h4 class="fw-bold text-dark">No Events Found</h4>
                <p class="text-muted max-w-500 mx-auto mb-4">
                    There are no published events matching your selected criteria. Try adjusting your search query, choosing a different category, or resetting all filters.
                </p>
                <a href="<?= BASE_URL ?>events.php" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Clear All Filters
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

