<?php
header('Content-Type: application/json');

session_name('tracker_session');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "This API endpoint is called using a POST request."]);
    exit();
}

if (! isset($_POST['weight'])) {
    echo json_encode(["error" => "Missing argument : weight is needed"]);
    exit();
}
$weight = $_POST['weight'];

if (! is_numeric($weight)) {
    echo json_encode(["error" => "Weight should be a number"]);
    exit();
}

if (! isset($_POST['date'])) {
    $date = date('Y-m-d');
} else {
    $d = DateTime::createFromFormat('Y-m-d', $_POST['date']);
    if (! $d) {
        echo json_encode(["error" => "Invalid date format, expected YYY-MM-DD"]);
        exit();
    }
    $date = $d->format('Y-m-d');
}

if (! isset($_SESSION['email'])) {
    echo json_encode(["error" => "You need to be connected to add a weight"]);
    exit();
}
$email = $_SESSION['email'];

require_once __DIR__ . "/../lib/helpers.php";

$weightData = loadJSONFile(__DIR__ . "/../data/weights.json");
if (! isset($weightData[$email])) {
    $weightData[$email] = [];
}

$weightData[$email][] = [
    "date" => $date,
    "weight" => $weight
];

$response = saveJSONFile(__DIR__ . "/../data/weights.json", $weightData);
if ($response === true) {
    header('Location: ../public/weight.php');
    exit();   
}
echo json_encode(["error" => "Could not save in weight.json file."]);
