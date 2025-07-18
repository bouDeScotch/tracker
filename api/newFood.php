<?php

// Use the function already defined in getNutritionalValues.php
require_once 'getNutritionalValues.php';
header('Content-Type: application/json');
// This function adds a new food item with its nutritional values

if (isset($_POST['food']) && isset($_POST['values'])) {
    $foodItem = $_POST['food'];
    $values = $_POST['values'];
    $unit = isset($_POST['unit']) ? $_POST['unit'] : "grams";
    $newItem = addNutritionalValues($foodItem, $unit, $values);
    echo json_encode($newItem);
} else {
    echo json_encode(["error" => "Invalid input"]);
} if (isset($_POST['food']) && !isset($_POST['values'])) {
    echo json_encode(["error" => "Nutritional values are required"]);
} else {
    echo json_encode(["error" => "Food item is required"]);
}