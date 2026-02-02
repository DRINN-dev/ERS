<?php
// Seed sample units into the database for demo/testing
// Creates three units (police, fire, ambulance) set to 'available' with coordinates.
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

 $pdo = get_db_connection();
if (!$pdo) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'DB connection unavailable']);
try {
	// Ensure table exists with minimally required columns
	$pdo->exec(
		'CREATE TABLE IF NOT EXISTS `units` (
			`id` INT AUTO_INCREMENT PRIMARY KEY,
			`identifier` VARCHAR(100) NOT NULL UNIQUE,
			`unit_type` VARCHAR(50) NOT NULL,
			`status` VARCHAR(50) NOT NULL DEFAULT "available",
			`driver_name` VARCHAR(100) NULL,
			`plate_number` VARCHAR(50) NULL,
			`location` VARCHAR(255) NULL,
			`latitude` DECIMAL(10,6) NULL,
			`longitude` DECIMAL(10,6) NULL,
			`current_incident_id` INT NULL,
			`updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`last_status_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
	);
	$samples = [
		[
			'identifier' => 'police-unit-1',
			'unit_type' => 'police',
			'status' => 'available',
			'location' => 'Station 1',
			'latitude' => 14.6500,
			'longitude' => 121.0300,
			'driver_name' => 'Officer Cruz',
			'plate_number' => 'PN-1281'
		],
		[
			'identifier' => 'fire-truck-1',
			'unit_type' => 'fire',
			'status' => 'available',
			'location' => 'Station 2',
			'latitude' => 14.6700,
			'longitude' => 121.0450,
			'driver_name' => 'FF Santos',
			'plate_number' => 'FT-3482'
		],
		[
			'identifier' => 'ambulance-1',
			'unit_type' => 'ambulance',
			'status' => 'available',
			'location' => 'Station 3',
			'latitude' => 14.6900,
			'longitude' => 121.0600,
			'driver_name' => 'EMT Dela Cruz',
			'plate_number' => 'AB-5523'
	$inserted = 0;
	foreach ($samples as $u) {
		// Upsert by identifier
	$stmt = $pdo->prepare('SELECT id FROM units WHERE identifier = ? LIMIT 1');
	$stmt->execute([$u['identifier']]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($row) {
			$upd = $pdo->prepare('UPDATE units SET unit_type = :type, status = :status, location = :location, latitude = :lat, longitude = :lng, driver_name = :driver, plate_number = :plate, last_status_at = CURRENT_TIMESTAMP WHERE id = :id');
			$upd->execute([
				':type' => $u['unit_type'],
				':status' => $u['status'],
				':location' => $u['location'],
				':lat' => $u['latitude'],
				':lng' => $u['longitude'],
				':driver' => $u['driver_name'],
				':plate' => $u['plate_number'],
				':id' => (int)$row['id']
			]);
		} else {
			$ins = $pdo->prepare('INSERT INTO units (identifier, unit_type, status, location, latitude, longitude, driver_name, plate_number, last_status_at) VALUES (:identifier, :type, :status, :location, :lat, :lng, :driver, :plate, CURRENT_TIMESTAMP)');
			$ins->execute([
				':identifier' => $u['identifier'],
				':type' => $u['unit_type'],
				':status' => $u['status'],
				':location' => $u['location'],
				':lat' => $u['latitude'],
				':lng' => $u['longitude'],
				':driver' => $u['driver_name'],
				':plate' => $u['plate_number']
			]);
			$inserted++;
		}
	}
	// Return available units count
	$count = (int)$pdo->query("SELECT COUNT(*) AS c FROM units WHERE status='available'")->fetch()['c'];
	echo json_encode(['ok' => true, 'inserted' => $inserted, 'available_count' => $count]);
} catch (Throwable $e) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'Seeding failed']);
}
