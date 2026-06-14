<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/quiz_email_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

// Trim only,htmlspecialchars is applied once inside email templates
$name        = trim($data['name']        ?? '');
$email       = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$phone       = trim($data['phone']       ?? '');
$result_type = trim($data['result_type'] ?? '');
$answers_json = json_encode($data['answers'] ?? []);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

try {
    $conn = getDbConnection();

    $conn->query("CREATE TABLE IF NOT EXISTS quiz_responses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        answers JSON,
        result_type VARCHAR(100),
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $conn->prepare(
        "INSERT INTO quiz_responses (name, email, phone, answers, result_type) VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param('sssss', $name, $email, $phone, $answers_json, $result_type);

    if (!$stmt->execute()) {
        throw new Exception('Could not save quiz response');
    }

    $submission_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log('Quiz submission error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

$emailData = [
    'name'        => $name,
    'email'       => $email,
    'phone'       => $phone,
    'result_type' => $result_type,
    'answers'     => $data['answers'] ?? [],
];

sendQuizAdminEmail($emailData, $submission_id);
sendQuizClientEmail($emailData, $submission_id);

echo json_encode(['success' => true, 'submission_id' => $submission_id]);
