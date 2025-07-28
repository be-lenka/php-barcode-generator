<?php declare(strict_types=1);

namespace BeLenka\BarcodeGenerator;

class BarcodeGeneratorEAN13
{
    private const EAN13_LENGTH = 13;

    private const FONT_FAMILY = 'Libre Barcode EAN13 Text';

    public function __construct(
        private TCPDFBarcodeFactory $TCPDFBarcodeFactory,
    ) {
    }

    /**
     * @throws BarcodeGeneratorException
     */
    public function getSVGcode(string $code): string
    {
        if (!$this->isValidEAN13($code)) {
            throw new BarcodeGeneratorException('Invalid EAN13 code.');
        }

        try {
            $TCPDFBarcode = $this->TCPDFBarcodeFactory->create($code, TCPDFBarcodeType::TYPE_EAN13);
        } catch (\Throwable $th) {
            throw new BarcodeGeneratorException('Unable to instantiate (TCPDFBarcode); Probably invalid EAN13 code.', 0, $th);
        }

        $barcodeArray = $TCPDFBarcode->getBarcodeArray();

        return $this->getBarcodeSVGcode($barcodeArray);
    }

    /**
     * Performs very simple validation of EAN13 code.
     */
    private function isValidEAN13(string $code): bool
    {
        if (!preg_match('/^\d{' . self::EAN13_LENGTH . '}$/', $code)) {
            return false;
        }
        return true;
    }

    /**
     * Return a SVG string representation of barcode.
     *
     * This is a modified copy of the function:
     * @see vendor/tecnickcom/tcpdf/tcpdf_barcodes_1d.php::getBarcodeSVGcode()
     *
     * @param array<string,mixed> $barcode_array
     * @param int $w Minimum width of a single bar in user units.
     * @param int $h Height of barcode in user units.
     * @param string $color Foreground color (in SVG format) for bar elements (background is transparent).
     *
     * @return string SVG code.
     */
    private function getBarcodeSVGcode($barcode_array, $w=2, $h=70, $color='black'): string {
        $bars_x_offset = 16;

        // replace table for special characters
        $repstr = array("\0" => '', '&' => '&amp;', '<' => '&lt;', '>' => '&gt;');
        $svg = '<'.'?'.'xml version="1.0" standalone="no"'.'?'.'>'."\n";
        $svg .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'."\n";
        $svg .= '<svg width="'.round(($barcode_array['maxw'] * $w) + $bars_x_offset, 3).'" height="'.($h+20).'" version="1.1" xmlns="http://www.w3.org/2000/svg">'."\n";
        $svg .= "\t".'<desc>'.strtr($barcode_array['code'], $repstr).'</desc>'."\n";
        $svg .= "\t".'<g id="bars" fill="'.$color.'" stroke="none">'."\n";
        // print bars
        $x = 0;
        $x += $bars_x_offset;
        foreach ($barcode_array['bcode'] as $k => $v) {
            $bw = round(($v['w'] * $w), 3);
            $bh = ($v['h'] * $h) / $barcode_array['maxh'];
            if (
                // guard start
                ($k >= 0 && $k <= 2)
                // guard centre
                || ($k >= 28 && $k <= 30)
                // guard end
                || ($k >= 56 && $k <= 58)
            ) {
                $bh += 10;
            }
            $bh = round($bh, 3);
            if ($v['t']) {
                $y = round(($v['p'] * $h / $barcode_array['maxh']), 3);
                // draw a vertical bar
                $svg .= "\t\t".'<rect x="'.$x.'" y="'.$y.'" width="'.$bw.'" height="'.$bh.'" />'."\n";
            }
            $x += $bw;
        }
        $svg .= "\t".'</g>'."\n";

        $svg .= $this->renderLegend($barcode_array['code']);

        $svg .= '</svg>'."\n";
        return $svg;
    }

    private function renderLegend(string $code): string {
        [
            $codeStart,
            $codeCentre,
            $codeEnd,
        ] = $this->parseHumanFriendlyCodeParts($code);

        $svg = '';

        $svg .= "\t".'<g id="legend">'."\n";
        $svg .= "\t\t".'<text y="90" font-family="' . self::FONT_FAMILY . '" font-size="24">'."\n";
        $svg .= "\t\t\t".'<tspan x="0">'.$codeStart.'</tspan>'."\n";
        $svg .= "\t\t\t".'<tspan x="30">'.$codeCentre.'</tspan>'."\n";
        $svg .= "\t\t\t".'<tspan x="122">'.$codeEnd.'</tspan>'."\n";
        $svg .= "\t\t".'</text>'."\n";
        $svg .= "\t".'</g>'."\n";

        return $svg;
    }

    /**
     * @return list<string> {1-digit, 6-digits, 6-digits}
     */
    private function parseHumanFriendlyCodeParts(string $code): array {
        if (mb_strlen($code) !== self::EAN13_LENGTH) {
            throw new BarcodeGeneratorException('The (code) must be (' . self::EAN13_LENGTH . ') chars long.');
        }

        $cursor = 1;
        $length = 6;

        $codeStart = substr($code, 0, $cursor);
        $codeCentre = substr($code, $cursor, $length);
        $codeEnd = substr($code, $cursor + $length, $length);

        return [
            $codeStart,
            $codeCentre,
            $codeEnd,
        ];
    }
}
