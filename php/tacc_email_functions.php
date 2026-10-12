<?php
/**
 * TACC registration email helpers.
 *
 * Called by tacc/process_email_queue.php with the payload that register.php
 * stores in tacc_email_queue. Both send functions throw on failure so the
 * queue keeps the job pending and retries it.
 *
 * Adjust ADMIN_EMAIL and FROM_EMAIL as needed.
 */

const TACC_ADMIN_EMAIL = 'khaya@thekconsult.co.za'; // <- change to real admin address
const TACC_FROM_EMAIL  = 'no-reply@thekconsult.co.za';
const TACC_ADMIN_URL   = 'https://www.thekconsult.co.za/tacc/admin.php';
const TACC_PRIVACY_URL = 'https://www.thekconsult.co.za/privacy-policy.html#data-deletion';
const TACC_CHURCH_NAME = "The 12 Apostle Church in Trinity";

// Brand colours (match tacc/admin.php)
const TACC_BLUE      = '#0d47a1';
const TACC_BLUE_SOFT = '#e3f2fd';
const TACC_GOLD      = '#c9a227';
const TACC_TEXT      = '#1a1f2b';
const TACC_MUTED     = '#6b7280';
const TACC_BORDER    = '#e5e7eb';
const TACC_BG        = '#f4f6fa';

/* ------------------------------------------------------------------ */
/*  Sending                                                            */
/* ------------------------------------------------------------------ */

function sendTaccAdminEmail(array $d, int $member_id): void {
    [$subject, $html] = taccAdminEmail($d, $member_id);
    taccSend(TACC_ADMIN_EMAIL, $subject, $html);
}

function sendTaccClientEmail(array $d, int $member_id): void {
    $to = taccVal($d, 'email');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return;

    [$subject, $html] = taccClientEmail($d, $member_id);
    taccSend($to, $subject, $html);
}

/** @return array{0: string, 1: string} subject and HTML */
function taccAdminEmail(array $d, int $member_id): array {
    $name    = trim(taccVal($d, 'full_names') . ' ' . taccVal($d, 'surname'));
    $subject = "New TACC membership application #{$member_id}: {$name}";

    $searchUrl = TACC_ADMIN_URL . '?' . http_build_query(['q' => taccVal($d, 'email')]);

    $body  = taccParagraph('A new membership application was submitted through WhatsApp.');
    $body .= taccSummaryCard([
        'Reference'    => '#' . $member_id,
        'Name'         => $name,
        'Mobile'       => taccVal($d, 'mobile'),
        'Email'        => taccVal($d, 'email'),
        'Congregation' => taccVal($d, 'congregation'),
        'Center'       => taccVal($d, 'church_center'),
    ]);
    $body .= taccButton('Open in TACC Admin', $searchUrl);
    $body .= taccAllDetails($d);

    $html = taccLayout(
        "New application #{$member_id} from {$name}",
        'New Membership Application',
        'Reference #' . $member_id . ' · ' . date('d M Y, H:i'),
        $body,
        'Submitted via the TACC WhatsApp registration form.'
    );

    return [$subject, $html];
}

/** @return array{0: string, 1: string} subject and HTML */
function taccClientEmail(array $d, int $member_id): array {
    $first   = taccVal($d, 'full_names');
    $subject = 'We received your TACC membership application';

    $body  = taccParagraph('Dear ' . taccEsc($first) . ',', true);
    $body .= taccParagraph(
        'Thank you for applying for membership with <strong>' . taccEsc(TACC_CHURCH_NAME) . '</strong>. '
        . 'We have received your application and it is now with church administration.',
        true
    );
    $body .= taccReferenceBox($member_id);

    $body .= taccSectionTitle('Your application');
    $body .= taccTable([
        'Name'                => trim($first . ' ' . taccVal($d, 'surname')),
        'Mobile'              => taccVal($d, 'mobile'),
        'Occupation'          => taccLabel('occupation', taccVal($d, 'occupation')),
        'Congregation'        => taccVal($d, 'congregation'),
        'Global'              => taccVal($d, 'church_global'),
        'Province'            => taccVal($d, 'church_province'),
        'Regional / District' => taccVal($d, 'region'),
        'Zonal'               => taccVal($d, 'church_zonal'),
        'Center'              => taccVal($d, 'church_center'),
    ]);
    $deps = is_array($d['dependents'] ?? null) ? count($d['dependents']) : 0;
    if ($deps > 0) {
        $body .= taccParagraph(
            'You also registered <strong>' . $deps . ' dependent' . ($deps === 1 ? '' : 's') . '</strong>.',
            true
        );
    }

    $body .= taccSectionTitle('What happens next');
    $body .= taccSteps([
        'Church administration reviews your application.',
        'Someone from your congregation will contact you on the mobile number above.',
        'Keep your reference number for any questions about your application.',
    ]);

    $body .= taccParagraph(
        'If any of these details are wrong, simply reply on WhatsApp or contact your congregation leadership.',
        true
    );

    $html = taccLayout(
        "Your reference number is #{$member_id}.",
        'Application Received',
        TACC_CHURCH_NAME,
        $body,
        'Your information is used only for church membership records. '
        . '<a href="' . taccEsc(TACC_PRIVACY_URL) . '" style="color:' . TACC_MUTED . ';text-decoration:underline;">'
        . 'How to view or delete your data</a>.'
    );

    return [$subject, $html];
}

/**
 * Sends a multipart (plain text + HTML) email. Throws if the mail server
 * rejects it, so the queue retries instead of marking the job as sent.
 */
function taccSend(string $to, string $subject, string $html): void {
    $boundary = 'tacc_' . bin2hex(random_bytes(12));

    $headers = implode("\r\n", [
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        'From: TACC Registration <' . TACC_FROM_EMAIL . '>',
        'Reply-To: ' . TACC_FROM_EMAIL,
        'X-Mailer: PHP/' . phpversion(),
    ]);

    $message = "--{$boundary}\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode(taccPlainText($html)))
        . "--{$boundary}\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($html))
        . "--{$boundary}--";

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    if (!mail($to, $encodedSubject, $message, $headers)) {
        throw new RuntimeException("mail() failed sending '{$subject}' to {$to}");
    }
}

function taccPlainText(string $html): string {
    $text = preg_replace('/<(style|head)\b.*?<\/\1>/is', '', $html);
    $text = preg_replace('/<\/(p|div|h[1-6]|tr|li|table)>|<br\s*\/?>/i', "\n", $text);
    $text = preg_replace('/<\/td>/i', "  ", $text);
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $text);
    return trim(implode("\n", array_map('trim', explode("\n", $text))));
}

/* ------------------------------------------------------------------ */
/*  Data helpers                                                       */
/* ------------------------------------------------------------------ */

function taccEsc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function taccVal(array $d, string $key): string {
    return trim((string)($d[$key] ?? ''));
}

/** Turns dropdown ids from the Flow (e.g. "self_employed", "kzn") into their labels. */
function taccLabel(string $field, string $value): string {
    static $labels = [
        'gender'         => ['male' => 'Male', 'female' => 'Female'],
        'marital_status' => ['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'divorced' => 'Divorced'],
        'occupation'     => ['working' => 'Working', 'student' => 'Student', 'self_employed' => 'Self Employed', 'unemployed' => 'Unemployed'],
        'province'       => [
            'ec' => 'Eastern Cape', 'fs' => 'Free State', 'gp' => 'Gauteng', 'kzn' => 'KwaZulu-Natal',
            'lp' => 'Limpopo', 'mp' => 'Mpumalanga', 'nc' => 'Northern Cape', 'nw' => 'North West', 'wc' => 'Western Cape',
        ],
        'ministries'     => [
            'choir' => 'Choir / Singing', 'youth' => 'Youth Movement', 'women' => "Women's / Mothers'",
            'sunday_school' => 'Sunday School', 'evangelism' => 'Evangelism & Outreach', 'admin' => 'Admin / Maintenance',
        ],
    ];
    return $labels[$field][strtolower($value)] ?? $value;
}

function taccDate(string $value): string {
    if ($value === '') return '';
    $ts = strtotime($value);
    return $ts ? date('d M Y', $ts) : $value;
}

function taccYesNo($flag, string $year = ''): string {
    if (empty($flag)) return 'No';
    return $year !== '' ? "Yes ({$year})" : 'Yes';
}

/* ------------------------------------------------------------------ */
/*  Building blocks (tables + inline styles for email clients)        */
/* ------------------------------------------------------------------ */

function taccLayout(string $preheader, string $title, string $subtitle, string $body, string $footerNote): string {
    $font = "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif";
    $year = date('Y');

    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light only">
<meta name="supported-color-schemes" content="light only">
<title>' . taccEsc($title) . '</title>
<style>
  @media only screen and (max-width:620px){
    .tacc-container{width:100% !important;}
    .tacc-pad{padding-left:20px !important;padding-right:20px !important;}
    .tacc-label{width:42% !important;}
  }
</style>
</head>
<body style="margin:0;padding:0;background:' . TACC_BG . ';">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">' . taccEsc($preheader) . '</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:' . TACC_BG . ';">
  <tr><td align="center" style="padding:28px 12px;">
    <table role="presentation" class="tacc-container" width="600" cellpadding="0" cellspacing="0" border="0"
           style="width:600px;max-width:600px;background:#ffffff;border:1px solid ' . TACC_BORDER . ';border-radius:12px;overflow:hidden;font-family:' . $font . ';color:' . TACC_TEXT . ';">
      <tr><td style="background:' . TACC_BLUE . ';padding:28px 32px 24px;" class="tacc-pad">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
          <td style="width:44px;height:44px;background:#ffffff;border-radius:10px;text-align:center;vertical-align:middle;font-size:13px;font-weight:800;color:' . TACC_BLUE . ';">TACC</td>
          <td style="padding-left:14px;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#bbd3f5;">' . taccEsc(TACC_CHURCH_NAME) . '</td>
        </tr></table>
        <h1 style="margin:20px 0 4px;font-size:24px;line-height:1.3;font-weight:700;color:#ffffff;">' . taccEsc($title) . '</h1>
        <p style="margin:0;font-size:14px;color:#dbe7fb;">' . taccEsc($subtitle) . '</p>
      </td></tr>
      <tr><td style="height:4px;background:' . TACC_GOLD . ';font-size:0;line-height:0;">&nbsp;</td></tr>
      <tr><td class="tacc-pad" style="padding:28px 32px 8px;font-size:15px;line-height:1.6;">' . $body . '</td></tr>
      <tr><td class="tacc-pad" style="padding:20px 32px 28px;border-top:1px solid ' . TACC_BORDER . ';font-size:12px;line-height:1.6;color:' . TACC_MUTED . ';">
        ' . $footerNote . '<br>
        &copy; ' . $year . ' ' . taccEsc(TACC_CHURCH_NAME) . ' &middot; One vision, One church, One God.
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>';
}

/** $html = true when the text already contains trusted markup. */
function taccParagraph(string $text, bool $html = false): string {
    return '<p style="margin:0 0 16px;">' . ($html ? $text : taccEsc($text)) . '</p>';
}

function taccSectionTitle(string $title): string {
    return '<h2 style="margin:28px 0 10px;padding-bottom:6px;border-bottom:2px solid ' . TACC_BLUE_SOFT . ';'
         . 'font-size:13px;letter-spacing:1px;text-transform:uppercase;color:' . TACC_BLUE . ';">' . taccEsc($title) . '</h2>';
}

/** Two-column label/value table. Empty values show as a dash. */
function taccTable(array $rows): string {
    $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;font-size:14px;">';
    foreach ($rows as $label => $value) {
        $value = trim((string)$value);
        $html .= '<tr>'
            . '<td class="tacc-label" width="38%" style="padding:9px 12px 9px 0;border-bottom:1px solid ' . TACC_BORDER . ';color:' . TACC_MUTED . ';vertical-align:top;">' . taccEsc((string)$label) . '</td>'
            . '<td style="padding:9px 0;border-bottom:1px solid ' . TACC_BORDER . ';font-weight:600;vertical-align:top;">'
            . ($value === '' ? '<span style="color:#b0b7c3;font-weight:400;">&mdash;</span>' : nl2br(taccEsc($value)))
            . '</td></tr>';
    }
    return $html . '</table>';
}

function taccSummaryCard(array $rows): string {
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:' . TACC_BLUE_SOFT . ';border-radius:10px;margin:4px 0 20px;">'
         . '<tr><td style="padding:6px 18px;">' . taccTable($rows) . '</td></tr></table>';
}

function taccReferenceBox(int $member_id): string {
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 8px;">'
         . '<tr><td align="center" style="background:' . TACC_BLUE_SOFT . ';border:1px dashed ' . TACC_BLUE . ';border-radius:10px;padding:18px;">'
         . '<div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;color:' . TACC_MUTED . ';">Your reference number</div>'
         . '<div style="font-size:30px;font-weight:800;color:' . TACC_BLUE . ';letter-spacing:1px;">#' . $member_id . '</div>'
         . '</td></tr></table>';
}

function taccButton(string $label, string $url): string {
    return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 8px;"><tr>'
         . '<td style="background:' . TACC_BLUE . ';border-radius:8px;">'
         . '<a href="' . taccEsc($url) . '" style="display:inline-block;padding:12px 22px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;">'
         . taccEsc($label) . ' &rarr;</a></td></tr></table>';
}

function taccSteps(array $steps): string {
    $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 16px;">';
    foreach (array_values($steps) as $i => $step) {
        $html .= '<tr>'
            . '<td width="34" style="padding:6px 0;vertical-align:top;">'
            . '<div style="width:24px;height:24px;line-height:24px;border-radius:12px;background:' . TACC_GOLD . ';color:#ffffff;font-size:12px;font-weight:700;text-align:center;">' . ($i + 1) . '</div></td>'
            . '<td style="padding:7px 0;font-size:14px;">' . taccEsc($step) . '</td></tr>';
    }
    return $html . '</table>';
}

function taccPills(array $items): string {
    $html = '';
    foreach ($items as $item) {
        $html .= '<span style="display:inline-block;margin:0 6px 8px 0;padding:5px 12px;border-radius:999px;background:' . TACC_BLUE_SOFT . ';color:' . TACC_BLUE . ';font-size:13px;font-weight:600;">'
               . taccEsc($item) . '</span>';
    }
    return '<div style="margin:4px 0 8px;">' . $html . '</div>';
}

/** Full application, grouped like the WhatsApp Flow screens. Used in the admin email. */
function taccAllDetails(array $d): string {
    $html  = taccSectionTitle('Personal');
    $html .= taccTable([
        'Full Names'     => taccVal($d, 'full_names'),
        'Surname'        => taccVal($d, 'surname'),
        'Date of Birth'  => taccDate(taccVal($d, 'date_of_birth')),
        'Gender'         => taccLabel('gender', taccVal($d, 'gender')),
        'ID / Passport'  => taccVal($d, 'id_number'),
        'Marital Status' => taccLabel('marital_status', taccVal($d, 'marital_status')),
    ]);

    $html .= taccSectionTitle('Contact');
    $html .= taccTable([
        'Address'             => taccVal($d, 'address'),
        'Postal Code'         => taccVal($d, 'postal_code'),
        'Province'            => taccLabel('province', taccVal($d, 'province')),
        'Municipality / Town' => taccVal($d, 'municipality'),
        'Mobile'              => taccVal($d, 'mobile'),
        'Alt. Number'         => taccVal($d, 'alt_number'),
        'Email'               => taccVal($d, 'email'),
        'Occupation'          => taccLabel('occupation', taccVal($d, 'occupation')),
    ]);

    $html .= taccSectionTitle('Church');
    $html .= taccTable([
        'Congregation'        => taccVal($d, 'congregation'),
        'Global'              => taccVal($d, 'church_global'),
        'Province'            => taccVal($d, 'church_province'),
        'Regional / District' => taccVal($d, 'region'),
        'Zonal'               => taccVal($d, 'church_zonal'),
        'Center'              => taccVal($d, 'church_center'),
        'Water Baptised'      => taccYesNo($d['water_baptised'] ?? 0, taccVal($d, 'water_baptism_year')),
        'Sealed (Holy Spirit)'=> taccYesNo($d['sealed'] ?? 0, taccVal($d, 'sealing_year')),
        'Previous Church'     => taccVal($d, 'previous_church'),
    ]);

    $deps = is_array($d['dependents'] ?? null) ? $d['dependents'] : [];
    if ($deps) {
        $html .= taccSectionTitle('Family / Dependents (' . count($deps) . ')');
        $cell = 'padding:8px 8px 8px 0;border-bottom:1px solid ' . TACC_BORDER . ';';
        $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;font-size:14px;">'
               . '<tr>'
               . '<th align="left" style="' . $cell . 'color:' . TACC_MUTED . ';font-weight:600;font-size:12px;">Name</th>'
               . '<th align="left" style="' . $cell . 'color:' . TACC_MUTED . ';font-weight:600;font-size:12px;">Relationship</th>'
               . '<th align="left" style="' . $cell . 'color:' . TACC_MUTED . ';font-weight:600;font-size:12px;">Date of Birth</th>'
               . '<th align="left" style="' . $cell . 'color:' . TACC_MUTED . ';font-weight:600;font-size:12px;">Baptised</th>'
               . '</tr>';
        foreach ($deps as $dep) {
            $html .= '<tr>'
                . '<td style="' . $cell . 'font-weight:600;">' . taccEsc((string)($dep['name'] ?? '')) . '</td>'
                . '<td style="' . $cell . '">' . taccEsc((string)($dep['relationship'] ?? '')) . '</td>'
                . '<td style="' . $cell . '">' . taccEsc(taccDate((string)($dep['dob'] ?? ''))) . '</td>'
                . '<td style="' . $cell . '">' . taccEsc((string)($dep['baptised'] ?? '')) . '</td>'
                . '</tr>';
        }
        $html .= '</table>';
    }

    $mins = is_array($d['ministries'] ?? null) ? $d['ministries'] : [];
    $html .= taccSectionTitle('Ministries');
    $html .= $mins
        ? taccPills(array_map(static fn($m) => taccLabel('ministries', (string)$m), $mins))
        : taccParagraph('No ministries selected.');

    $html .= taccSectionTitle('Declaration');
    $html .= taccTable([
        'Initials' => taccVal($d, 'initials'),
        'Consent'  => 'Accepted',
    ]);

    return $html;
}
