<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

$allowed_origins = ['https://www.thekconsult.co.za', 'https://thekconsult.co.za', 'http://localhost', 'http://127.0.0.1'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/audit_email_functions.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Trim only,htmlspecialchars is applied once inside email templates
function sanitize($value) {
    return trim($value ?? '');
}

$name        = sanitize($data['name'] ?? '');
$business    = sanitize($data['business_name'] ?? '');
$email       = sanitize($data['email'] ?? '');
$phone       = sanitize($data['phone'] ?? '');
$website_url = sanitize($data['website_url'] ?? '');
$main_goal   = sanitize($data['main_goal'] ?? '');
$challenge   = sanitize($data['biggest_challenge'] ?? '');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required']);
    exit;
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'A valid email address is required']);
    exit;
}
if (empty($website_url)) {
    echo json_encode(['success' => false, 'message' => 'Website URL is required']);
    exit;
}

// Log submission
$log = date('Y-m-d H:i:s') . " | Name: $name | Business: $business | Email: $email | URL: $website_url | Goal: $main_goal\n";
file_put_contents(__DIR__ . '/audit_requests.log', $log, FILE_APPEND | LOCK_EX);

try {
    $conn = getDbConnection();

    $conn->query("CREATE TABLE IF NOT EXISTS website_audit_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        business_name VARCHAR(255),
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        website_url VARCHAR(500) NOT NULL,
        main_goal VARCHAR(255),
        biggest_challenge TEXT,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $conn->prepare(
        "INSERT INTO website_audit_requests (name, business_name, email, phone, website_url, main_goal, biggest_challenge)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param('sssssss', $name, $business, $email, $phone, $website_url, $main_goal, $challenge);

    if (!$stmt->execute()) {
        throw new Exception('Could not save your request. Please try again.');
    }

    $submission_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log('Audit DB error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

$emailData = [
    'name'             => $name,
    'business_name'    => $business,
    'email'            => $email,
    'phone'            => $phone,
    'website_url'      => $website_url,
    'main_goal'        => $main_goal,
    'biggest_challenge' => $challenge,
];

sendAuditAdminEmail($emailData, $submission_id);
sendAuditClientEmail($emailData, $submission_id);

echo json_encode([
    'success'       => true,
    'message'       => 'Your audit request has been received. We will be in touch within one business day.',
    'submission_id' => $submission_id,
]);
