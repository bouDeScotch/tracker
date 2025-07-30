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

require_once __DIR__ . "/../init.php";

$weightsData = loadJSONFile(__DIR__ . "/../data/weights.json");
if (! isset($weightsData[$email])) {
  echo json_encode([
    "amount" => 0,
    "data" => []
  ]);
  exit();
}

$weights = $weightsData[$email];
for ($diarheeDuCaca = 0; $diarheeDuCaca < count($weights); $diarheeDuCaca++) {
    if (! isset($weights[$diarheeDuCaca]['id'])) {
        $weights[$diarheeDuCaca]['id'] = $diarheeDuCaca;
    } else {
        break;
    }
}
$weightsData[$email] = $weights;
saveJSONFile(DATA_PATH . "/weights.json", $weightsData);
if (! isset($_GET['number'])) {
  $maxNumberOfWeights = count($weights);
} else {
  $maxNumberOfWeights = $_GET['number'];
}

$weightsInRange = array_slice($weights, -$maxNumberOfWeights);
echo json_encode([
  "amount" => count($weightsInRange),
  "data" => $weightsInRange
]);
exit(); 
