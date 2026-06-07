<?php
require_once __DIR__ . '/mailer.php';

function sendContactAdminEmail(array $d, int $submission_id): bool {
    // Expose individual variables so the existing template works unchanged
    $name    = $d['name'];
    $email   = $d['email'];
    $company = $d['company'] ?? '';
    $service = $d['service'] ?? '';
    $message = $d['message'];

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
        error_log('Contact admin email failed: ' . $e->getMessage());
        return false;
    }
}

function sendContactClientEmail(array $d, int $submission_id): bool {
    $name    = $d['name'];
    $email   = $d['email'];
    $company = $d['company'] ?? '';
    $service = $d['service'] ?? '';
    $message = $d['message'];

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
        error_log('Contact client email failed: ' . $e->getMessage());
        return false;
    }
}
