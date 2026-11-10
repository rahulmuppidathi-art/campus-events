<?php
/**
 * CAMPUS EVENTS - Admin Inquiries & Messages
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

// Handle message status or deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_validate()) {
        $action = $_POST['action'] ?? '';
        $msgId = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);

        if ($msgId && $action === 'toggle_read') {
            $newStatus = sanitize($_POST['new_status'] ?? 'read');
            $upd = $db->prepare("UPDATE contact_messages SET status = :status WHERE id = :id");
            $upd->execute(['status' => $newStatus, 'id' => $msgId]);
            set_flash('success', "Message #{$msgId} marked as {$newStatus}.");
        } elseif ($msgId && $action === 'delete') {
            $del = $db->prepare("DELETE FROM contact_messages WHERE id = :id");
            $del->execute(['id' => $msgId]);
            set_flash('success', "Message #{$msgId} deleted.");
        }
    }
    header('Location: ' . BASE_URL . 'admin/messages.php');
    exit;
}

$stmt = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();

$adminTitle = 'Inquiries & Messages | Campus Events Admin';
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1">Campus Inquiries &amp; Support Messages</h1>
        <p class="text-muted small mb-0">Review student questions and external sponsorship or event inquiries.</p>
    </div>
    <div class="text-muted small">
        Total Inquiries: <strong class="text-dark"><?= count($messages) ?></strong>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h5 class="admin-card-title">All Inquiries</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Sender Name &amp; Email</th>
                    <th>Subject</th>
                    <th>Message Snippet</th>
                    <th>Received Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <tr class="<?= ($msg['status'] === 'unread') ? 'table-light fw-semibold' : '' ?>">
                            <td><span class="text-muted small">#<?= $msg['id'] ?></span></td>
                            <td>
                                <div class="text-dark"><?= htmlspecialchars($msg['name']) ?></div>
                                <div class="small text-muted fw-normal">
                                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($msg['email']) ?>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-truncate" style="max-width: 200px;">
                                    <?= htmlspecialchars($msg['subject']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="small text-muted fw-normal text-truncate" style="max-width: 250px;">
                                    <?= htmlspecialchars($msg['message']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="small text-muted fw-normal"><?= format_date($msg['created_at'], 'M d, Y h:i A') ?></span>
                            </td>
                            <td>
                                <?php if ($msg['status'] === 'unread'): ?>
                                    <span class="badge bg-danger">Unread</span>
                                <?php elseif ($msg['status'] === 'read'): ?>
                                    <span class="badge bg-secondary-subtle text-secondary border">Read</span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border">Replied</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <!-- View Modal Trigger -->
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#msgModal<?= $msg['id'] ?>" title="View Message">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <!-- Toggle Read Form -->
                                    <form method="POST" action="messages.php" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle_read">
                                        <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                        <?php if ($msg['status'] === 'unread'): ?>
                                            <input type="hidden" name="new_status" value="read">
                                            <button type="submit" class="btn btn-outline-secondary" title="Mark as Read">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="new_status" value="unread">
                                            <button type="submit" class="btn btn-outline-secondary" title="Mark as Unread">
                                                <i class="bi bi-envelope"></i>
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                    <!-- Delete Form -->
                                    <form method="POST" action="messages.php" class="d-inline" onsubmit="return confirm('Delete this inquiry permanently?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Inquiry Modal -->
                                <div class="modal fade text-start" id="msgModal<?= $msg['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border">
                                            <div class="modal-header border-bottom">
                                                <h5 class="modal-title fw-bold fs-6">Inquiry #<?= $msg['id'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <span class="text-muted small d-block">From:</span>
                                                    <strong class="text-dark"><?= htmlspecialchars($msg['name']) ?></strong> &lt;<?= htmlspecialchars($msg['email']) ?>&gt;
                                                </div>
                                                <div class="mb-3">
                                                    <span class="text-muted small d-block">Subject:</span>
                                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($msg['subject']) ?></span>
                                                </div>
                                                <div class="mb-3">
                                                    <span class="text-muted small d-block">Sent On:</span>
                                                    <span class="text-muted small"><?= format_date($msg['created_at'], 'l, F d, Y at h:i A') ?></span>
                                                </div>
                                                <hr>
                                                <div class="p-3 bg-light rounded-3 text-dark small" style="white-space: pre-wrap; line-height: 1.7;">
                                                    <?= htmlspecialchars($msg['message']) ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top">
                                                <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: <?= urlencode($msg['subject']) ?>" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-reply-fill me-1"></i> Reply via Email
                                                </a>
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted small">
                            No contact inquiries recorded.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>

