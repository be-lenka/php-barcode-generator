<?php declare(strict_types=1);

namespace BeLenka\BarcodeGenerator;

class BarcodeGeneratorEAN13Factory
{
    public function create(): BarcodeGeneratorEAN13
    {
        return new BarcodeGeneratorEAN13(
            new TCPDFBarcodeFactory(),
        );
    }
}
