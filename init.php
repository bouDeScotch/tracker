<?php


if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}
require_once __DIR__ . '/config.default.php';


require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/jwt_helper.php';
