<?php
header('Content-Type: application/json');
// This function retrieves nutritional values for a given food item

function findFood($foodName, $data) {
    if (isset($data[$foodName])) {
        return $data[$foodName];
    }

    // Check if alias exists before searching it in the data
    $exists = false;
    $aliases = json_decode(file_get_contents('data/globalAliases.json'), true);
    foreach ($aliases as $alias) {
        if (isset($data[$alias]) && $data[$alias]['name'] === $foodName) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        return null;
    }

    // We know the alias exists so now we can search for it in the data
    foreach ($data as $key => $value) {
        foreach ($value["aliases"] as $alias) {
            if ($alias === $foodName) {
                return $value;
            }
        }
    }

    // This should never happen as we already checked if the alias exists
    // but we return null just in case
    return null;
}

function getNutritionalValues($foodItem, $unit = "grams") {
    $data = json_decode(file_get_contents('data/foodMacros.json'), true);
    $foodItem = strtolower(trim($foodItem));
    $foodItem = findFood($foodItem, $data);
    return $foodItem ? $foodItem['macros'] : null;
}

function getNutritionalValuesByAI($foodItem, $unit = "grams") {
    // As the server currently can't do external API calls, we will simulate this with a static response.
    $staticResponse = [
        "kcal" => 200,
        "prot" => 5,
        "carbs" => 40,
        "fats" => 3,
        "fromAI" => true
    ];

    return $staticResponse;
}


function addNutritionalValues($foodItem, $unit, $values) {
    $data = json_decode(file_get_contents('data/foodMacros.json'), true);
    $newEntry = [
        "name" => $foodItem,
        "macros" => [
            [
                "unit" => $unit,
                "kcal" => $values['kcal'],
                "prot" => $values['prot'],
                "carbs" => $values['carbs'],
                "fats" => $values['fats']
            ]
        ]
    ];
    $data[] = $newEntry;
    file_put_contents('data/foodMacros.json', json_encode($data, JSON_PRETTY_PRINT));
}


// Check if the food item is provided
if (isset($_GET['food'])) {
    $foodItem = $_GET['food'];
    $unit = isset($_GET['unit']) ? $_GET['unit'] : "grams";
    $nutritionalValues = getNutritionalValues($foodItem, $unit);
    if ($nutritionalValues) {
        echo json_encode($nutritionalValues);
    } else {
        $nutritionalValues = getNutritionalValuesByAI($foodItem, $unit);
        if ($nutritionalValues) {
            echo json_encode($nutritionalValues);
            addNutritionalValues($foodItem, $unit, $nutritionalValues);
        } else {
            echo json_encode(["error" => "Food item not found"]);
        }
    }
} else {
    echo json_encode(["error" => "No food item provided"]);
}