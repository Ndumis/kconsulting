<?php
declare(strict_types=1);

use phpseclib3\Crypt\RSA;

/**
 * TACC Membership Registration — WhatsApp Flow endpoint
 *
 * This endpoint ONLY:
 *   - decrypts the Meta Flow request
 *   - validates the registration
 *   - checks duplicate email
 *   - saves the registration
 *   - queues email jobs
 *   - returns SUCCESS
 *
 * It deliberately does NOT send email.
 */

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

$logFile = __DIR__ . '/tacc_requests.log';
$errorLogFile = __DIR__ . '/tacc_php_errors.log';
@ini_set('error_log', $errorLogFile);

function taccLog(string $message, array $context = []): void
{
    global $errorLogFile;

    $line = date('Y-m-d H:i:s') . ' | ' . $message;

    if ($context) {
        $json = json_encode(
            $context,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if ($json !== false) {
            $line .= ' | ' . $json;
        }
    }

    $line .= PHP_EOL;
    @file_put_contents($errorLogFile, $line, FILE_APPEND | LOCK_EX);
    @error_log('TACC FLOW: ' . rtrim($line));
}

register_shutdown_function(function (): void {
    $error = error_get_last();

    if (
        $error &&
        in_array(
            $error['type'],
            [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR],
            true
        )
    ) {
        taccLog('FATAL PHP ERROR', [
            'type'    => $error['type'],
            'message' => $error['message'] ?? 'Unknown',
            'file'    => $error['file'] ?? 'Unknown',
            'line'    => $error['line'] ?? 0,
        ]);
    }
});

set_exception_handler(function (Throwable $e): void {
    taccLog('UNCAUGHT THROWABLE', [
        'class'   => get_class($e),
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
        'trace'   => $e->getTraceAsString(),
    ]);

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode(['error' => 'Internal server error']);
    exit;
});

function sendEncrypted(array $response, string $aesKey, string $iv): void
{
    /*
     * IMPORTANT:
     * Do not log AES key, IV, encrypted payload, or full response data.
     * We only log safe diagnostic metadata.
     */

    taccLog('SEND ENCRYPTED: entered', [
        'screen' => $response['screen'] ?? null,
        'has_version' => isset($response['version']),
        'data_type' => isset($response['data'])
            ? gettype($response['data'])
            : 'missing',
    ]);

    try {

        /* ---------------------------------------------------------
         * STEP 1 — Validate AES key
         * --------------------------------------------------------- */
        $aesKeyLength = strlen($aesKey);

        taccLog('SEND ENCRYPTED: AES key length checked', [
            'length' => $aesKeyLength,
        ]);

        if ($aesKeyLength !== 16) {
            throw new RuntimeException(
                'Invalid AES key length: ' . $aesKeyLength
            );
        }


        /* ---------------------------------------------------------
         * STEP 2 — Validate IV
         * --------------------------------------------------------- */
        $ivLength = strlen($iv);

        taccLog('SEND ENCRYPTED: IV length checked', [
            'length' => $ivLength,
        ]);

        if ($ivLength !== 16) {
            throw new RuntimeException(
                'Invalid IV length: ' . $ivLength
            );
        }


        /* ---------------------------------------------------------
         * STEP 3 — Add Flow response version
         * --------------------------------------------------------- */
        if (!isset($response['version'])) {
            $response = ['version' => '3.0'] + $response;
        }

        taccLog('SEND ENCRYPTED: response structure prepared', [
            'version' => $response['version'] ?? null,
            'screen' => $response['screen'] ?? null,
            'data_type' => isset($response['data'])
                ? gettype($response['data'])
                : 'missing',
        ]);


        /* ---------------------------------------------------------
         * STEP 4 — JSON encode response
         * --------------------------------------------------------- */
        $plaintext = json_encode(
            $response,
            JSON_UNESCAPED_SLASHES
        );

        if ($plaintext === false) {
            throw new RuntimeException(
                'JSON encoding failed: ' . json_last_error_msg()
            );
        }

        taccLog('SEND ENCRYPTED: JSON encoding successful', [
            'plaintext_length' => strlen($plaintext),
        ]);


        /* ---------------------------------------------------------
         * STEP 5 — Generate response IV
         * --------------------------------------------------------- */
        $responseIv = '';

        for ($i = 0; $i < strlen($iv); $i++) {
            $responseIv .= chr(ord($iv[$i]) ^ 0xFF);
        }

        taccLog('SEND ENCRYPTED: response IV generated', [
            'response_iv_length' => strlen($responseIv),
        ]);


        /* ---------------------------------------------------------
         * STEP 6 — AES-128-GCM encryption
         * --------------------------------------------------------- */
        $tag = '';

        taccLog('SEND ENCRYPTED: starting AES-128-GCM encryption');

        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-128-gcm',
            $aesKey,
            OPENSSL_RAW_DATA,
            $responseIv,
            $tag,
            '',
            16
        );

        if ($ciphertext === false) {
            $opensslError = openssl_error_string();

            throw new RuntimeException(
                'AES-128-GCM response encryption failed' .
                ($opensslError ? ': ' . $opensslError : '')
            );
        }

        taccLog('SEND ENCRYPTED: AES encryption successful', [
            'ciphertext_length' => strlen($ciphertext),
            'tag_length' => strlen($tag),
        ]);


        /* ---------------------------------------------------------
         * STEP 7 — Validate authentication tag
         * --------------------------------------------------------- */
        if (strlen($tag) !== 16) {
            throw new RuntimeException(
                'Invalid AES-GCM authentication tag length: ' .
                strlen($tag)
            );
        }

        taccLog('SEND ENCRYPTED: authentication tag validated', [
            'tag_length' => strlen($tag),
        ]);


        /* ---------------------------------------------------------
         * STEP 8 — Combine ciphertext + tag
         * --------------------------------------------------------- */
        $encryptedPayload = $ciphertext . $tag;

        taccLog('SEND ENCRYPTED: ciphertext and tag combined', [
            'encrypted_binary_length' => strlen($encryptedPayload),
        ]);


        /* ---------------------------------------------------------
         * STEP 9 — Base64 encode
         * --------------------------------------------------------- */
        $base64Response = base64_encode($encryptedPayload);

        if ($base64Response === false) {
            throw new RuntimeException(
                'Base64 encoding failed'
            );
        }

        taccLog('SEND ENCRYPTED: Base64 encoding successful', [
            'base64_length' => strlen($base64Response),
        ]);


        /* ---------------------------------------------------------
         * STEP 10 — Send HTTP response
         * --------------------------------------------------------- */
        if (!headers_sent()) {
            header('Content-Type: text/plain; charset=utf-8');
        }

        taccLog('SEND ENCRYPTED: sending HTTP 200 response', [
            'screen' => $response['screen'] ?? null,
            'http_status' => http_response_code(),
            'base64_length' => strlen($base64Response),
        ]);

        echo $base64Response;

        taccLog('SEND ENCRYPTED: response body echoed, exiting');

        exit;

    } catch (Throwable $e) {

        taccLog('SEND ENCRYPTED: FAILED', [
            'error_class' => get_class($e),
            'message' => $e->getMessage(),
            'screen' => $response['screen'] ?? null,
        ]);

        http_response_code(500);

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode([
            'error' => 'Encryption failed'
        ]);

        exit;
    }
}


function sendSuccess(
    string $aesKey,
    string $iv,
    string $flowToken
): void {
    /*
     * Terminal Flow responses echo the flow_token through
     * extension_message_response.params.flow_token.
     */
    sendEncrypted(
        [
            'screen' => 'SUCCESS',
            'data'   => [
                'extension_message_response' => [
                    'params' => [
                        'flow_token' => $flowToken,
                    ],
                ],
            ],
        ],
        $aesKey,
        $iv
    );
}

function sendError(string $aesKey, string $iv, string $message): void
{
    /*
     * The currently published Flow has no ERROR screen.
     * Therefore this keeps the user on DECLARATION.
     *
     * For the duplicate-email message to be visibly displayed, add the
     * optional Flow UI patch supplied with this package.
     */
    sendEncrypted(
        [
            'screen' => 'DECLARATION',
            'data'   => [
                'error_message' => $message,
                'has_error'     => true,
            ],
        ],
        $aesKey,
        $iv
    );
}

function s(array $a, string $key, string $default = ''): string
{
    return trim((string)($a[$key] ?? $default));
}

/* -------------------------------------------------------------------------
 * 1. Crypto bootstrap
 * ---------------------------------------------------------------------- */

try {
    taccLog('TACC endpoint START', [
        'php'    => PHP_VERSION,
        'sapi'   => PHP_SAPI,
        'script' => __FILE__,
    ]);

    foreach (['openssl', 'json', 'mysqli'] as $extension) {
        if (!extension_loaded($extension)) {
            throw new RuntimeException(
                'Required PHP extension is not loaded: ' . $extension
            );
        }
    }

    $autoloadCandidates = [
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/vendor/autoload.php',
    ];

    $autoloadLoaded = false;

    foreach ($autoloadCandidates as $autoloadPath) {
        if (is_file($autoloadPath) && is_readable($autoloadPath)) {
            require_once $autoloadPath;
            $autoloadLoaded = true;
            break;
        }
    }

    if (!$autoloadLoaded) {
        throw new RuntimeException('Composer autoload.php not found.');
    }

    if (!class_exists(RSA::class)) {
        throw new RuntimeException(
            'phpseclib3\\Crypt\\RSA is not available.'
        );
    }

    $privateKeyPath = __DIR__ . '/../tacc_keys/private.pem';

    if (!is_file($privateKeyPath) || !is_readable($privateKeyPath)) {
        throw new RuntimeException(
            'Private RSA key file is missing or unreadable.'
        );
    }

    $privateKeyContents = file_get_contents($privateKeyPath);

    if (
        $privateKeyContents === false ||
        trim($privateKeyContents) === ''
    ) {
        throw new RuntimeException(
            'Private RSA key exists but could not be read.'
        );
    }

    RSA::loadPrivateKey($privateKeyContents);

    taccLog('TACC crypto bootstrap completed successfully');
} catch (Throwable $e) {
    taccLog('TACC BOOTSTRAP ERROR', [
        'class'   => get_class($e),
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ]);

    http_response_code(500);
    echo json_encode(['error' => 'Endpoint bootstrap failed']);
    exit;
}

/* -------------------------------------------------------------------------
 * 2. Read and decrypt Meta request
 * ---------------------------------------------------------------------- */

$raw = file_get_contents('php://input');

if ($raw === false || $raw === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Empty request body']);
    exit;
}

$request = json_decode($raw, true);

if (!is_array($request)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$encryptedAesKey = base64_decode(
    (string)($request['encrypted_aes_key'] ?? ''),
    true
);

$encryptedFlowData = base64_decode(
    (string)($request['encrypted_flow_data'] ?? ''),
    true
);

$initialVector = base64_decode(
    (string)($request['initial_vector'] ?? ''),
    true
);

if (
    $encryptedAesKey === false ||
    $encryptedFlowData === false ||
    $initialVector === false ||
    $encryptedAesKey === '' ||
    $encryptedFlowData === '' ||
    $initialVector === ''
) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid encrypted fields']);
    exit;
}

if (strlen($encryptedAesKey) !== 256) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid encrypted AES key length']);
    exit;
}

if (strlen($initialVector) !== 16) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid initialization vector length']);
    exit;
}

try {
    $rsa = RSA::loadPrivateKey($privateKeyContents)
        ->withPadding(RSA::ENCRYPTION_OAEP)
        ->withHash('sha256')
        ->withMGFHash('sha256');

    $decryptedAesKey = $rsa->decrypt($encryptedAesKey);

    if (
        $decryptedAesKey === false ||
        strlen($decryptedAesKey) !== 16
    ) {
        throw new RuntimeException(
            'Unexpected AES key length: ' .
            strlen((string)$decryptedAesKey)
        );
    }
} catch (Throwable $e) {
    taccLog('TACC RSA AES key decryption failed', [
        'message' => $e->getMessage(),
    ]);

    http_response_code(500);
    echo json_encode(['error' => 'Failed to decrypt AES key']);
    exit;
}

try {
    $tagLength = 16;
    $cipherText = substr($encryptedFlowData, 0, -$tagLength);
    $tag = substr($encryptedFlowData, -$tagLength);

    if (strlen($tag) !== $tagLength) {
        throw new RuntimeException(
            'Invalid AES-GCM authentication tag length.'
        );
    }

    $decrypted = openssl_decrypt(
        $cipherText,
        'aes-128-gcm',
        $decryptedAesKey,
        OPENSSL_RAW_DATA,
        $initialVector,
        $tag
    );

    if ($decrypted === false) {
        $opensslError = openssl_error_string();

        throw new RuntimeException(
            'AES-128-GCM decryption failed' .
            ($opensslError ? ': ' . $opensslError : '')
        );
    }
} catch (Throwable $e) {
    taccLog('AES flow-data decryption failed', [
        'message' => $e->getMessage(),
    ]);

    http_response_code(500);
    echo json_encode(['error' => 'Failed to decrypt flow data']);
    exit;
}

$payload = json_decode($decrypted, true);

taccLog('FLOW REQUEST', [
    'action' => $payload['action'] ?? '',
    'screen' => $payload['screen'] ?? '',
    'flow_token_present' => !empty($payload['flow_token']),
]);

if (!is_array($payload)) {
    taccLog('Decrypted payload JSON error', [
        'message' => json_last_error_msg(),
    ]);

    http_response_code(400);
    echo json_encode(['error' => 'Decrypted payload invalid']);
    exit;
}

/*
 * Meta supplies flow_token with the encrypted Flow request.
 * Keep it only for the terminal SUCCESS response; do not log its value.
 */
$flowToken = trim((string)($payload['flow_token'] ?? ''));

taccLog('FLOW REQUEST', [
    'action' => $payload['action'] ?? '',
    'screen' => $payload['screen'] ?? '',
    'flow_token_present' => ($flowToken !== ''),
]);

/* -------------------------------------------------------------------------
 * 3. Meta health check
 * ---------------------------------------------------------------------- */

if (($payload['action'] ?? '') === 'ping') {
    sendEncrypted(
        ['data' => ['status' => 'active']],
        $decryptedAesKey,
        $initialVector
    );
}

/* -------------------------------------------------------------------------
 * 4. Load DB only after health-check
 * ---------------------------------------------------------------------- */

try {
    require_once __DIR__ . '/../php/config.php';
    require_once __DIR__ . '/../php/db.php';

    if (!function_exists('getDbConnection')) {
        throw new RuntimeException(
            'Required function getDbConnection() was not loaded.'
        );
    }
} catch (Throwable $e) {
    taccLog('Application dependency load failed', [
        'class'   => get_class($e),
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ]);

    sendError(
        $decryptedAesKey,
        $initialVector,
        'Server configuration error'
    );
}

/* -------------------------------------------------------------------------
 * 5. Extract / validate
 * ---------------------------------------------------------------------- */

$data = $payload['data'] ?? [];

if (empty($data) || empty($data['full_names'])) {
    sendSuccess(
        $decryptedAesKey,
        $initialVector,
        $flowToken
    );
}

$full_names         = s($data, 'full_names');
$surname            = s($data, 'surname');
$date_of_birth      = s($data, 'date_of_birth');
$gender             = s($data, 'gender');
$id_number          = s($data, 'id_number');
$marital_status     = s($data, 'marital_status');
$address            = s($data, 'address');
$postal_code        = s($data, 'postal_code');
$province           = s($data, 'province');
$municipality       = s($data, 'municipality');
$mobile             = s($data, 'mobile');
$alt_number         = s($data, 'alt_number');
$email              = strtolower(s($data, 'email'));
$congregation       = s($data, 'congregation');
$region             = s($data, 'region');
$water_baptised     = !empty($data['water_baptised']) ? 1 : 0;
$water_baptism_year = s($data, 'water_baptism_year');
$sealed             = !empty($data['sealed']) ? 1 : 0;
$sealing_year       = s($data, 'sealing_year');
$previous_church    = s($data, 'previous_church');
$initials           = s($data, 'initials');
$consent            = !empty($data['consent']) ? 1 : 0;

$required = [
    'full_names'     => $full_names,
    'surname'        => $surname,
    'date_of_birth'  => $date_of_birth,
    'gender'         => $gender,
    'id_number'      => $id_number,
    'marital_status' => $marital_status,
    'address'        => $address,
    'postal_code'    => $postal_code,
    'province'       => $province,
    'municipality'   => $municipality,
    'mobile'         => $mobile,
    'email'          => $email,
    'congregation'   => $congregation,
    'region'         => $region,
    'initials'       => $initials,
];

foreach ($required as $field => $value) {
    if ($value === '') {
        taccLog('Validation failed', ['field' => $field]);

        sendError(
            $decryptedAesKey,
            $initialVector,
            "Missing field: $field"
        );
    }
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    taccLog('Validation failed', ['field' => 'email']);

    sendError(
        $decryptedAesKey,
        $initialVector,
        'Invalid email address'
    );
}

if (!$consent) {
    taccLog('Validation failed', ['field' => 'consent']);

    sendError(
        $decryptedAesKey,
        $initialVector,
        'Consent not accepted'
    );
}

@file_put_contents(
    $logFile,
    date('Y-m-d H:i:s') .
    " | $full_names $surname | $email | $mobile | $congregation\n",
    FILE_APPEND | LOCK_EX
);

$hash = hash('sha256', $raw);

if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

$conn = null;

/* -------------------------------------------------------------------------
 * 6. Save registration + queue email jobs
 * ---------------------------------------------------------------------- */

try {
    $conn = getDbConnection();
    $conn->set_charset('utf8mb4');
    $conn->begin_transaction();

    /* Exact encrypted-request deduplication */
    $stmt = $conn->prepare(
        'SELECT member_id
         FROM tacc_submission_log
         WHERE submission_hash = ?
         LIMIT 1'
    );

    $stmt->bind_param('s', $hash);
    $stmt->execute();
    $stmt->bind_result($existingMemberId);

    if ($stmt->fetch()) {
        $stmt->close();
        $conn->rollback();
        $conn->close();

        taccLog('Duplicate encrypted submission accepted as already processed', [
            'member_id' => $existingMemberId,
        ]);

        sendSuccess(
            $decryptedAesKey,
            $initialVector,
            $flowToken
        );
    }

    $stmt->close();

    /* Business rule: email must already be unique */
    $stmt = $conn->prepare(
        'SELECT id
         FROM tacc_members
         WHERE LOWER(TRIM(email)) = ?
         LIMIT 1'
    );

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($existingEmailMemberId);

    if ($stmt->fetch()) {
        $stmt->close();
        $conn->rollback();
        $conn->close();

        taccLog('Duplicate email rejected', [
            'existing_member_id' => $existingEmailMemberId,
        ]);

        sendEncrypted(
            [
                'screen' => 'DECLARATION',
                'data'   => [
                    'error_message' =>
                        'This email address is already registered.',
                    'has_error' => true,
                ],
            ],
            $decryptedAesKey,
            $initialVector
        );
    }

    $stmt->close();

    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

    /* Member */
    $stmt = $conn->prepare(
        'INSERT INTO tacc_members
        (
            full_names, surname, date_of_birth, gender, id_number,
            marital_status, address, postal_code, province, municipality,
            mobile, alt_number, email, congregation, region,
            water_baptised, water_baptism_year, sealed, sealing_year,
            previous_church, initials, consent, ip_address, user_agent
        )
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    );

    $stmt->bind_param(
        'sssssssssssssssisisssiss',
        $full_names,
        $surname,
        $date_of_birth,
        $gender,
        $id_number,
        $marital_status,
        $address,
        $postal_code,
        $province,
        $municipality,
        $mobile,
        $alt_number,
        $email,
        $congregation,
        $region,
        $water_baptised,
        $water_baptism_year,
        $sealed,
        $sealing_year,
        $previous_church,
        $initials,
        $consent,
        $ip,
        $ua
    );

    $stmt->execute();
    $member_id = $stmt->insert_id;
    $stmt->close();

    /* Dependents */
    $dep_stmt = $conn->prepare(
        'INSERT INTO tacc_dependents
        (member_id, full_name, relationship, date_of_birth, baptised)
        VALUES (?,?,?,?,?)'
    );

    for ($i = 1; $i <= 5; $i++) {
        $dn = trim((string)($data["dep{$i}_name"] ?? ''));

        if ($dn === '') {
            continue;
        }

        $dr = trim((string)($data["dep{$i}_relationship"] ?? ''));
        $dd = trim((string)($data["dep{$i}_dob"] ?? ''));
        $db = !empty($data["dep{$i}_baptised"]) ? 1 : 0;

        $dep_stmt->bind_param(
            'isssi',
            $member_id,
            $dn,
            $dr,
            $dd,
            $db
        );

        $dep_stmt->execute();
    }

    $dep_stmt->close();

    /* Ministries */
    $ministries = $data['ministries'] ?? [];

    $min_stmt = $conn->prepare(
        'INSERT IGNORE INTO tacc_member_ministries
        (member_id, ministry)
        VALUES (?,?)'
    );

    if (is_array($ministries)) {
        foreach ($ministries as $ministry) {
            $ministry = trim((string)$ministry);

            if ($ministry === '') {
                continue;
            }

            $min_stmt->bind_param(
                'is',
                $member_id,
                $ministry
            );

            $min_stmt->execute();
        }
    }

    $min_stmt->close();

    /* Exact submission log */
    $log_stmt = $conn->prepare(
        'INSERT INTO tacc_submission_log
        (submission_hash, member_id)
        VALUES (?,?)'
    );

    $log_stmt->bind_param('si', $hash, $member_id);
    $log_stmt->execute();
    $log_stmt->close();

    /* Prepare notification payload without sending anything */
    $emailData = [
        'full_names'         => $full_names,
        'surname'            => $surname,
        'email'              => $email,
        'mobile'             => $mobile,
        'address'            => $address,
        'congregation'       => $congregation,
        'region'             => $region,
        'gender'             => $gender,
        'marital_status'     => $marital_status,
        'date_of_birth'      => $date_of_birth,
        'id_number'          => $id_number,
        'postal_code'        => $postal_code,
        'province'            => $province,
        'municipality'       => $municipality,
        'alt_number'         => $alt_number,
        'water_baptised'     => $water_baptised,
        'water_baptism_year' => $water_baptism_year,
        'sealed'             => $sealed,
        'sealing_year'       => $sealing_year,
        'previous_church'    => $previous_church,
        'initials'           => $initials,
    ];

    $emailData['dependents'] = [];

    for ($i = 1; $i <= 5; $i++) {
        $dn = trim((string)($data["dep{$i}_name"] ?? ''));

        if ($dn === '') {
            continue;
        }

        $emailData['dependents'][] = [
            'name'         => $dn,
            'relationship' => trim((string)($data["dep{$i}_relationship"] ?? '')),
            'dob'          => trim((string)($data["dep{$i}_dob"] ?? '')),
            'baptised'     => !empty($data["dep{$i}_baptised"]) ? 'Yes' : 'No',
        ];
    }

    $emailData['ministries'] = is_array($ministries)
        ? array_values(array_filter(array_map(
            static fn($m) => trim((string)$m),
            $ministries
        )))
        : [];

    $payloadJson = json_encode(
        $emailData,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );

    if ($payloadJson === false) {
        throw new RuntimeException(
            'Could not encode email queue payload: ' .
            json_last_error_msg()
        );
    }

    /* Two independent jobs: admin + client */
    $queue_stmt = $conn->prepare(
        'INSERT INTO tacc_email_queue
        (member_id, email_type, payload, status, attempts)
        VALUES (?, ?, ?, "pending", 0)'
    );

    $emailType = 'admin';
    $queue_stmt->bind_param(
        'iss',
        $member_id,
        $emailType,
        $payloadJson
    );
    $queue_stmt->execute();

    $emailType = 'client';
    $queue_stmt->bind_param(
        'iss',
        $member_id,
        $emailType,
        $payloadJson
    );
    $queue_stmt->execute();

    $queue_stmt->close();

    /*
     * No SMTP, HTTP, API or email operation has occurred.
     * Everything required for registration is committed now.
     */
    $conn->commit();
    $conn->close();
    $conn = null;

    taccLog('Registration committed; email jobs queued', [
        'member_id' => $member_id,
    ]);

    /* Return SUCCESS immediately */
    sendSuccess(
        $decryptedAesKey,
        $initialVector,
        $flowToken
    );
} catch (mysqli_sql_exception $e) {
    if ($conn instanceof mysqli) {
        try {
            $conn->rollback();
        } catch (Throwable $ignored) {
        }

        try {
            $conn->close();
        } catch (Throwable $ignored) {
        }
    }

    taccLog('TACC registration DB error', [
        'class'   => get_class($e),
        'code'    => $e->getCode(),
        'message' => $e->getMessage(),
    ]);

    if ((int)$e->getCode() === 1062) {
        sendEncrypted(
            [
                'screen' => 'DECLARATION',
                'data'   => [
                    'error_message' =>
                        'This email address is already registered.'
                ],
            ],
            $decryptedAesKey,
            $initialVector
        );
    }

    sendError(
        $decryptedAesKey,
        $initialVector,
        'Database error'
    );
} catch (Throwable $e) {
    if ($conn instanceof mysqli) {
        try {
            $conn->rollback();
        } catch (Throwable $ignored) {
        }

        try {
            $conn->close();
        } catch (Throwable $ignored) {
        }
    }

    taccLog('TACC registration DB error', [
        'class'   => get_class($e),
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ]);

    sendError(
        $decryptedAesKey,
        $initialVector,
        'Database error'
    );
}
