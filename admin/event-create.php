<?php
/**
 * CAMPUS EVENTS - Admin Create New Event
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

$adminTitle = 'Create New Event | Campus Events Admin';
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();
if (!$db) {
    die("Database connection offline.");
}

// Fetch categories
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$errors = [];

// Form values
$title = '';
$categoryId = '';
$shortDesc = '';
$desc = '';
$eventDate = '';
$startTime = '';
$endTime = '';
$venue = '';
$totalSeats = 100;
$deadline = '';
$price = '0.00';
$status = 'published';
$isFeatured = 0;
$coordinatorName = $currentUser['name'];
$coordinatorEmail = $currentUser['email'];
$coordinatorPhone = '+1 (555) 019-2831';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $errors[] = 'Security token validation failed. Please try again.';
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
        if (empty($coordinatorName)) $errors[] = 'Coordinator name is required.';

        // Image Upload
        $bannerImagePath = 'assets/images/events/default.jpg';
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $targetDir = UPLOADS_PATH . DIRECTORY_SEPARATOR . 'events';
                $uploadedFileName = handle_image_upload($_FILES['banner_image'], $targetDir);
                if ($uploadedFileName) {
                    $bannerImagePath = 'assets/uploads/events/' . $uploadedFileName;
                }
            } catch (Exception $e) {
                $errors[] = 'Image upload error: ' . $e->getMessage();
            }
        } elseif (!empty($_POST['preset_banner'])) {
            $bannerImagePath = sanitize($_POST['preset_banner']);
        }

        if (empty($errors)) {
            try {
                // Generate unique slug
                $baseSlug = slugify($title);
                $slug = $baseSlug;
                $counter = 1;
                while (true) {
                    $checkSlug = $db->prepare("SELECT id FROM events WHERE slug = :slug LIMIT 1");
                    $checkSlug->execute(['slug' => $slug]);
                    if (!$checkSlug->fetch()) break;
                    $slug = $baseSlug . '-' . (++$counter);
                }

                $stmt = $db->prepare("
                    INSERT INTO events (
                        category_id, title, slug, short_description, description, banner_image, 
                        event_date, start_time, end_time, venue, registration_deadline, 
                        total_seats, available_seats, price, status, is_featured, 
                        coordinator_name, coordinator_email, coordinator_phone, created_by
                    ) VALUES (
                        :category_id, :title, :slug, :short_description, :description, :banner_image, 
                        :event_date, :start_time, :end_time, :venue, :registration_deadline, 
                        :total_seats, :available_seats, :price, :status, :is_featured, 
                        :coordinator_name, :coordinator_email, :coordinator_phone, :created_by
                    )
                ");

                $stmt->execute([
                    'category_id' => $categoryId,
                    'title' => $title,
                    'slug' => $slug,
                    'short_description' => $shortDesc,
                    'description' => $desc,
                    'banner_image' => $bannerImagePath,
                    'event_date' => $eventDate,
                    'start_time' => $startTime,
                    'end_time' => !empty($endTime) ? $endTime : null,
                    'venue' => $venue,
                    'registration_deadline' => $deadline,
                    'total_seats' => $totalSeats,
                    'available_seats' => $totalSeats,
                    'price' => $price,
                    'status' => $status,
                    'is_featured' => $isFeatured,
                    'coordinator_name' => $coordinatorName,
                    'coordinator_email' => $coordinatorEmail,
                    'coordinator_phone' => $coordinatorPhone,
                    'created_by' => $currentUser['id']
                ]);

                set_flash('success', "Event '{$title}' has been successfully created and published!");
                header('Location: ' . BASE_URL . 'admin/events.php');
                exit;

            } catch (PDOException $e) {
                $errors[] = 'Database insertion error: ' . $e->getMessage();
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
                <li class="breadcrumb-item active" aria-current="page">Create New</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold text-dark mb-1">Create New College Event</h1>
        <p class="text-muted small mb-0">Fill in the event logistics, venue, schedule, and coordinator contacts.</p>
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

<form method="POST" action="event-create.php" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- Main Event Information (Left 8 Cols) -->
        <div class="col-lg-8">
            <div class="card border rounded-4 shadow-sm p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-card-heading text-primary me-2"></i>Event Information</h5>

                <div class="mb-3">
                    <label for="title" class="form-label small fw-semibold text-muted">Event Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" placeholder="e.g. CodeCraft 2026: Annual Inter-College Coding Contest" value="<?= htmlspecialchars($title) ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="category_id" class="form-label small fw-semibold text-muted">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">Select Domain Category...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($categoryId == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label small fw-semibold text-muted">Publish Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="published" <?= ($status === 'published') ? 'selected' : '' ?>>Published (Live to Students)</option>
                            <option value="draft" <?= ($status === 'draft') ? 'selected' : '' ?>>Draft (Hidden)</option>
                            <option value="completed" <?= ($status === 'completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= ($status === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="short_description" class="form-label small fw-semibold text-muted">Short Summary / Tagline (Max 300 chars) <span class="text-danger">*</span></label>
                    <textarea name="short_description" id="short_description" rows="2" class="form-control" maxlength="300" placeholder="Brief 1-2 sentence hook displayed on event cards..." required><?= htmlspecialchars($shortDesc) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label small fw-semibold text-muted">Full Event Description &amp; Schedule <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" rows="7" class="form-control" placeholder="Detailed breakdown of rules, event rounds, prizes, prerequisites, and schedules..." required><?= htmlspecialchars($desc) ?></textarea>
                </div>

                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" <?= $isFeatured ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-semibold" for="is_featured">
                        Pin this as a <strong>Featured Event</strong> on the Homepage
                    </label>
                </div>
            </div>

            <!-- Coordinator Information Card -->
            <div class="card border rounded-4 shadow-sm p-4 bg-white">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-person-lines-fill text-primary me-2"></i>Event Coordinator Information</h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Coordinator Name <span class="text-danger">*</span></label>
                        <input type="text" name="coordinator_name" class="form-control" value="<?= htmlspecialchars($coordinatorName) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Coordinator Email <span class="text-danger">*</span></label>
                        <input type="email" name="coordinator_email" class="form-control" value="<?= htmlspecialchars($coordinatorEmail) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Coordinator Phone <span class="text-danger">*</span></label>
                        <input type="tel" name="coordinator_phone" class="form-control" value="<?= htmlspecialchars($coordinatorPhone) ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logistics & Schedule (Right 4 Cols) -->
        <div class="col-lg-4">
            <!-- Schedule & Venue Card -->
            <div class="card border rounded-4 shadow-sm p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Schedule &amp; Logistics</h5>

                <div class="mb-3">
                    <label for="event_date" class="form-label small fw-semibold text-muted">Event Date <span class="text-danger">*</span></label>
                    <input type="date" name="event_date" id="event_date" class="form-control" value="<?= htmlspecialchars($eventDate) ?>" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="start_time" class="form-label small fw-semibold text-muted">Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="start_time" class="form-control" value="<?= htmlspecialchars($startTime) ?>" required>
                    </div>
                    <div class="col-6">
                        <label for="end_time" class="form-label small fw-semibold text-muted">End Time</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" value="<?= htmlspecialchars($endTime) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="venue" class="form-label small fw-semibold text-muted">Venue / Hall Location <span class="text-danger">*</span></label>
                    <input type="text" name="venue" id="venue" class="form-control" placeholder="e.g. Central Auditorium, Room 102" value="<?= htmlspecialchars($venue) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="registration_deadline" class="form-label small fw-semibold text-muted">Registration Cutoff Date <span class="text-danger">*</span></label>
                    <input type="date" name="registration_deadline" id="registration_deadline" class="form-control" value="<?= htmlspecialchars($deadline) ?>" required>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label for="total_seats" class="form-label small fw-semibold text-muted">Total Seats <span class="text-danger">*</span></label>
                        <input type="number" name="total_seats" id="total_seats" min="1" max="5000" class="form-control" value="<?= (int) $totalSeats ?>" required>
                    </div>
                    <div class="col-6">
                        <label for="price" class="form-label small fw-semibold text-muted">Entry Fee ($)</label>
                        <input type="number" name="price" id="price" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars($price) ?>">
                    </div>
                </div>
            </div>

            <!-- Banner Poster Card -->
            <div class="card border rounded-4 shadow-sm p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-image text-primary me-2"></i>Event Poster</h5>

                <div class="mb-3">
                    <label for="event_banner" class="form-label small fw-semibold text-muted">Upload Custom Image (JPG, PNG, WEBP)</label>
                    <input type="file" name="banner_image" id="event_banner" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Or Pick Default Preset</label>
                    <select name="preset_banner" class="form-select form-select-sm">
                        <option value="assets/images/events/hackathon.jpg">Hackathon / Coding</option>
                        <option value="assets/images/events/cultural.jpg">Cultural / Arts</option>
                        <option value="assets/images/events/sports.jpg">Sports / Athletics</option>
                        <option value="assets/images/events/workshop.jpg">Tech Workshop</option>
                        <option value="assets/images/events/symposium.jpg">Symposium / Academic</option>
                        <option value="assets/images/events/carnival.jpg">Carnival / Fest</option>
                        <option value="assets/images/events/default.jpg">General Campus Event</option>
                    </select>
                </div>

                <div class="border rounded-2 p-1 bg-light text-center">
                    <img id="banner_preview" src="<?= BASE_URL ?>assets/images/events/default.jpg" class="img-fluid rounded" style="max-height: 120px;" alt="Banner Preview">
                </div>
            </div>

            <!-- Submit Button Card -->
            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold shadow-sm">
                <i class="bi bi-check2-circle me-1"></i> Save &amp; Publish Event
            </button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>

