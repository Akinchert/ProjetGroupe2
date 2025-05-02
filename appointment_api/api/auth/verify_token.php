<?php
require_once '../config/jwt.php';
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (!$authHeader || !preg_match('/Bearer\s(.*)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["message" => "Token manquant"]);
    exit;
}

$jwt = $matches[1];

try {
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    $user_id = $decoded->data->id;
    $role = $decoded->data->role;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => "Token invalide"]);
    exit;
}
?>