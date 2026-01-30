<?php
// Returns incident details and available units for modal
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$pdo = get_db_connection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$out = ["incident"=>null,"units"=>[]];
if ($pdo && $id) {
    $stmt = $pdo->prepare("SELECT * FROM incidents WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $out['incident'] = $stmt->fetch(PDO::FETCH_ASSOC);
    $units = $pdo->query("SELECT * FROM units WHERE status='available' ORDER BY unit_type, identifier")->fetchAll(PDO::FETCH_ASSOC);
    $out['units'] = $units;
}
echo json_encode($out);