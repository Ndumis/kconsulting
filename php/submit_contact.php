<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/contact_email_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

// Validate required fields
if (empty(trim($input['name'] ?? ''))) {
    echo json_encode(['success' => false, 'message' => 'Please enter your name.']);
    exit;
}
if (empty(trim($input['email'] ?? ''))) {
    echo json_encode(['success' => false, 'message' => 'Please enter your email address.']);
    exit;
}
if (!filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}
if (empty(trim($input['message'] ?? ''))) {
    echo json_encode(['success' => false, 'message' => 'Please enter your message.']);
    exit;
}

// Trim only, htmlspecialchars is applied once inside email templates
$name    = trim($input['name']);
$email   = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
$company = trim($input['company'] ?? '');
$service = trim($input['service'] ?? '');
$message = trim($input['message']);

$submission_id = 0;

try {
    $conn = getDbConnection();

    $conn->query("CREATE TABLE IF NOT EXISTS contact_enquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        company VARCHAR(255),
        service VARCHAR(255),
        message TEXT,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $conn->prepare(
        "INSERT INTO contact_enquiries (name, email, company, service, message) VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param('sssss', $name, $email, $company, $service, $message);

    if (!$stmt->execute()) {
        throw new Exception('Failed to save your message.');
    }

    $submission_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log('Contact form DB error: ' . $e->getMessage());
    // Fall back to log file so no enquiry is lost
    $log = ['name' => $name, 'email' => $email, 'company' => $company, 'service' => $service, 'timestamp' => date('Y-m-d H:i:s')];
    file_put_contents(__DIR__ . '/contact_enquiries.log', json_encode($log) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

$emailData = compact('name', 'email', 'company', 'service', 'message');

sendContactAdminEmail($emailData, $submission_id);
sendContactClientEmail($emailData, $submission_id);

echo json_encode(['success' => true, 'message' => 'Thank you for your message! We will get back to you within 24 hours.']);
