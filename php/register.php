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
    $requiredFields = ['first_name', 'last_name', 'email', 'password', 'address_line_1', 'city', 'postcode', 'county'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            sendJsonResponse(['success' => false, 'message' => "Field '$field' is required"], 400);
        }
    }
    
    // Sanitize inputs
    $userData = [
        'first_name' => sanitizeInput($input['first_name']),
        'last_name' => sanitizeInput($input['last_name']),
        'email' => sanitizeInput(strtolower($input['email'])),
        'password' => $input['password'],
        'phone' => isset($input['phone']) ? sanitizeInput($input['phone']) : null,
        'company' => isset($input['company']) ? sanitizeInput($input['company']) : null,
        'address_line_1' => sanitizeInput($input['address_line_1']),
        'address_line_2' => isset($input['address_line_2']) ? sanitizeInput($input['address_line_2']) : null,
        'city' => sanitizeInput($input['city']),
        'postcode' => sanitizeInput(strtoupper($input['postcode'])),
        'county' => sanitizeInput($input['county']),
        'country' => isset($input['country']) ? sanitizeInput($input['country']) : 'GB',
        'newsletter_subscribed' => isset($input['newsletter']) ? (bool)$input['newsletter'] : false
    ];
    
    // Validate email format
    if (!validateEmail($userData['email'])) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
    }
    
    // Validate UK postcode
    if (!validateUKPostcode($userData['postcode'])) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid UK postcode format'], 400);
    }
    
    // Validate password strength
    if (strlen($userData['password']) < 8) {
        sendJsonResponse(['success' => false, 'message' => 'Password must be at least 8 characters long'], 400);
    }
    
    // Check if password contains at least one letter and one number
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)/', $userData['password'])) {
        sendJsonResponse(['success' => false, 'message' => 'Password must contain at least one letter and one number'], 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$userData['email']]);
    if ($stmt->fetch()) {
        sendJsonResponse(['success' => false, 'message' => 'Email address already registered'], 409);
    }
    
    // Hash password
    $passwordHash = hashPassword($userData['password']);
    
    // Insert user
    $stmt = $db->prepare("
        INSERT INTO users (
            email, password_hash, first_name, last_name, phone, company,
            address_line_1, address_line_2, city, postcode, county, country,
            newsletter_subscribed, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        $userData['email'],
        $passwordHash,
        $userData['first_name'],
        $userData['last_name'],
        $userData['phone'],
        $userData['company'],
        $userData['address_line_1'],
        $userData['address_line_2'],
        $userData['city'],
        $userData['postcode'],
        $userData['county'],
        $userData['country'],
        $userData['newsletter_subscribed']
    ]);
    
    if ($result) {
        $userId = $db->lastInsertId();
        
        // Log successful registration
        logError("User registered successfully", ['user_id' => $userId, 'email' => $userData['email']]);
        
        // Send welcome email (optional)
        try {
            sendWelcomeEmail($userData['email'], $userData['first_name']);
        } catch (Exception $e) {
            // Don't fail registration if email fails
            logError("Failed to send welcome email", ['error' => $e->getMessage(), 'email' => $userData['email']]);
        }
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Registration successful! Please log in to continue.',
            'user_id' => $userId
        ], 201);
    } else {
        throw new Exception('Failed to create user account');
    }
    
} catch (PDOException $e) {
    logError("Database error during registration", ['error' => $e->getMessage()]);
    sendJsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    logError("Registration error", ['error' => $e->getMessage()]);
    sendJsonResponse(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()], 500);
}

function sendWelcomeEmail($email, $firstName) {
    $subject = "Welcome to VYLO - Your Premium Hardware Store";
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background-color: #007acc; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f4f4f4; padding: 15px; text-align: center; font-size: 12px; }
            .button { background-color: #007acc; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Welcome to V<span style='color: #0099ff;'>Y</span>LO</h1>
        </div>
        <div class='content'>
            <h2>Hello $firstName,</h2>
            <p>Thank you for registering with VYLO, your premium hardware solutions provider!</p>
            <p>We're excited to have you join our community of technology enthusiasts and professionals.</p>
            
            <h3>What's Next?</h3>
            <ul>
                <li>Browse our extensive range of premium hardware components</li>
                <li>Enjoy next-day delivery across the UK</li>
                <li>Access exclusive member pricing and early product releases</li>
                <li>Get expert technical support from our team</li>
            </ul>
            
            <a href='" . SITE_URL . "/hardware.html' class='button'>Start Shopping</a>
            
            <p>If you have any questions, our support team is here to help at <a href='mailto:" . ADMIN_EMAIL . "'>" . ADMIN_EMAIL . "</a></p>
            
            <p>Best regards,<br>The VYLO Team</p>
        </div>
        <div class='footer'>
            <p>&copy; " . date('Y') . " VYLO. All rights reserved.<br>
            Premium hardware solutions delivered with excellence across the UK.</p>
        </div>
    </body>
    </html>
    ";
    
    // Add to email queue instead of sending immediately
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        INSERT INTO email_queue (recipient_email, subject, body, type, status, created_at)
        VALUES (?, ?, ?, 'welcome', 'pending', NOW())
    ");
    $stmt->execute([$email, $subject, $body]);
}
?>