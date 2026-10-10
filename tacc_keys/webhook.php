<?php
declare(strict_types=1);

/*
 * TACC WhatsApp Webhook — diagnostic + REGISTER -> published Flow
 *
 * Live URL:
 * https://www.thekconsult.co.za/tacc/webhook.php
 *
 * Current Meta verification token is intentionally preserved:
 * KC_WEBHOOK_VERIFY_2026
 */

const VERIFY_TOKEN = 'KC_WEBHOOK_VERIFY_2026';
const PHONE_NUMBER_ID = '1291728070693445';
const FLOW_ID = '28650494857973140';
const GRAPH_API_VERSION = 'v23.0';

const CONFIG_FILE = __DIR__ . '/../php/tacc_whatsapp_config.php';
const LOG_FILE = __DIR__ . '/tacc_webhook.log';
const PROCESSED_FILE = __DIR__ . '/tacc_webhook_processed.log';

function log_event(string $event, array $data = []): void {
    $line = gmdate('Y-m-d H:i:s') . ' UTC | ' . $event;
    if ($data) {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json !== false) $line .= ' | ' . $json;
    }
    $line .= PHP_EOL;
    @file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
    @error_log('TACC WEBHOOK: ' . rtrim($line));
}

function mask_phone(string $phone): string {
    $p = preg_replace('/\D+/', '', $phone) ?? '';
    return strlen($p) > 4 ? str_repeat('*', strlen($p) - 4) . substr($p, -4) : $p;
}

function fatal_handler(): void {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR], true)) {
        log_event('FATAL_PHP_ERROR', [
            'type' => $e['type'],
            'message' => $e['message'] ?? '',
            'file' => $e['file'] ?? '',
            'line' => $e['line'] ?? 0
        ]);
    }
}
register_shutdown_function('fatal_handler');

/*
 * IMPORTANT: this must be the first executable diagnostic.
 * If this file is reached by PHP, the log should be created.
 */
log_event('WEBHOOK_START', [
    'method' => $_SERVER['REQUEST_METHOD'] ?? '',
    'uri' => $_SERVER['REQUEST_URI'] ?? '',
    'php' => PHP_VERSION,
    'script' => __FILE__
]);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
    $mode = (string)($_GET['hub.mode'] ?? $_GET['hub_mode'] ?? '');
    $token = (string)($_GET['hub.verify_token'] ?? $_GET['hub_verify_token'] ?? '');
    $challenge = (string)($_GET['hub.challenge'] ?? $_GET['hub_challenge'] ?? '');

    log_event('VERIFY_REQUEST', [
        'mode' => $mode,
        'token_match' => hash_equals(VERIFY_TOKEN, $token),
        'challenge_present' => $challenge !== ''
    ]);

    if ($mode === 'subscribe' && hash_equals(VERIFY_TOKEN, $token)) {
        http_response_code(200);
        header('Content-Type: text/plain; charset=utf-8');
        echo $challenge;
        exit;
    }

    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Forbidden';
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    log_event('UNSUPPORTED_METHOD', ['method' => $_SERVER['REQUEST_METHOD'] ?? '']);
    http_response_code(405);
    header('Allow: GET, POST');
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Method Not Allowed';
    exit;
}

$raw = file_get_contents('php://input');

log_event('POST_RECEIVED', [
    'body_length' => is_string($raw) ? strlen($raw) : 0,
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? ''
]);

if (!is_string($raw) || trim($raw) === '') {
    log_event('EMPTY_POST');
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'ok']);
    exit;
}

$payload = json_decode($raw, true);

if (!is_array($payload)) {
    log_event('INVALID_JSON', ['error' => json_last_error_msg()]);
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'ok']);
    exit;
}

log_event('JSON_RECEIVED', [
    'object' => $payload['object'] ?? null,
    'entry_count' => is_array($payload['entry'] ?? null) ? count($payload['entry']) : 0
]);

foreach (($payload['entry'] ?? []) as $entry) {
    foreach (($entry['changes'] ?? []) as $change) {
        $value = $change['value'] ?? [];

        foreach (($value['statuses'] ?? []) as $status) {
            log_event('MESSAGE_STATUS', [
                'id' => $status['id'] ?? null,
                'status' => $status['status'] ?? null,
                'recipient_id' => mask_phone((string)($status['recipient_id'] ?? '')),
                'errors' => $status['errors'] ?? null
            ]);
        }

        foreach (($value['messages'] ?? []) as $message) {
            $from = trim((string)($message['from'] ?? ''));
            $messageId = trim((string)($message['id'] ?? ''));
            $type = (string)($message['type'] ?? '');

            log_event('INCOMING_MESSAGE', [
                'from' => mask_phone($from),
                'id' => $messageId,
                'type' => $type,
                'timestamp' => $message['timestamp'] ?? null
            ]);

            if ($type !== 'text') {
                continue;
            }

            $text = trim((string)($message['text']['body'] ?? ''));

            log_event('TEXT_RECEIVED', [
                'from' => mask_phone($from),
                'text_normalized' => strtoupper($text)
            ]);

            if (strcasecmp($text, 'REGISTER') !== 0) {
                continue;
            }

            if ($from === '') {
                log_event('REGISTER_NO_SENDER', ['message_id' => $messageId]);
                continue;
            }

            /*
             * Simple retry protection.
             */
            if ($messageId !== '' && is_file(PROCESSED_FILE)) {
                $processed = @file(PROCESSED_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if (is_array($processed) && in_array($messageId, $processed, true)) {
                    log_event('REGISTER_DUPLICATE_IGNORED', ['message_id' => $messageId]);
                    continue;
                }
            }

            log_event('REGISTER_MATCHED', [
                'from' => mask_phone($from),
                'message_id' => $messageId
            ]);

            try {
                if (!is_file(CONFIG_FILE) || !is_readable(CONFIG_FILE)) {
                    throw new RuntimeException('Missing/unreadable config: ' . CONFIG_FILE);
                }

                require_once CONFIG_FILE;

                if (!defined('WHATSAPP_ACCESS_TOKEN')) {
                    throw new RuntimeException('WHATSAPP_ACCESS_TOKEN is not defined.');
                }

                $token = trim((string)constant('WHATSAPP_ACCESS_TOKEN'));

                if ($token === '' || str_contains($token, 'PASTE_')) {
                    throw new RuntimeException('WhatsApp access token is not configured.');
                }

                $flowToken = 'tacc_register_' . bin2hex(random_bytes(16));

                $body = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $from,
                    'type' => 'interactive',
                    'interactive' => [
                        'type' => 'flow',
                        'header' => [
                            'type' => 'text',
                            'text' => 'Twelve Apostles Church'
                        ],
                        'body' => [
                            'text' => 'Please complete your TACC church registration.'
                        ],
                        'footer' => [
                            'text' => 'TACC Registration'
                        ],
                        'action' => [
                            'name' => 'flow',
                            'parameters' => [
                                'flow_message_version' => '3',
                                'flow_token' => $flowToken,
                                'flow_id' => FLOW_ID,
                                'flow_cta' => 'Start Registration',
                                'flow_action' => 'navigate',
                                'flow_action_payload' => [
                                    'screen' => 'WELCOME'
                                ]
                            ]
                        ]
                    ]
                ];

                $json = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if ($json === false) throw new RuntimeException('JSON encoding failed.');

                $url = 'https://graph.facebook.com/' . GRAPH_API_VERSION . '/' . PHONE_NUMBER_ID . '/messages';

                if (!function_exists('curl_init')) {
                    throw new RuntimeException('PHP cURL extension is not available.');
                }

                $ch = curl_init($url);
                if ($ch === false) throw new RuntimeException('curl_init failed.');

                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $token,
                        'Content-Type: application/json'
                    ],
                    CURLOPT_POSTFIELDS => $json,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_TIMEOUT => 20
                ]);

                $apiResponse = (string)curl_exec($ch);
                $curlError = (string)curl_error($ch);
                $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($curlError !== '') {
                    throw new RuntimeException('cURL error: ' . $curlError);
                }

                $decoded = json_decode($apiResponse, true);

                if ($httpCode < 200 || $httpCode >= 300) {
                    $apiError = is_array($decoded) ? ($decoded['error']['message'] ?? 'Unknown Graph API error') : 'Unknown Graph API error';
                    log_event('FLOW_API_FAILED', [
                        'http_code' => $httpCode,
                        'error' => $apiError,
                        'response' => is_array($decoded) ? $decoded : null
                    ]);
                } else {
                    log_event('FLOW_API_SUCCESS', [
                        'http_code' => $httpCode,
                        'whatsapp_message_id' => is_array($decoded) ? ($decoded['messages'][0]['id'] ?? null) : null
                    ]);

                    if ($messageId !== '') {
                        @file_put_contents(PROCESSED_FILE, $messageId . PHP_EOL, FILE_APPEND | LOCK_EX);
                    }
                }
            } catch (Throwable $e) {
                log_event('FLOW_SEND_EXCEPTION', [
                    'class' => get_class($e),
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}

log_event('POST_COMPLETE');

http_response_code(200);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'ok']);
exit;
