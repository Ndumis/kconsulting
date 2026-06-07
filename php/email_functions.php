<?php
require_once __DIR__ . '/mailer.php';

function sendAdminEmail($data, $submission_id) {
    $qualification_score = $data['qualificationScore'];
    ob_start();
    include __DIR__ . '/admin_email_template.php';
    $body = ob_get_clean();

    try {
        $mail = createMailer();
        $mail->setFrom(MAIL_FROM, 'Consultation Form');
        $mail->addAddress(MAIL_ADMIN_ADDR, MAIL_ADMIN_NAME);
        $mail->isHTML(true);
        $mail->Subject = 'New Consultation Request #' . $submission_id;
        $mail->Body    = $body;
        $mail->AltBody = plainText($body);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Admin email failed: ' . $mail->ErrorInfo);
        return false;
    }
}

function sendClientEmail($data, $submission_id) {
    ob_start();
    include __DIR__ . '/client_email_template.php';
    $body = ob_get_clean();

    try {
        $mail = createMailer();
        $mail->setFrom(MAIL_FROM, 'KConsulting Firm');
        $mail->addAddress($data['email'], $data['name']);
        $mail->isHTML(true);
        $mail->Subject = 'Consultation Request Confirmation #' . $submission_id;
        $mail->Body    = $body;
        $mail->AltBody = plainText($body);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Client email failed: ' . $mail->ErrorInfo);
        return false;
    }
}

// Keep old name as alias so existing callers still work
function generatePlainTextFromHTML($html) {
    return plainText($html);
}

function plainText($html) {
    $text = preg_replace('/<style.*?<\/style>|<script.*?<\/script>/s', '', $html);
    $text = html_entity_decode($text);
    $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
    $text = strip_tags($text);
    $text = preg_replace("/\n{3,}/", "\n\n", $text);
    return trim($text);
}
