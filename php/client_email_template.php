<?php
// This template expects the following variables to be passed:
// $data - array containing all form data
// $submission_id - the ID of the submission
/**function getClientEmailTemplate($data, $submission_id) {
	
}*/
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
        .email-body {
            padding: 30px;
        }
        .confirmation-box {
            background-color: #f0f7ff;
            border-left: 4px solid #1a4f72;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 4px;
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
        }
        .detail-label {
            font-weight: 600;
            color: #1a4f72;
            min-width: 160px;
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
            background-color: #1a4f72;
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
            background-color: #f0f7ff;
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
        }
        .logo {
            max-width: 180px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Consultation Request Confirmation</h1>
            <p>Reference #: <?php echo $submission_id; ?></p>
        </div>
        
        <div class="email-body">
            <p>Dear <?php echo htmlspecialchars($data['name']); ?>,</p>
            
            <div class="confirmation-box">
                <h3 style="margin-top: 0;">Thank you for your consultation request</h3>
                <p>We have received your information and our team will review your request shortly.</p>
            </div>
            
            <h3>Request Summary</h3>
            <div class="details-box">
                <div class="detail-item">
                    <span class="detail-label">Reference ID:</span>
                    <span>#<?php echo $submission_id; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Company Name:</span>
                    <span><?php echo htmlspecialchars($data['company']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"> What do you need help with?:</span>
                    <span><?php echo htmlspecialchars($data['services']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Consultation Type:</span>
                    <span><?php echo htmlspecialchars($data['consultation-type']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Submitted On:</span>
                    <span><?php echo date('F j, Y, g:i a'); ?></span>
                </div>
            </div>
            
            <div class="next-steps">
                <h3 style="margin-top: 0;">What Happens Next?</h3>
                
                <div class="step">
                    <div class="step-number">1</div>
                    <div>
                        <strong>Review Process</strong>
                        <p>Our team will carefully review your requirements and qualifications.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div>
                        <strong>Initial Contact</strong>
                        <p>We will contact you within 24-48 hours to discuss your needs.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div>
                        <strong>Consultation Scheduling</strong>
                        <p>We'll work with you to schedule your consultation at your convenience.</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-info">
                <h3 style="margin-top: 0;">Need Immediate Assistance?</h3>
                <p>If you have any urgent questions, please don't hesitate to contact us:</p>
                <p>
                    <strong>Email:</strong> info@thekconsult.co.za<br>
                    <strong>Phone:</strong> +27 64 519 0549<br>
                    <strong>Hours:</strong> Monday-Friday, 8:00 AM - 5:00 PM SAST
                </p>
            </div>
            
            <p>We look forward to the opportunity to work with you.</p>
            
            <p>Best regards, <br>
            <strong>The KConsulting Team</strong></p>
            <p style="color: #666; font-size: 14px; margin-top: 5px;">
                <em>IT & Marketing Solutions | Built on Excellence</em>
            </p>
            
        </div>
        
        <div class="footer">
            <p>This email was sent to <?php echo htmlspecialchars($data['email']); ?> in response to your consultation request.</p>
            <p>&copy; <?php echo date('Y'); ?> KConsulting Firm (Pty) Ltd. All rights reserved.</p>
            <p>Physical Address: Cape Town, South Africa</p>
        </div>
    </div>
</body>
</html>