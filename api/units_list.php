<?php
// Returns list of units, optionally filtered by status; includes linked incident info
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB connection unavailable']);
    exit;
}

$status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';

// Map status filter
$statuses = [];
if ($status === 'dispatched') {
    // Consider units not available and not maintenance
    $statuses = ['assigned','enroute','on_scene'];
} elseif ($status !== '') {
    $statuses = [$status];
}

$sql = 'SELECT u.id, u.identifier, u.unit_type, u.status, u.latitude, u.longitude, u.current_incident_id,
               i.reference_no AS incident_code, i.title AS incident_title, i.type AS incident_type,
               i.location_address AS incident_location, i.latitude AS incident_latitude, i.longitude AS incident_longitude
        FROM units u
        LEFT JOIN incidents i ON i.id = u.current_incident_id';
$params = [];
if (!empty($statuses)) {
    $in = implode(',', array_fill(0, count($statuses), '?'));
    $sql .= " WHERE u.status IN ($in)";
    $params = $statuses;
}
$sql .= ' ORDER BY u.unit_type, u.identifier';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'items' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed']);
}
