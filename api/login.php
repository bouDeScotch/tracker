<?php

require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST Only']);
    exit;
}

$data = loadJSONFile('php://input');

if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required']);
    exit;
}

$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

$password = $data['password'];

$userInfo = getUserInfo($email);
if ($userInfo === null) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

if (!password_verify($password, $userInfo['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid password']);
    exit;
}

$jwt = generateJWT([
    'id' => $userInfo['id'],
    'email' => $email,
    'exp' => time() + 3600
]);
if ($jwt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to generate JWT']);
    exit;
}
echo json_encode(['jwt' => $jwt]);
exit();