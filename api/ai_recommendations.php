<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/gemini_helper.php';

$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB connection unavailable']);
    exit;
}

try {
    $activeIncidents = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE status IN ('pending','dispatched')")->fetch()['c'];
    $availableUnits = (int)$pdo->query("SELECT COUNT(*) AS c FROM units WHERE status='available'")->fetch()['c'];
    $pendingCalls = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE status='pending'")->fetch()['c'];

    $dispatchData = [
        'active_incidents' => $activeIncidents,
        'available_units' => $availableUnits,
        'pending_calls' => $pendingCalls,
        'current_incident' => 'Live Refresh'
    ];
    $text = getDispatchRecommendations($dispatchData);
    if ($text) {
        echo json_encode(['ok' => true, 'text' => $text]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'AI unavailable']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed']);
}
