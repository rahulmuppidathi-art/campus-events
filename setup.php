<?php
/**
 * CAMPUS EVENTS - One-Click Database Setup & Installer
 * Can be run via Web Browser (http://localhost/.../setup.php) or CLI (php setup.php)
 */

require_once __DIR__ . '/config/config.php';

$outputMessages = [];
$status = 'pending';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || (php_sapi_name() === 'cli')) {
    try {
        // Step 1: Connect to MySQL server without selecting DB
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $outputMessages[] = "✅ Connected to MySQL server at " . DB_HOST . ":" . DB_PORT;

        // Step 2: Read schema.sql file
        $sqlPath = __DIR__ . '/database/schema.sql';
        if (!file_exists($sqlPath)) {
            throw new Exception("Schema file not found at: {$sqlPath}");
        }

        $sqlContent = file_get_contents($sqlPath);
        $outputMessages[] = "✅ Read database/schema.sql successfully (" . strlen($sqlContent) . " bytes)";

        // Step 3: Execute SQL queries
        // Execute whole batch or split by delimiter
        $pdo->exec($sqlContent);
        $outputMessages[] = "✅ Database `" . DB_NAME . "` created and schema tables installed!";
        $outputMessages[] = "✅ Categories, default admin, student accounts, and seed events populated!";
        $outputMessages[] = "🎉 Setup completed successfully!";
        $status = 'success';
    } catch (Exception $e) {
        $outputMessages[] = "❌ Error: " . $e->getMessage();
        $status = 'error';
    }
}

// If run via CLI
if (php_sapi_name() === 'cli') {
    echo "========================================\n";
    echo "CAMPUS EVENTS - Database Installer CLI\n";
    echo "========================================\n";
    foreach ($outputMessages as $msg) {
        echo $msg . "\n";
    }
    exit($status === 'success' ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - <?= htmlspecialchars(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #F8FAFC;
            color: #1F2937;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-card {
            background: #FFFFFF;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            max-width: 600px;
            width: 100%;
            padding: 2.5rem;
        }
        .brand-badge {
            background: #111827;
            color: #FFFFFF;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="text-center mb-4">
            <span class="brand-badge mb-2"><i class="bi-calendar-event me-2"></i><?= htmlspecialchars(APP_NAME) ?></span>
            <h2 class="h4 fw-bold mt-2">Database Setup & Installer</h2>
            <p class="text-muted small">Academic Web Technology Implementation Project • Team Leader: <?= htmlspecialchars(TEAM_LEADER) ?></p>
        </div>

        <?php if ($status === 'success'): ?>
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="bi-check-circle-fill fs-3 me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1 fw-bold">Installation Successful!</h5>
                    <p class="mb-0 small">The database has been initialized with all tables and sample records.</p>
                </div>
            </div>
            <div class="p-3 bg-light rounded small mb-4 font-monospace">
                <?php foreach ($outputMessages as $msg): ?>
                    <div><?= htmlspecialchars($msg) ?></div>
                <?php endforeach; ?>
            </div>

            <div class="card mb-4 border">
                <div class="card-header bg-white fw-semibold small">Default Credentials:</div>
                <div class="card-body small">
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <span class="badge bg-primary">Admin Account</span><br>
                            <strong>Email:</strong> admin@campusevents.edu<br>
                            <strong>Password:</strong> Admin@123
                        </div>
                        <div class="col-sm-6">
                            <span class="badge bg-secondary">Student Account</span><br>
                            <strong>Email:</strong> patrick@student.edu<br>
                            <strong>Password:</strong> Student@123
                        </div>
                    </div>
                </div>
            </div>

            <a href="index.php" class="btn btn-primary w-100 py-2 fw-semibold">
                Go to Website Home <i class="bi-arrow-right ms-1"></i>
            </a>
        <?php else: ?>
            <?php if (!empty($outputMessages)): ?>
                <div class="alert alert-danger small mb-4">
                    <?php foreach ($outputMessages as $msg): ?>
                        <div><?= htmlspecialchars($msg) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="bg-light p-3 rounded mb-4 small text-muted">
                <p class="mb-2"><strong>Target Database:</strong> <code><?= DB_NAME ?></code> on <code><?= DB_HOST ?>:<?= DB_PORT ?></code></p>
                <p class="mb-0">Clicking below will create the database, build all 6 tables (users, categories, events, registrations, testimonials, messages), and inject realistic university event seed data.</p>
            </div>

            <form method="POST">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="bi-database-fill-gear me-2"></i> Initialize Database Now
                </button>
            </form>
            <div class="text-center mt-3">
                <a href="index.php" class="text-decoration-none small text-muted">Skip to Home</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

