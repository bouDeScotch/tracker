<?php

require_once __DIR__ . '/helpers.php';

function getUserInfo(string $email): array|null {
    $usersData = loadJSONFile(__DIR__ . '/../data/users.json');
    foreach ($usersData as $user) {
        if ($user['email'] === $email) {
            return $user;
        }
    }
    return null;
}