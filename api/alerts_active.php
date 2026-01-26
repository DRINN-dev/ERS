<?php
// API endpoint: /api/alerts_active.php
// Returns currently active alerts (e.g., high response time, resource utilization, weather)
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB connection unavailable']);
    exit;
}
$alerts = [];
// Example: High response time alert
$rt = $pdo->query("SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, responded_at)) AS avg_rt FROM incidents WHERE responded_at IS NOT NULL AND created_at >= NOW() - INTERVAL 1 HOUR")->fetch();
if ($rt && $rt['avg_rt'] > 10) {
    $alerts[] = [
        'type' => 'critical',
        'title' => 'High Response Time',
        'details' => 'Average response time exceeds 10 minutes (last hour)'
    ];
}
// Example: Resource utilization
$amb = $pdo->query("SELECT COUNT(*) AS used FROM units WHERE unit_type='ambulance' AND status!='available'")->fetch();
$total = $pdo->query("SELECT COUNT(*) AS total FROM units WHERE unit_type='ambulance'")->fetch();
if ($total && $total['total'] > 0 && $amb['used'] / $total['total'] > 0.8) {
    $alerts[] = [
        'type' => 'warning',
        'title' => 'Resource Utilization',
        'details' => 'Ambulance fleet at over 80% capacity'
    ];
}
// Example: Weather alert (from dashboard)
if ($condition !== 'Unavailable' && stripos($condition, 'rain') !== false) {
    $alerts[] = [
        'type' => 'info',
        'title' => 'Weather Alert',
        'details' => $condition
    ];
}
echo json_encode(['ok' => true, 'data' => $alerts]);
