<?php
/**
 * CAMPUS EVENTS - Admin Gate Check-In & Scanner Tool
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();
$db = getDB();
if (!$db) {
    die("Database connection offline.");
}

$searchCode = sanitize($_POST['code'] ?? $_GET['code'] ?? '');
$checkinResult = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_attendance') {
    if (csrf_validate()) {
        $regId = filter_input(INPUT_POST, 'reg_id', FILTER_VALIDATE_INT);
        if ($regId) {
            $upd = $db->prepare("UPDATE registrations SET status = 'attended', attended_at = NOW() WHERE id = :id");
            $upd->execute(['id' => $regId]);
            set_flash('success', "Student check-in confirmed! Attendance recorded.");
            header('Location: checkin.php?code=' . urlencode($searchCode));
            exit;
        }
    }
}

if (!empty($searchCode)) {
    $stmt = $db->prepare("
        SELECT r.*, 
               u.name AS student_name, u.email AS student_email, u.roll_no, u.department, u.year_of_study, u.phone,
               e.title AS event_title, e.event_date, e.start_time, e.venue 
        FROM registrations r 
        JOIN users u ON r.user_id = u.id 
        JOIN events e ON r.event_id = e.id 
        WHERE r.registration_code = :code 
        LIMIT 1
    ");
    $stmt->execute(['code' => $searchCode]);
    $checkinResult = $stmt->fetch();

    if (!$checkinResult) {
        $errorMessage = "No registration found with pass code: '{$searchCode}'. Please verify.";
    }
}

// Fetch list of upcoming events for quick stats
$eventsList = $db->query("
    SELECT e.id, e.title, e.event_date, 
           COUNT(r.id) AS total_regs, 
           SUM(CASE WHEN r.status = 'attended' THEN 1 ELSE 0 END) AS attended_count 
    FROM events e 
    LEFT JOIN registrations r ON e.id = r.event_id 
    WHERE e.status = 'published' 
    GROUP BY e.id 
    ORDER BY e.event_date ASC 
    LIMIT 5
")->fetchAll();

$adminTitle = 'Gate Check-In & Scanner | Campus Events Admin';
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Gate Check-In &amp; Passcode Verifier</h1>
        <p class="text-muted small mb-0">Verify digital passes at the event entrance and record student attendance in real time.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Scanner / Search Column (Left 7) -->
    <div class="col-lg-7">
        <div class="card border rounded-4 p-4 shadow-sm bg-white mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-qr-code-scan text-primary me-2"></i>Verify Attendee Pass</h5>
            
            <form method="GET" action="checkin.php" class="mb-4">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-upc-scan text-muted"></i></span>
                    <input type="text" name="code" class="form-control border-start-0 font-monospace text-uppercase" placeholder="Enter or scan REG-XXXX..." value="<?= htmlspecialchars($searchCode) ?>" required autofocus>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                        Verify Pass
                    </button>
                </div>
                <div class="form-text small text-muted">
                    Tip: USB/Bluetooth barcode scanners automatically type the code and submit.
                </div>
            </form>

            <?php if ($errorMessage): ?>
                <div class="alert alert-danger d-flex align-items-center">
                    <i class="bi bi-x-circle-fill fs-4 me-3 text-danger"></i>
                    <div>
                        <strong>Verification Failed</strong>
                        <div class="small"><?= htmlspecialchars($errorMessage) ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($checkinResult): ?>
                <div class="card border rounded-3 p-4 bg-light">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-primary font-monospace px-3 py-1.5 fs-6 mb-2">
                                <?= htmlspecialchars($checkinResult['registration_code']) ?>
                            </span>
                            <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($checkinResult['student_name']) ?></h4>
                            <div class="text-muted small"><?= htmlspecialchars($checkinResult['student_email']) ?></div>
                        </div>
                        <div>
                            <?php if ($checkinResult['status'] === 'attended'): ?>
                                <span class="badge bg-primary px-3 py-2 fs-6">
                                    <i class="bi bi-check-all me-1"></i> Already Checked-In
                                </span>
                            <?php elseif ($checkinResult['status'] === 'confirmed'): ?>
                                <span class="badge bg-success px-3 py-2 fs-6">
                                    <i class="bi bi-check-circle me-1"></i> Valid Pass
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger px-3 py-2 fs-6">
                                    <i class="bi bi-slash-circle me-1"></i> Cancelled
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-3 small bg-white p-3 rounded-2 border mb-3">
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Student Roll No:</span>
                            <strong class="text-dark font-monospace fs-6"><?= htmlspecialchars($checkinResult['roll_no'] ?: 'N/A') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Department &amp; Year:</span>
                            <strong class="text-dark"><?= htmlspecialchars($checkinResult['department'] ?: 'General') ?> (<?= htmlspecialchars($checkinResult['year_of_study'] ?: '') ?>)</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Event:</span>
                            <strong class="text-dark"><?= htmlspecialchars($checkinResult['event_title']) ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Venue &amp; Time:</span>
                            <span class="text-dark"><?= htmlspecialchars($checkinResult['venue']) ?> at <?= format_time($checkinResult['start_time']) ?></span>
                        </div>
                    </div>

                    <?php if ($checkinResult['status'] === 'confirmed'): ?>
                        <form method="POST" action="checkin.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="confirm_attendance">
                            <input type="hidden" name="reg_id" value="<?= $checkinResult['id'] ?>">
                            <input type="hidden" name="code" value="<?= htmlspecialchars($searchCode) ?>">
                            <button type="submit" class="btn btn-success w-100 py-2.5 fw-bold shadow-sm">
                                <i class="bi bi-check2-circle me-1.5"></i> Confirm Entry &amp; Mark Attended
                            </button>
                        </form>
                    <?php elseif ($checkinResult['status'] === 'attended'): ?>
                        <div class="alert alert-info py-2 mb-0 small text-center">
                            Checked in on: <strong><?= format_date($checkinResult['attended_at'], 'M d, Y h:i A') ?></strong>.
                            <a href="<?= BASE_URL ?>certificate.php?code=<?= urlencode($checkinResult['registration_code']) ?>" target="_blank" class="fw-bold ms-2">View Certificate &rarr;</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Live Event Attendance Monitor (Right 5) -->
    <div class="col-lg-5">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-people me-2 text-primary"></i>Live Event Turnout</h5>
            </div>
            <div class="p-3">
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($eventsList as $ev): 
                        $pct = ($ev['total_regs'] > 0) ? round(($ev['attended_count'] / $ev['total_regs']) * 100) : 0;
                    ?>
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold mb-0 text-dark small text-truncate" style="max-width: 220px;">
                                    <?= htmlspecialchars($ev['title']) ?>
                                </h6>
                                <span class="badge bg-dark font-monospace" style="font-size: 0.7rem;">
                                    <?= format_date($ev['event_date']) ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-1.5 mt-2">
                                <span>Checked-in: <strong class="text-primary"><?= (int) $ev['attended_count'] ?></strong></span>
                                <span>Total Registered: <strong><?= (int) $ev['total_regs'] ?></strong></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>

