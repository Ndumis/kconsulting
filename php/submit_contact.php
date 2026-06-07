<?php
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// Response array
$response = ['success' => false, 'message' => ''];

try {
    // Get JSON data from AJAX request
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('No data received');
    }
    
    // Validate required fields
    if (empty($input['name']) || trim($input['name']) === '') {
        throw new Exception('Please enter your name.');
    }
    
    if (empty($input['email']) || trim($input['email']) === '') {
        throw new Exception('Please enter your email address.');
    }
    
    if (empty($input['message']) || trim($input['message']) === '') {
        throw new Exception('Please enter your message.');
    }
    
    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address.');
    }
    
    // Sanitize inputs
    $name = htmlspecialchars(trim($input['name']), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
    $company = isset($input['company']) ? htmlspecialchars(trim($input['company']), ENT_QUOTES, 'UTF-8') : '';
    $service = isset($input['service']) ? htmlspecialchars(trim($input['service']), ENT_QUOTES, 'UTF-8') : '';
    $message = htmlspecialchars(trim($input['message']), ENT_QUOTES, 'UTF-8');
    
    // Log the enquiry
    $logFile = __DIR__ . '/contact_enquiries.log';
    $loggingData = [
        'name' => $name,
        'email' => $email,
        'company' => $company,
        'service' => $service,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    file_put_contents(
        $logFile,
        json_encode($loggingData) . PHP_EOL,
        FILE_APPEND
    );
    
    // Send emails
    $adminEmailResult = sendContactAdminEmail($name, $email, $company, $service, $message);
    $clientEmailResult = sendContactClientEmail($name, $email, $company, $service, $message);
    
    if (!$adminEmailResult) {
        error_log("Failed to send admin email for contact form submission from: " . $email);
    }
    
    if (!$clientEmailResult) {
        error_log("Failed to send client email for contact form submission to: " . $email);
    }
    
    $response['success'] = true;
    $response['message'] = 'Thank you for your message! We will get back to you within 24 hours.';
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Contact form error: ' . $e->getMessage());
}

echo json_encode($response);

// Function to send email to admin using template
function sendContactAdminEmail($name, $email, $company, $service, $message) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = 'fyre.aserv.co.za';
        $mail->SMTPAuth = true;
        $mail->Username = 'mail@thekconsult.co.za';
        $mail->Password = '8GAzt_-NK=#7}SE]';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;     
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom('no-reply@thekconsult.co.za', 'KConsulting Contact Form');
        $mail->addAddress('info@thekconsult.co.za', 'KConsulting Admin');
        $mail->addReplyTo($email, $name);
        
        // Content - use template
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Form Message from ' . $name;
        
        // Start output buffering to capture template
        ob_start();
        include 'contact_admin_email_template.php';
        $body = ob_get_clean();
        
        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $body));
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Contact admin email failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Function to send confirmation email to client using template
function sendContactClientEmail($name, $email, $company, $service, $message) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = 'fyre.aserv.co.za';
        $mail->SMTPAuth = true;
        $mail->Username = 'mail@thekconsult.co.za';
        $mail->Password = '8GAzt_-NK=#7}SE]';
        $mail->Port = 465;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom('no-reply@thekconsult.co.za', 'KConsulting Firm');
        $mail->addAddress($email, $name);
        
        // Content - use template
        $mail->isHTML(true);
        $mail->Subject = 'Thank you for contacting KConsulting';
        
        // Start output buffering to capture template
        ob_start();
        include 'contact_client_email_template.php';
        $body = ob_get_clean();
        
        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $body));
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Contact client email failed: " . $mail->ErrorInfo);
        return false;
    }
}
?>