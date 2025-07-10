<?php
header('Content-Type: application/json');
// This function retrieves nutritional values for a given food item

function getNutritionalValues($foodItem, $unit = "grams") {
    $data = json_decode(file_get_contents('data/foodMacros.json'), true);
    foreach ($data as $item) {
        if ($item['name'] === $foodItem) {
            foreach ($item['macros'] as $macro) {
                if ($macro['unit'] === $unit) {
                    return $macro;
                }
            }
        }
    }
    return null;
}

function getNutritionalValuesByAI($foodItem, $unit = "grams") {
    $apiKey = json_decode(file_get_contents('data/APIKeys.json'), true)['openai'];
    $url = 'https://api.openai.com/v1/responses';

    $data = [
        "prompt" => [
            "id" => "pmpt_6870387505e881959842cc001f7c90660e5487e8a87c5f82",
            "version" => "1",
            "variables" => [
                "foodname" => $foodItem,
                "unit" => $unit
            ]
        ],
        "input" => [
            [
                "role" => "user",
                "content" => [
                    [
                        "type" => "input_text",
                        "text" => "You will receive a JSON object describing a food item with the following keys:\n- meal_name: the name of the food\n- unit: the unit of measurement (e.g. grams, ounces)\n- values_requested: list of nutrition values to return (e.g. kcal, prot, carbs, fats)\n\nYour task:\n- Return a JSON object containing:\n  - meal_name (same as input)\n  - quantity set to 100 if unit is grams, or 1 if unit is ounces (standard reference amount)\n  - unit (same as input)\n  - the requested nutrition values, per standard unit (e.g. per 100g or per 1 oz)\n- Use typical nutrition values from standard food databases (e.g. USDA or CIQUAL)\n- Do not calculate based on any quantity, just provide typical values per unit\n- Round all numeric values to 1 decimal place\n- Include only the requested fields\n\nExample input:\n{\n  \"meal_name\": \"Rice\",\n  \"unit\": \"grams\",\n  \"values_requested\": [\"kcal\", \"prot\", \"carbs\", \"fats\"]\n}"
                    ]
                ]
            ],
            [
                "id" => "msg_687038569f2481a19757a17d7b891bed0bc31cc34f4d90cf",
                "role" => "assistant",
                "content" => [
                    [
                        "type" => "output_text",
                        "text" => "{\n  \"meal_name\": \"Rice\",\n  \"quantity\": 100,\n  \"unit\": \"grams\",\n  \"kcal\": 130,\n  \"prot\": 2.7,\n  \"carbs\": 28,\n  \"fats\": 0.3\n}"
                    ]
                ]
            ]
        ],
        "reasoning" => new stdClass(),
        "max_output_tokens" => 2048,
        "store" => true
    ];

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return null; // Handle error appropriately
    }
    $responseData = json_decode($response, true);
    if (isset($responseData['choices'][0]['message']['content'])) {
        return json_decode($responseData['choices'][0]['message']['content'], true);
    }
    return null;
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