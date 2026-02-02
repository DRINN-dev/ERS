<?php
// Temporary helper: outputs bcrypt hash for a chosen password
// Usage: http://localhost/ERS/gen_hash.php?password=admin11

header('Content-Type: text/plain');
$pwd = isset($_GET['password']) ? (string)$_GET['password'] : 'admin11';
echo password_hash($pwd, PASSWORD_DEFAULT);
