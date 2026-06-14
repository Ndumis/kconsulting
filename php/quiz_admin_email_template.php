<?php
/* Variables in scope: $d (array), $submission_id (int), $resultInfo (array), $resultDesc (string) */
$submitted = date('d M Y, H:i');
$answers   = is_array($d['answers']) ? $d['answers'] : (json_decode($d['answers'] ?? '[]', true) ?: []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Growth Quiz Lead #<?= $submission_id ?></title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:'Inter',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:40px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);max-width:600px;">

      <!-- Header -->
      <tr>
        <td style="background:#0a0a0a;padding:32px 40px;text-align:center;">
          <p style="color:#9ca3af;font-size:0.75rem;font-weight:700;letter-spacing:3px;margin:0 0 8px;">NEW QUIZ LEAD</p>
          <h1 style="color:#ffffff;font-size:1.4rem;font-weight:700;margin:0;">Growth Quiz #<?= $submission_id ?></h1>
          <p style="color:#888;font-size:0.85rem;margin:8px 0 0;"><?= $submitted ?></p>
        </td>
      </tr>

      <!-- Result Badge -->
      <tr>
        <td style="padding:28px 40px 0;text-align:center;">
          <div style="display:inline-block;background:#f5f5f5;border:2px solid #9ca3af;border-radius:8px;padding:12px 28px;">
            <p style="margin:0;color:#888;font-size:0.7rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Recommended Service</p>
            <p style="margin:6px 0 0;color:#0a0a0a;font-size:1.1rem;font-weight:700;"><?= htmlspecialchars($resultInfo['label']) ?></p>
          </div>
        </td>
      </tr>

      <!-- Contact Info -->
      <tr>
        <td style="padding:28px 40px 0;">
          <h2 style="font-size:0.85rem;font-weight:700;color:#0a0a0a;text-transform:uppercase;letter-spacing:1.5px;margin:0 0 14px;padding-bottom:8px;border-bottom:2px solid #f0f0f0;">Contact Details</h2>
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="padding:8px 0;color:#777;font-size:0.88rem;width:38%;"><strong>Name</strong></td>
              <td style="padding:8px 0;color:#0a0a0a;font-size:0.9rem;"><?= htmlspecialchars($d['name'] ?: '—') ?></td>
            </tr>
            <tr style="background:#f9f9f9;">
              <td style="padding:8px 0;color:#777;font-size:0.88rem;"><strong>Email</strong></td>
              <td style="padding:8px 0;color:#0a0a0a;font-size:0.9rem;">
                <a href="mailto:<?= htmlspecialchars($d['email']) ?>" style="color:#6b7280;text-decoration:none;"><?= htmlspecialchars($d['email']) ?></a>
              </td>
            </tr>
            <tr>
              <td style="padding:8px 0;color:#777;font-size:0.88rem;"><strong>Phone</strong></td>
              <td style="padding:8px 0;color:#0a0a0a;font-size:0.9rem;">
                <?php if (!empty($d['phone'])): ?>
                  <a href="tel:<?= htmlspecialchars($d['phone']) ?>" style="color:#0a0a0a;text-decoration:none;"><?= htmlspecialchars($d['phone']) ?></a>
                <?php else: ?>,<?php endif; ?>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Quiz Answers -->
      <tr>
        <td style="padding:24px 40px 0;">
          <h2 style="font-size:0.85rem;font-weight:700;color:#0a0a0a;text-transform:uppercase;letter-spacing:1.5px;margin:0 0 14px;padding-bottom:8px;border-bottom:2px solid #f0f0f0;">Quiz Answers</h2>
          <table width="100%" cellpadding="0" cellspacing="0">
            <?php
            $questionLabels = QUIZ_QUESTIONS;
            foreach ($answers as $i => $ans):
                $qNum  = isset($ans['question']) ? (int)$ans['question'] : ($i + 1);
                $qText = $questionLabels[$qNum] ?? 'Question ' . $qNum;
                $bg    = $i % 2 === 1 ? 'background:#f9f9f9;' : '';
            ?>
            <tr style="<?= $bg ?>">
              <td style="padding:10px 0;color:#777;font-size:0.82rem;width:42%;vertical-align:top;"><strong>Q<?= $qNum ?>. <?= htmlspecialchars($qText) ?></strong></td>
              <td style="padding:10px 0;color:#0a0a0a;font-size:0.9rem;"><?= htmlspecialchars($ans['selected'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
          </table>
        </td>
      </tr>

      <!-- Recommendation -->
      <tr>
        <td style="padding:24px 40px;">
          <h2 style="font-size:0.85rem;font-weight:700;color:#0a0a0a;text-transform:uppercase;letter-spacing:1.5px;margin:0 0 12px;padding-bottom:8px;border-bottom:2px solid #f0f0f0;">Why This Lead Needs <?= htmlspecialchars($resultInfo['label']) ?></h2>
          <div style="background:#f5f5f5;border-left:4px solid #9ca3af;padding:14px 18px;border-radius:0 8px 8px 0;">
            <p style="margin:0;color:#0a0a0a;font-size:0.92rem;line-height:1.65;"><?= htmlspecialchars($resultDesc) ?></p>
          </div>
        </td>
      </tr>

      <!-- Action Buttons -->
      <tr>
        <td style="padding:0 40px 36px;text-align:center;">
          <a href="mailto:<?= htmlspecialchars($d['email']) ?>" style="display:inline-block;background:#0a0a0a;color:#ffffff;text-decoration:none;padding:13px 28px;border-radius:8px;font-weight:600;font-size:0.88rem;margin:0 6px 8px;">Reply to Lead</a>
          <?php if (!empty($d['phone'])): ?>
          <a href="tel:<?= htmlspecialchars($d['phone']) ?>" style="display:inline-block;background:#9ca3af;color:#0a0a0a;text-decoration:none;padding:13px 28px;border-radius:8px;font-weight:600;font-size:0.88rem;margin:0 6px 8px;">Call Now</a>
          <?php endif; ?>
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="background:#f9f9f9;padding:18px 40px;text-align:center;border-top:1px solid #eeeeee;">
          <p style="margin:0;color:#999;font-size:0.78rem;">KConsulting Firm (Pty) Ltd &bull; info@thekconsult.co.za &bull; www.thekconsult.co.za</p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
