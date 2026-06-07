<?php
require_once __DIR__ . '/mailer.php';

function sendAuditAdminEmail($data, $submission_id) {
    ob_start();
    include __DIR__ . '/audit_admin_email_template.php';
    $body = ob_get_clean();

    try {
        $mail = createMailer();
        $mail->setFrom(MAIL_FROM, 'Website Audit Form');
        $mail->addAddress(MAIL_ADMIN_ADDR, MAIL_ADMIN_NAME);
        $mail->isHTML(true);
        $mail->Subject = 'New Website Audit Request #' . $submission_id;
        $mail->Body    = $body;
        $mail->AltBody = plainText($body);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Audit admin email failed: ' . $mail->ErrorInfo);
        return false;
    }
}

function sendAuditClientEmail($data, $submission_id) {
    ob_start();
    include __DIR__ . '/audit_client_email_template.php';
    $body = ob_get_clean();

    try {
        $mail = createMailer();
        $mail->setFrom(MAIL_FROM, 'KConsulting Firm');
        $mail->addAddress($data['email'], $data['name']);
        $mail->isHTML(true);
        $mail->Subject = 'Website Audit Request Confirmation #' . $submission_id;
        $mail->Body    = $body;
        $mail->AltBody = plainText($body);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Audit client email failed: ' . $mail->ErrorInfo);
        return false;
    }
}
