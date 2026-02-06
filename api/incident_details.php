<?php
// Returns incident details and available units for modal
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$pdo = get_db_connection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$code = isset($_GET['code']) ? trim((string)$_GET['code']) : '';

$out = ["ok"=>false, "incident"=>null, "units"=>[]];
if ($pdo) {
    try {
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM incidents WHERE id=? LIMIT 1");
            $stmt->execute([$id]);
        } elseif ($code !== '') {
            $stmt = $pdo->prepare("SELECT * FROM incidents WHERE reference_no=? LIMIT 1");
            $stmt->execute([$code]);
        } else {
            $stmt = null;
        }

        if ($stmt) {
            $incident = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($incident) {
                $out['incident'] = $incident;
                $out['ok'] = true;
            }
        }

        $units = $pdo->query("SELECT * FROM units WHERE status='available' ORDER BY unit_type, identifier")->fetchAll(PDO::FETCH_ASSOC);
        $out['units'] = $units;
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(["ok"=>false, "error"=>"Query failed"]);
        exit;
    }
}
echo json_encode($out);