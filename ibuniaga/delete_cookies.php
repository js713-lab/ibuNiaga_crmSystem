<?php
require_once 'config.php';

// Delete all cookies except the cookie consent
function deleteAllCookies()
{
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach ($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            if ($name !== COOKIE_CONSENT) {
                setcookie($name, '', time() - 3600, '/');
                unset($_COOKIE[$name]);
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    deleteAllCookies();
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false]);
