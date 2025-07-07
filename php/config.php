<?php
/**
 * VYLO Database Configuration
 * Update these settings according to your Plesk hosting environment
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'vylo_store');
define('DB_USER', 'vylodma');
define('DB_PASS', 'M0l3y1993#][');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_URL', 'https://vylodma.com');
define('SITE_NAME', 'VYLO');
define('ADMIN_EMAIL', 'admin@vylodma.com');

// Security configuration
define('JWT_SECRET', 'your-super-secret-jwt-key-change-this-in-production');
define('PASSWORD_SALT', 'your-password-salt-change-this');
define('SESSION_LIFETIME', 86400); // 24 hours

// MoonPay configuration
define('MOONPAY_API_KEY', 'your-moonpay-api-key');
define('MOONPAY_SECRET_KEY', 'your-moonpay-secret-key');
define('MOONPAY_WEBHOOK_SECRET', 'your-moonpay-webhook-secret');

// Email configuration (for order confirmations)
define('SMTP_HOST', 'your-smtp-host');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-smtp-username');
define('SMTP_PASSWORD', 'your-smtp-password');
define('FROM_EMAIL', 'noreply@yourdomain.com');
define('FROM_NAME', 'VYLO Store');

// FedEx API configuration (for tracking)
define('FEDEX_API_KEY', 'your-fedex-api-key');
define('FEDEX_SECRET_KEY', 'your-fedex-secret-key');
define('FEDEX_ACCOUNT_NUMBER', 'your-fedex-account-number');

// File upload configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_PATH', '../uploads/');

// Error reporting (set to false in production)
define('DEBUG_MODE', true);

// CORS settings
define('ALLOWED_ORIGINS', [
    'https://yourdomain.com',
    'http://localhost:3000' // For development
]);

// Database connection class
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Utility functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function hashPassword($password) {
    return password_hash($password . PASSWORD_SALT, PASSWORD_HASH_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password . PASSWORD_SALT, $hash);
}

function generateJWT($payload) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode($payload);
    
    $headerEncoded = base64url_encode($header);
    $payloadEncoded = base64url_encode($payload);
    
    $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, JWT_SECRET, true);
    $signatureEncoded = base64url_encode($signature);
    
    return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
}

function verifyJWT($jwt) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        return false;
    }
    
    [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;
    
    $signature = base64url_decode($signatureEncoded);
    $expectedSignature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, JWT_SECRET, true);
    
    if (!hash_equals($signature, $expectedSignature)) {
        return false;
    }
    
    $payload = json_decode(base64url_decode($payloadEncoded), true);
    
    // Check expiration
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false;
    }
    
    return $payload;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    // CORS headers
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, ALLOWED_ORIGINS)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    
    echo json_encode($data);
    exit;
}

function getAuthUser() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return null;
    }
    
    $jwt = $matches[1];
    $payload = verifyJWT($jwt);
    
    if (!$payload) {
        return null;
    }
    
    return $payload;
}

function logError($message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if (!empty($context)) {
        $logMessage .= " Context: " . json_encode($context);
    }
    
    error_log($logMessage . PHP_EOL, 3, '../logs/error.log');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateUKPostcode($postcode) {
    $postcode = strtoupper(trim($postcode));
    $pattern = '/^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$/';
    return preg_match($pattern, $postcode);
}

function formatPrice($amount) {
    return '£' . number_format($amount, 2);
}

function generateOrderId() {
    return 'VYLO-' . date('Y') . '-' . sprintf('%06d', mt_rand(1, 999999));
}

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, ALLOWED_ORIGINS)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    http_response_code(200);
    exit;
}

// Set error reporting based on debug mode
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Ensure logs directory exists
if (!file_exists('../logs')) {
    mkdir('../logs', 0755, true);
}
?>