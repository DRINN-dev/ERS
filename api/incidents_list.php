<?php
declare(strict_types=1);
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB connection unavailable']);
    exit;
}

// Optional filters (client-side filtering also supported)
$priority = isset($_GET['priority']) ? trim((string)$_GET['priority']) : '';
$day = isset($_GET['day']) ? trim((string)$_GET['day']) : ''; // YYYY-MM-DD
$month = isset($_GET['month']) ? trim((string)$_GET['month']) : ''; // YYYY-MM

$sql = 'SELECT i.id, i.reference_no, i.type, i.priority, i.status, i.location_address, i.description, i.created_at,
        u.identifier AS unit_identifier, u.unit_type AS unit_type
        FROM incidents i
        LEFT JOIN (
            SELECT d1.incident_id, d1.unit_id
            FROM dispatches d1
            INNER JOIN (
                SELECT incident_id, MAX(assigned_at) AS max_assigned_at
                FROM dispatches
                GROUP BY incident_id
            ) t ON t.incident_id = d1.incident_id AND t.max_assigned_at = d1.assigned_at
        ) ld ON ld.incident_id = i.id
        LEFT JOIN units u ON u.id = ld.unit_id';
$where = [];
$params = [];

if ($priority !== '') {
    $where[] = 'priority = :priority';
    $params[':priority'] = $priority;
}
if ($day !== '') {
    $where[] = 'DATE(created_at) = :day';
    $params[':day'] = $day;
}
if ($month !== '') {
    $where[] = 'DATE_FORMAT(created_at, "%Y-%m") = :month';
    $params[':month'] = $month;
}

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY i.created_at DESC LIMIT 200';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    // Transform to client structure
    $items = array_map(function ($r) {
        return [
            'incident_code' => $r['reference_no'],
            'type' => $r['type'],
            'location' => $r['location_address'],
            'description' => $r['description'],
            'priority' => $r['priority'],
            'status' => $r['status'],
            'created_at' => $r['created_at'],
            'assigned_unit' => $r['unit_identifier'] ?? null,
            'assigned_unit_type' => $r['unit_type'] ?? null,
        ];
    }, $rows);
    echo json_encode(['ok' => true, 'items' => $items]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed']);
}
