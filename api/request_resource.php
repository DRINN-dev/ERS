<?php
// api/request_resource.php
require_once '../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Get POST data
$requestor = $_POST['requestor'] ?? '';
$resource_name = $_POST['resource_name'] ?? '';
$resource_type = $_POST['resource_type'] ?? '';
$quantity = $_POST['quantity'] ?? 1;
$priority = $_POST['priority'] ?? 'medium';
$location = $_POST['location'] ?? '';
$notes = $_POST['notes'] ?? '';
$urgency = $_POST['urgency'] ?? 'normal';

if (!$requestor || !$resource_name || !$resource_type) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO resource_requests (requestor, resource_name, date_requested, status, details) VALUES (?, ?, NOW(), "pending", ? )');
    $details = json_encode([
        'type' => $resource_type,
        'quantity' => $quantity,
        'priority' => $priority,
        'location' => $location,
        'notes' => $notes,
        'urgency' => $urgency
    ]);
    $stmt->execute([$requestor, $resource_name, $details]);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
