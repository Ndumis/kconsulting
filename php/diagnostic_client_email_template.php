<?php
/* Variables available: $d (array), $submission_id (int) */
$firstName = explode(' ', trim($d['name']))[0] ?: 'there';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your KConsulting Growth Diagnostic Results</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:'Inter',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);max-width:600px;">

      <!-- Header -->
      <tr>
        <td style="background:#0a0a0a;padding:40px;text-align:center;">
          <p style="color:#9ca3af;font-size:0.75rem;font-weight:700;letter-spacing:3px;margin:0 0 10px;">YOUR RESULTS ARE IN</p>
          <h1 style="color:#ffffff;font-size:1.8rem;font-weight:700;margin:0;">Marketing Growth Diagnostic</h1>
        </td>
      </tr>

      <!-- Greeting -->
      <tr>
        <td style="padding:40px 40px 0;">
          <h2 style="font-size:1.3rem;font-weight:600;color:#0a0a0a;margin:0 0 12px;">Hi <?= htmlspecialchars($firstName) ?>,</h2>
          <p style="color:#555;font-size:1rem;line-height:1.7;margin:0 0 20px;">Thanks for completing the KConsulting Marketing Growth Diagnostic. Based on your answers, here's what we've identified and what we recommend to move your business forward.</p>
        </td>
      </tr>

      <!-- Recommendation Box -->
      <tr>
        <td style="padding:24px 40px;">
          <div style="background:#f5f5f5;border:2px solid #9ca3af;border-radius:12px;padding:28px;">
            <p style="color:#6b7280;font-size:0.75rem;font-weight:700;letter-spacing:2px;margin:0 0 10px;">OUR RECOMMENDATION FOR YOU</p>
            <p style="color:#0a0a0a;font-size:1rem;line-height:1.7;margin:0;"><?= htmlspecialchars($d['recommendation']) ?></p>
          </div>
        </td>
      </tr>

      <!-- What's next -->
      <tr>
        <td style="padding:0 40px 28px;">
          <h3 style="font-size:1.1rem;font-weight:700;color:#0a0a0a;margin:0 0 16px;">What happens next?</h3>
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="width:36px;vertical-align:top;padding-top:2px;">
                <div style="width:28px;height:28px;background:#0a0a0a;border-radius:50%;text-align:center;line-height:28px;color:#9ca3af;font-size:0.8rem;font-weight:700;">1</div>
              </td>
              <td style="padding:0 0 16px 12px;">
                <p style="margin:0;font-weight:600;color:#0a0a0a;font-size:0.95rem;">One of our consultants reviews your diagnostic</p>
                <p style="margin:4px 0 0;color:#777;font-size:0.88rem;">We look at your answers and prepare personalised insights for your business.</p>
              </td>
            </tr>
            <tr>
              <td style="width:36px;vertical-align:top;padding-top:2px;">
                <div style="width:28px;height:28px;background:#0a0a0a;border-radius:50%;text-align:center;line-height:28px;color:#9ca3af;font-size:0.8rem;font-weight:700;">2</div>
              </td>
              <td style="padding:0 0 16px 12px;">
                <p style="margin:0;font-weight:600;color:#0a0a0a;font-size:0.95rem;">We reach out within 1 business day</p>
                <p style="margin:4px 0 0;color:#777;font-size:0.88rem;">Expect a call or email from our team,no pressure, just a conversation about your goals.</p>
              </td>
            </tr>
            <tr>
              <td style="width:36px;vertical-align:top;padding-top:2px;">
                <div style="width:28px;height:28px;background:#9ca3af;border-radius:50%;text-align:center;line-height:28px;color:#0a0a0a;font-size:0.8rem;font-weight:700;">3</div>
              </td>
              <td style="padding:0 0 0 12px;">
                <p style="margin:0;font-weight:600;color:#0a0a0a;font-size:0.95rem;">We build a clear growth plan together</p>
                <p style="margin:4px 0 0;color:#777;font-size:0.88rem;">A specific, actionable plan aligned to your budget, goals, and timeline.</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- CTA -->
      <tr>
        <td style="padding:0 40px 40px;text-align:center;">
          <p style="color:#555;font-size:0.95rem;margin:0 0 20px;">Want to fast-track your results? Book a free 30-minute strategy session and let's map out the path forward.</p>
          <a href="https://www.thekconsult.co.za/consultation.html" style="display:inline-block;background:#9ca3af;color:#0a0a0a;text-decoration:none;padding:16px 36px;border-radius:8px;font-weight:700;font-size:0.95rem;">Book Free Strategy Session &rarr;</a>
          <p style="margin:20px 0 0;color:#999;font-size:0.85rem;">Or reply directly to this email,we read every message.</p>
        </td>
      </tr>

      <!-- Your Answers Summary -->
      <tr>
        <td style="padding:0 40px 40px;">
          <p style="color:#aaa;font-size:0.8rem;text-align:center;margin:0 0 16px;text-transform:uppercase;letter-spacing:1px;">Your Diagnostic Summary</p>
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eeeeee;border-radius:8px;overflow:hidden;">
            <tr style="background:#f9f9f9;">
              <td style="padding:10px 16px;color:#666;font-size:0.82rem;width:50%;"><strong>Challenge</strong></td>
              <td style="padding:10px 16px;color:#0a0a0a;font-size:0.82rem;"><?= htmlspecialchars($d['challenge'] ?: '—') ?></td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#666;font-size:0.82rem;"><strong>6-month Goal</strong></td>
              <td style="padding:10px 16px;color:#0a0a0a;font-size:0.82rem;"><?= htmlspecialchars($d['goal'] ?: '—') ?></td>
            </tr>
            <tr style="background:#f9f9f9;">
              <td style="padding:10px 16px;color:#666;font-size:0.82rem;"><strong>Budget Range</strong></td>
              <td style="padding:10px 16px;color:#0a0a0a;font-size:0.82rem;"><?= htmlspecialchars($d['budget'] ?: '—') ?></td>
            </tr>
            <tr>
              <td style="padding:10px 16px;color:#666;font-size:0.82rem;"><strong>Team Setup</strong></td>
              <td style="padding:10px 16px;color:#0a0a0a;font-size:0.82rem;"><?= htmlspecialchars($d['team_size'] ?: '—') ?></td>
            </tr>
            <tr style="background:#f9f9f9;">
              <td style="padding:10px 16px;color:#666;font-size:0.82rem;"><strong>Timeline</strong></td>
              <td style="padding:10px 16px;color:#0a0a0a;font-size:0.82rem;"><?= htmlspecialchars($d['timeline'] ?: '—') ?></td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="background:#f9f9f9;padding:24px 40px;text-align:center;border-top:1px solid #eeeeee;">
          <p style="margin:0 0 6px;color:#0a0a0a;font-weight:600;font-size:0.9rem;">KConsulting Firm (Pty) Ltd</p>
          <p style="margin:0;color:#999;font-size:0.8rem;">info@thekconsult.co.za &bull; +27 64 519 0549 &bull; Cape Town, South Africa</p>
          <p style="margin:10px 0 0;color:#bbb;font-size:0.75rem;">You received this because you completed a marketing diagnostic on our website.</p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
