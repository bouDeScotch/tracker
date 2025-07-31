<?php
require_once __DIR__ . '/../init.php';

class NutritionalValuesHandler {
    private const LIST_VALUES = ["kcal", "prot", "carbs", "fats"];

    public static function findFood(string $foodName, array $data): ?array {
        $aliasesMap = loadJSONFile(DATA_PATH . "/globalAliases.json");
        $foodName = strtolower(trim($foodName));
        if (isset($aliasesMap[$foodName])) {
            $foodName = $aliasesMap[$foodName];
        }
        return $data[$foodName] ?? null;
    }

    public static function findTrueName(string $foodName): ?string {
        $aliasesData = loadJSONFile(DATA_PATH . '/globalAliases.json');
        return isset($aliasesData[$foodName]) ? $aliasesData[$foodName] : null;
    }

    public static function getNutritionalValues(string $foodItem, string $unit = "grams"): ?array {
        $data = loadJSONFile(DATA_PATH . '/foodMacros.json');
        $foodItem = strtolower(trim($foodItem));
        $foodData = self::findFood($foodItem, $data);
        return $foodData[$unit] ?? null;
    }

    private static function loadPrompt(string $path, array $vars): string {
        $prompt = file_get_contents($path);
        foreach ($vars as $key => $value) {
            $prompt = str_replace("{{$key}}", $value, $prompt);
        }
        return $prompt;
    }

    public static function getNutritionalValuesByAI(string $foodItem, string $unit = "grams", string $promptPath = DATA_PATH . "/prompt.txt") {
        $apiKey = loadJSONFile('../data/APIKeys.json')['openai'] ?? '';
        $url = 'https://api.openai.com/v1/responses';

        $text = self::loadPrompt($promptPath, [
            'meal_name' => $foodItem,
            'unit' => $unit
        ]);

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
            return ["error" => "cURL error: " . curl_error($ch)];
        }

        curl_close($ch);
        $responseData = json_decode($response, true);

        if (empty($responseData["output"])) {
            return ["error" => "Pas de contenu texte dans la reponse"];
        }

        foreach ($responseData["output"] as $msg) {
            $content = $msg["content"][0] ?? null;

            if (!is_array($content)) {
                continue;
            }

            if (($content["type"] ?? null) !== "output_text") {
                continue;
            }

            $jsonString = $content["text"] ?? null;
            if (!$jsonString) {
                continue;
            }

            $parsed = json_decode($jsonString, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            }

            return [
                "error" => "Erreur lors du parse JSON: " . json_last_error_msg(),
                "message" => $jsonString
            ];
        }

        return ["error" => "Pas de contenu texte dans la réponse"];
    }

    public static function addNutritionalValues(string $foodItem, array $values) {
        $data = loadJSONFile(DATA_PATH . "/foodMacros.json");

        $foodName = strtolower(trim($foodItem));
        $trueName = findFoodTrueName($foodName);
        if ($trueName === null) {
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

        foreach (self::LIST_VALUES as $nutritionalValueIndex) {
            $item[$unit][$nutritionalValueIndex] = $values[$nutritionalValueIndex];
        }
        
        $result = saveJSONFile(DATA_PATH . "/foodMacros.json", $data);
        if ($result !== true) {
            return [
                "error" => "The item couldn't be written in foodMacros.json"
            ];
        }

        $aliasesData = loadJSONFile(DATA_PATH . "/globalAliases.json");
        $aliasesData[$foodName] = $trueName;
        $result = saveJSONFile(DATA_PATH . "/globalAliases.json", $aliasesData);
        if ($result !== true) {
            return [
                "error" => "The item couldn't be written in globalAliases.json"
            ];
        }

        return 1;
    }

    public static function handleNutritionalQuery(string $foodItem, string $unit = "grams"): array {
        $foodItem = strtolower(trim($foodItem));

        $values = self::getNutritionalValues($foodItem, $unit);
        if ($values) {
            return array_merge($values, [
                "meal_name" => self::findTrueName($foodItem),
                "unit" => $unit,
                "metadata" => ["from" => "database"]
            ]);
        }

        $values = self::getNutritionalValuesByAI($foodItem, $unit);
        if (!$values) {
            return ["error" => "Couldn't access nutritional values by AI"];
        }
        $addResult = self::addNutritionalValues($foodItem, $values);
        if ($addResult !== 1) {
            return is_array($addResult) ? $addResult : ["error" => "Error during data saving of new data"];
        }

        return array_merge($values,
            "metadata" => ["from" => "AI"]
        ]);
    }
}
