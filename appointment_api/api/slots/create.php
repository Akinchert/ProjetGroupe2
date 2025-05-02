<?php
require_once '../config/db.php';
require_once '../auth/verify_token.php';

if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(["message" => "Accès interdit"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->datetime)) {
    $stmt = $pdo->prepare("INSERT INTO slots (datetime) VALUES (?)");
    $stmt->execute([$data->datetime]);
    echo json_encode(["message" => "Créneau ajouté."]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Date/heure manquante"]);
}
?>