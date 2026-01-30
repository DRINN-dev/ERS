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
$type = trim((string)($input['type'] ?? ''));
$priority = trim((string)($input['priority'] ?? ''));
$description = trim((string)($input['description'] ?? ''));
$location = trim((string)($input['location'] ?? ''));
$status = trim((string)($input['status'] ?? ''));

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('UPDATE incidents SET type = :type, priority = :priority, description = :description, location = :location, status = :status WHERE id = :id');
    $stmt->execute([
        ':type' => $type,
        ':priority' => $priority,
        ':description' => $description,
        ':location' => $location,
        ':status' => $status,
        ':id' => $id
    ]);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Update failed']);
}
