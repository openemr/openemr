<?php

/**
 * Window geometry for patient statements mailed in a double-window envelope.
 *
 * Stock modern/plain statements are unchanged. Windowed profiles zero the
 * mPDF page margins and print the return and patient addresses in the first
 * trifold panel using table row heights (mPDF ignores position:absolute).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Pdf\Config_Mpdf;

final class StatementEnvelope
{
    public const PROFILE_DEFAULT = 'default';
    public const PROFILE_HASH9 = 'hash9';
    public const PROFILE_HASH10 = 'hash10';
    public const PROFILE_CUSTOM = 'custom';

    /**
     * Custom carton numbers: window height x width, from left, from bottom,
     * flap up. Envelope height converts bottoms to letter-top.
     *
     * @param array{
     *   units?: string,
     *   envelope_height?: float|int|string,
     *   return_left?: float|int|string,
     *   return_bottom?: float|int|string,
     *   return_width?: float|int|string,
     *   return_height?: float|int|string,
     *   to_left?: float|int|string,
     *   to_bottom?: float|int|string,
     *   to_width?: float|int|string,
     *   to_height?: float|int|string
     * } $custom
     */
    public function __construct(
        private readonly string $profile,
        private readonly array $custom = []
    ) {
    }

    public static function fromGlobals(): self
    {
        $bag = OEGlobalsBag::getInstance();
        $profile = $bag->getString('statement_envelope');
        if ($profile === '') {
            $profile = self::PROFILE_DEFAULT;
        }
        $units = $bag->getString('statement_env_units');
        if ($units === '') {
            $units = 'in';
        }
        return new self($profile, [
            'units' => $units,
            'envelope_height' => $bag->getString('statement_env_height'),
            'return_left' => $bag->getString('statement_env_return_left'),
            'return_bottom' => $bag->getString('statement_env_return_bottom'),
            'return_width' => $bag->getString('statement_env_return_width'),
            'return_height' => $bag->getString('statement_env_return_height'),
            'to_left' => $bag->getString('statement_env_to_left'),
            'to_bottom' => $bag->getString('statement_env_to_bottom'),
            'to_width' => $bag->getString('statement_env_to_width'),
            'to_height' => $bag->getString('statement_env_to_height'),
        ]);
    }

    public function isWindowed(): bool
    {
        return $this->geometry() !== null;
    }

    /**
     * @return array{
     *   page_w: float,
     *   page_h: float,
     *   panel: float,
     *   left: float,
     *   return: array{top: float, h: float, w: float, left: float},
     *   to: array{top: float, h: float, w: float, left: float}
     * }|null
     */
    public function geometry(): ?array
    {
        return match ($this->profile) {
            self::PROFILE_HASH9 => self::presetHash9(),
            self::PROFILE_HASH10 => self::presetHash10(),
            self::PROFILE_CUSTOM => $this->customGeometry(),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function mpdfConfig(): array
    {
        $cfg = Config_Mpdf::getConfigMpdf();
        if (!$this->isWindowed()) {
            return $cfg;
        }
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
        if (!$this->isWindowed()) {
            return '';
        }
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
        if ($g === null) {
            return '';
        }
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
     * US #10: return 1 x 3-1/2, 1/2 left, 2-1/2 from bottom; patient 1-3/8 x 4, 1/2 left, 3/4 from bottom.
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
    public static function presetHash10(): array
    {
        return self::fromBottomOrigin(
            left: 0.5,
            returnH: 1.0,
            returnW: 3.5,
            returnFromBottom: 2.5,
            toH: 1.375,
            toW: 4.0,
            toFromBottom: 0.75,
            envH: 4.125
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
     * @return array{
     *   page_w: float,
     *   page_h: float,
     *   panel: float,
     *   left: float,
     *   return: array{top: float, h: float, w: float, left: float},
     *   to: array{top: float, h: float, w: float, left: float}
     * }|null
     */
    private function customGeometry(): ?array
    {
        $unit = self::normalizeUnit($this->custom['units'] ?? 'in');
        $envH = self::parseLength($this->custom['envelope_height'] ?? null, $unit);
        $returnLeft = self::parseLength($this->custom['return_left'] ?? null, $unit);
        $returnBottom = self::parseLength($this->custom['return_bottom'] ?? null, $unit);
        $returnW = self::parseLength($this->custom['return_width'] ?? null, $unit);
        $returnH = self::parseLength($this->custom['return_height'] ?? null, $unit);
        $toLeft = self::parseLength($this->custom['to_left'] ?? null, $unit);
        $toBottom = self::parseLength($this->custom['to_bottom'] ?? null, $unit);
        $toW = self::parseLength($this->custom['to_width'] ?? null, $unit);
        $toH = self::parseLength($this->custom['to_height'] ?? null, $unit);
        if (
            $envH === null || $returnLeft === null || $returnBottom === null
            || $returnW === null || $returnH === null
            || $toLeft === null || $toBottom === null || $toW === null || $toH === null
            || $envH <= 0.0 || $returnW <= 0.0 || $returnH <= 0.0 || $toW <= 0.0 || $toH <= 0.0
            || $returnLeft < 0.0 || $toLeft < 0.0 || $returnBottom < 0.0 || $toBottom < 0.0
        ) {
            return null;
        }
        $g = self::fromBottomOrigin(
            left: $returnLeft,
            returnH: $returnH,
            returnW: $returnW,
            returnFromBottom: $returnBottom,
            toH: $toH,
            toW: $toW,
            toFromBottom: $toBottom,
            envH: $envH
        );
        $g['to']['left'] = $toLeft;
        $g['left'] = $returnLeft;
        return $g;
    }

    /**
     * 1 in = 2.54 cm exactly (127/50).
     */
    public static function inchesToCentimeters(float $inches): float
    {
        return round($inches * 127.0 / 50.0, 4);
    }

    public static function centimetersToInches(float $cm): float
    {
        return round($cm * 50.0 / 127.0, 6);
    }

    public static function normalizeUnit(mixed $unit): string
    {
        if (!is_string($unit) && !is_int($unit) && !is_float($unit)) {
            return 'in';
        }
        $u = strtolower(trim((string) $unit));
        return $u === 'cm' ? 'cm' : 'in';
    }

    public static function unitFromText(string $text): ?string
    {
        $s = strtolower($text);
        if (preg_match('/\bcm\b|centimet/', $s) === 1) {
            return 'cm';
        }
        if (preg_match('/\bin\b|inch/', $s) === 1) {
            return 'in';
        }
        return null;
    }

    /**
     * Length to inches. Text may name in or cm; otherwise $unit is used.
     */
    public static function parseLength(mixed $value, string $unit = 'in'): ?float
    {
        if (is_string($value)) {
            $unit = self::unitFromText($value) ?? self::normalizeUnit($unit);
        } else {
            $unit = self::normalizeUnit($unit);
        }
        return self::toInches(self::parseInch($value), $unit);
    }

    public static function toInches(?float $n, string $unit): ?float
    {
        if ($n === null) {
            return null;
        }
        if (self::normalizeUnit($unit) === 'cm') {
            return self::centimetersToInches($n);
        }
        return $n;
    }

    /**
     * Number from a carton or a ruler: 3/8, 1-3/16, 3 7/8, 0.375, or 9.8425.
     */
    public static function parseInch(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        if (!is_string($value)) {
            return null;
        }
        $s = strtolower(trim($value));
        $s = str_replace(['centimeters', 'centimetres', 'centimeter', 'centimetre', 'inches', 'inch', '"'], '', $s);
        $s = preg_replace('/\b(in|cm)\.?\b/', '', $s) ?? $s;
        $s = trim(preg_replace('/\s+/', ' ', $s) ?? $s);
        if ($s === '') {
            return null;
        }
        if (preg_match('/^(\d+)\s*-\s*(\d+)\s*\/\s*(\d+)$/', $s, $m) === 1) {
            $den = (int) $m[3];
            return $den === 0 ? null : (float) $m[1] + ((float) $m[2] / $den);
        }
        if (preg_match('/^(\d+)\s+(\d+)\s*\/\s*(\d+)$/', $s, $m) === 1) {
            $den = (int) $m[3];
            return $den === 0 ? null : (float) $m[1] + ((float) $m[2] / $den);
        }
        if (preg_match('/^(\d+)\s*\/\s*(\d+)$/', $s, $m) === 1) {
            $den = (int) $m[2];
            return $den === 0 ? null : (float) $m[1] / $den;
        }
        if (is_numeric($s)) {
            return (float) $s;
        }
        return null;
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
