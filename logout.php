<?php
// logout.php - User Logout
require_once 'config.php';

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Start a new session for the redirect message
session_start();
$_SESSION['logout_message'] = 'You have been successfully logged out.';

// Redirect to homepage
redirect('index.php');
?>