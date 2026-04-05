<?php
// ログをファイルに記録する関数
function write_ansible_log($user, $status, $summary) {
    $log_file = '/var/www/html/wp-content/ansible-exec.log';
    $date = date('Y-m-d H:i:s');
    $line = "[{$date}] USER:{$user} STATUS:{$status} SUMMARY:{$summary}\n";
    file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
}
?>

