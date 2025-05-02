<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$authMiddleware = function ($request, $handler) {
    $authHeader = $request->getHeaderLine('Authorization');
    if (!$authHeader) {
        return (new \Slim\Psr7\Response())->withStatus(401)->write('Unauthorized');
    }
    $token = json_decode(base64_decode(str_replace('Bearer ', '', $authHeader)), true);
    $request = $request->withAttribute('user', $token);
    return $handler->handle($request);
};

$app->post('/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch();
    if ($user && password_verify($data['password'], $user['password'])) {
        $token = base64_encode(json_encode(['id' => $user['id'], 'role' => $user['role']]));
        $response->getBody()->write(json_encode(['token' => $token]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    return $response->withStatus(401)->write('Invalid credentials');
});

$app->post('/slots', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    if ($user['role'] !== 'admin') {
        return $response->withStatus(403)->write('Forbidden');
    }
    $data = $request->getParsedBody();
    $stmt = $this->db->prepare("INSERT INTO slots (start_time, end_time) VALUES (?, ?)");
    $stmt->execute([$data['start_time'], $data['end_time']]);
    return $response->withJson(['message' => 'Slot created']);
})->add($authMiddleware);

$app->get('/slots', function (Request $request, Response $response) {
    $stmt = $this->db->query("SELECT * FROM slots WHERE is_booked = FALSE");
    $slots = $stmt->fetchAll();
    $response->getBody()->write(json_encode($slots));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/appointments', function (Request $request, Response $response) {
    $user = $request->getAttribute('user');
    if ($user['role'] !== 'client') {
        return $response->withStatus(403)->write('Forbidden');
    }
    $data = $request->getParsedBody();
    $stmt = $this->db->prepare("SELECT * FROM slots WHERE id = ? AND is_booked = FALSE");
    $stmt->execute([$data['slot_id']]);
    $slot = $stmt->fetch();
    if (!$slot) {
        return $response->withStatus(400)->write('Slot unavailable');
    }
    $this->db->beginTransaction();
    $stmt = $this->db->prepare("INSERT INTO appointments (user_id, slot_id) VALUES (?, ?)");
    $stmt->execute([$user['id'], $data['slot_id']]);
    $stmt = $this->db->prepare("UPDATE slots SET is_booked = TRUE WHERE id = ?");
    $stmt->execute([$data['slot_id']]);
    $this->db->commit();
    return $response->withJson(['message' => 'Appointment booked']);
})->add($authMiddleware);