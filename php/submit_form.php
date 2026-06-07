<?php
header('Content-Type: application/json');

// Database configuration
$db_host = 'localhost';
$db_name = 'kconsulting';
$db_user = 'root';
$db_pass = '';

$logFile = __DIR__ . '/consultation_enquiries.log';

require_once __DIR__ . '/email_functions.php';

// Response array
$response = ['success' => false, 'message' => ''];

try {
    // Get JSON data from AJAX request
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('No data received');
    }
    
    // Validate required fields
    if (empty($input['name'])) {
        throw new Exception('Please enter your name.');
    }
    
    if (empty($input['email'])) {
        throw new Exception('Please enter your email address.');
    }
    
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address.');
    }
    
    // Sanitize inputs
    foreach ($input as $key => $value) {
        if (is_string($value)) {
            $input[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
    }
    
    // Log the enquiry
    $loggingData = [
        'consultation data' => $input,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    file_put_contents(
        $logFile,
        json_encode($loggingData) . PHP_EOL,
        FILE_APPEND
    );
    
    // Connect to database
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed. Please try again later.');
    }
    
    // Check if table exists, create if not
    $conn->query("
        CREATE TABLE IF NOT EXISTS consultation_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company VARCHAR(255),
            industry VARCHAR(255),
            company_size VARCHAR(50),
            name VARCHAR(255) NOT NULL,
            position VARCHAR(255),
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50),
            services TEXT,
            consultation_type VARCHAR(100),
            timeline VARCHAR(100),
            budget VARCHAR(100),
            current_challenges TEXT,
            desired_outcomes TEXT,
            current_systems TEXT,
            decision_maker VARCHAR(255),
            decision_timeline VARCHAR(100),
            competitors TEXT,
            meeting_type VARCHAR(100),
            preferred_location VARCHAR(255),
            availability TEXT,
            additional_info TEXT,
            qualification_score INT DEFAULT 0,
            submitted_at DATETIME NOT NULL
        )
    ");
    
    // Prepare SQL statement
    $stmt = $conn->prepare("
        INSERT INTO consultation_requests 
        (company, industry, company_size, name, position, email, phone, 
         services, consultation_type, timeline, budget, current_challenges, 
         desired_outcomes, current_systems, decision_maker, decision_timeline, 
         competitors, meeting_type, preferred_location, availability, 
         additional_info, qualification_score, submitted_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    if (!$stmt) {
        $dbError = $conn->error;
        throw new Exception('Database prepare error: ' . $dbError);
    }
    
    // Set default values for missing fields
    $fields = [
        'company' => $input['company'] ?? '',
        'industry' => $input['industry'] ?? '',
        'company-size' => $input['company-size'] ?? '',
        'name' => $input['name'] ?? '',
        'position' => $input['position'] ?? '',
        'email' => $input['email'] ?? '',
        'phone' => $input['phone'] ?? '',
        'services' => isset($input['services']) ? (is_array($input['services']) ? implode(', ', $input['services']) : $input['services']) : '',
        'consultation-type' => $input['consultation-type'] ?? '',
        'timeline' => $input['timeline'] ?? '',
        'budget' => $input['budget'] ?? '',
        'current-challenges' => $input['current-challenges'] ?? '',
        'desired-outcomes' => $input['desired-outcomes'] ?? '',
        'current-systems' => $input['current-systems'] ?? '',
        'decision-maker' => $input['decision-maker'] ?? '',
        'decision-timeline' => $input['decision-timeline'] ?? '',
        'competitors' => $input['competitors'] ?? '',
        'meeting-type' => $input['meeting-type'] ?? '',
        'preferred-location' => $input['preferred-location'] ?? '',
        'availability' => $input['availability'] ?? '',
        'additional-info' => $input['additional-info'] ?? '',
        'qualificationScore' => isset($input['qualificationScore']) ? intval($input['qualificationScore']) : 0
    ];
    
    // Bind parameters
    $stmt->bind_param(
        "sssssssssssssssssssssi",
        $fields['company'],
        $fields['industry'],
        $fields['company-size'],
        $fields['name'],
        $fields['position'],
        $fields['email'],
        $fields['phone'],
        $fields['services'],
        $fields['consultation-type'],
        $fields['timeline'],
        $fields['budget'],
        $fields['current-challenges'],
        $fields['desired-outcomes'],
        $fields['current-systems'],
        $fields['decision-maker'],
        $fields['decision-timeline'],
        $fields['competitors'],
        $fields['meeting-type'],
        $fields['preferred-location'],
        $fields['availability'],
        $fields['additional-info'],
        $fields['qualificationScore']
    );
    
    // Execute query
    if (!$stmt->execute()) {
        throw new Exception('Failed to save your request. Please try again.');
    }
    
    $insert_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();
    
    // Send emails
    $adminEmailResult = sendAdminEmail($fields, $insert_id);
    $clientEmailResult = sendClientEmail($fields, $insert_id);
    
    if (!$adminEmailResult) {
        error_log("Failed to send admin email for submission ID: " . $insert_id);
    }
    
    if (!$clientEmailResult) {
        error_log("Failed to send client email for submission ID: " . $insert_id);
    }
    
    $response['success'] = true;
    $response['message'] = 'Thank you for your consultation request! We will contact you within 24 hours.';
    $response['submission_id'] = $insert_id;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Form submission error: ' . $e->getMessage());
}

echo json_encode($response);
?>