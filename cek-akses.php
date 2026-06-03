<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// If NOT logged in, throw back to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// SINGLE SESSION LOGIC (CHECK DATABASE VS CURRENT SESSION ID)
// This will trigger if the same user logs in on another device/browser
$stmt = $pdo->prepare("SELECT session_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_db = $stmt->fetch();

if (!$user_db || $user_db['session_id'] !== session_id()) {
    // Session has expired or another device has logged in
    session_unset();
    session_destroy();
    header("Location: login.php?error=expired");
    exit;
}

// Permission checking helper functions
function checkAdmin() {
    if ($_SESSION['role'] !== 'admin') {
        header("Location: index.php");
        exit;
    }
}

function checkUser() {
    if ($_SESSION['role'] !== 'user' && $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit;
    }
}