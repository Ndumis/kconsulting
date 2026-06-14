<?php
/* Variables in scope: $d (array), $submission_id (int), $resultInfo (array), $resultDesc (string) */
$firstName = explode(' ', trim($d['name']))[0] ?: 'there';
$answers   = is_array($d['answers']) ? $d['answers'] : (json_decode($d['answers'] ?? '[]', true) ?: []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your KConsulting Growth Quiz Results</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:'Inter',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);max-width:600px;">

      <!-- Header -->
      <tr>
        <td style="background:#0a0a0a;padding:40px;text-align:center;">
          <p style="color:#9ca3af;font-size:0.75rem;font-weight:700;letter-spacing:3px;margin:0 0 10px;">YOUR RESULTS ARE IN</p>
          <h1 style="color:#ffffff;font-size:1.6rem;font-weight:700;margin:0;">Growth Quiz Results</h1>
        </td>
      </tr>

      <!-- Greeting -->
      <tr>
        <td style="padding:36px 40px 0;">
          <h2 style="font-size:1.2rem;font-weight:600;color:#0a0a0a;margin:0 0 12px;">Hi <?= htmlspecialchars($firstName) ?>,</h2>
          <p style="color:#555;font-size:1rem;line-height:1.7;margin:0;">Thanks for completing the KConsulting Growth Quiz. Based on your three answers, here's exactly what we recommend to accelerate your business growth.</p>
        </td>
      </tr>

      <!-- Result Box -->
      <tr>
        <td style="padding:24px 40px;">
          <div style="background:#0a0a0a;border-radius:12px;padding:32px;text-align:center;">
            <p style="color:#9ca3af;font-size:0.7rem;font-weight:700;letter-spacing:2.5px;margin:0 0 10px;text-transform:uppercase;">Your Recommended Solution</p>
            <h2 style="color:#ffffff;font-size:1.5rem;font-weight:700;margin:0 0 16px;"><?= htmlspecialchars($resultInfo['label']) ?></h2>
            <p style="color:#aaa;font-size:0.95rem;line-height:1.65;margin:0 0 24px;"><?= htmlspecialchars($resultDesc) ?></p>
            <a href="<?= htmlspecialchars($resultInfo['link']) ?>" style="display:inline-block;background:#9ca3af;color:#0a0a0a;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:700;font-size:0.9rem;">Get Started Now &rarr;</a>
          </div>
        </td>
      </tr>

      <!-- What Happens Next -->
      <tr>
        <td style="padding:0 40px 28px;">
          <h3 style="font-size:1rem;font-weight:700;color:#0a0a0a;margin:0 0 18px;">What happens next?</h3>
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="width:36px;vertical-align:top;padding-top:2px;">
                <div style="width:28px;height:28px;background:#0a0a0a;border-radius:50%;text-align:center;line-height:28px;color:#9ca3af;font-size:0.8rem;font-weight:700;">1</div>
              </td>
              <td style="padding:0 0 16px 12px;">
                <p style="margin:0;font-weight:600;color:#0a0a0a;font-size:0.93rem;">Our team reviews your quiz answers</p>
                <p style="margin:4px 0 0;color:#777;font-size:0.86rem;">We look at what you've shared and prepare tailored insights for your specific situation.</p>
              </td>
            </tr>
            <tr>
              <td style="width:36px;vertical-align:top;padding-top:2px;">
                <div style="width:28px;height:28px;background:#0a0a0a;border-radius:50%;text-align:center;line-height:28px;color:#9ca3af;font-size:0.8rem;font-weight:700;">2</div>
              </td>
              <td style="padding:0 0 16px 12px;">
                <p style="margin:0;font-weight:600;color:#0a0a0a;font-size:0.93rem;">We reach out within 1 business day</p>
                <p style="margin:4px 0 0;color:#777;font-size:0.86rem;">Expect a quick message from us, no pressure, just a conversation about where you want to go.</p>
              </td>
            </tr>
            <tr>
              <td style="width:36px;vertical-align:top;padding-top:2px;">
                <div style="width:28px;height:28px;background:#9ca3af;border-radius:50%;text-align:center;line-height:28px;color:#0a0a0a;font-size:0.8rem;font-weight:700;">3</div>
              </td>
              <td style="padding:0 0 0 12px;">
                <p style="margin:0;font-weight:600;color:#0a0a0a;font-size:0.93rem;">We build your growth plan together</p>
                <p style="margin:4px 0 0;color:#777;font-size:0.86rem;">A specific, actionable plan built around your goals, not a generic proposal.</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Answers Summary -->
      <tr>
        <td style="padding:0 40px 32px;">
          <p style="color:#aaa;font-size:0.78rem;text-align:center;margin:0 0 14px;text-transform:uppercase;letter-spacing:1px;">Your Quiz Answers</p>
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eeeeee;border-radius:8px;overflow:hidden;">
            <?php foreach ($answers as $i => $ans):
              $bg = $i % 2 === 1 ? 'background:#f9f9f9;' : '';
            ?>
            <tr style="<?= $bg ?>">
              <td style="padding:10px 14px;color:#aaa;font-size:0.78rem;width:34%;vertical-align:top;font-weight:600;">Question <?= (int)($ans['question'] ?? $i + 1) ?></td>
              <td style="padding:10px 14px;color:#0a0a0a;font-size:0.85rem;"><?= htmlspecialchars($ans['selected'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
          </table>
        </td>
      </tr>

      <!-- CTA -->
      <tr>
        <td style="padding:0 40px 40px;text-align:center;">
          <p style="color:#555;font-size:0.95rem;margin:0 0 18px;">Want to skip the wait? Book your free 30-minute strategy session and let's map out the fastest path forward.</p>
          <a href="https://www.thekconsult.co.za/consultation.html" style="display:inline-block;background:#0a0a0a;color:#ffffff;text-decoration:none;padding:15px 34px;border-radius:8px;font-weight:700;font-size:0.9rem;">Book Free Strategy Session &rarr;</a>
          <p style="margin:16px 0 0;color:#999;font-size:0.83rem;">Or simply reply to this email, we read every one.</p>
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="background:#f9f9f9;padding:22px 40px;text-align:center;border-top:1px solid #eeeeee;">
          <p style="margin:0 0 4px;color:#0a0a0a;font-weight:600;font-size:0.88rem;">KConsulting Firm (Pty) Ltd</p>
          <p style="margin:0;color:#999;font-size:0.78rem;">info@thekconsult.co.za &bull; +27 64 519 0549 &bull; Cape Town, South Africa</p>
          <p style="margin:10px 0 0;color:#bbb;font-size:0.73rem;">You received this because you completed a growth quiz on our website.</p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
