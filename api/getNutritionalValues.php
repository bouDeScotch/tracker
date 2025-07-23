<?php
require_once __DIR__ . '/../init.php';
header('Content-Type: application/json');
// This function retrieves nutritional values for a given food item

function findFood($foodName, $data) {
    $aliasesMap = loadJSONFile('../data/globalAliases.json');
    $foodName = strtolower(trim($foodName));
    if (isset($aliasesMap[$foodName])) {
        $foodName = $aliasesMap[$foodName];
    }
    return isset($data[$foodName]) ? $data[$foodName] : null;
}

function findFoodTrueName($foodName) {
    $aliasesData = loadJSONFile('../data/globalAliases.json');
    return isset($aliasesData[$foodName]) ? $aliasesData[$foodName] : null;
} 

function getNutritionalValues($foodItem, $unit = "grams") {
    $data = loadJSONFile('../data/foodMacros.json');
    $foodItem = strtolower(trim($foodItem));
    $foodItem = findFood($foodItem, $data);
    return isset($foodItem[$unit]) ? $foodItem[$unit] : null;
}

function getNutritionalValuesByAI($foodItem, $unit = "grams") {
    $apiKey = loadJSONFile('../data/APIKeys.json')['openai'];
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
    - if the unit is "serving", give the value for a typical serving
    - Use typical nutrition values from standard food databases (e.g. USDA or CIQUAL)
    - Do not calculate based on any quantity, just provide typical values per unit
    - Round all numeric values to 1 decimal place
    - Include only the requested fields
    - Do not put anything before or after the {}, as it will be parsed directly


Expected output :
{
    "meal_name": "name",
    "quantity": 100, // or 1 if the unit is neither grams or mL
    "unit": "grams", // will usualy be grams, oz, mL or serving, this is given in the input below
    "kcal": 300, // values next including this one are only used as an example, do not copy them
    "prot": 10,
    "carbs": 0.3,
    "fats": 6.5
}


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
                    return [
                        "error" => "Erreur lors du parse JSON: " . json_last_error_msg(),
                        "message" => $jsonString
                    ];
                }
            }
        }
    }
    return ["error" => "Pas de contenu texte dans la réponse"];

}

function addNutritionalValues($foodItem, $values) {
    // Two possibilities : the item exists, but not with this unit
    // The item doesn't exists at all

    define("LIST_VALUES", [
        "kcal", "prot", "carbs", "fats"
    ]);

    $data = file_get_contents('../data/foodMacros.json');
    $data = json_decode($data, true);

    $foodName = strtolower(trim($foodItem));
    $trueName = findFoodTrueName($foodName);
    if (! $trueName) {
        $trueName = $foodName;
        $item = [
            "aliases" => []
        ];
        $data[$trueName] = $item;
    }

    $item =& $data[$trueName];
    $unit = $values["unit"];

    $item[$unit] = [
        "per" => $values["quantity"]
    ];

    foreach(LIST_VALUES as $nutritionalValueIndex) {
        $item[$unit][$nutritionalValueIndex] = $values[$nutritionalValueIndex];
    }

    // Save in data
    $bytesWritten = file_put_contents('../data/foodMacros.json', json_encode($data));
    if (! $bytesWritten) {
        return [
            "error" => "The item couldn't be written in foodMacros.json"
        ];
    }

    // Add the alias the the globalAliases file for later
    $aliasesData = loadJSONFile('../data/globalAliases.json');
    $aliasesData[$foodName] = $trueName;
    $bytesWritten = file_put_contents('../data/globalAliases.json', json_encode($aliasesData));
    if (! $bytesWritten) {
        return [
            "error" => "The item couldn't be written in globalAliases.json"
        ];
    }

    return 1;
}


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

$foodItem = $_GET['food'];
$foodItem = strtolower(trim($foodItem));
$unit = isset($_GET['unit']) ? $_GET['unit'] : "grams";
$nutritionalValues = getNutritionalValues($foodItem, $unit);
if ($nutritionalValues) {
    $nutritionalValues["meal_name"] = findFoodTrueName($foodItem);
    $nutritionalValues["unit"] = $unit;
    $nutritionalValues["metadata"] = [
        "from" => "database"
    ];
    echo json_encode($nutritionalValues);
    exit();
}

$nutritionalValues = getNutritionalValuesByAI($foodItem, $unit);
if ($nutritionalValues) {
    $addResult = addNutritionalValues($foodItem, $nutritionalValues);
    if ($addResult !== 1) {
        echo $addResult;
        exit();
    }
    $nutritionalValues["metadata"] = [
        "from" => "AI"
    ];
    echo json_encode($nutritionalValues);
    exit();
}

echo json_encode($nutritionalValues);
exit();