<?php
// This template expects the following variables to be passed:
// $name - recipient's name
// $email - recipient's email
// $company - company name (optional)
// $service - service interest (optional)
// $message - the message they sent
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #4a4a4a 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
        }
        .email-header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px;
        }
        .confirmation-box {
            background-color: #f5f5f5;
            border-left: 4px solid #0a0a0a;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        .confirmation-box h3 {
            margin-top: 0;
            color: #0a0a0a;
        }
        .details-box {
            background-color: #f9f9f9;
            border: 1px solid #eeeeee;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-item {
            margin-bottom: 12px;
            display: flex;
            flex-wrap: wrap;
        }
        .detail-label {
            font-weight: 600;
            color: #0a0a0a;
            min-width: 140px;
        }
        .detail-value {
            color: #333333;
            flex: 1;
        }
        .message-preview {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
            font-style: italic;
            border-left: 4px solid #0a0a0a;
        }
        .next-steps {
            background-color: #f9f9f9;
            border-radius: 6px;
            padding: 20px;
            margin: 25px 0;
        }
        .step {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }
        .step-number {
            background-color: #0a0a0a;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
            font-weight: bold;
        }
        .contact-info {
            background-color: #f5f5f5;
            border-radius: 6px;
            padding: 20px;
            margin: 25px 0;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 25px;
            text-align: center;
            font-size: 14px;
            color: #666666;
            border-top: 1px solid #e0e0e0;
        }
        .signature {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
        }
        @media (max-width: 600px) {
            .detail-item {
                flex-direction: column;
            }
            .detail-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Thank You for Contacting KConsulting</h1>
            <p>We've received your message</p>
        </div>
        
        <div class="email-body">
            <p>Dear <?php echo htmlspecialchars($name); ?>,</p>
            
            <div class="confirmation-box">
                <h3>Message Received</h3>
                <p>Thank you for reaching out to us. We have successfully received your message and our team will review it shortly.</p>
            </div>
            
            <h3 style="color: #0a0a0a;">Your Message Summary</h3>
            <div class="details-box">
                <div class="detail-item">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($name); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($email); ?></span>
                </div>
                <?php if (!empty($company)): ?>
                <div class="detail-item">
                    <span class="detail-label">Company Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($company); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($service)): ?>
                <div class="detail-item">
                    <span class="detail-label"> What do you need help with?:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($service); ?></span>
                </div>
                <?php endif; ?>
                <div class="detail-item">
                    <span class="detail-label">Submitted:</span>
                    <span class="detail-value"><?php echo date('F j, Y \a\t g:i a'); ?></span>
                </div>
                <div class="detail-item" style="margin-top: 15px;">
                    <span class="detail-label">What’s your biggest challenge right now?:</span>
                    <div class="message-preview">
                        "<?php echo htmlspecialchars(substr($message, 0, 200)) . (strlen($message) > 200 ? '...' : ''); ?>"
                    </div>
                </div>
            </div>
            
            <div class="next-steps">
                <h3 style="margin-top: 0; color: #0a0a0a;">What Happens Next?</h3>
                
                <div class="step">
                    <div class="step-number">1</div>
                    <div>
                        <strong>Review Process</strong>
                        <p style="margin: 5px 0 0; color: #666;">Our team will carefully review your message</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div>
                        <strong>Initial Contact</strong>
                        <p style="margin: 5px 0 0; color: #666;">We will contact you within 24-48 hours to discuss your needs</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div>
                        <strong>Consultation Scheduling</strong>
                        <p style="margin: 5px 0 0; color: #666;">We'll work with you to schedule your consultation at your convenience.</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-info">
                <h3 style="margin-top: 0; color: #0a0a0a;">Need Faster Assistance?</h3>
                <p>If your matter is urgent, feel free to contact us directly:</p>
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 5px 0;"><strong>Phone:</strong></td>
                        <td>+27 64 519 0549</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Hours:</strong></td>
                        <td>Monday-Friday, 8:00 AM - 5:00 PM SAST</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>WhatsApp:</strong></td>
                        <td><a href="https://wa.me/27645190549" style="color: #6b7280;">+27 64 519 0549</a></td>
                    </tr>
                </table>
            </div>
            
            <div class="signature">
                <p>Best regards,</p>
                <p><strong>The KConsulting Team</strong></p>
                <p style="color: #666; font-size: 14px; margin-top: 5px;">
                    <em>IT & Marketing Solutions | Built on Excellence</em>
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p>This email was sent to <?php echo htmlspecialchars($email); ?> in response to your contact request.</p>
            <p>&copy; <?php echo date('Y'); ?> KConsulting Firm (Pty) Ltd. All rights reserved.</p>
            <p style="font-size: 12px; margin-top: 10px;">
                <a href="https://www.thekconsult.co.za/privacy-policy.html" style="color: #666;">Privacy Policy</a> | 
                <a href="https://www.thekconsult.co.za/contact.html" style="color: #666;">Contact Us</a>
            </p>
        </div>
    </div>
</body>
</html>