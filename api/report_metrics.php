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

try {
    // Totals
    $total_incidents_month = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())")->fetch()['c'];
    $total_incidents_last_month = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE YEAR(created_at)=YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(created_at)=MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))")->fetch()['c'];
    $total_calls_today = (int)$pdo->query("SELECT COUNT(*) AS c FROM calls WHERE DATE(received_at)=CURDATE()")->fetch()['c'];

    // Success rate: resolved / total
    $resolved_count = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE status='resolved'")->fetch()['c'];
    $total_incidents = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents")->fetch()['c'];
    $success_rate = $total_incidents > 0 ? round(($resolved_count / $total_incidents) * 100, 1) : 0.0;

    // Resource utilization: units busy / total
    $total_units = (int)$pdo->query("SELECT COUNT(*) AS c FROM units")->fetch()['c'];
    $busy_units = (int)$pdo->query("SELECT COUNT(*) AS c FROM units WHERE status IN ('assigned','enroute','on_scene')")->fetch()['c'];
    $resource_utilization = $total_units > 0 ? round(($busy_units / $total_units) * 100, 1) : 0.0;

    // Avg response time (minutes): assigned -> on_scene for recent month
    $avg_response_time = 0.0;
    $stmt = $pdo->query("SELECT AVG(TIMESTAMPDIFF(MINUTE, assigned_at, on_scene_at)) AS avg_min FROM dispatches WHERE assigned_at IS NOT NULL AND on_scene_at IS NOT NULL AND YEAR(assigned_at)=YEAR(CURDATE()) AND MONTH(assigned_at)=MONTH(CURDATE())");
    $row = $stmt->fetch();
    if ($row && $row['avg_min'] !== null) {
        $avg_response_time = round((float)$row['avg_min'], 1);
    }

    // Incidents by priority
    $priorityCounts = [
        'high' => 0,
        'medium' => 0,
        'low' => 0,
    ];
    $q = $pdo->query("SELECT priority, COUNT(*) AS c FROM incidents GROUP BY priority");
    foreach ($q->fetchAll() as $r) {
        $p = $r['priority'] === 'critical' ? 'low' : $r['priority'];
        if (isset($priorityCounts[$p])) {
            $priorityCounts[$p] = (int)$r['c'];
        }
    }

    // Incidents by type
    $typeCounts = [
        'medical' => 0,
        'fire' => 0,
        'police' => 0,
        'traffic' => 0,
        'other' => 0,
    ];
    $qt = $pdo->query("SELECT type, COUNT(*) AS c FROM incidents GROUP BY type");
    foreach ($qt->fetchAll() as $r) {
        $t = $r['type'];
        if (isset($typeCounts[$t])) {
            $typeCounts[$t] = (int)$r['c'];
        } else {
            $typeCounts['other'] += (int)$r['c'];
        }
    }

    echo json_encode([
        'ok' => true,
        'metrics' => [
            'total_calls_today' => $total_calls_today,
            'total_incidents_month' => $total_incidents_month,
            'total_incidents_last_month' => $total_incidents_last_month,
            'success_rate' => $success_rate,
            'resource_utilization' => $resource_utilization,
            'avg_response_time_min' => $avg_response_time,
            'incidents_by_priority' => $priorityCounts,
            'incidents_by_type' => $typeCounts,
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed']);
}
