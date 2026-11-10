<?php
/**
 * CAMPUS EVENTS - Admin Delete Event Handler
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        set_flash('danger', 'Security validation mismatch.');
        header('Location: ' . BASE_URL . 'admin/events.php');
        exit;
    }

    $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    if ($eventId) {
        $db = getDB();
        if ($db) {
            try {
                // Delete event (foreign key constraint ON DELETE CASCADE removes registrations)
                $stmt = $db->prepare("DELETE FROM events WHERE id = :id");
                $stmt->execute(['id' => $eventId]);

                set_flash('success', "Event #{$eventId} and associated registrations have been permanently deleted.");
            } catch (PDOException $e) {
                set_flash('danger', 'Failed to delete event: ' . $e->getMessage());
            }
        }
    }
}

header('Location: ' . BASE_URL . 'admin/events.php');
exit;

