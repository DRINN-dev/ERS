<?php
// api/resource_request_update.php
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

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = $_POST['status'] ?? '';
$reason = $_POST['reason'] ?? '';

if ($id <= 0 || !in_array($status, ['approved','rejected'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid id or status']);
    exit;
}

try {
    // Fetch existing details JSON
    $stmt = $pdo->prepare('SELECT details FROM resource_requests WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Request not found']);
        exit;
    }
    $details = json_decode($row['details'] ?? '{}', true);
    if (!is_array($details)) { $details = []; }
    $details['decision_reason'] = $reason;

    // Update status and details
    $upd = $pdo->prepare('UPDATE resource_requests SET status = ?, details = ? WHERE id = ?');
    $upd->execute([$status, json_encode($details), $id]);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
