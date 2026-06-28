<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';
$logFile = __DIR__ . '/../storage/logs/app.log';
$content = file_exists($logFile) ? file_get_contents($logFile) : 'Nenhum log encontrado.';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Logs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="result">
    <div class="card">
        <h1>Logs</h1>
        <pre><?= htmlspecialchars($content) ?></pre>
        <a class="button-link" href="/">Voltar</a>
    </div>
</div>
</body>
</html>
