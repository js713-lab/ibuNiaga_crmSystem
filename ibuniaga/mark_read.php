<?php
require_once 'config.php';

if (!isLoggedIn() || !isset($_POST['message_id'])) {
    exit;
}

$message_id = sanitize_input($_POST['message_id']);
$stmt = $conn->prepare("UPDATE inbox SET is_read = 1 WHERE id = ? AND user_id = ?");
$stmt->bind_param("is", $message_id, $_SESSION['user_id']);
$stmt->execute();