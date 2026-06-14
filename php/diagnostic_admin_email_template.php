<?php
/* Variables available: $d (array), $submission_id (int) */
$submitted = date('d M Y, H:i');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Marketing Diagnostic #<?= $submission_id ?></title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:'Inter',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);max-width:600px;">

      <!-- Header -->
      <tr>
        <td style="background:#0a0a0a;padding:32px 40px;text-align:center;">
          <p style="color:#9ca3af;font-size:0.75rem;font-weight:700;letter-spacing:3px;margin:0 0 8px;">NEW LEAD</p>
          <h1 style="color:#ffffff;font-size:1.5rem;font-weight:700;margin:0;">Marketing Diagnostic #<?= $submission_id ?></h1>
          <p style="color:#888;font-size:0.85rem;margin:8px 0 0;"><?= $submitted ?></p>
        </td>
      </tr>

      <!-- Contact Info -->
      <tr>
        <td style="padding:32px 40px 0;">
          <h2 style="font-size:1rem;font-weight:700;color:#0a0a0a;text-transform:uppercase;letter-spacing:1px;margin:0 0 16px;padding-bottom:10px;border-bottom:2px solid #f0f0f0;">Contact Details</h2>
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="padding:8px 0;color:#666;font-size:0.9rem;width:40%;"><strong>Name</strong></td>
              <td style="padding:8px 0;color:#0a0a0a;font-size:0.9rem;"><?= htmlspecialchars($d['name'] ?: '—') ?></td>
            </tr>
            <tr style="background:#f9f9f9;">
              <td style="padding:8px 0;color:#666;font-size:0.9rem;"><strong>Email</strong></td>
              <td style="padding:8px 0;color:#0a0a0a;font-size:0.9rem;"><a href="mailto:<?= htmlspecialchars($d['email']) ?>" style="color:#6b7280;"><?= htmlspecialchars($d['email']) ?></a></td>
            </tr>
            <tr>
              <td style="padding:8px 0;color:#666;font-size:0.9rem;"><strong>Phone</strong></td>
              <td style="padding:8px 0;color:#0a0a0a;font-size:0.9rem;"><?= htmlspecialchars($d['phone'] ?: '—') ?></td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Diagnostic Answers -->
      <tr>
        <td style="padding:28px 40px 0;">
          <h2 style="font-size:1rem;font-weight:700;color:#0a0a0a;text-transform:uppercase;letter-spacing:1px;margin:0 0 16px;padding-bottom:10px;border-bottom:2px solid #f0f0f0;">Diagnostic Answers</h2>
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="padding:10px 0;color:#666;font-size:0.85rem;width:45%;vertical-align:top;"><strong>1. Biggest Challenge</strong></td>
              <td style="padding:10px 0;color:#0a0a0a;font-size:0.9rem;"><?= htmlspecialchars($d['challenge'] ?: '—') ?></td>
            </tr>
            <tr style="background:#f9f9f9;">
              <td style="padding:10px 0;color:#666;font-size:0.85rem;vertical-align:top;"><strong>2. Primary Goal (6 months)</strong></td>
              <td style="padding:10px 0;color:#0a0a0a;font-size:0.9rem;"><?= htmlspecialchars($d['goal'] ?: '—') ?></td>
            </tr>
            <tr>
              <td style="padding:10px 0;color:#666;font-size:0.85rem;vertical-align:top;"><strong>3. Marketing Budget</strong></td>
              <td style="padding:10px 0;color:#0a0a0a;font-size:0.9rem;"><?= htmlspecialchars($d['budget'] ?: '—') ?></td>
            </tr>
            <tr style="background:#f9f9f9;">
              <td style="padding:10px 0;color:#666;font-size:0.85rem;vertical-align:top;"><strong>4. In-house Team</strong></td>
              <td style="padding:10px 0;color:#0a0a0a;font-size:0.9rem;"><?= htmlspecialchars($d['team_size'] ?: '—') ?></td>
            </tr>
            <tr>
              <td style="padding:10px 0;color:#666;font-size:0.85rem;vertical-align:top;"><strong>5. Timeline for Results</strong></td>
              <td style="padding:10px 0;color:#0a0a0a;font-size:0.9rem;"><?= htmlspecialchars($d['timeline'] ?: '—') ?></td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Recommendation -->
      <tr>
        <td style="padding:28px 40px;">
          <h2 style="font-size:1rem;font-weight:700;color:#0a0a0a;text-transform:uppercase;letter-spacing:1px;margin:0 0 12px;padding-bottom:10px;border-bottom:2px solid #f0f0f0;">System Recommendation</h2>
          <div style="background:#f5f5f5;border-left:4px solid #9ca3af;padding:16px 20px;border-radius:0 8px 8px 0;">
            <p style="margin:0;color:#0a0a0a;font-size:0.95rem;line-height:1.6;"><?= htmlspecialchars($d['recommendation'] ?: '—') ?></p>
          </div>
        </td>
      </tr>

      <!-- Action Buttons -->
      <tr>
        <td style="padding:0 40px 40px;text-align:center;">
          <a href="mailto:<?= htmlspecialchars($d['email']) ?>" style="display:inline-block;background:#0a0a0a;color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:600;font-size:0.9rem;margin-right:12px;">Reply to Lead</a>
          <?php if (!empty($d['phone'])): ?>
          <a href="tel:<?= htmlspecialchars($d['phone']) ?>" style="display:inline-block;background:#6b7280;color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:8px;font-weight:600;font-size:0.9rem;">Call Now</a>
          <?php endif; ?>
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="background:#f9f9f9;padding:20px 40px;text-align:center;border-top:1px solid #eeeeee;">
          <p style="margin:0;color:#999;font-size:0.8rem;">KConsulting Firm (Pty) Ltd &bull; info@thekconsult.co.za &bull; www.thekconsult.co.za</p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
