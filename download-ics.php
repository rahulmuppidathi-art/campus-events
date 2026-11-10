<?php
/**
 * CAMPUS EVENTS - iCalendar (.ics) File Generator
 * Generates an RFC-5545 compliant calendar file to allow 1-click addition
 * to Google Calendar, Apple Calendar, and Outlook.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$eventId) {
    header('Location: ' . BASE_URL . 'events.php');
    exit;
}

$db = getDB();
if (!$db) {
    die("Database offline.");
}

$stmt = $db->prepare("SELECT * FROM events WHERE id = :id AND status = 'published' LIMIT 1");
$stmt->execute(['id' => $eventId]);
$event = $stmt->fetch();

if (!$event) {
    die("Event not found.");
}

// Format UTC dates for iCalendar
$startDateTime = new DateTime($event['event_date'] . ' ' . $event['start_time']);
$endDateTime = !empty($event['end_time']) 
    ? new DateTime($event['event_date'] . ' ' . $event['end_time'])
    : (clone $startDateTime)->modify('+2 hours');

$dtStart = $startDateTime->format('Ymd\THis');
$dtEnd = $endDateTime->format('Ymd\THis');
$dtStamp = gmdate('Ymd\THis\Z');
$uid = 'event-' . $event['id'] . '-' . time() . '@campusevents.edu';

$summary = addcslashes($event['title'], ",;");
$description = addcslashes($event['short_description'] . "\n\nMore info: " . BASE_URL . "event-details.php?id=" . $event['id'], ",;");
$location = addcslashes($event['venue'], ",;");

// Set headers for .ics download
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . slugify($event['title']) . '.ics"');

echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//Campus Events//EN\r\n";
echo "CALSCALE:GREGORIAN\r\n";
echo "METHOD:PUBLISH\r\n";
echo "BEGIN:VEVENT\r\n";
echo "UID:" . $uid . "\r\n";
echo "DTSTAMP:" . $dtStamp . "\r\n";
echo "DTSTART:" . $dtStart . "\r\n";
echo "DTEND:" . $dtEnd . "\r\n";
echo "SUMMARY:" . $summary . "\r\n";
echo "DESCRIPTION:" . $description . "\r\n";
echo "LOCATION:" . $location . "\r\n";
echo "STATUS:CONFIRMED\r\n";
echo "END:VEVENT\r\n";
echo "END:VCALENDAR\r\n";
exit;

