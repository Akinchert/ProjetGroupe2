<?php
require_once '../config/db.php';
require_once '../config/jwt.php';
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$data->email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data->password, $user['password'])) {
        $payload = [
            "iss" => $iss,
            "aud" => $aud,
            "iat" => $iat,
            "nbf" => $nbf,
            "data" => [
                "id" => $user['id'],
                "email" => $user['email'],
                "role" => $user['role']
            ]
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');
        echo json_encode(["token" => $jwt]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Identifiants invalides."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Email et mot de passe requis."]);
}
?>