<?php

require_once __DIR__ . "/../init.php";

class WeightAPI {
    public static function getWeights($email, $max = null, $dataPath) {
        $weightsData = loadJSONFile($dataPath);
        if (! isset($weightsData[$email])) {
            return ["amount" => 0, "data" => []];
        }

        $weights = $weightsData[$email];
        for ($i = 0; $i < count($weights); $i++) {
            if (! isset($weights[$i]['id'])) {
                $weights[$i]['id'] = $i;
            } else {
                break;
                // Break because now id are stored in the database, and array is sorted so once an 
                // id is met, there is no need to check further
            }
        }

        if ($max === null) {
            $max = count($weights);
        }

        return [
            "amount" => min($max, count($weights)),
            "data" => array_slice($weights, -$max)
        ];
    }

    public static function logWeight($email, $weight, $date, $dataPath) {
        if (!is_numeric($weight)) {
            throw new InvalidArgumentException("Weight should be a number");
        }
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException("Invalid date format, expected Y-m-d");
        }

        $weightData = loadJSONFile($dataPath);
        if (! isset($weightData[$email])) {
            $weightData[$email] = [];
        }

        $id = count($weightData[$email]);
        $weightData[$email][] = [
            "date" => $date,
            "weight" => $weight,
            "id" => $id
        ];

        saveJSONFile($dataPath, $weightData);
        return true;
    }
}
