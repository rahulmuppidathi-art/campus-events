<?php
/**
 * CAMPUS EVENTS - Contact & Support Page
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = false;
$name = '';
$email = '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $errors[] = 'Security validation token mismatch.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if (empty($name)) $errors[] = 'Your full name is required.';
        if (!$email) $errors[] = 'A valid email address is required.';
        if (empty($subject)) $errors[] = 'Please specify the inquiry subject.';
        if (empty($message)) $errors[] = 'Please enter your message.';

        if (empty($errors)) {
            $db = getDB();
            if ($db) {
                try {
                    $stmt = $db->prepare("
                        INSERT INTO contact_messages (name, email, subject, message, status) 
                        VALUES (:name, :email, :subject, :message, 'unread')
                    ");
                    $stmt->execute([
                        'name' => $name,
                        'email' => $email,
                        'subject' => $subject,
                        'message' => $message
                    ]);
                    $success = true;
                    set_flash('success', 'Thank you! Your message has been dispatched to the event administration team.');
                    // Reset form fields
                    $name = $email = $subject = $message = '';
                } catch (PDOException $e) {
                    $errors[] = 'Database error: ' . $e->getMessage();
                }
            } else {
                $errors[] = 'Database is offline. Please make sure MySQL is started.';
            }
        }
    }
}

$pageTitle = 'Contact & Support | Campus Events';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Banner -->
<div class="bg-white border-bottom py-5 mb-5">
    <div class="container text-center max-w-700 mx-auto">
        <span class="badge bg-primary-subtle text-primary px-3 py-1.5 fw-semibold mb-2">Get In Touch</span>
        <h1 class="h2 fw-bold text-dark mb-2">Contact Event Support</h1>
        <p class="text-muted small mb-0">Have questions regarding student event registrations, approvals, or venue logistics? We're here to assist.</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-5">
        <!-- Contact Form Column -->
        <div class="col-lg-7">
            <div class="card border rounded-4 shadow-sm p-4 p-md-5 bg-white">
                <h3 class="h4 fw-bold mb-3 text-dark">Send an Inquiry</h3>
                <p class="text-muted small mb-4">Our student affairs event committee replies to queries within 24 business hours.</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger small mb-4">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center mb-4">
                        <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                        <div>
                            <strong>Message Delivered!</strong>
                            <div class="small">Our coordinators will reach back out via your provided email address.</div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="contact.php">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label small fw-semibold text-muted">Your Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Patrick" value="<?= htmlspecialchars($name) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-semibold text-muted">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="e.g. patrick@student.edu" value="<?= htmlspecialchars($email) ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="subject" class="form-label small fw-semibold text-muted">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="subject" class="form-control" placeholder="e.g. Query regarding HackInnovate 2026 eligibility" value="<?= htmlspecialchars($subject) ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="message" class="form-label small fw-semibold text-muted">Message <span class="text-danger">*</span></label>
                            <textarea name="message" id="message" rows="5" class="form-control" placeholder="Write your question or request here..." required><?= htmlspecialchars($message) ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary px-4 py-2.5 fw-semibold shadow-sm">
                            <i class="bi bi-send-fill me-1.5"></i> Submit Inquiry
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Campus Contact Details & Help Column -->
        <div class="col-lg-5">
            <div class="card border rounded-4 p-4 shadow-sm bg-white mb-4">
                <h4 class="h5 fw-bold mb-4 text-dark">University Office Information</h4>
                
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="brand-icon bg-light text-primary border" style="width: 44px; height: 44px; font-size: 1.25rem;">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Central Campus Office</h6>
                        <p class="text-muted small mb-0">Student Affairs &amp; Activities Directorate, Block A, Room 204, Central Campus, University Avenue.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="brand-icon bg-light text-primary border" style="width: 44px; height: 44px; font-size: 1.25rem;">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Email Inquiries</h6>
                        <p class="text-muted small mb-0">
                            General: <a href="mailto:events@campusevents.edu" class="text-decoration-none">events@campusevents.edu</a><br>
                            Support: <a href="mailto:support@campusevents.edu" class="text-decoration-none">support@campusevents.edu</a>
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="brand-icon bg-light text-primary border" style="width: 44px; height: 44px; font-size: 1.25rem;">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Direct Phone Line</h6>
                        <p class="text-muted small mb-0">+1 (555) 019-4820 (Ext: 104)</p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <div class="brand-icon bg-light text-primary border" style="width: 44px; height: 44px; font-size: 1.25rem;">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Office Hours</h6>
                        <p class="text-muted small mb-0">
                            Monday to Friday: 09:00 AM – 05:00 PM<br>
                            Saturday: 10:00 AM – 01:00 PM (Event Days)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Visual Map Placeholder Card -->
            <div class="card border rounded-4 overflow-hidden shadow-sm">
                <div class="p-3 bg-light border-bottom fw-semibold small text-muted">
                    <i class="bi bi-pin-map-fill text-danger me-1"></i> Campus Location Map
                </div>
                <div style="background-color: #E2E8F0; height: 180px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                    <i class="bi bi-geo-alt-fill text-primary display-6 mb-1"></i>
                    <span class="small fw-semibold text-dark">Central University Quadrangle</span>
                    <span class="small text-muted" style="font-size: 0.75rem;">GPS: 37.7749° N, 122.4194° W</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

