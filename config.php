<?php
// config.php - Configuration file for VYLO website

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'vylo_store');
define('DB_USER', 'vylo');  // Change this
define('DB_PASS', 'uolojill669474');  // Change this

// Site Configuration
define('SITE_NAME', 'VYLO');
define('SITE_URL', 'https://vylodma.com');  // Change this
define('SITE_EMAIL', 'admin@vylo.co.uk');   // Change this
define('ADMIN_EMAIL', 'admin@vylo.co.uk');   // Change this

// Payment Configuration
define('CRYPTO_WALLET_ADDRESS', 'your_crypto_wallet_address');  // Change this
define('MOONPAY_API_KEY', 'your_moonpay_api_key');  // Change this

// Email Configuration (SMTP)
define('SMTP_HOST', 'your_smtp_host');     // Change this
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_smtp_username');  // Change this
define('SMTP_PASSWORD', 'your_smtp_password');  // Change this

// FedEx Tracking Configuration
define('FEDEX_TRACKING_URL', 'https://www.fedex.com/fedextrack/?trknbr=');

// Cart abandonment email delay (in seconds) - 1 hour = 3600
define('CART_ABANDONMENT_DELAY', 3600);

// Database Connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper Functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_order_number() {
    return 'VYLO-' . strtoupper(uniqid());
}

function send_email($to, $subject, $message, $is_html = true) {
    require_once 'includes/PHPMailer/PHPMailer.php';
    require_once 'includes/PHPMailer/SMTP.php';
    require_once 'includes/PHPMailer/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom(SITE_EMAIL, SITE_NAME);
        $mail->addAddress($to);
        
        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_cart_count() {
    global $pdo;
    
    if (is_logged_in()) {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE session_id = ?");
        $stmt->execute([session_id()]);
    }
    
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

function format_price($price) {
    return '£' . number_format($price, 2);
}

function redirect($url) {
    header("Location: $url");
    exit();
}
?>