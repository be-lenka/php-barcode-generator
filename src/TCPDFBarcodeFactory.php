<?php declare(strict_types=1);

namespace BeLenka\BarcodeGenerator;

use TCPDFBarcode;

class TCPDFBarcodeFactory
{
    public function create(string $code, string $barcodeType): TCPDFBarcode
    {
        return new TCPDFBarcode($code, $barcodeType);
    }
}
