<?php
require_once 'config.php';

// Clear all session variables
$_SESSION = array();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Delete remember me cookie if it exists
if (isset($_COOKIE[REMEMBER_COOKIE_NAME])) {
    setcookie(REMEMBER_COOKIE_NAME, '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();