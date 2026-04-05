<?php
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}
require_once(ABSPATH . 'wp-load.php');

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    http_response_code(403);
    exit;
}

$log_file = ABSPATH . 'wp-content/ansible-exec.log';
$logs = [];

if (file_exists($log_file)) {
    $lines = array_reverse(array_filter(explode("\n", file_get_contents($log_file))));
    foreach ($lines as $line) {
        if (preg_match('/\[(.+?)\] EXECUTOR:(\S+) STATUS:(\S+) SUMMARY:(.*)/', $line, $m)) {
            $logs[] = [
                'date' => $m[1],
                'executor' => $m[2],
                'status' => $m[3],
                'summary' => $m[4]
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($logs);
