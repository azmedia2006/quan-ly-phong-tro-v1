<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

// If user is logged in, we could optionaly clear their session_id in DB,
// but it's not strictly necessary as login will overwrite it anyway.
// However, clearing it helps ensure the "single session" state is clean.
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET session_id = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        // Silent fail for logout DB cleanup
    }
}

// Clear all session data
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>