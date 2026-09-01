<?php

/**
 * Window geometry for #9 double-window patient statements.
 *
 * Zeros mPDF page margins and prints the return and patient addresses in
 * the first trifold panel using table row heights (mPDF ignores position:absolute).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\Pdf\Config_Mpdf;

final class StatementEnvelope
{
    public function isWindowed(): bool
    {
        return true;
    }

    /**
     * @return array{
     *   page_w: float,
     *   page_h: float,
     *   panel: float,
     *   left: float,
     *   return: array{top: float, h: float, w: float, left: float},
     *   to: array{top: float, h: float, w: float, left: float}
     * }
     */
    public function geometry(): array
    {
        return self::presetHash9();
    }

    /**
     * @return array<string, mixed>
     */
    public function mpdfConfig(): array
    {
        $cfg = Config_Mpdf::getConfigMpdf();
        $cfg['margin_left'] = 0;
        $cfg['margin_right'] = 0;
        $cfg['margin_top'] = 0;
        $cfg['margin_bottom'] = 0;
        $cfg['margin_header'] = 0;
        $cfg['margin_footer'] = 0;
        $cfg['default_font'] = 'helvetica';
        return $cfg;
    }

    public function windowCss(): string
    {
        return '<style>
@page { sheet-size: Letter; margin: 0; }
body { margin: 0; padding: 0; }
.stmt-env-sheet { margin: 0; padding: 0; }
.stmt-env-windows td { font-family: helvetica; }
.stmt-env-body { margin: 0; padding: 0.15in 0.40in 0.08in 0.40in; }
.stmt-env-stub { margin: 0; padding: 0.08in 0.40in 0.10in 0.40in; }
.stmt-env-sheet + .stmt-env-sheet { page-break-before: always; }
</style>';
    }

    /**
     * @param list<string>|mixed $toLines
     */
    public function windowHtml(string $returnName, string $returnStreet, string $returnCsz, mixed $toLines): string
    {
        $g = $this->geometry();
        $returnLines = [$returnName];
        foreach (preg_split("/\n/", $returnStreet) ?: [] as $ln) {
            $ln = trim((string) $ln);
            if ($ln !== '') {
                $returnLines[] = $ln;
            }
        }
        if (trim($returnCsz) !== '') {
            $returnLines[] = $returnCsz;
        }
        $to = [];
        if (is_array($toLines)) {
            foreach ($toLines as $line) {
                if (!is_scalar($line)) {
                    continue;
                }
                $line = trim((string) $line);
                if ($line !== '') {
                    $to[] = $line;
                }
            }
        }

        $returnBox = $g['return'];
        $toBox = $g['to'];
        $h = static fn(float $n): string => sprintf('%.4fin', $n);
        $pt = static fn(float $in): string => sprintf('%.2f', $in * 72);

        $top = $returnBox['top'];
        $gap = $toBox['top'] - ($returnBox['top'] + $returnBox['h']);
        $rest = $g['panel'] - ($toBox['top'] + $toBox['h']);

        return '<table class="stmt-env-windows" cellpadding="0" cellspacing="0" style="width:612pt;height:' . $h($g['panel']) . ';border:0;">'
            . '<tr><td height="' . $pt($top) . '" style="height:' . $h($top) . ';font-size:1px;line-height:' . $h($top) . ';">&nbsp;</td></tr>'
            . '<tr>' . $this->addrCell($returnLines, $returnBox, 11.0, $g['page_w']) . '</tr>'
            . '<tr><td height="' . $pt($gap) . '" style="height:' . $h($gap) . ';font-size:1px;line-height:' . $h($gap) . ';">&nbsp;</td></tr>'
            . '<tr>' . $this->addrCell($to, $toBox, 16.0, $g['page_w']) . '</tr>'
            . '<tr><td height="' . $pt($rest) . '" style="height:' . $h($rest) . ';font-size:1px;line-height:' . $h($rest) . ';">&nbsp;</td></tr>'
            . '</table>';
    }

    /**
     * @param list<string> $lines
     * @return array{0: float, 1: list<string>}
     */
    public static function fitLines(
        array $lines,
        float $maxW,
        float $maxH,
        float $maxPt,
        float $minPt = 7.0,
        float $lineHeight = 1.00
    ): array {
        for ($pt = $maxPt; $pt >= $minPt - 0.0001; $pt -= 0.05) {
            $wrapped = self::wrapLines($lines, $maxW, $pt);
            if ($wrapped === []) {
                return [$pt, $wrapped];
            }
            $widest = 0.0;
            foreach ($wrapped as $ln) {
                $widest = max($widest, self::textWidthIn($ln, $pt));
            }
            $h = count($wrapped) * ($pt / 72.0) * $lineHeight;
            if ($widest <= $maxW && $h <= $maxH) {
                return [$pt, $wrapped];
            }
        }
        return [$minPt, self::wrapLines($lines, $maxW, $minPt)];
    }

    /**
     * US #9: return 1-3/16 x 3-1/2, 3/8 left, 2 from bottom; patient 1 x 4, 3/8 left, 1/2 from bottom.
     *
     * @return array{
     *   page_w: float,
     *   page_h: float,
     *   panel: float,
     *   left: float,
     *   return: array{top: float, h: float, w: float, left: float},
     *   to: array{top: float, h: float, w: float, left: float}
     * }
     */
    public static function presetHash9(): array
    {
        return self::fromBottomOrigin(
            left: 0.375,
            returnH: 1.1875,
            returnW: 3.500,
            returnFromBottom: 2.000,
            toH: 1.000,
            toW: 4.000,
            toFromBottom: 0.500,
            envH: 3.875
        );
    }

    /**
     * Convert carton from-bottom numbers to letter-top geometry.
     *
     * @return array{
     *   page_w: float,
     *   page_h: float,
     *   panel: float,
     *   left: float,
     *   return: array{top: float, h: float, w: float, left: float},
     *   to: array{top: float, h: float, w: float, left: float}
     * }
     */
    private static function fromBottomOrigin(
        float $left,
        float $returnH,
        float $returnW,
        float $returnFromBottom,
        float $toH,
        float $toW,
        float $toFromBottom,
        float $envH
    ): array {
        return [
            'page_w' => 8.5,
            'page_h' => 11.0,
            'panel' => 11.0 / 3.0,
            'left' => $left,
            'return' => [
                'top' => $envH - ($returnFromBottom + $returnH),
                'h' => $returnH,
                'w' => $returnW,
                'left' => $left,
            ],
            'to' => [
                'top' => $envH - ($toFromBottom + $toH),
                'h' => $toH,
                'w' => $toW,
                'left' => $left,
            ],
        ];
    }

    /**
     * @param list<string> $lines
     * @param array{top: float, h: float, w: float, left: float} $box
     */
    private function addrCell(array $lines, array $box, float $maxPt, float $pageW): string
    {
        $inset = 0.050;
        $maxW = $box['w'] - 2 * $inset;
        $maxH = $box['h'] - 2 * $inset;
        [$pt, $fitted] = self::fitLines($lines, $maxW, $maxH, $maxPt);
        $lh = 1.00;
        $n = max(1, count($fitted));
        $textH = $n * ($pt / 72.0) * $lh;
        $padY = max(0.0, ($box['h'] - $textH) / 2.0);
        $html = [];
        foreach ($fitted as $ln) {
            $html[] = self::esc($ln);
        }
        $inner = implode('<br />', $html);
        $padL = $box['left'];
        $padR = $pageW - $box['left'] - $box['w'];
        $h = static fn(float $n): string => sprintf('%.4fin', $n);

        return '<td height="' . sprintf('%.2f', $box['h'] * 72) . '" align="center" valign="top" style="'
            . 'height:' . $h($box['h']) . ';'
            . 'padding-left:' . $h($padL) . ';'
            . 'padding-right:' . $h($padR) . ';'
            . 'padding-top:' . $h($padY) . ';'
            . 'padding-bottom:0;'
            . 'text-align:center;vertical-align:top;'
            . 'font-family:helvetica;font-size:' . sprintf('%.2fpt', $pt) . ';'
            . 'line-height:' . $lh . ';">'
            . $inner
            . '</td>';
    }

    private static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @param list<string> $lines
     * @return list<string>
     */
    private static function wrapLines(array $lines, float $maxWidthIn, float $pt): array
    {
        $out = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (self::textWidthIn($line, $pt) <= $maxWidthIn) {
                $out[] = $line;
                continue;
            }
            $words = preg_split('/\s+/', $line) ?: [$line];
            $cur = '';
            foreach ($words as $word) {
                $try = ($cur === '') ? $word : ($cur . ' ' . $word);
                if (self::textWidthIn($try, $pt) <= $maxWidthIn) {
                    $cur = $try;
                } else {
                    if ($cur !== '') {
                        $out[] = $cur;
                    }
                    $cur = $word;
                }
            }
            if ($cur !== '') {
                $out[] = $cur;
            }
        }
        return $out;
    }

    private static function textWidthIn(string $s, float $pt): float
    {
        return self::helveticaWidthEm($s) * $pt / 72.0;
    }

    private static function helveticaWidthEm(string $s): float
    {
        /** @var array<int, int> $w */
        static $w = [
            278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278,
            556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556,
            1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778,
            667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 278, 278, 278, 469, 556,
            333, 556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556,
            556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584,
        ];
        $sum = 0;
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($s[$i]);
            if ($c >= 32 && $c <= 126) {
                $sum += $w[$c - 32];
            } else {
                $sum += 600;
            }
        }
        return $sum / 1000.0;
    }
}
