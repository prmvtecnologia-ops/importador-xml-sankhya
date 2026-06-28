<?php

namespace App\Services;

use Exception;
use SimpleXMLElement;

class XmlNfeParser
{
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception('Arquivo XML não encontrado.');
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($filePath);

        if (!$xml instanceof SimpleXMLElement) {
            throw new Exception('XML inválido ou ilegível.');
        }

        $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');

        $infNfe = $this->first($xml->xpath('//nfe:infNFe')) ?: $this->first($xml->xpath('//infNFe'));
        if (!$infNfe) {
            throw new Exception('Não foi possível localizar a tag infNFe no XML.');
        }

        $dest = $this->first($xml->xpath('//nfe:dest')) ?: $this->first($xml->xpath('//dest'));
        $total = $this->first($xml->xpath('//nfe:ICMSTot')) ?: $this->first($xml->xpath('//ICMSTot'));
        $itensNodes = $xml->xpath('//nfe:det') ?: $xml->xpath('//det') ?: [];

        $cliente = [
            'nome' => $this->str($dest->xNome ?? ''),
            'documento' => $this->str($dest->CNPJ ?? $dest->CPF ?? ''),
            'cidade' => $this->str($dest->enderDest->xMun ?? ''),
            'uf' => $this->str($dest->enderDest->UF ?? ''),
        ];

        $itens = [];
        foreach ($itensNodes as $det) {
            $prod = $det->prod ?? null;
            if (!$prod) {
                continue;
            }

            $itens[] = [
                'cprod' => $this->str($prod->cProd ?? ''),
                'cean' => $this->str($prod->cEAN ?? ''),
                'descricao' => $this->str($prod->xProd ?? ''),
                'qtd' => $this->num($prod->qCom ?? 0),
                'un' => $this->str($prod->uCom ?? ''),
                'vlrunit' => $this->num($prod->vUnCom ?? 0),
                'vlrtot' => $this->num($prod->vProd ?? 0),
            ];
        }

        return [
            'chave' => $this->str($infNfe['Id'] ?? ''),
            'cliente' => $cliente,
            'itens' => $itens,
            'total' => $this->num($total->vNF ?? 0),
        ];
    }

    private function first(array|false $items): mixed
    {
        return $items && count($items) > 0 ? $items[0] : null;
    }

    private function str(mixed $value): string
    {
        return trim((string)$value);
    }

    private function num(mixed $value): float
    {
        return (float)str_replace(',', '.', (string)$value);
    }
}
