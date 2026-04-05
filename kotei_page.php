<div id="ansible-ui">
    <!-- 実行者ごとのボタン -->
    <button class="exec-btn button button-primary" data-label="田中">田中さん用ボタン</button>
    <button class="exec-btn button button-primary" data-label="佐藤">佐藤さん用ボタン</button>
    <button class="exec-btn button button-primary" data-label="鈴木">鈴木さん用ボタン</button>

    <div id="status-badge" style="display:none; margin-top:10px;">実行中...</div>
    <pre id="log-window" style="background:#222; color:#0f0; padding:15px; margin-top:15px; height:400px; overflow-y:auto; font-family:monospace; font-size:12px;"></pre>

    <h4 style="margin-top:20px;">📋 操作ログ</h4>
    <table id="op-log-table" style="width:100%; border-collapse:collapse; font-size:12px;">
        <thead>
            <tr style="background:#333; color:#fff;">
                <th style="padding:5px;">日時</th>
                <th style="padding:5px;">実行者</th>
                <th style="padding:5px;">ステータス</th>
                <th style="padding:5px;">サマリー</th>
            </tr>
        </thead>
        <tbody id="op-log-body"></tbody>
    </table>
</div>

<script>
function appendLog(date, executor, status, summary) {
    const tbody = document.getElementById('op-log-body');
    const color = status === 'SUCCESS' ? '#0f0' : '#f00';
    const row = `<tr>
        <td style="padding:5px; border:1px solid #ccc;">${date}</td>
        <td style="padding:5px; border:1px solid #ccc;">${executor}</td>
        <td style="padding:5px; border:1px solid #ccc; color:${color};">${status}</td>
        <td style="padding:5px; border:1px solid #ccc;">${summary}</td>
    </tr>`;
    tbody.insertAdjacentHTML('afterbegin', row);
}

// ページ読み込み時に既存ログを取得
fetch('/ansible-log-viewer.php')
    .then(r => r.json())
    .then(logs => {
        logs.forEach(l => appendLog(l.date, l.executor, l.status, l.summary));
    });

// 全ボタンにイベントを設定
document.querySelectorAll('.exec-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const targetLabel = this.dataset.label;
        const logWindow = document.getElementById('log-window');
        const badge = document.getElementById('status-badge');
        let fullLog = '';
        let hasError = false;
        let isCompleted = false;
        let afterRecap = false;

        // 全ボタンを無効化
        document.querySelectorAll('.exec-btn').forEach(b => b.disabled = true);
        badge.style.display = 'block';
        logWindow.textContent = `--- ${targetLabel}さんが実行中... ---\n`;

        const es = new EventSource('/ansible-executor.php');

        es.onmessage = function(e) {
            console.log('受信データ:', e.data);

            if (e.data === '__DONE__') {
                isCompleted = true;
                logWindow.textContent += '\n--- 実行完了 ---\n';
                badge.style.display = 'none';
                document.querySelectorAll('.exec-btn').forEach(b => b.disabled = false);
                es.close();

                const status = hasError ? 'FAILED' : 'SUCCESS';
                const lines = fullLog.split('\n');
                const recapIdx = lines.findIndex(l => l.includes('PLAY RECAP'));
                const summary = recapIdx >= 0 ? lines[recapIdx + 1].trim() : fullLog.slice(-100);

                fetch('/ansible-log-writer.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        status: status,
                        summary: summary,
                        executor: targetLabel
                    })
                })
                .then(r => r.json())
                .then(l => appendLog(l.date, l.executor, l.status, l.summary));
                return;
            }

if (afterRecap) {
    // 空行はスキップ
    if (e.data.trim() === '') {
        // afterRecapをfalseにしない（次の行を待つ）
    } else {
        console.log('RECAP行の次:', e.data);
        const checks = ['failed', 'unreachable'];
        checks.forEach(key => {
            const match = e.data.match(new RegExp(`${key}=(\\d+)`));
            if (match && parseInt(match[1]) > 0) {
                hasError = true;
            }
        });
        afterRecap = false;
    }
}


            if (e.data.includes('PLAY RECAP')) {
                afterRecap = true;
            }

            fullLog += e.data + '\n';
            logWindow.textContent += e.data + '\n';
            logWindow.scrollTop = logWindow.scrollHeight;
        };

        es.onerror = function() {
            if (isCompleted) return;
            logWindow.textContent += '\n--- 接続エラー ---\n';
            badge.style.display = 'none';
            document.querySelectorAll('.exec-btn').forEach(b => b.disabled = false);
            es.close();
        };
    });
});
</script>
