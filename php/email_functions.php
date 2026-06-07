<?php
// PHPMailer setup
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
    
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Function to send email to admin
function sendAdminEmail($data, $submission_id) {
    // Start output buffering to capture the template
    ob_start();
    
    // Set variables for the template
    $qualification_score = $data['qualificationScore'];
    
    // Include the template (it will be captured in the buffer)
    include 'admin_email_template.php';
    
    // Get the captured template content
    $body = ob_get_clean();
    
    try {
        // Server settings
        $mail = new PHPMailer(true);
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
        $mail->setFrom('no-reply@thekconsult.co.za', 'Consultation Form');
        $mail->addAddress('info@thekconsult.co.za', 'Info');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Consultation Request #' . $submission_id;
        $mail->Body = $body;
        $mail->AltBody = generatePlainTextFromHTML($body);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Admin email failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Function to send confirmation email to client
function sendClientEmail($data, $submission_id) {
    // Start output buffering to capture the template
    ob_start();
    
    // Include the template (it will be captured in the buffer)
    include 'client_email_template.php';
    
    // Get the captured template content
    $body = ob_get_clean();
    
    
    try {
        // Server settings
        $mail = new PHPMailer(true);
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
        $mail->setFrom('no-reply@thekconsult.co.za', 'KConsulting Firm');
        $mail->addAddress($data['email'], $data['name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Consultation Request Confirmation #' . $submission_id;
        $mail->Body = $body;
        $mail->AltBody = generatePlainTextFromHTML($body);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Client email failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Helper function to generate plain text from HTML
function generatePlainTextFromHTML($html) {
    // Remove style and script tags
    $plain_text = preg_replace('/<style.*?<\/style>|<script.*?<\/script>/s', '', $html);
    
    // Convert HTML entities
    $plain_text = html_entity_decode($plain_text);
    
    // Convert <br> tags to newlines
    $plain_text = preg_replace('/<br\s*\/?>/i', "\n", $plain_text);
    
    // Remove all other HTML tags
    $plain_text = strip_tags($plain_text);
    
    // Trim multiple consecutive newlines
    $plain_text = preg_replace("/\n{3,}/", "\n\n", $plain_text);
    
    // Trim whitespace
    $plain_text = trim($plain_text);
    
    return $plain_text;
}
?>