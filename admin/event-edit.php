<?php
/**
 * CAMPUS EVENTS - Admin Edit Event
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

$adminTitle = 'Edit Event | Campus Events Admin';
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();
if (!$db) {
    die("Database connection offline.");
}

$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$eventId) {
    header('Location: ' . BASE_URL . 'admin/events.php');
    exit;
}

// Fetch event
$stmt = $db->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $eventId]);
$event = $stmt->fetch();

if (!$event) {
    set_flash('danger', 'Event not found.');
    header('Location: ' . BASE_URL . 'admin/events.php');
    exit;
}

// Fetch categories
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $errors[] = 'Security token validation failed.';
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $shortDesc = sanitize($_POST['short_description'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $eventDate = sanitize($_POST['event_date'] ?? '');
        $startTime = sanitize($_POST['start_time'] ?? '');
        $endTime = sanitize($_POST['end_time'] ?? '');
        $venue = sanitize($_POST['venue'] ?? '');
        $totalSeats = filter_input(INPUT_POST, 'total_seats', FILTER_VALIDATE_INT);
        $deadline = sanitize($_POST['registration_deadline'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT) ?: 0.00;
        $status = sanitize($_POST['status'] ?? 'published');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $coordinatorName = sanitize($_POST['coordinator_name'] ?? '');
        $coordinatorEmail = sanitize($_POST['coordinator_email'] ?? '');
        $coordinatorPhone = sanitize($_POST['coordinator_phone'] ?? '');

        // Validation
        if (empty($title)) $errors[] = 'Event title is required.';
        if (!$categoryId) $errors[] = 'Please select a valid event category.';
        if (empty($shortDesc)) $errors[] = 'Short summary description is required.';
        if (empty($desc)) $errors[] = 'Full event details are required.';
        if (empty($eventDate)) $errors[] = 'Event date is required.';
        if (empty($startTime)) $errors[] = 'Start time is required.';
        if (empty($venue)) $errors[] = 'Event venue is required.';
        if (!$totalSeats || $totalSeats < 1) $errors[] = 'Total seats must be at least 1.';
        if (empty($deadline)) $errors[] = 'Registration deadline date is required.';

        // Handle Image
        $bannerImagePath = $event['banner_image'];
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $targetDir = UPLOADS_PATH . DIRECTORY_SEPARATOR . 'events';
                $uploaded = handle_image_upload($_FILES['banner_image'], $targetDir);
                if ($uploaded) {
                    $bannerImagePath = 'assets/uploads/events/' . $uploaded;
                }
            } catch (Exception $e) {
                $errors[] = 'Image upload error: ' . $e->getMessage();
            }
        } elseif (!empty($_POST['preset_banner'])) {
            $bannerImagePath = sanitize($_POST['preset_banner']);
        }

        if (empty($errors)) {
            try {
                // Adjust available seats proportionally if total seats was modified
                $seatsDifference = $totalSeats - $event['total_seats'];
                $newAvailableSeats = max(0, $event['available_seats'] + $seatsDifference);

                $updateStmt = $db->prepare("
                    UPDATE events SET 
                        category_id = :category_id,
                        title = :title,
                        short_description = :short_description,
                        description = :description,
                        banner_image = :banner_image,
                        event_date = :event_date,
                        start_time = :start_time,
                        end_time = :end_time,
                        venue = :venue,
                        registration_deadline = :registration_deadline,
                        total_seats = :total_seats,
                        available_seats = :available_seats,
                        price = :price,
                        status = :status,
                        is_featured = :is_featured,
                        coordinator_name = :coordinator_name,
                        coordinator_email = :coordinator_email,
                        coordinator_phone = :coordinator_phone
                    WHERE id = :id
                ");

                $updateStmt->execute([
                    'category_id' => $categoryId,
                    'title' => $title,
                    'short_description' => $shortDesc,
                    'description' => $desc,
                    'banner_image' => $bannerImagePath,
                    'event_date' => $eventDate,
                    'start_time' => $startTime,
                    'end_time' => !empty($endTime) ? $endTime : null,
                    'venue' => $venue,
                    'registration_deadline' => $deadline,
                    'total_seats' => $totalSeats,
                    'available_seats' => $newAvailableSeats,
                    'price' => $price,
                    'status' => $status,
                    'is_featured' => $isFeatured,
                    'coordinator_name' => $coordinatorName,
                    'coordinator_email' => $coordinatorEmail,
                    'coordinator_phone' => $coordinatorPhone,
                    'id' => $eventId
                ]);

                set_flash('success', "Event '{$title}' updated successfully!");
                header('Location: ' . BASE_URL . 'admin/events.php');
                exit;

            } catch (PDOException $e) {
                $errors[] = 'Database update error: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/events.php">Events</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit #<?= $event['id'] ?></li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold text-dark mb-1">Edit Event #<?= $event['id'] ?></h1>
        <p class="text-muted small mb-0">Update event details, timing, seat caps, and status.</p>
    </div>
    <a href="<?= BASE_URL ?>admin/events.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Events
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger small mb-4">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="event-edit.php?id=<?= $event['id'] ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- Main Form (Left 8 Cols) -->
        <div class="col-lg-8">
            <div class="card border rounded-4 shadow-sm p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-card-heading text-primary me-2"></i>Event Information</h5>

                <div class="mb-3">
                    <label for="title" class="form-label small fw-semibold text-muted">Event Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($event['title']) ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="category_id" class="form-label small fw-semibold text-muted">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($event['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label small fw-semibold text-muted">Publish Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="published" <?= ($event['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                            <option value="draft" <?= ($event['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                            <option value="completed" <?= ($event['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= ($event['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="short_description" class="form-label small fw-semibold text-muted">Short Summary (Max 300 chars) <span class="text-danger">*</span></label>
                    <textarea name="short_description" id="short_description" rows="2" class="form-control" maxlength="300" required><?= htmlspecialchars($event['short_description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label small fw-semibold text-muted">Full Event Description <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" rows="7" class="form-control" required><?= htmlspecialchars($event['description']) ?></textarea>
                </div>

                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" <?= $event['is_featured'] ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-semibold" for="is_featured">
                        Featured Highlight on Homepage
                    </label>
                </div>
            </div>

            <!-- Coordinator Details -->
            <div class="card border rounded-4 shadow-sm p-4 bg-white">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-person-lines-fill text-primary me-2"></i>Event Coordinator Information</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Name <span class="text-danger">*</span></label>
                        <input type="text" name="coordinator_name" class="form-control" value="<?= htmlspecialchars($event['coordinator_name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Email <span class="text-danger">*</span></label>
                        <input type="email" name="coordinator_email" class="form-control" value="<?= htmlspecialchars($event['coordinator_email']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Phone <span class="text-danger">*</span></label>
                        <input type="tel" name="coordinator_phone" class="form-control" value="<?= htmlspecialchars($event['coordinator_phone']) ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Options (Right 4 Cols) -->
        <div class="col-lg-4">
            <div class="card border rounded-4 shadow-sm p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Schedule &amp; Venue</h5>

                <div class="mb-3">
                    <label for="event_date" class="form-label small fw-semibold text-muted">Event Date <span class="text-danger">*</span></label>
                    <input type="date" name="event_date" id="event_date" class="form-control" value="<?= htmlspecialchars($event['event_date']) ?>" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="start_time" class="form-label small fw-semibold text-muted">Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="start_time" class="form-control" value="<?= htmlspecialchars($event['start_time']) ?>" required>
                    </div>
                    <div class="col-6">
                        <label for="end_time" class="form-label small fw-semibold text-muted">End Time</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" value="<?= htmlspecialchars($event['end_time'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="venue" class="form-label small fw-semibold text-muted">Venue <span class="text-danger">*</span></label>
                    <input type="text" name="venue" id="venue" class="form-control" value="<?= htmlspecialchars($event['venue']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="registration_deadline" class="form-label small fw-semibold text-muted">Registration Deadline <span class="text-danger">*</span></label>
                    <input type="date" name="registration_deadline" id="registration_deadline" class="form-control" value="<?= htmlspecialchars($event['registration_deadline']) ?>" required>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label for="total_seats" class="form-label small fw-semibold text-muted">Total Seats</label>
                        <input type="number" name="total_seats" id="total_seats" min="1" class="form-control" value="<?= (int) $event['total_seats'] ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold text-muted">Available Now</label>
                        <input type="text" class="form-control bg-light" value="<?= (int) $event['available_seats'] ?>" readonly>
                    </div>
                </div>
            </div>

            <!-- Banner Card -->
            <div class="card border rounded-4 shadow-sm p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-image text-primary me-2"></i>Event Banner</h5>
                <div class="mb-3 text-center">
                    <img id="banner_preview" src="<?= BASE_URL . htmlspecialchars($event['banner_image'] ?: 'assets/images/events/default.jpg') ?>" class="img-fluid rounded border mb-2" style="max-height: 140px; width: 100%; object-fit: cover;">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Replace Image</label>
                    <input type="file" name="banner_image" id="event_banner" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold shadow-sm">
                <i class="bi bi-save me-1"></i> Update Event Changes
            </button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>

