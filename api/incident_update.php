<?php
// API endpoint: /api/incident_update.php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || !isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing incident id']);
    exit;
}

$id = (int)$input['id'];
// Accept optional fields; update only provided values
$type = isset($input['type']) ? trim((string)$input['type']) : null;
$priority = isset($input['priority']) ? trim((string)$input['priority']) : null;
$description = isset($input['description']) ? trim((string)$input['description']) : null;
// Map either 'location' or 'location_address' to DB column 'location_address'
$location_address = null;
if (array_key_exists('location_address', $input)) {
    $location_address = trim((string)$input['location_address']);
} elseif (array_key_exists('location', $input)) {
    $location_address = trim((string)$input['location']);
}
$status = isset($input['status']) ? trim((string)$input['status']) : null;

try {
    $pdo = get_db_connection();
    $fields = [];
    $params = [':id' => $id];
    // Validate enums if provided
    if ($priority !== null) {
        $p = strtolower($priority);
        if (!in_array($p, ['low','medium','high'], true)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid priority']);
            exit;
        }
        $fields[] = 'priority = :priority';
        $params[':priority'] = $p;
    }
    if ($type !== null) {
        $fields[] = 'type = :type';
        $params[':type'] = $type;
    }
    if ($description !== null) {
        $fields[] = 'description = :description';
        $params[':description'] = $description;
    }
    if ($location_address !== null) {
        $fields[] = 'location_address = :location_address';
        $params[':location_address'] = $location_address;
    }
    if ($status !== null) {
        $s = strtolower($status);
        if (!in_array($s, ['pending','dispatched','resolved','cancelled'], true)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid status']);
            exit;
        }
        $fields[] = 'status = :status';
        $params[':status'] = $s;
    }
    if (!$fields) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'No fields to update']);
        exit;
    }
    $sql = 'UPDATE incidents SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Update failed']);
}
