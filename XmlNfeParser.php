<?php

namespace App\Services;

use Exception;
use SimpleXMLElement;

class XmlNfeParser
{
    public function parse(string $file): array
    {
        if (!file_exists($file)) {
            throw new Exception('Arquivo XML não encontrado.');
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($file);

        if (!$xml instanceof SimpleXMLElement) {
            throw new Exception('XML inválido.');
        }

        $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');

        $nodes = $xml->xpath('//nfe:infNFe');
        if (!$nodes) {
            $nodes = $xml->xpath('//infNFe');
        }

        if (!$nodes) {
            throw new Exception('Estrutura infNFe não localizada.');
        }

        $inf = $nodes[0];
        $ide = $inf->ide;
        $dest = $inf->dest ?: $inf->emit;

        $cliente = $this->parseCliente($dest);

        $itens = [];
        foreach ($inf->det as $det) {
            $prod = $det->prod;

            $itens[] = [
                'nitem' => (string)$det['nItem'],
                'cprod' => trim((string)$prod->cProd),
                'cean' => trim((string)$prod->cEAN),
                'descricao' => trim((string)$prod->xProd),
                'ncm' => trim((string)$prod->NCM),
                'cfop' => trim((string)$prod->CFOP),
                'un' => trim((string)$prod->uCom),
                'qtd' => (float)str_replace(',', '.', (string)$prod->qCom),
                'vlrunit' => (float)str_replace(',', '.', (string)$prod->vUnCom),
                'vlrtot' => (float)str_replace(',', '.', (string)$prod->vProd),
            ];
        }

        $total = isset($inf->total->ICMSTot->vNF)
            ? (float)str_replace(',', '.', (string)$inf->total->ICMSTot->vNF)
            : array_sum(array_column($itens, 'vlrtot'));

        return [
            'numero' => (string)($ide->nNF ?? ''),
            'serie' => (string)($ide->serie ?? ''),
            'data' => (string)($ide->dhEmi ?? ''),
            'cliente' => $cliente,
            'itens' => $itens,
            'total' => $total,
        ];
    }

    private function parseCliente(?SimpleXMLElement $pessoa): array
    {
        if (!$pessoa) {
            throw new Exception('Cliente/parceiro não localizado no XML.');
        }

        $ender = $pessoa->enderDest ?: $pessoa->enderEmit;
        $documento = (string)($pessoa->CNPJ ?? $pessoa->CPF ?? '');

        return [
            'nome' => trim((string)$pessoa->xNome),
            'documento' => preg_replace('/\D/', '', $documento),
            'ie' => trim((string)($pessoa->IE ?? '')),
            'email' => trim((string)($pessoa->email ?? '')),
            'logradouro' => $ender ? trim((string)$ender->xLgr) : '',
            'numero' => $ender ? trim((string)$ender->nro) : '',
            'bairro' => $ender ? trim((string)$ender->xBairro) : '',
            'cidade' => $ender ? trim((string)$ender->xMun) : '',
            'uf' => $ender ? trim((string)$ender->UF) : '',
            'cep' => $ender ? preg_replace('/\D/', '', (string)$ender->CEP) : '',
            'fone' => $ender ? preg_replace('/\D/', '', (string)$ender->fone) : '',
        ];
    }
}
