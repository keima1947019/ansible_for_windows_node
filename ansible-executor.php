<?php
// SSE用のヘッダー設定
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no'); // バッファリングを無効化

// Ansibleコマンドの定義（PYTHONUNBUFFEREDで出力を即時化）
$command = "export PYTHONUNBUFFERED=1; export HOME=/tmp; ansible-playbook -i ./hosts.ini ./notification_01.yml 2>&1";

$descriptorspec = [
    1 => ["pipe", "w"], // 標準出力
    2 => ["pipe", "w"]  // 標準エラー出力
];

$process = proc_open($command, $descriptorspec, $pipes);

if (is_resource($process)) {
    // 1行ずつ読み取って出力
    while ($line = fgets($pipes[1])) {
        // SSEの形式でブラウザへ送信
        echo "data: " . rtrim($line) . "\n\n";

        // PHPの出力バッファを強制フラッシュ
        if (ob_get_level() > 0) ob_flush();
        flush();
    }

    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);
}

// Ansible実行後の末尾に追加
echo "data: __DONE__\n\n";
flush();
