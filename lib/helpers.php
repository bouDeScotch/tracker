<?php

function loadJSONFile(string $filePath): array|string {
    if (!file_exists($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    return json_decode($content, true) ?? [];
}

function saveJSONFile(string $filePath, array $data): bool|string {
    $result = file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    
    if (! $result) {
        return "Error : file " . $filePath . " couldn't be edited";
    }

    return true;
}