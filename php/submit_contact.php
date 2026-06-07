<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/mailer.php';

$response = ['success' => false, 'message' => ''];

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('No data received');
    }

    if (empty($input['name']) || trim($input['name']) === '') {
        throw new Exception('Please enter your name.');
    }

    if (empty($input['email']) || trim($input['email']) === '') {
        throw new Exception('Please enter your email address.');
    }

    if (empty($input['message']) || trim($input['message']) === '') {
        throw new Exception('Please enter your message.');
    }

    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address.');
    }

    $name    = htmlspecialchars(trim($input['name']),    ENT_QUOTES, 'UTF-8');
    $email   = filter_var(trim($input['email']),         FILTER_SANITIZE_EMAIL);
    $company = isset($input['company']) ? htmlspecialchars(trim($input['company']), ENT_QUOTES, 'UTF-8') : '';
    $service = isset($input['service']) ? htmlspecialchars(trim($input['service']), ENT_QUOTES, 'UTF-8') : '';
    $message = htmlspecialchars(trim($input['message']), ENT_QUOTES, 'UTF-8');

    // Log the enquiry
    $logData = ['name' => $name, 'email' => $email, 'company' => $company, 'service' => $service, 'timestamp' => date('Y-m-d H:i:s')];
    file_put_contents(__DIR__ . '/contact_enquiries.log', json_encode($logData) . PHP_EOL, FILE_APPEND);

    sendContactAdminEmail($name, $email, $company, $service, $message);
    sendContactClientEmail($name, $email, $company, $service, $message);

    $response['success'] = true;
    $response['message'] = 'Thank you for your message! We will get back to you within 24 hours.';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Contact form error: ' . $e->getMessage());
}

echo json_encode($response);

function sendContactAdminEmail($name, $email, $company, $service, $message) {
    try {
        $mail = createMailer();
        $mail->setFrom(MAIL_FROM, 'KConsulting Contact Form');
        $mail->addAddress(MAIL_ADMIN_ADDR, MAIL_ADMIN_NAME . ' Admin');
        $mail->addReplyTo($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Form Message from ' . $name;
        ob_start();
        include __DIR__ . '/contact_admin_email_template.php';
        $body = ob_get_clean();
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $body));
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Contact admin email failed: ' . $mail->ErrorInfo);
        return false;
    }
}

function sendContactClientEmail($name, $email, $company, $service, $message) {
    try {
        $mail = createMailer();
        $mail->setFrom(MAIL_FROM, 'KConsulting Firm');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Thank you for contacting KConsulting';
        ob_start();
        include __DIR__ . '/contact_client_email_template.php';
        $body = ob_get_clean();
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $body));
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Contact client email failed: ' . $mail->ErrorInfo);
        return false;
    }
}
