<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Services\XmlNfeParser;

$app = config('app');
$api = config('sankhya');

$error = null;
$xmlData = null;
$fileName = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['xml'])) {
    try {
        if ($_FILES['xml']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Falha no upload.');
        }

        if (strtolower(pathinfo($_FILES['xml']['name'], PATHINFO_EXTENSION)) !== 'xml') {
            throw new Exception('Envie apenas XML.');
        }

        $fileName = date('YmdHis') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['xml']['name']);
        $target = public_path('uploads/' . $fileName);

        if (!move_uploaded_file($_FILES['xml']['tmp_name'], $target)) {
            throw new Exception('Não foi possível salvar o arquivo.');
        }

        $xmlData = (new XmlNfeParser())->parse($target);
        log_app('XML lido com sucesso.', ['file' => $fileName]);
    } catch (Throwable $e) {
        $error = $e->getMessage();
        log_app('Erro ao ler XML.', ['error' => $error]);
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Importador XML Sankhya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="layout">
    <aside>
        <div class="logo">SX</div>
        <h2>Importador XML</h2>
        <p>Sankhya Render</p>
        <nav>
            <a class="active" href="/">Importação</a>
            <a href="/logs.php">Logs</a>
        </nav>
        <div class="env">
            <span>Modo</span>
            <strong><?= e($app['sandbox'] ? 'Simulação' : 'Produção') ?></strong>
        </div>
    </aside>

    <main>
        <header>
            <div>
                <h1>XML → Parceiro → Pedido</h1>
                <p>Upload de XML, validação e geração de payload para TOP <?= e($api['codtipoper']) ?>.</p>
            </div>
            <span class="badge"><?= e($app['sandbox'] ? 'Seguro para testes' : 'Envia para Sankhya') ?></span>
        </header>

        <?php if ($error): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <section class="grid">
            <div class="card">
                <form method="post" enctype="multipart/form-data">
                    <label class="drop">
                        <input type="file" name="xml" accept=".xml" required>
                        <span>⬆</span>
                        <h3>Selecione o XML</h3>
                        <p>Arquivo NF-e em XML</p>
                    </label>
                    <button type="submit">Ler XML</button>
                </form>
            </div>

            <div class="card">
                <h2>Resumo</h2>
                <?php if (!$xmlData): ?>
                    <div class="empty">Carregue um XML para visualizar os dados.</div>
                <?php else: ?>
                    <div class="kpis">
                        <div><span>Parceiro</span><strong><?= e($xmlData['cliente']['nome']) ?></strong></div>
                        <div><span>Documento</span><strong><?= e($xmlData['cliente']['documento']) ?></strong></div>
                        <div><span>Itens</span><strong><?= count($xmlData['itens']) ?></strong></div>
                        <div><span>Total</span><strong>R$ <?= number_format($xmlData['total'], 2, ',', '.') ?></strong></div>
                    </div>
                    <form method="post" action="/process.php">
                        <input type="hidden" name="xml_file" value="<?= e($fileName) ?>">
                        <button class="success" type="submit">Validar e importar</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($xmlData): ?>
        <section class="card">
            <h2>Parceiro</h2>
            <div class="details">
                <div><span>Nome</span><strong><?= e($xmlData['cliente']['nome']) ?></strong></div>
                <div><span>CPF/CNPJ</span><strong><?= e($xmlData['cliente']['documento']) ?></strong></div>
                <div><span>Cidade</span><strong><?= e($xmlData['cliente']['cidade']) ?></strong></div>
                <div><span>UF</span><strong><?= e($xmlData['cliente']['uf']) ?></strong></div>
            </div>
        </section>

        <section class="card">
            <h2>Itens</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>cProd</th>
                            <th>EAN</th>
                            <th>Descrição</th>
                            <th>Qtd</th>
                            <th>Un</th>
                            <th>Unitário</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($xmlData['itens'] as $item): ?>
                        <tr>
                            <td><?= e($item['cprod']) ?></td>
                            <td><?= e($item['cean'] ?: '-') ?></td>
                            <td><?= e($item['descricao']) ?></td>
                            <td><?= number_format($item['qtd'], 4, ',', '.') ?></td>
                            <td><?= e($item['un']) ?></td>
                            <td>R$ <?= number_format($item['vlrunit'], 2, ',', '.') ?></td>
                            <td>R$ <?= number_format($item['vlrtot'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>
    </main>
</div>
<script src="/assets/js/app.js"></script>
</body>
</html>
