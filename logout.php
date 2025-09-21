<?php
require 'config.php'; // to start session if not already

// Unset all session variables
$_SESSION = [];

// Destroy the session completely
session_destroy();

// Optionally delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page
header('Location: index.php');
exit;
