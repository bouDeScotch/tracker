<?php


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

require_once __DIR__ . '/../src/AuthAPI.php';
require_once __DIR__ . '/../init.php';

try {
    $jwt = AuthAPI::login($data["email"], $data["password"]);
    echo json_encode(["jwt" => $jwt]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch (RuntimeException $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}

exit();
