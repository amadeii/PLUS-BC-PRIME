<?php

namespace App\Utils;

class SintegraUtil
{
    public function getXml($venda, $path)
    {
        $arquivo = public_path($path) . $venda->chave . '.xml';

        if (!file_exists($arquivo)) {
            return null;
        }

        try {
            return simplexml_load_file($arquivo);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getItensNfe($xml)
    {
        return $xml->NFe->infNFe->det ?? [];
    }

    public function getEmitente($xml)
    {
        return $xml->NFe->infNFe->emit ?? null;
    }

    public function getDestinatario($xml)
    {
        return $xml->NFe->infNFe->dest ?? null;
    }

    public function getIde($xml)
    {
        return $xml->NFe->infNFe->ide ?? null;
    }

    public function getChave($xml)
    {
        return isset($xml->NFe->infNFe)
            ? substr((string)$xml->NFe->infNFe->attributes()->Id, 3, 44)
            : '';
    }

    public function getTotal($xml)
    {
        return $xml->NFe->infNFe->total->ICMSTot ?? null;
    }
}