<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../src/NutritionalValuesHandler.php';

header('Content-Type: application/json');
// This function retrieves nutritional values for a given food item

// Check if the food item is provided
if (!isset($_GET['food'])) {
    echo json_encode(["error" => "No food item provided"]);
    exit();
}

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $jwt = $matches[1];
} else {
    echo json_encode(["error" => "Authorization header not found"]);
    exit();
}

$jwtPayload = decodeJWT($jwt);
if (isset($jwtPayload['error'])) {
    http_response_code(401);
    echo json_encode($jwtPayload);
    exit();
}

$food = $_GET["food"];
$unit = isset($_GET["unit"]) ? $_GET["unit"] : "grams";
echo json_encode(NutritionalValuesHandler::handleNutritionalQuery($food, $unit));
exit();
