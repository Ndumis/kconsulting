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
        .email-header { background: linear-gradient(135deg, #1a1a1a 0%, #3a3a3a 100%); color: #fff; padding: 30px; text-align: center; }
        .email-header h1 { margin: 0 0 8px; font-size: 22px; }
        .email-body { padding: 30px; }
        .confirmation-box { background: #f0f7ff; border-left: 4px solid #1a4f72; padding: 18px 20px; margin-bottom: 24px; border-radius: 4px; }
        .confirmation-box h3 { margin: 0 0 8px; font-size: 16px; color: #1a4f72; }
        .confirmation-box p { margin: 0; font-size: 14px; color: #444; }
        .details-box { background: #f9f9f9; border: 1px solid #eee; border-radius: 6px; padding: 18px 20px; margin: 20px 0; }
        .detail-item { display: flex; margin-bottom: 10px; font-size: 14px; }
        .detail-label { font-weight: 600; color: #1a4f72; min-width: 160px; }
        .next-steps { background: #f9f9f9; border-radius: 6px; padding: 18px 20px; margin: 24px 0; }
        .step { display: flex; margin-bottom: 14px; align-items: flex-start; }
        .step-number { background: #1a1a1a; color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 14px; flex-shrink: 0; font-weight: 700; font-size: 13px; }
        .step strong { display: block; font-size: 14px; margin-bottom: 4px; }
        .step p { margin: 0; font-size: 13px; color: #666; }
        .contact-info { background: #f0f7ff; border-radius: 6px; padding: 18px 20px; margin: 24px 0; }
        .footer { background: #f5f5f5; padding: 24px 30px; text-align: center; font-size: 13px; color: #888; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Audit Request Received</h1>
            <p style="margin: 0; opacity: 0.7; font-size: 14px;">Reference #<?php echo $submission_id; ?></p>
        </div>

        <div class="email-body">
            <p>Dear <?php echo htmlspecialchars($data['name']); ?>,</p>

            <div class="confirmation-box">
                <h3>Thank you for your free audit request!</h3>
                <p>We've received your submission and our team will review your website shortly. You can expect to hear from us within one business day.</p>
            </div>

            <h3 style="font-size: 16px; margin-bottom: 12px;">Your Request Summary</h3>
            <div class="details-box">
                <div class="detail-item">
                    <span class="detail-label">Reference ID:</span>
                    <span>#<?php echo $submission_id; ?></span>
                </div>
                <?php if (!empty($data['business_name'])): ?>
                <div class="detail-item">
                    <span class="detail-label">Business Name:</span>
                    <span><?php echo htmlspecialchars($data['business_name']); ?></span>
                </div>
                <?php endif; ?>
                <div class="detail-item">
                    <span class="detail-label">Website:</span>
                    <span><?php echo htmlspecialchars($data['website_url']); ?></span>
                </div>
                <?php if (!empty($data['main_goal'])): ?>
                <div class="detail-item">
                    <span class="detail-label">Main Goal:</span>
                    <span><?php echo htmlspecialchars($data['main_goal']); ?></span>
                </div>
                <?php endif; ?>
                <div class="detail-item">
                    <span class="detail-label">Submitted On:</span>
                    <span><?php echo date('F j, Y, g:i a'); ?></span>
                </div>
            </div>

            <div class="next-steps">
                <h3 style="margin-top: 0; font-size: 16px;">What Happens Next?</h3>
                <div class="step">
                    <div class="step-number">1</div>
                    <div>
                        <strong>We Review Your Website</strong>
                        <p>Our team analyses your website for clarity, lead generation, SEO, AI visibility, and conversion opportunities.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div>
                        <strong>We Prepare Your Findings</strong>
                        <p>We document our observations and identify your biggest growth opportunities.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div>
                        <strong>We Get In Touch</strong>
                        <p>We will contact you within one business day to share our findings and recommendations.</p>
                    </div>
                </div>
            </div>

            <div class="contact-info">
                <h3 style="margin-top: 0; font-size: 15px;">Need To Reach Us Sooner?</h3>
                <p style="font-size: 14px; margin: 0;">
                    <strong>Email:</strong> info@thekconsult.co.za<br>
                    <strong>Phone:</strong> +27 64 519 0549<br>
                    <strong>WhatsApp:</strong> <a href="https://wa.me/27645190549">wa.me/27645190549</a><br>
                    <strong>Hours:</strong> Monday–Friday, 8:00 AM – 5:00 PM SAST
                </p>
            </div>

            <p>We look forward to sharing what we find.</p>
            <p>Best regards,<br><strong>The KConsulting Team</strong></p>
            <p style="color: #888; font-size: 13px; margin-top: 4px;"><em>IT &amp; Marketing Solutions | Built on Excellence</em></p>
        </div>

        <div class="footer">
            <p>This email was sent to <?php echo htmlspecialchars($data['email']); ?> in response to your audit request.</p>
            <p>&copy; <?php echo date('Y'); ?> KConsulting Firm (Pty) Ltd. All rights reserved.</p>
            <p>Cape Town, South Africa</p>
        </div>
    </div>
</body>
</html>
