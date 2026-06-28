<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Services\XmlNfeParser;
use App\Services\SankhyaClient;
use App\Services\ImportService;

$result = null;

try {
    $file = basename($_POST['xml_file'] ?? '');
    $path = __DIR__ . '/uploads/' . $file;

    if (!$file || !file_exists($path)) {
        throw new Exception('XML não encontrado.');
    }

    $xmlData = (new XmlNfeParser())->parse($path);

    $app = config('app');
    $api = config('sankhya');

    $client = new SankhyaClient($api, $app);
    $service = new ImportService($client, $api);
    $result = $service->importar($xmlData);
} catch (Throwable $e) {
    $result = [
        'success' => false,
        'message' => $e->getMessage(),
        'logs' => [],
        'payload' => null,
    ];
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Resultado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="result">
    <div class="card center">
        <div class="icon <?= $result['success'] ? 'ok' : 'fail' ?>"><?= $result['success'] ? '✓' : '!' ?></div>
        <h1><?= $result['success'] ? 'Processado' : 'Erro' ?></h1>
        <p><?= htmlspecialchars($result['message']) ?></p>

        <?php if (!empty($result['nunota'])): ?>
            <div class="nunota">
                <span>NUNOTA</span>
                <strong><?= htmlspecialchars((string)$result['nunota']) ?></strong>
            </div>
        <?php endif; ?>

        <a class="button-link" href="/">Nova importação</a>
    </div>

    <div class="card">
        <h2>Etapas</h2>
        <ul class="timeline">
            <?php foreach ($result['logs'] ?? [] as $log): ?>
                <li><?= htmlspecialchars($log) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if (!empty($result['payload'])): ?>
    <div class="card">
        <h2>Payload</h2>
        <pre><?= htmlspecialchars(json_encode($result['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
