<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'vylo_store';
    private $username = 'vylodma';
    private $password = 'M0l3y1993#][';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// GENERATE A NEW RANDOM SECRET KEY FOR PRODUCTION!
define('JWT_SECRET_KEY', 'vylo_jwt_secret_2025_change_this_in_production_k3y9x8m2n4v7b1c6');

// JWT Helper functions
function generateJWT($user_id) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'user_id' => $user_id,
        'exp' => time() + (24 * 60 * 60), // 24 hours
        'iat' => time() // issued at
    ]);
    
    $headerEncoded = base64url_encode($header);
    $payloadEncoded = base64url_encode($payload);
    $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, JWT_SECRET_KEY, true);
    $signatureEncoded = base64url_encode($signature);
    
    return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
}

function verifyJWT($jwt) {
    if (empty($jwt)) return false;
    
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return false;
    
    $header = base64url_decode($parts[0]);
    $payload = base64url_decode($parts[1]);
    $signature = base64url_decode($parts[2]);
    
    $expectedSignature = hash_hmac('sha256', $parts[0] . "." . $parts[1], JWT_SECRET_KEY, true);
    
    if (!hash_equals($signature, $expectedSignature)) return false;
    
    $payloadData = json_decode($payload, true);
    if (!$payloadData || $payloadData['exp'] < time()) return false;
    
    return $payloadData;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

// Enhanced getallheaders function for compatibility
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

// CORS headers with error handling
function setCorsHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(200);
        exit(0);
    }
}

// Error logging function
function logError($message, $context = []) {
    $logEntry = date('[Y-m-d H:i:s] ') . $message;
    if (!empty($context)) {
        $logEntry .= ' | Context: ' . json_encode($context);
    }
    error_log($logEntry);
}

// Enhanced authentication check
function requireAuth() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authorization header missing or invalid']);
        exit;
    }
    
    $token = substr($authHeader, 7);
    $payload = verifyJWT($token);
    
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
        exit;
    }
    
    return $payload;
}

// Set CORS headers by default
setCorsHeaders();
?>