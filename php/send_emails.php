<?php
/**
 * VYLO Email Sender
 * This script processes the email queue and sends pending emails
 * Run this as a cron job every few minutes
 */

require_once 'config.php';

// Check if running from command line or web
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    // If accessed via web, require admin authentication
    $authUser = getAuthUser();
    if (!$authUser || $authUser['email'] !== ADMIN_EMAIL) {
        sendJsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
    }
}

try {
    $emailsSent = 0;
    $emailsFailed = 0;
    $maxEmails = 10; // Limit emails per run to avoid timeouts
    
    $db = Database::getInstance()->getConnection();
    
    // Get pending emails
    $stmt = $db->prepare("
        SELECT id, recipient_email, subject, body, type, order_id, user_id, attempts
        FROM email_queue 
        WHERE status = 'pending' AND attempts < 3
        ORDER BY created_at ASC 
        LIMIT ?
    ");
    $stmt->execute([$maxEmails]);
    $emails = $stmt->fetchAll();
    
    foreach ($emails as $email) {
        try {
            // Attempt to send email
            $success = sendEmail($email['recipient_email'], $email['subject'], $email['body']);
            
            if ($success) {
                // Mark as sent
                $stmt = $db->prepare("
                    UPDATE email_queue 
                    SET status = 'sent', sent_at = NOW(), attempts = attempts + 1
                    WHERE id = ?
                ");
                $stmt->execute([$email['id']]);
                $emailsSent++;
                
                if ($isCli) {
                    echo "✓ Sent email to {$email['recipient_email']} - {$email['subject']}\n";
                }
            } else {
                throw new Exception('Failed to send email');
            }
            
        } catch (Exception $e) {
            // Increment attempt count
            $stmt = $db->prepare("
                UPDATE email_queue 
                SET attempts = attempts + 1, status = CASE WHEN attempts >= 2 THEN 'failed' ELSE 'pending' END
                WHERE id = ?
            ");
            $stmt->execute([$email['id']]);
            $emailsFailed++;
            
            logError("Email sending failed", [
                'email_id' => $email['id'],
                'recipient' => $email['recipient_email'],
                'error' => $e->getMessage(),
                'attempts' => $email['attempts'] + 1
            ]);
            
            if ($isCli) {
                echo "✗ Failed to send email to {$email['recipient_email']} - {$e->getMessage()}\n";
            }
        }
        
        // Small delay between emails to be respectful to mail servers
        usleep(500000); // 0.5 seconds
    }
    
    if ($isCli) {
        echo "\nEmail processing complete:\n";
        echo "- Emails sent: $emailsSent\n";
        echo "- Emails failed: $emailsFailed\n";
        echo "- Emails processed: " . ($emailsSent + $emailsFailed) . "\n";
    } else {
        sendJsonResponse([
            'success' => true,
            'emails_sent' => $emailsSent,
            'emails_failed' => $emailsFailed,
            'emails_processed' => $emailsSent + $emailsFailed
        ]);
    }
    
} catch (Exception $e) {
    logError("Email processing error", ['error' => $e->getMessage()]);
    
    if ($isCli) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    } else {
        sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

function sendEmail($to, $subject, $htmlBody) {
    // For basic PHP mail() function (works on most shared hosting)
    if (function_exists('mail') && !defined('SMTP_HOST')) {
        return sendEmailBasic($to, $subject, $htmlBody);
    }
    
    // For SMTP (if configured)
    if (defined('SMTP_HOST') && SMTP_HOST) {
        return sendEmailSMTP($to, $subject, $htmlBody);
    }
    
    throw new Exception('No email sending method configured');
}

function sendEmailBasic($to, $subject, $htmlBody) {
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . FROM_NAME . ' <' . FROM_EMAIL . '>',
        'Reply-To: ' . FROM_EMAIL,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($to, $subject, $htmlBody, implode("\r\n", $headers));
}

function sendEmailSMTP($to, $subject, $htmlBody) {
    // Basic SMTP implementation
    // For production, consider using PHPMailer or SwiftMailer
    
    $boundary = md5(time());
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        'From: ' . FROM_NAME . ' <' . FROM_EMAIL . '>',
        'Reply-To: ' . FROM_EMAIL
    ];
    
    // Create plain text version from HTML
    $plainText = html_to_plain_text($htmlBody);
    
    $message = "--$boundary\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $plainText . "\r\n\r\n";
    
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $htmlBody . "\r\n\r\n";
    
    $message .= "--$boundary--";
    
    // Use PHP's mail function with SMTP headers
    // In production, implement proper SMTP connection
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

function html_to_plain_text($html) {
    // Simple HTML to plain text conversion
    $text = $html;
    
    // Replace common HTML elements
    $text = str_replace(['<br>', '<br/>', '<br />'], "\n", $text);
    $text = str_replace(['</p>', '</div>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>'], "\n\n", $text);
    $text = str_replace(['</li>'], "\n", $text);
    
    // Remove HTML tags
    $text = strip_tags($text);
    
    // Clean up whitespace
    $text = preg_replace('/\n\s*\n/', "\n\n", $text);
    $text = trim($text);
    
    return $text;
}

// Clean up old emails (older than 30 days)
function cleanupOldEmails() {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM email_queue WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
        
        $deletedCount = $stmt->rowCount();
        if ($deletedCount > 0) {
            logError("Cleaned up old emails", ['deleted_count' => $deletedCount]);
        }
    } catch (Exception $e) {
        logError("Error cleaning up old emails", ['error' => $e->getMessage()]);
    }
}

// Run cleanup occasionally
if (rand(1, 100) <= 5) { // 5% chance
    cleanupOldEmails();
}
?>