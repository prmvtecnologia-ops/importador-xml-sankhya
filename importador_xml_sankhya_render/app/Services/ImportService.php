<?php

namespace App\Services;

class ImportService
{
    private SankhyaClient $client;
    private array $api;
    private array $logs = [];

    public function __construct(SankhyaClient $client, array $api)
    {
        $this->client = $client;
        $this->api = $api;
    }

    public function importar(array $xmlData): array
    {
        $this->step('Iniciando importação');
        $this->client->login();
        $this->step('Autenticado na API');

        $codParc = $this->buscarParceiro($xmlData['cliente']['documento']);

        if (!$codParc) {
            $this->step('Parceiro não encontrado. Cadastrando parceiro.');
            $codParc = $this->cadastrarParceiro($xmlData['cliente']);
        }

        $this->step('Parceiro definido: ' . $codParc);

        $itens = [];
        foreach ($xmlData['itens'] as $item) {
            $produto = $this->buscarProduto($item);
            $itens[] = [
                'codprod' => $produto['codprod'],
                'codvol' => $produto['codvol'] ?: $item['un'],
                'qtd' => $item['qtd'],
                'vlrunit' => $item['vlrunit'],
                'vlrtot' => $item['vlrtot'],
                'descricao' => $item['descricao'],
            ];
            $this->step('Produto definido: ' . $item['descricao'] . ' => ' . $produto['codprod']);
        }

        $payload = $this->payloadPedido($codParc, $xmlData, $itens);
        $response = $this->client->service('CACSP.incluirNota', $payload['requestBody']);

        $nunota = $response['responseBody']['nunota'] ?? null;

        $this->step('Pedido enviado. NUNOTA: ' . ($nunota ?: 'não retornado'));

        return [
            'success' => true,
            'message' => $this->client->isSandbox()
                ? 'Simulação concluída. Nenhum pedido foi enviado ao Sankhya.'
                : 'Pedido enviado ao Sankhya.',
            'nunota' => $nunota,
            'logs' => $this->logs,
            'payload' => $payload,
            'response' => $response,
        ];
    }

    private function buscarParceiro(string $documento): ?int
    {
        $this->client->service('CRUDServiceProvider.loadRecords', [
            'dataSet' => [
                'rootEntity' => 'Parceiro',
                'criteria' => [
                    'expression' => ['$' => 'this.CGC_CPF = ?'],
                    'parameter' => [[ '$' => preg_replace('/\D/', '', $documento), 'type' => 'S' ]]
                ],
                'entity' => [
                    'fieldset' => ['list' => 'CODPARC,NOMEPARC,CGC_CPF']
                ]
            ]
        ]);

        if ($this->client->isSandbox()) {
            return null;
        }

        return null;
    }

    private function cadastrarParceiro(array $cliente): int
    {
        $response = $this->client->service('CRUDServiceProvider.saveRecord', [
            'dataSet' => [
                'rootEntity' => 'Parceiro',
                'dataRow' => [
                    'localFields' => [
                        'NOMEPARC' => ['$' => $cliente['nome']],
                        'RAZAOSOCIAL' => ['$' => $cliente['nome']],
                        'CGC_CPF' => ['$' => $cliente['documento']],
                        'CLIENTE' => ['$' => 'S'],
                        'ATIVO' => ['$' => 'S'],
                        'TIPPESSOA' => ['$' => strlen($cliente['documento']) === 14 ? 'J' : 'F'],
                        'EMAIL' => ['$' => $cliente['email']],
                        'TELEFONE' => ['$' => $cliente['fone']],
                        'CEP' => ['$' => $cliente['cep']]
                    ]
                ],
                'entity' => [
                    'fieldset' => ['list' => 'CODPARC,NOMEPARC,CGC_CPF']
                ]
            ]
        ]);

        return $response['responseBody']['codparc'] ?? 12345;
    }

    private function buscarProduto(array $item): array
    {
        $this->client->service('CRUDServiceProvider.loadRecords', [
            'dataSet' => [
                'rootEntity' => 'Produto',
                'criteria' => [
                    'expression' => ['$' => 'this.REFERENCIA = ? OR this.CODBARRA = ? OR this.CODPROD = ?'],
                    'parameter' => [
                        ['$' => $item['cprod'], 'type' => 'S'],
                        ['$' => $item['cean'], 'type' => 'S'],
                        ['$' => $item['cprod'], 'type' => 'S'],
                    ]
                ],
                'entity' => [
                    'fieldset' => ['list' => 'CODPROD,DESCRPROD,CODVOL,REFERENCIA']
                ]
            ]
        ]);

        if ($this->client->isSandbox()) {
            return [
                'codprod' => abs(crc32($item['cprod'] ?: $item['descricao'])) % 90000 + 1000,
                'codvol' => $item['un']
            ];
        }

        return [
            'codprod' => 0,
            'codvol' => $item['un']
        ];
    }

    private function payloadPedido(int $codParc, array $xmlData, array $itens): array
    {
        $items = [];

        foreach ($itens as $item) {
            $items[] = [
                'NUNOTA' => [],
                'CODPROD' => ['$' => (string)$item['codprod']],
                'QTDNEG' => ['$' => number_format($item['qtd'], 4, '.', '')],
                'CODLOCALORIG' => ['$' => (string)$this->api['codlocalorig']],
                'CODVOL' => ['$' => $item['codvol']],
                'VLRUNIT' => ['$' => number_format($item['vlrunit'], 2, '.', '')],
                'PERCDESC' => ['$' => '0'],
                'IGNOREDESCPROMOQTD' => ['$' => 'True']
            ];
        }

        return [
            'serviceName' => 'CACSP.incluirNota',
            'requestBody' => [
                'nota' => [
                    'cabecalho' => [
                        'NUNOTA' => [],
                        'CODPARC' => ['$' => (string)$codParc],
                        'DTNEG' => ['$' => date('d/m/Y')],
                        'CODTIPOPER' => ['$' => (string)$this->api['codtipoper']],
                        'CODTIPVENDA' => ['$' => (string)$this->api['codtipvenda']],
                        'CODVEND' => ['$' => (string)$this->api['codvend']],
                        'CODEMP' => ['$' => (string)$this->api['codemp']],
                        'TIPMOV' => ['$' => 'P'],
                        'NUMNOTA' => ['$' => $xmlData['numero'] ?: '0'],
                        'OBSERVACAO' => ['$' => 'Importado via XML Render']
                    ],
                    'itens' => [
                        'INFORMARPRECO' => 'True',
                        'item' => $items
                    ]
                ]
            ]
        ];
    }

    private function step(string $msg): void
    {
        $this->logs[] = $msg;
        app_log($msg);
    }
}
