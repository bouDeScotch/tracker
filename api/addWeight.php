<?php
session_name('tracker_session');
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../src/WeightAPI.php';

$email = $_SESSION['user_id'];
$weight = $_POST['weight'] ?? null;
$date = $_POST['date'] ?? null;

try {
    WeightAPI::logWeight($email, $weight, $date, __DIR__ . '/../data/weights.json');
    echo json_encode(["success" => true]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
} catch (RuntimeException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Unexpected error"]);
}
