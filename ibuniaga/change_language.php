<?php
session_start();

if (isset($_POST['lang'])) {
    $_SESSION['lang'] = $_POST['lang'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}