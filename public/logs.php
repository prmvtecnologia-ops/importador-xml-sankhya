<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

$logFile = storage_path('logs/app.log');
$logs = file_exists($logFile) ? file_get_contents($logFile) : 'Nenhum log encontrado.';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Logs - Importador XML Sankhya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="single">
    <a href="/" class="back">← Voltar</a>
    <div class="card">
        <h1>Logs</h1>
        <pre><?= e($logs) ?></pre>
    </div>
</div>
</body>
</html>
