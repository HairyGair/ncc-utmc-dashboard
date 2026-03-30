<?php

declare(strict_types=1);

require_once __DIR__ . '/api/config.php';

// Secure session settings
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
ini_set('session.cookie_lifetime', (string) SESSION_LIFETIME);

session_start();

// Check idle timeout (4 hours)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 14400)) {
    session_unset();
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}

// Check if authenticated
if (empty($_SESSION['authenticated'])) {
    $return = $_SERVER['REQUEST_URI'] ?? '';
    header('Location: login.php' . ($return ? '?return=' . urlencode($return) : ''));
    exit;
}

$_SESSION['last_activity'] = time();
