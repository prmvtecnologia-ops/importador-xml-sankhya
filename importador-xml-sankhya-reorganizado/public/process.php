<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Services\ImportService;
use App\Services\SankhyaClient;
use App\Services\XmlNfeParser;

$error = null;
$result = null;
$payload = null;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido.');
    }

    $xmlFile = basename($_POST['xml_file'] ?? '');
    if (!$xmlFile) {
        throw new Exception('Arquivo XML não informado.');
    }

    $path = public_path('uploads/' . $xmlFile);
    if (!file_exists($path)) {
        throw new Exception('Arquivo XML não encontrado em uploads.');
    }

    $xmlData = (new XmlNfeParser())->parse($path);
    $payload = (new ImportService())->montarPayloadPedido($xmlData);

    $client = new SankhyaClient();
    $result = $client->enviarPedido($payload);

    log_app('Processamento concluído.', ['file' => $xmlFile]);
} catch (Throwable $e) {
    $error = $e->getMessage();
    log_app('Erro no processamento.', ['error' => $error]);
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Resultado - Importador XML Sankhya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="single">
    <a href="/" class="back">← Voltar</a>
    <div class="card">
        <h1>Resultado da importação</h1>

        <?php if ($error): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php else: ?>
            <div class="ok">Processamento concluído.</div>
            <h2>Retorno</h2>
            <pre><?= e(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
            <h2>Payload gerado</h2>
            <pre><?= e(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
