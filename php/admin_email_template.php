<?php
// This template expects the following variables to be passed:
// $data - array containing all form data
// $submission_id - the ID of the submission
// $qualification_score - the calculated qualification score
/**function getAdminEmailTemplate($data, $submission_id) {
	
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
        .email-body {
            padding: 25px;
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
            color: #1a4f72;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e6e6e6;
        }
        .field {
            margin-bottom: 12px;
        }
        .field-label {
            font-weight: 600;
            color: #555555;
            display: inline-block;
            width: 180px;
            vertical-align: top;
        }
        .field-value {
            display: inline-block;
            width: calc(100% - 190px);
            vertical-align: top;
        }
        .score-badge {
            display: inline-block;
            background-color: #1a4f72;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666666;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>New Consultation Request</h1>
            <p>Submission ID: #<?php echo $submission_id; ?> | Qualification Score: <span class="score-badge"><?php echo $qualification_score; ?>/100</span></p>
        </div>
        
        <div class="email-body">
            <div class="section">
                <div class="section-title">Company Information</div>
                <div class="field">
                    <span class="field-label">Company:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['company']); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Industry:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['industry']); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Company Size:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['company-size']); ?></span>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Contact Information</div>
                <div class="field">
                    <span class="field-label">Name:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['name']); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Position:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['position']); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Email:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['email']); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Phone:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['phone']); ?></span>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Project Requirements</div>
                <div class="field">
                    <span class="field-label">Service Interest:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['services']); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Consultation Type:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['consultation-type']); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Timeline:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['timeline']); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Budget:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['budget']); ?></span>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Project Details</div>
                <div class="field">
                    <span class="field-label">Current Challenges:</span>
                    <span class="field-value"><?php echo nl2br(htmlspecialchars($data['current-challenges'])); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Desired Outcomes:</span>
                    <span class="field-value"><?php echo nl2br(htmlspecialchars($data['desired-outcomes'])); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Current Systems:</span>
                    <span class="field-value"><?php echo isset($data['current-systems']) ? nl2br(htmlspecialchars($data['current-systems'])) : 'Not provided'; ?></span>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Decision Process</div>
                <div class="field">
                    <span class="field-label">Decision Maker:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['decision-maker']); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Decision Timeline:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['decision-timeline']); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Evaluating Competitors:</span>
                    <span class="field-value"><?php echo isset($data['competitors']) ? htmlspecialchars($data['competitors']) : 'Not provided'; ?></span>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Consultation Preferences</div>
                <div class="field">
                    <span class="field-label">Meeting Type:</span>
                    <span class="field-value"><?php echo htmlspecialchars($data['meeting-type']); ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Preferred Location:</span>
                    <span class="field-value"><?php echo isset($data['preferred-location']) ? htmlspecialchars($data['preferred-location']) : 'Not specified'; ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Availability:</span>
                    <span class="field-value"><?php echo isset($data['availability']) ? htmlspecialchars($data['availability']) : 'Not specified'; ?></span>
                </div>
                <div class="field">
                    <span class="field-label">Additional Info:</span>
                    <span class="field-value"><?php echo isset($data['additional-info']) ? nl2br(htmlspecialchars($data['additional-info'])) : 'None'; ?></span>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>This email was automatically generated from the consultation form on your website.</p>
            <p>&copy; <?php echo date('Y'); ?> KConsulting Firm (Pty) Ltd. All rights reserved.</p>
        </div>
    </div>
</body>
</html>