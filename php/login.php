<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid JSON input'], 400);
    }
    
    // Validate required fields
    if (empty($input['email']) || empty($input['password'])) {
        sendJsonResponse(['success' => false, 'message' => 'Email and password are required'], 400);
    }
    
    $email = sanitizeInput(strtolower($input['email']));
    $password = $input['password'];
    $rememberMe = isset($input['remember_me']) ? (bool)$input['remember_me'] : false;
    
    // Validate email format
    if (!validateEmail($email)) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Get user from database
    $stmt = $db->prepare("
        SELECT id, email, password_hash, first_name, last_name, phone, company,
               address_line_1, address_line_2, city, postcode, county, country,
               status, created_at, last_login
        FROM users 
        WHERE email = ? AND status = 'active'
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Log failed login attempt
        logError("Failed login attempt - user not found", ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
        sendJsonResponse(['success' => false, 'message' => 'Invalid email or password'], 401);
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password_hash'])) {
        // Log failed login attempt
        logError("Failed login attempt - wrong password", ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
        sendJsonResponse(['success' => false, 'message' => 'Invalid email or password'], 401);
    }
    
    // Update last login
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Generate JWT token
    $tokenExpiry = $rememberMe ? time() + (30 * 24 * 60 * 60) : time() + SESSION_LIFETIME; // 30 days if remember me, otherwise 24 hours
    
    $payload = [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'iat' => time(),
        'exp' => $tokenExpiry
    ];
    
    $token = generateJWT($payload);
    
    // Create session record
    $sessionToken = generateToken();
    $stmt = $db->prepare("
        INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address, user_agent)
        VALUES (?, ?, FROM_UNIXTIME(?), ?, ?)
    ");
    $stmt->execute([
        $user['id'],
        $sessionToken,
        $tokenExpiry,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    // Clean up expired sessions
    $stmt = $db->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
    $stmt->execute();
    
    // Prepare user data for response (excluding sensitive information)
    $userData = [
        'id' => $user['id'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'phone' => $user['phone'],
        'company' => $user['company'],
        'address_line_1' => $user['address_line_1'],
        'address_line_2' => $user['address_line_2'],
        'city' => $user['city'],
        'postcode' => $user['postcode'],
        'county' => $user['county'],
        'country' => $user['country'],
        'member_since' => $user['created_at'],
        'last_login' => $user['last_login']
    ];
    
    // Log successful login
    logError("User logged in successfully", ['user_id' => $user['id'], 'email' => $email]);
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Login successful',
        'user' => $userData,
        'token' => $token,
        'expires_at' => date('c', $tokenExpiry)
    ]);
    
} catch (PDOException $e) {
    logError("Database error during login", ['error' => $e->getMessage()]);
    sendJsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    logError("Login error", ['error' => $e->getMessage()]);
    sendJsonResponse(['success' => false, 'message' => 'Login failed: ' . $e->getMessage()], 500);
}
?>