<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/diagnostic_email_functions.php';

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

// Trim only, htmlspecialchars is applied once inside email templates
$name           = trim($data['name']           ?? '');
$email          = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$phone          = trim($data['phone']          ?? '');
$challenge      = trim($data['challenge']      ?? '');
$goal           = trim($data['goal']           ?? '');
$budget         = trim($data['budget']         ?? '');
$team_size      = trim($data['team_size']      ?? '');
$timeline       = trim($data['timeline']       ?? '');
$recommendation = trim($data['recommendation'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

try {
    $conn = getDbConnection();

    $conn->query("CREATE TABLE IF NOT EXISTS diagnostic_responses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        challenge VARCHAR(255),
        goal VARCHAR(255),
        budget VARCHAR(100),
        team_size VARCHAR(100),
        timeline VARCHAR(100),
        recommendation TEXT,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $conn->prepare(
        "INSERT INTO diagnostic_responses
         (name, email, phone, challenge, goal, budget, team_size, timeline, recommendation)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param('sssssssss', $name, $email, $phone, $challenge, $goal, $budget, $team_size, $timeline, $recommendation);

    if (!$stmt->execute()) {
        throw new Exception('Could not save diagnostic response');
    }

    $submission_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log('Diagnostic DB error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

$emailData = compact('name', 'email', 'phone', 'challenge', 'goal', 'budget', 'team_size', 'timeline', 'recommendation');

sendDiagnosticAdminEmail($emailData, $submission_id);
sendDiagnosticClientEmail($emailData, $submission_id);

echo json_encode(['success' => true, 'submission_id' => $submission_id]);
