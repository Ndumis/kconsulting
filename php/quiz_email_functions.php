<?php
require_once __DIR__ . '/mailer.php';

const QUIZ_RESULT_LABELS = [
    'seo'        => ['label' => 'SEO & Online Visibility',    'link' => 'https://www.thekconsult.co.za/free-website-audit.html'],
    'conversion' => ['label' => 'Conversion Optimisation',    'link' => 'https://www.thekconsult.co.za/consultation.html'],
    'leads'      => ['label' => 'Lead Generation System',     'link' => 'https://www.thekconsult.co.za/free-website-audit.html'],
    'systems'    => ['label' => 'Systems Integration',        'link' => 'https://www.thekconsult.co.za/consultation.html'],
];

const QUIZ_RESULT_DESCRIPTIONS = [
    'seo'        => "Your website isn't being found. We'll help you rank for the searches your ideal clients are already making, so the right people discover your business every day.",
    'conversion' => "You're getting traffic but losing sales. We'll identify exactly where visitors drop off and fix those gaps so more of them become paying customers.",
    'leads'      => "A great-looking site alone won't grow your business. Let's build a system that pulls in qualified enquiries every month,consistently and predictably.",
    'systems'    => "Disconnected tools are costing you time and money. We'll connect your stack and automate the repetitive work so your team can focus on growth.",
];

const QUIZ_QUESTIONS = [
    1 => "What's your biggest online challenge right now?",
    2 => 'How would you describe your current website?',
    3 => 'What result matters most to you in the next 90 days?',
];

function sendQuizAdminEmail(array $d, int $submission_id): bool {
    $resultInfo = QUIZ_RESULT_LABELS[$d['result_type']] ?? ['label' => ucfirst($d['result_type']), 'link' => ''];
    $resultDesc = QUIZ_RESULT_DESCRIPTIONS[$d['result_type']] ?? '';

    try {
        $mail = createMailer();
        $mail->setFrom(MAIL_FROM, 'KConsulting Growth Quiz');
        $mail->addAddress(MAIL_ADMIN_ADDR, MAIL_ADMIN_NAME . ' Admin');
        if (!empty($d['email'])) {
            $mail->addReplyTo($d['email'], $d['name'] ?: $d['email']);
        }
        $mail->isHTML(true);
        $displayName = $d['name'] ?: $d['email'];
        $mail->Subject = 'New Growth Quiz Lead #' . $submission_id . ' — ' . $displayName . ' — ' . $resultInfo['label'];
        ob_start();
        include __DIR__ . '/quiz_admin_email_template.php';
        $body = ob_get_clean();
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</li>'], "\n", $body));
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Quiz admin email failed: ' . $e->getMessage());
        return false;
    }
}

function sendQuizClientEmail(array $d, int $submission_id): bool {
    if (empty($d['email'])) return false;
    $resultInfo = QUIZ_RESULT_LABELS[$d['result_type']] ?? ['label' => ucfirst($d['result_type']), 'link' => 'https://www.thekconsult.co.za/consultation.html'];
    $resultDesc = QUIZ_RESULT_DESCRIPTIONS[$d['result_type']] ?? '';

    try {
        $mail = createMailer();
        $mail->setFrom(MAIL_FROM, 'KConsulting Firm');
        $mail->addAddress($d['email'], $d['name'] ?: $d['email']);
        $mail->isHTML(true);
        $mail->Subject = 'Your KConsulting Growth Quiz Results';
        ob_start();
        include __DIR__ . '/quiz_client_email_template.php';
        $body = ob_get_clean();
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</li>'], "\n", $body));
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Quiz client email failed: ' . $e->getMessage());
        return false;
    }
}
