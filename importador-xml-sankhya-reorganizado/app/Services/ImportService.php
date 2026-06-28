<?php

namespace App\Services;

class ImportService
{
    public function montarPayloadPedido(array $xmlData): array
    {
        $api = config('sankhya');

        return [
            'cabecalho' => [
                'CODTIPOPER' => $api['codtipoper'],
                'CODEMP' => $api['codemp'],
                'CODNAT' => $api['codnat'],
                'CODCENCUS' => $api['codcencus'],
                'CODPROJ' => $api['codproj'],
                'DTNEG' => date('d/m/Y'),
                'OBSERVACAO' => 'Pedido gerado via importador XML Sankhya.',
            ],
            'parceiro' => [
                'NOMEPARC' => $xmlData['cliente']['nome'] ?? '',
                'DOCUMENTO' => $xmlData['cliente']['documento'] ?? '',
                'CIDADE' => $xmlData['cliente']['cidade'] ?? '',
                'UF' => $xmlData['cliente']['uf'] ?? '',
            ],
            'itens' => array_map(function (array $item): array {
                return [
                    'CODPROD_EXTERNO' => $item['cprod'],
                    'EAN' => $item['cean'],
                    'DESCRPROD' => $item['descricao'],
                    'QTDNEG' => $item['qtd'],
                    'CODVOL' => $item['un'],
                    'VLRUNIT' => $item['vlrunit'],
                    'VLRTOT' => $item['vlrtot'],
                ];
            }, $xmlData['itens'] ?? []),
            'total' => $xmlData['total'] ?? 0,
            'chave_nfe' => $xmlData['chave'] ?? '',
        ];
    }
}
