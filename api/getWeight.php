<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

session_name('tracker_session');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  echo json_encode(["error" => "This API endpoint should be used with a GET request."]);
  exit();
}

if (! isset($_SESSION['email'])) {
  echo json_encode(["error" => "You should be logged in to call this API endpoint"]);
  exit();
}
$email = $_SESSION['email'];

$maxNumberOfWeights = $_GET['number'] ?? null;

require_once __DIR__ . "/../init.php";
require_once __DIR__ . "/../src/WeightAPI.php";

$result = WeightAPI::getWeights($email, $maxNumberOfWeights, DATA_PATH . "/weights.json");
echo json_encode($result);
exit(); 
