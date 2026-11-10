<?php
/**
 * CAMPUS EVENTS - Official Verified E-Certificate of Participation
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

// Fetch registration details
$stmt = $db->prepare("
    SELECT r.*, 
           u.name AS student_name, u.roll_no AS student_roll_no, u.department AS student_dept,
           e.title AS event_title, e.event_date, e.venue, e.coordinator_name,
           c.name AS category_name
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
    JOIN categories c ON e.category_id = c.id
    WHERE r.registration_code = :code
    LIMIT 1
");
$stmt->execute(['code' => $regCode]);
$cert = $stmt->fetch();

// Authorization: only student or admin
if (!$cert || ($cert['user_id'] != $currentUser['id'] && !is_admin())) {
    set_flash('danger', 'Access denied.');
    header('Location: ' . BASE_URL . 'my-registrations.php');
    exit;
}

// If student is not marked as attended yet, inform them
if ($cert['status'] !== 'attended' && !is_admin()) {
    set_flash('info', 'Your Certificate of Participation will become available once your attendance is recorded at the event.');
    header('Location: ' . BASE_URL . 'ticket.php?code=' . urlencode($regCode));
    exit;
}

$certId = 'CERT-' . strtoupper(substr(md5($cert['registration_code']), 0, 10));
$pageTitle = 'Certificate of Participation - ' . $cert['student_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #F1F5F9;
            font-family: 'Inter', sans-serif;
            color: #1E293B;
            padding: 2rem 0;
        }
        .cert-container {
            max-width: 900px;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 3rem 4rem;
            border: 12px double #1E293B;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .cert-header {
            text-align: center;
            border-bottom: 2px solid #E2E8F0;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }
        .cert-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 2.5rem;
            color: #111827;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-top: 0.5rem;
        }
        .cert-subtitle {
            font-size: 0.95rem;
            color: #64748B;
            letter-spacing: 0.15em;
            text-transform: uppercase;
        }
        .cert-body {
            text-align: center;
            margin: 2.5rem 0;
            line-height: 2;
        }
        .student-name {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 2.25rem;
            color: #2563EB;
            font-weight: 700;
            border-bottom: 2px solid #CBD5E1;
            display: inline-block;
            padding: 0 2rem 0.25rem;
            margin: 1rem 0;
        }
        .event-highlight {
            font-weight: 700;
            color: #111827;
            font-size: 1.25rem;
        }
        .cert-footer {
            margin-top: 3.5rem;
            padding-top: 2rem;
            border-top: 1px solid #E2E8F0;
        }
        .signature-line {
            border-top: 1px solid #94A3B8;
            padding-top: 0.5rem;
            margin-top: 3rem;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .cert-seal {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            margin: 0 auto;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
            }
            .cert-container {
                box-shadow: none;
                border-width: 6px;
                padding: 2rem;
            }
            .d-print-none {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container d-print-none mb-4 max-w-900 text-center" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center">
        <a href="<?= BASE_URL ?>my-registrations.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Registrations
        </a>
        <div>
            <button onclick="window.print();" class="btn btn-primary btn-sm">
                <i class="bi bi-printer me-1"></i> Print / Save as PDF
            </button>
        </div>
    </div>
</div>

<div class="cert-container">
    <div class="cert-header">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
            <div style="background: #111827; color: #FFFFFF; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <strong class="fs-5 text-dark"><?= htmlspecialchars(APP_NAME) ?></strong>
        </div>
        <div class="cert-subtitle">Official University Recognition &amp; Achievement</div>
        <h1 class="cert-title">Certificate of Participation</h1>
    </div>

    <div class="cert-body">
        <p class="text-muted mb-1 fs-6">This is to officially certify that</p>
        <div class="student-name"><?= htmlspecialchars($cert['student_name']) ?></div>
        <p class="text-muted mb-2">
            Roll Number: <strong class="text-dark font-monospace"><?= htmlspecialchars($cert['student_roll_no'] ?: 'STUDENT') ?></strong> 
            • Department of <strong><?= htmlspecialchars($cert['student_dept'] ?: 'General Studies') ?></strong>
        </p>

        <p class="text-muted mb-2">
            has actively participated and successfully completed the university program
        </p>

        <div class="event-highlight mb-2">
            "<?= htmlspecialchars($cert['event_title']) ?>"
        </div>

        <p class="text-muted small">
            Organized under the category of <strong><?= htmlspecialchars($cert['category_name']) ?></strong> at 
            <strong><?= htmlspecialchars($cert['venue']) ?></strong> on <strong><?= format_date($cert['event_date'], 'F d, Y') ?></strong>.
        </p>
    </div>

    <div class="cert-footer">
        <div class="row align-items-center text-center">
            <div class="col-4">
                <div class="signature-line">
                    <span class="d-block text-dark"><?= htmlspecialchars($cert['coordinator_name']) ?></span>
                    <span class="text-muted small">Faculty Event Coordinator</span>
                </div>
            </div>
            <div class="col-4">
                <div class="cert-seal">
                    <i class="bi bi-award-fill fs-4 mb-1"></i>
                    <span>VERIFIED</span>
                </div>
            </div>
            <div class="col-4">
                <div class="signature-line">
                    <span class="d-block text-dark"><?= htmlspecialchars(TEAM_LEADER) ?></span>
                    <span class="text-muted small">Student Affairs Team Leader</span>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top small text-muted" style="font-size: 0.75rem;">
            <span>Certificate ID: <strong class="font-monospace text-dark"><?= $certId ?></strong></span>
            <span>Registration Code: <strong class="font-monospace text-dark"><?= htmlspecialchars($cert['registration_code']) ?></strong></span>
            <span>Issued by Campus Events Academic Portal</span>
        </div>
    </div>
</div>

</body>
</html>

