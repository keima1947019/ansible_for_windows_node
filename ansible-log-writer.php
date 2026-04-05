<?php
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}
require_once(ABSPATH . 'wp-load.php');

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$executor = sanitize_text_field($data['executor'] ?? '不明');
$status = sanitize_text_field($data['status'] ?? 'UNKNOWN');
date_default_timezone_set('Asia/Tokyo');
$summary = sanitize_text_field($data['summary'] ?? '');
$date = date('Y-m-d H:i:s');

$log_file = ABSPATH . 'wp-content/ansible-exec.log';
$line = "[{$date}] EXECUTOR:{$executor} STATUS:{$status} SUMMARY:{$summary}\n";
file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);

header('Content-Type: application/json');
echo json_encode([
    'date' => $date,
    'executor' => $executor,
    'status' => $status,
    'summary' => $summary
]);
