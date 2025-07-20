<?php

if (!defined('JWT_SECRET'))
    define('JWT_SECRET', 'your-secret-key'); // Replace with your actual secret key

if (!defined('DATA_PATH')) 
    define('DATA_PATH', __DIR__ . '/data/'); // Path to your data directory