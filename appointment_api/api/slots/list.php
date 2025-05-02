<?php
require_once '../config/db.php';
require_once '../auth/verify_token.php';

$stmt = $pdo->prepare("SELECT * FROM slots WHERE is_reserved = 0");
$stmt->execute();
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($slots);
?>