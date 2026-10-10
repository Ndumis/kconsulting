<?php
declare(strict_types=1);

/**
 * TACC Email Queue Processor
 *
 * Run from cron/CLI:
 *   php /path/to/process_email_queue.php
 *
 * This file is intentionally separate from register.php.
 * A slow or failed email provider cannot delay the WhatsApp Flow SUCCESS.
 */

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$errorLogFile = __DIR__ . '/tacc_php_errors.log';
@ini_set('error_log', $errorLogFile);

function queueLog(string $message, array $context = []): void
{
    global $errorLogFile;

    $line = date('Y-m-d H:i:s') . ' | EMAIL QUEUE | ' . $message;

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
    @error_log(rtrim($line));
}

try {
    require_once __DIR__ . '/../php/config.php';
    require_once __DIR__ . '/../php/db.php';
    require_once __DIR__ . '/../php/tacc_email_functions.php';

    if (!function_exists('getDbConnection')) {
        throw new RuntimeException('getDbConnection() is unavailable.');
    }

    if (!function_exists('sendTaccAdminEmail')) {
        throw new RuntimeException('sendTaccAdminEmail() is unavailable.');
    }

    if (!function_exists('sendTaccClientEmail')) {
        throw new RuntimeException('sendTaccClientEmail() is unavailable.');
    }

    if (function_exists('mysqli_report')) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

    $conn = getDbConnection();
    $conn->set_charset('utf8mb4');

    $processed = 0;
    $failed = 0;
    $maxJobs = 20;

    for ($n = 0; $n < $maxJobs; $n++) {
        /*
         * Claim exactly one job in a short transaction.
         * Stale "processing" jobs older than 15 minutes become eligible again.
         */
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare(
                'SELECT id, member_id, email_type, payload
                 FROM tacc_email_queue
                 WHERE
                     status = "pending"
                     OR (
                         status = "processing"
                         AND processing_started_at < (NOW() - INTERVAL 15 MINUTE)
                     )
                 ORDER BY created_at ASC, id ASC
                 LIMIT 1
                 FOR UPDATE'
            );

            $stmt->execute();
            $stmt->bind_result(
                $jobId,
                $memberId,
                $emailType,
                $payloadJson
            );

            if (!$stmt->fetch()) {
                $stmt->close();
                $conn->commit();
                break;
            }

            $stmt->close();

            $stmt = $conn->prepare(
                'UPDATE tacc_email_queue
                 SET status = "processing",
                     attempts = attempts + 1,
                     processing_started_at = NOW()
                 WHERE id = ?'
            );

            $stmt->bind_param('i', $jobId);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
        } catch (Throwable $e) {
            try {
                $conn->rollback();
            } catch (Throwable $ignored) {
            }

            throw $e;
        }

        $emailData = json_decode($payloadJson, true);

        if (!is_array($emailData)) {
            $failed++;

            $stmt = $conn->prepare(
                'UPDATE tacc_email_queue
                 SET status = "failed",
                     last_error = ?,
                     finished_at = NOW()
                 WHERE id = ?'
            );

            $error = 'Invalid JSON payload in email queue.';
            $stmt->bind_param('si', $error, $jobId);
            $stmt->execute();
            $stmt->close();

            queueLog('Invalid queue payload', ['job_id' => $jobId]);
            continue;
        }

        try {
            if ($emailType === 'admin') {
                sendTaccAdminEmail($emailData, (int)$memberId);
            } elseif ($emailType === 'client') {
                sendTaccClientEmail($emailData, (int)$memberId);
            } else {
                throw new RuntimeException(
                    'Unknown email type: ' . $emailType
                );
            }

            $stmt = $conn->prepare(
                'UPDATE tacc_email_queue
                 SET status = "sent",
                     sent_at = NOW(),
                     finished_at = NOW(),
                     last_error = NULL
                 WHERE id = ?'
            );

            $stmt->bind_param('i', $jobId);
            $stmt->execute();
            $stmt->close();

            $processed++;

            queueLog('Email sent', [
                'job_id' => $jobId,
                'member_id' => $memberId,
                'email_type' => $emailType,
            ]);
        } catch (Throwable $e) {
            $failed++;

            /*
             * Leave the job pending for retry until 5 attempts.
             * After 5 failures it becomes failed for manual inspection.
             */
            $stmt = $conn->prepare(
                'UPDATE tacc_email_queue
                 SET status = CASE
                     WHEN attempts >= 5 THEN "failed"
                     ELSE "pending"
                 END,
                     last_error = ?,
                     finished_at = NOW()
                 WHERE id = ?'
            );

            $error = substr($e->getMessage(), 0, 1000);
            $stmt->bind_param('si', $error, $jobId);
            $stmt->execute();
            $stmt->close();

            queueLog('Email failed', [
                'job_id' => $jobId,
                'member_id' => $memberId,
                'email_type' => $emailType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    $conn->close();

    queueLog('Queue run complete', [
        'processed' => $processed,
        'failed' => $failed,
    ]);

    echo "TACC email queue complete. Sent: {$processed}; Failed/retried: {$failed}\n";
    exit($failed > 0 ? 1 : 0);
} catch (Throwable $e) {
    queueLog('QUEUE PROCESSOR FATAL ERROR', [
        'class' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    fwrite(STDERR, "TACC email queue error: {$e->getMessage()}\n");
    exit(1);
}
