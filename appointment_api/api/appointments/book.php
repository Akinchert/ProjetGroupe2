<?php
require_once '../config/db.php';
require_once '../auth/verify_token.php';

if ($role !== 'client') {
    http_response_code(403);
    echo json_encode(["message" => "Accès interdit"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
$slot_id = $data->slot_id ?? null;

if ($slot_id) {
    $stmt = $pdo->prepare("SELECT is_reserved FROM slots WHERE id = ?");
    $stmt->execute([$slot_id]);
    $slot = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($slot && !$slot['is_reserved']) {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO appointments (user_id, slot_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $slot_id]);

        $stmt = $pdo->prepare("UPDATE slots SET is_reserved = 1 WHERE id = ?");
        $stmt->execute([$slot_id]);

        $pdo->commit();

        echo json_encode(["message" => "Créneau réservé."]);
    } else {
        echo json_encode(["message" => "Créneau déjà réservé."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "ID du créneau manquant"]);
}
?>