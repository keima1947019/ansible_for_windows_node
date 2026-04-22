<?php
// merge-executor.php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// merge.sh のパスを指定
$command = '/bin/bash ./merge.sh 2>&1';

$handle = popen($command, 'r');
while (!feof($handle)) {
    $line = fgets($handle);
    if ($line) {
        echo "data: " . trim($line) . "\n\n";
        ob_flush();
        flush();
    }
}
pclose($handle);

echo "data: __DONE__\n\n";
ob_flush();
flush();
?>
