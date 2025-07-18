<?php
header('Content-Type: application/json');
// This function retrieves nutritional values for a given food item

function findFood($foodName, $data) {
    $aliasesMap = json_decode(file_get_contents('../data/globalAliases.json'), true);
    $foodName = strtolower(trim($foodName));
    if (isset($aliasesMap[$foodName])) {
        $foodName = $aliasesMap[$foodName];
    }
    return isset($data[$foodName]) ? $data[$foodName] : null;
}

function getNutritionalValues($foodItem, $unit = "grams") {
    $data = json_decode(file_get_contents('../data/foodMacros.json'), true);
    $foodItem = strtolower(trim($foodItem));
    $foodItem = findFood($foodItem, $data);
    return $foodItem ? $foodItem['macros'] : null;
}

function getNutritionalValuesByAI($foodItem, $unit = "grams") {
    $apiKey = json_decode(file_get_contents('../data/APIKeys.json'), true)['openai'];
    $url = 'https://api.openai.com/v1/responses';

    $text = <<<EOT
You will receive a JSON object describing a food item with the following keys:
- meal_name: the name of the food
- unit: the unit of measurement (e.g. grams, ounces)
- values_requested: list of nutrition values to return (e.g. kcal, prot, carbs, fats)

Your task:
- Return a JSON object containing:
  - meal_name (same as input)
  - quantity set to 100 if unit is grams, or 1 if unit is ounces (standard reference amount)
  - unit (same as input)
  - the requested nutrition values, per standard unit (e.g. per 100g or per 1 oz)
- Use typical nutrition values from standard food databases (e.g. USDA or CIQUAL)
- Do not calculate based on any quantity, just provide typical values per unit
- Round all numeric values to 1 decimal place
- Include only the requested fields

Input :
{
  "meal_name": "$foodItem",
  "unit": "$unit",
  "values_requested": ["kcal", "prot", "carbs", "fats"]
}
EOT;

    $data = [
        "prompt" => [
            "id" => "pmpt_6870387505e881959842cc001f7c90660e5487e8a87c5f82",
            "version" => "1"
        ],
        "input" => [
            [
                "role" => "user",
                "content" => [
                    [
                        "type" => "input_text",
                        "text" => $text
                    ]
                ]
            ]
        ],
        "reasoning" => new stdClass(),
        "max_output_tokens" => 2048,
        "store" => true
    ];

    $payload = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return json_encode([
            "error" => "cURL error: " . curl_error($ch)
        ]);
    } 

    curl_close($ch);
    $responseData = json_decode($response, true);

    if (!empty($responseData["output"])) {
        foreach ($responseData["output"] as $msg) {
            if (
                isset($msg["content"][0]["type"]) &&
                $msg["content"][0]["type"] === "output_text" &&
                isset($msg["content"][0]["text"])
            ) {
                $jsonString = $msg["content"][0]["text"];
                $parsed = json_decode($jsonString, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $parsed;
                } else {
                    return ["error" => "Erreur lors du parse JSON: " . json_last_error_msg()];
                }
            }
        }
    }
    return ["error" => "Pas de contenu texte dans la réponse"];

}

function addNutritionalValues($foodItem, $unit, $values) {
    $data = json_decode(file_get_contents('data/foodMacros.json'), true);
    $alias = $foodItem;
    $foodItem = strtolower(trim($foodItem));
    $newItem = [
        "aliases" => [$alias],
        $unit => $values,
    ];
    $data[$foodItem] = $newItem;
    file_put_contents('data/foodMacros.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    // Also update the global aliases
    $globalAliases = json_decode(file_get_contents('data/globalAliases.json'), true);
    $globalAliases[$alias] = $foodItem;
    file_put_contents('data/globalAliases.json', json_encode($globalAliases, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return $newItem;
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
            // addNutritionalValues($foodItem, $unit, $nutritionalValues);
            echo json_encode($nutritionalValues);
        } else {
            echo json_encode(["error" => "Food item not found"]);
        }
    }
} else {
    echo json_encode(["error" => "No food item provided"]);
}