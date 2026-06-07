<?php
require_once __DIR__ . '/mailer.php';

function sendDiagnosticAdminEmail(array $d, int $submission_id): bool {
    try {
        $mail = createMailer();
        $mail->setFrom(MAIL_FROM, 'KConsulting Diagnostic');
        $mail->addAddress(MAIL_ADMIN_ADDR, MAIL_ADMIN_NAME . ' Admin');
        if (!empty($d['email'])) {
            $mail->addReplyTo($d['email'], $d['name'] ?: $d['email']);
        }
        $mail->isHTML(true);
        $displayName = $d['name'] ?: $d['email'];
        $mail->Subject = 'New Marketing Diagnostic #' . $submission_id . ' — ' . $displayName;
        ob_start();
        include __DIR__ . '/diagnostic_admin_email_template.php';
        $body = ob_get_clean();
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</li>'], "\n", $body));
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Diagnostic admin email failed: ' . $e->getMessage());
        return false;
    }
}

function sendDiagnosticClientEmail(array $d, int $submission_id): bool {
    if (empty($d['email'])) return false;
    try {
        $mail = createMailer();
        $mail->setFrom(MAIL_FROM, 'KConsulting Firm');
        $mail->addAddress($d['email'], $d['name'] ?: $d['email']);
        $mail->isHTML(true);
        $mail->Subject = 'Your KConsulting Marketing Growth Diagnostic Results';
        ob_start();
        include __DIR__ . '/diagnostic_client_email_template.php';
        $body = ob_get_clean();
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</li>'], "\n", $body));
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Diagnostic client email failed: ' . $e->getMessage());
        return false;
    }
}
