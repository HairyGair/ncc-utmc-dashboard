<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'gairware_utmc');
define('DB_USER', 'gairware_utmc');
define('DB_PASS', 'CHANGE_ME');

// Auth
define('AUTH_HASH', '$2y$12$QQXPSdzEx07t5dLpb6wBB.4/qK0GXVB4kplzu0DDSRx8GuCKSK18S');
define('SESSION_LIFETIME', 86400); // 24 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_MINUTES', 15);

function get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    return $pdo;
}
