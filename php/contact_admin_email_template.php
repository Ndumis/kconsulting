<?php
// This template expects the following variables to be passed:
// $name - sender's name
// $email - sender's email
// $company - sender's company (optional)
// $service - service interest (optional)
// $message - the message
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
            max-width: 700px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #4a4a4a 100%);
            color: #ffffff;
            padding: 25px;
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
        .section {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eeeeee;
        }
        .section:last-child {
            border-bottom: none;
        }
        .section-title {
            color: #0a0a0a;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e6e6e6;
        }
        .field {
            margin-bottom: 12px;
            display: flex;
            flex-wrap: wrap;
        }
        .field-label {
            font-weight: 600;
            color: #555555;
            min-width: 150px;
            display: inline-block;
        }
        .field-value {
            color: #333333;
            flex: 1;
        }
        .message-box {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
            border-left: 4px solid #0a0a0a;
        }
        .message-box p {
            margin: 0;
            white-space: pre-line;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666666;
            border-top: 1px solid #e0e0e0;
        }
        .badge {
            display: inline-block;
            background-color: #0a0a0a;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        @media (max-width: 600px) {
            .field {
                flex-direction: column;
            }
            .field-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>New Contact Form Message</h1>
            <p>Received from <?php echo htmlspecialchars($name); ?></p>
        </div>
        
        <div class="email-body">
            <div class="section">
                <div class="section-title">Sender Information</div>
                <div class="field">
                    <span class="field-label">Name:</span>
                    <span class="field-value"><?php echo htmlspecialchars($name); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Email:</span>
                    <span class="field-value">
                        <a href="mailto:<?php echo htmlspecialchars($email); ?>" style="color: #6b7280; text-decoration: none;">
                            <?php echo htmlspecialchars($email); ?>
                        </a>
                    </span>
                </div>
                <?php if (!empty($company)): ?>
                <div class="field">
                    <span class="field-label">Company:</span>
                    <span class="field-value"><?php echo htmlspecialchars($company); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($service)): ?>
                <div class="field">
                    <span class="field-label">Service Interest:</span>
                    <span class="field-value"><?php echo htmlspecialchars($service); ?></span>
                </div>
                <?php endif; ?>
                <div class="field">
                    <span class="field-label">Submitted:</span>
                    <span class="field-value"><?php echo date('F j, Y \a\t g:i a'); ?></span>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Message</div>
                <div class="message-box">
                    <p><?php echo nl2br(htmlspecialchars($message)); ?></p>
                </div>
            </div>
            
            <div style="background-color: #f5f5f5; padding: 15px; border-radius: 6px; margin-top: 20px;">
                <p style="margin: 0; color: #0a0a0a;">
                    <strong>Quick Actions:</strong><br>
                    • <a href="mailto:<?php echo htmlspecialchars($email); ?>?subject=Re:%20Your%20KConsulting%20Inquiry" style="color: #6b7280;">Reply to <?php echo htmlspecialchars($name); ?></a><br>
                    • <a href="#" style="color: #6b7280;">View in CRM</a> (coming soon)
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p>This email was automatically generated from the contact form on your website.</p>
            <p>&copy; <?php echo date('Y'); ?> KConsulting Firm (Pty) Ltd. All rights reserved.</p>
        </div>
    </div>
</body>
</html>