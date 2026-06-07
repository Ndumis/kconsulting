<?php
// Variables available: $data (array), $submission_id (int)
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background: #f9f9f9; }
        .email-container { max-width: 650px; margin: 0 auto; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .email-header { background: linear-gradient(135deg, #1a1a1a 0%, #3a3a3a 100%); color: #fff; padding: 28px 30px; }
        .email-header h1 { margin: 0 0 6px; font-size: 20px; }
        .badge { display: inline-block; background: rgba(255,255,255,0.15); border-radius: 20px; padding: 4px 14px; font-size: 13px; margin-top: 6px; }
        .email-body { padding: 28px 30px; }
        .info-row { display: flex; border-bottom: 1px solid #f0f0f0; padding: 10px 0; font-size: 14px; }
        .info-label { font-weight: 600; color: #555; min-width: 160px; flex-shrink: 0; }
        .info-value { color: #1a1a1a; }
        .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #888; margin: 24px 0 12px; }
        .footer { background: #f5f5f5; padding: 20px 30px; text-align: center; font-size: 13px; color: #888; border-top: 1px solid #e0e0e0; }
        a { color: #1a1a1a; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>New Website Audit Request</h1>
            <p style="margin: 4px 0 0; opacity: 0.7; font-size: 14px;">Submitted via free-website-audit.html</p>
            <div class="badge">Reference #<?php echo $submission_id; ?></div>
        </div>
        <div class="email-body">
            <p class="section-title">Contact Details</p>
            <div class="info-row"><span class="info-label">Name</span><span class="info-value"><?php echo htmlspecialchars($data['name']); ?></span></div>
            <div class="info-row"><span class="info-label">Business Name</span><span class="info-value"><?php echo htmlspecialchars($data['business_name'] ?: '—'); ?></span></div>
            <div class="info-row"><span class="info-label">Email</span><span class="info-value"><a href="mailto:<?php echo htmlspecialchars($data['email']); ?>"><?php echo htmlspecialchars($data['email']); ?></a></span></div>
            <div class="info-row"><span class="info-label">Phone</span><span class="info-value"><?php echo htmlspecialchars($data['phone'] ?: '—'); ?></span></div>

            <p class="section-title">Audit Details</p>
            <div class="info-row"><span class="info-label">Website URL</span><span class="info-value"><a href="<?php echo htmlspecialchars($data['website_url']); ?>" target="_blank"><?php echo htmlspecialchars($data['website_url']); ?></a></span></div>
            <div class="info-row"><span class="info-label">Main Goal</span><span class="info-value"><?php echo htmlspecialchars($data['main_goal'] ?: '—'); ?></span></div>

            <?php if (!empty($data['biggest_challenge'])): ?>
            <p class="section-title">Biggest Challenge</p>
            <div style="background: #f9f9f9; border-left: 3px solid #ddd; border-radius: 4px; padding: 14px 16px; font-size: 14px; color: #444; line-height: 1.6;">
                <?php echo nl2br(htmlspecialchars($data['biggest_challenge'])); ?>
            </div>
            <?php endif; ?>

            <p class="section-title">Submission Info</p>
            <div class="info-row"><span class="info-label">Reference ID</span><span class="info-value">#<?php echo $submission_id; ?></span></div>
            <div class="info-row"><span class="info-label">Submitted</span><span class="info-value"><?php echo date('F j, Y \a\t g:i a T'); ?></span></div>
        </div>
        <div class="footer">
            <p>KConsulting Firm (Pty) Ltd &mdash; info@thekconsult.co.za &mdash; +27 64 519 0549</p>
            <p>&copy; <?php echo date('Y'); ?> KConsulting Firm (Pty) Ltd. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
