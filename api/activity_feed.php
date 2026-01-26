<?php
// API endpoint: /api/activity_feed.php
// Returns the 10 most recent activities (incidents, dispatches, system events)
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB connection unavailable']);
    exit;
}
// Get recent incidents
$incidents = $pdo->query("SELECT 'incident' AS type, id, reference_no, type AS event_type, priority, status, location_address, created_at FROM incidents ORDER BY created_at DESC LIMIT 5")->fetchAll();
// Get recent dispatches
$dispatches = $pdo->query("SELECT 'dispatch' AS type, d.id, d.incident_id, u.identifier AS unit_identifier, u.unit_type, d.assigned_at AS created_at FROM dispatches d LEFT JOIN units u ON d.unit_id = u.id ORDER BY d.assigned_at DESC LIMIT 5")->fetchAll();
// Merge and sort by created_at desc
$all = array_merge($incidents, $dispatches);
usort($all, function($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});
$all = array_slice($all, 0, 10);
echo json_encode(['ok' => true, 'data' => $all]);
