<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ibuniaga');

// Application configuration
define('SITE_URL', 'http://localhost/webdev202408/ibuniaga');
define('UPLOAD_PATH', __DIR__ . '/uploads');
define('DEFAULT_LANG', 'en');
define('REMEMBER_COOKIE_NAME', 'ibuniaga_remember');
define('REMEMBER_COOKIE_DURATION', 60 * 60 * 24 * 90); // 90 days

// Establish database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Helper functions
function sanitize_input($data)
{
    global $conn;
    return $conn->real_escape_string(trim($data));
}

function generateUniqueID($prefix, $table, $field)
{
    global $conn;
    do {
        $number = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $id = $prefix . $number;
        $result = $conn->query("SELECT $field FROM $table WHERE $field = '$id'");
    } while ($result->num_rows > 0);

    return $id;
}

define('COOKIE_CONSENT', 'ibuniaga_cookie_consent');
define('COOKIE_DURATION', 60 * 60 * 24 * 90); // 90 days

// Function to check cookie consent
function hasCookieConsent()
{
    return isset($_COOKIE[COOKIE_CONSENT]) && $_COOKIE[COOKIE_CONSENT] === 'accepted';
}

// Function to handle cookie deletion
function deleteCookies()
{
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach ($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time() - 3600, '/');
        }
    }
}

function validateUsername($username)
{
    return preg_match('/^[a-zA-Z0-9@._-]{3,50}$/', $username);
}

function validatePassword($password)
{
    return strlen($password) >= 8;
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function getUserRole()
{
    return $_SESSION['role'] ?? 'guest';
}

function checkPermission($required_role)
{
    $user_role = getUserRole();
    if ($required_role === 'admin' && $user_role !== 'admin') {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
    if ($required_role === 'moderator' && !in_array($user_role, ['admin', 'moderator'])) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

function loadLanguage($lang = null) {
    if (!$lang) {
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : DEFAULT_LANG;
    }
    
    $langFile = __DIR__ . "/languages/$lang.txt";
    
    if (file_exists($langFile)) {
        $translations = parse_ini_file($langFile);
        if ($translations === false) {
            error_log("Failed to parse language file: $langFile");
            return [];
        }
        return $translations;
    }
    
    error_log("Language file not found: $langFile");
    $defaultFile = __DIR__ . "/languages/" . DEFAULT_LANG . ".txt";
    $translations = parse_ini_file($defaultFile);
    return $translations !== false ? $translations : [];
}

function __($key) {
    static $translations = null;
    
    if ($translations === null) {
        $translations = loadLanguage();
    }
    
    return isset($translations[$key]) ? $translations[$key] : $key;
}

function logActivity($user_id, $action, $details = '')
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("ssss", $user_id, $action, $details, $ip);
    $stmt->execute();
}

function isAllowedFile($filename)
{
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($file_extension, $allowed_extensions);
}

function formatDate($date)
{
    return date('Y-m-d H:i', strtotime($date));
}

// Auto-login from remember cookie
if (!isLoggedIn() && isset($_COOKIE[REMEMBER_COOKIE_NAME])) {
    $token = $_COOKIE[REMEMBER_COOKIE_NAME];
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE remember_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
    }
}
