<?php

require_once __DIR__ . '/../init.php';

function generateJWT($payload) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode($payload);
    
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", JWT_SECRET, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
}

function decodeJWT($jwt) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        return ['error' => 'Invalid JWT format'];
    }

    [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

    $header = json_decode(base64_decode(strtr($base64UrlHeader, '-_', '+/')), true);
    if (!$header || !isset($header['alg']) || $header['alg'] !== 'HS256') {
        return ['error' => 'Invalid JWT header'];
    }
    if (!isset($header['typ']) || $header['typ'] !== 'JWT') {
        return ['error' => 'Invalid token type'];
    }
    
    $payload = json_decode(base64_decode(strtr($base64UrlPayload, '-_', '+/')), true);
    if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
        return ["error" => "Token expired or expiration time unspecified"];
    }

    $signatureProvided = base64_decode(strtr($base64UrlSignature, '-_', '+/'));

    $expectedSignature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", JWT_SECRET, true);
    if (!hash_equals($expectedSignature, $signatureProvided)) {
        return ['error' => 'Invalid JWT signature'];
    }

    return $payload;
}