<?php
// Dispatch a unit to an incident
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$incident_id = isset($data['incident_id']) ? (int)$data['incident_id'] : 0;
$unit_id = isset($data['unit_id']) ? (int)$data['unit_id'] : 0;
if (!$incident_id || !$unit_id) {
    echo json_encode(['ok'=>false,'error'=>'Missing data']);
    exit;
}
require_once __DIR__ . '/../includes/db.php';
$pdo = get_db_connection();
if (!$pdo) {
    echo json_encode(['ok'=>false,'error'=>'DB error']);
    exit;
}
try {
    // Set unit to busy
    $stmt = $pdo->prepare("UPDATE units SET status='busy' WHERE id=?");
    $stmt->execute([$unit_id]);
    // Optionally, assign unit to incident (if you have a join table, add here)
    // For now, just update incident status to 'dispatched'
    $stmt2 = $pdo->prepare("UPDATE incidents SET status='dispatched' WHERE id=?");
    $stmt2->execute([$incident_id]);
    echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
