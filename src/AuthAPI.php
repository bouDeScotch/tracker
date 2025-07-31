<?php

require_once __DIR__ . '/../init.php';

class AuthAPI {
    public static function login($email, $password) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format");
        }

        $userInfos = getUserInfo($email);
        if ($userInfos === null) {
            throw new RuntimeException("User not found", 404);
        }

        if (!password_verify($password, $userInfos["password"])) {
            throw new RuntimeException("Invalid password", 401);
        }

        $jwt = generateJWT([
            'id' => $userInfos['id'],
            'email' => $email,
            'exp' => time() + 3600
        ]);

        if ($jwt == false) {
            throw new RuntimeException("Failed to generate JWT", 500);
        }

        return $jwt;
    }
}
