<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lang'])) {
    $allowed_languages = ['en', 'ms', 'zh'];
    $lang = $_POST['lang'];
    
    if (in_array($lang, $allowed_languages)) {
        $_SESSION['lang'] = $lang;
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false]);
exit;