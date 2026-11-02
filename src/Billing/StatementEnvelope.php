<?php

/**
 * Window geometry for patient statements mailed in a double-window envelope.
 *
 * None keeps the stock layout and the physical remit address. A windowed
 * profile zeroes the margins only when the site template draws the windows,
 * and places the addresses in the first trifold. The folded sheet is taken
 * to rest on the bottom of the envelope. mPDF ignores position:absolute, so
 * the windows are table rows. Type is measured in the font the PDF embeds.
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

final readonly class StatementEnvelope
{
    public const PROFILE_DEFAULT = 'default';
    public const PROFILE_HASH9 = 'hash9';
    public const PROFILE_HASH10 = 'hash10';
    public const PROFILE_CUSTOM = 'custom';

    /** Smallest type in a window. 8 pt is the USPS/Canada destination floor. */
    public const MIN_ADDRESS_PT = 8.0;

    /** 2 mm inside the glass (Royal Mail window-internal minimum). */
    public const WINDOW_INSET_IN = 10.0 / 127.0;

    /** mPDF font used in the windows. Same family as the stock PDF body. */
    public const WINDOW_FONT = 'dejavusans';

    /**
     * Remit column in the non-windowed text stub: 7 spaces, 30-char addressee, 1 space.
     */
    public const TEXT_REMIT_COL = 38;

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
        private string $profile,
        private array $custom = []
    ) {
    }

    /**
     * Build from Administration > Billing > Statement envelope globals.
     */
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

    /**
     * True when a #9, #10, or valid custom window profile is selected.
     */
    public function isWindowed(): bool
    {
        return $this->geometry() !== null;
    }

    /**
     * A chosen profile whose windows are blank or do not fit the page.
     * None is a valid choice and is not reported here.
     */
    public function isMisconfigured(): bool
    {
        return $this->profile !== self::PROFILE_DEFAULT && $this->geometry() === null;
    }

    /**
     * Billing-facility mailing address, or physical address if mailing is incomplete.
     *
     * @return array{0: string, 1: string}
     */
    public static function facilityRemitAddr(mixed $row): array
    {
        if (!is_array($row)) {
            return ['', ''];
        }
        $str = static fn(mixed $v): string => is_scalar($v) ? trim((string) $v) : '';
        $mailStreet = $str($row['mail_street'] ?? '');
        $mailStreet2 = $str($row['mail_street2'] ?? '');
        $mailCity = $str($row['mail_city'] ?? '');
        $mailState = $str($row['mail_state'] ?? '');
        $mailZip = $str($row['mail_zip'] ?? '');
        if ($mailStreet !== '' && $mailCity !== '' && $mailState !== '' && $mailZip !== '') {
            $street = $mailStreet;
            if ($mailStreet2 !== '') {
                $street .= "\n" . $mailStreet2;
            }
            return [$street, $mailCity . ', ' . $mailState . ', ' . $mailZip];
        }
        return [
            $str($row['street'] ?? ''),
            $str($row['city'] ?? '') . ', ' . $str($row['state'] ?? '') . ', ' . $str($row['postal_code'] ?? ''),
        ];
    }

    /**
     * Remit street and city line.
     *
     * A window uses a complete mailing address. None keeps the physical address,
     * which is what a site that never sets an envelope already prints.
     *
     * @return array{0: string, 1: string}
     */
    public static function remitLines(mixed $row, bool $windowed): array
    {
        if ($windowed) {
            return self::facilityRemitAddr($row);
        }
        if (!is_array($row)) {
            return ['', ''];
        }
        $str = static fn(mixed $v): string => is_scalar($v) ? trim((string) $v) : '';
        return [
            $str($row['street'] ?? ''),
            $str($row['city'] ?? '') . ', ' . $str($row['state'] ?? '') . ', ' . $str($row['postal_code'] ?? ''),
        ];
    }

    /**
     * Keep extra remit street lines under the remit column in fixed-width text.
     */
    public static function indentTextRemit(string $remit, int $column = self::TEXT_REMIT_COL): string
    {
        if ($column < 1 || !str_contains($remit, "\n")) {
            return $remit;
        }
        return str_replace("\n", "\n" . str_repeat(' ', $column), $remit);
    }

    /**
     * Scalar statement field as a string, or empty when missing.
     */
    public static function stmtString(mixed $stmt, string $key): string
    {
        if (!is_array($stmt) || !array_key_exists($key, $stmt)) {
            return '';
        }
        $v = $stmt[$key];
        return is_scalar($v) ? (string) $v : '';
    }

    /**
     * One addressee line from $stmt['to'], or empty when missing.
     */
    public static function stmtToLine(mixed $stmt, int $index): string
    {
        if (!is_array($stmt) || !isset($stmt['to']) || !is_array($stmt['to'])) {
            return '';
        }
        $v = $stmt['to'][$index] ?? null;
        return is_scalar($v) ? (string) $v : '';
    }

    /**
     * Monospace address block for plain-text statements, using envelope geometry.
     *
     * @param list<string> $toLines
     * @return array{text: string, lines: int}
     */
    public function textAddressBlock(string $remitName, string $remitStreet, string $remitCsz, array $toLines): array
    {
        $g = $this->geometry();
        if ($g === null) {
            return ['text' => '', 'lines' => 0];
        }
        $cpi = 10.0;
        $lpi = 6.0;
        $inset = self::WINDOW_INSET_IN;
        $returnPad = str_repeat(' ', max(0, (int) round(($g['return']['left'] + $inset) * $cpi)));
        $toPad = str_repeat(' ', max(0, (int) round(($g['to']['left'] + $inset) * $cpi)));
        $returnLines = [$remitName];
        foreach (preg_split("/\n/", $remitStreet) ?: [] as $ln) {
            $ln = trim($ln);
            if ($ln !== '') {
                $returnLines[] = $ln;
            }
        }
        if (trim($remitCsz) !== '') {
            $returnLines[] = $remitCsz;
        }
        $returnFitted = self::fitTextWindow(
            $returnLines,
            $g['return']['w'] - 2.0 * $inset,
            $g['return']['h'] - 2.0 * $inset
        );
        $toFitted = self::fitTextWindow(
            $toLines,
            $g['to']['w'] - 2.0 * $inset,
            $g['to']['h'] - 2.0 * $inset
        );
        $out = self::padTextToLine('', (int) ceil(($g['return']['top'] + $inset) * $lpi));
        foreach ($returnFitted as $ln) {
            $out .= $returnPad . $ln . "\n";
        }
        $out = self::padTextToLine($out, (int) ceil(($g['to']['top'] + $inset) * $lpi));
        foreach ($toFitted as $ln) {
            if ($ln !== '') {
                $out .= $toPad . $ln . "\n";
            }
        }
        $out = self::padTextToLine($out, (int) round($g['panel'] * $lpi));
        return ['text' => $out, 'lines' => substr_count($out, "\n")];
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
        return $cfg;
    }

    /**
     * Page CSS for windowed mPDF (zero margins, first-page table layout).
     */
    public function windowCss(): string
    {
        if (!$this->isWindowed()) {
            return '';
        }
        $sheet = self::sheetFormat();
        return '<style>
@page { sheet-size: ' . $sheet . '; margin: 0; }
body { margin: 0; padding: 0; }
.stmt-env-sheet { margin: 0; padding: 0; }
.stmt-env-windows td { font-family: ' . self::WINDOW_FONT . '; }
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

        $pagePt = sprintf('%.2f', $g['page_w'] * 72);
        return '<table class="stmt-env-windows" cellpadding="0" cellspacing="0" style="width:' . $pagePt . 'pt;height:' . $h($g['panel']) . ';border:0;">'
            . '<tr><td height="' . $pt($top) . '" style="height:' . $h($top) . ';font-size:1px;line-height:' . $h($top) . ';">&nbsp;</td></tr>'
            . '<tr>' . $this->addrCell($returnLines, $returnBox, 11.0, $g['page_w']) . '</tr>'
            . '<tr><td height="' . $pt($gap) . '" style="height:' . $h($gap) . ';font-size:1px;line-height:' . $h($gap) . ';">&nbsp;</td></tr>'
            . '<tr>' . $this->addrCell($to, $toBox, 16.0, $g['page_w']) . '</tr>'
            . '<tr><td height="' . $pt($rest) . '" style="height:' . $h($rest) . ';font-size:1px;line-height:' . $h($rest) . ';">&nbsp;</td></tr>'
            . '</table>';
    }

    /**
     * Wrap and shrink to fit. Type will not go below MIN_ADDRESS_PT.
     *
     * @param list<string> $lines
     * @return array{0: float, 1: list<string>}
     */
    public static function fitLines(
        array $lines,
        float $maxW,
        float $maxH,
        float $maxPt,
        float $minPt = self::MIN_ADDRESS_PT,
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
        // Floor size: keep whole lines that fit $maxH. Extra lines are dropped.
        return [$minPt, self::clipLines(self::wrapLines($lines, $maxW, $minPt), $maxH, $minPt, $lineHeight)];
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
            toFromBottom: 0.500
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
            toFromBottom: 0.75
        );
    }

    /**
     * Convert carton from-bottom numbers to the top of the folded panel.
     * The panel rests on the bottom of the envelope, so a taller envelope
     * leaves the spare under the flap instead of shifting the windows up.
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
        float $pageW = 8.5,
        float $pageH = 11.0
    ): array {
        $panel = $pageH / 3.0;
        return [
            'page_w' => $pageW,
            'page_h' => $pageH,
            'panel' => $panel,
            'left' => $left,
            'return' => [
                'top' => $panel - ($returnFromBottom + $returnH),
                'h' => $returnH,
                'w' => $returnW,
                'left' => $left,
            ],
            'to' => [
                'top' => $panel - ($toFromBottom + $toH),
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
            || !is_finite($envH) || !is_finite($returnLeft) || !is_finite($returnBottom)
            || !is_finite($returnW) || !is_finite($returnH)
            || !is_finite($toLeft) || !is_finite($toBottom) || !is_finite($toW) || !is_finite($toH)
            || $envH <= 0.0 || $returnW <= 0.0 || $returnH <= 0.0 || $toW <= 0.0 || $toH <= 0.0
            || $returnLeft < 0.0 || $toLeft < 0.0 || $returnBottom < 0.0 || $toBottom < 0.0
            || ($returnBottom + $returnH) > $envH
            || ($toBottom + $toH) > $envH
        ) {
            return null;
        }
        [$pageW, $pageH] = self::pageSizeInches();
        $g = self::fromBottomOrigin(
            left: $returnLeft,
            returnH: $returnH,
            returnW: $returnW,
            returnFromBottom: $returnBottom,
            toH: $toH,
            toW: $toW,
            toFromBottom: $toBottom,
            pageW: $pageW,
            pageH: $pageH
        );
        $g['to']['left'] = $toLeft;
        $g['left'] = $returnLeft;
        if (!self::geometryFitsPage($g)) {
            return null;
        }
        return $g;
    }

    /**
     * @param array{
     *   page_w: float,
     *   page_h: float,
     *   panel: float,
     *   left: float,
     *   return: array{top: float, h: float, w: float, left: float},
     *   to: array{top: float, h: float, w: float, left: float}
     * } $g
     */
    private static function geometryFitsPage(array $g): bool
    {
        $ret = $g['return'];
        $to = $g['to'];
        $top = $ret['top'];
        $gap = $to['top'] - ($ret['top'] + $ret['h']);
        $rest = $g['panel'] - ($to['top'] + $to['h']);
        if ($top < 0.0 || $gap < 0.0 || $rest < 0.0) {
            return false;
        }
        if ($ret['left'] + $ret['w'] > $g['page_w'] || $to['left'] + $to['w'] > $g['page_w']) {
            return false;
        }
        if ($ret['top'] + $ret['h'] > $g['panel'] || $to['top'] + $to['h'] > $g['panel']) {
            return false;
        }
        $minGlyph = self::MIN_ADDRESS_PT / 72.0;
        $minH = $minGlyph + (2.0 * self::WINDOW_INSET_IN);
        $minW = $minGlyph + (2.0 * self::WINDOW_INSET_IN);
        if ($ret['h'] < $minH || $to['h'] < $minH || $ret['w'] < $minW || $to['w'] < $minW) {
            return false;
        }
        return true;
    }

    /**
     * 1 in = 2.54 cm exactly (127/50).
     */
    public static function inchesToCentimeters(float $inches): float
    {
        return round($inches * 127.0 / 50.0, 4);
    }

    /**
     * Inverse of inchesToCentimeters. 1 in = 2.54 cm exactly (127/50).
     */
    public static function centimetersToInches(float $cm): float
    {
        return round($cm * 50.0 / 127.0, 6);
    }

    /**
     * 'cm' or 'in'. Anything else is inches.
     */
    public static function normalizeUnit(mixed $unit): string
    {
        if (!is_string($unit) && !is_int($unit) && !is_float($unit)) {
            return 'in';
        }
        $u = strtolower(trim((string) $unit));
        return $u === 'cm' ? 'cm' : 'in';
    }

    /**
     * Unit named in the text, if any.
     */
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
        $unit = is_string($value) ? self::unitFromText($value) ?? self::normalizeUnit($unit) : self::normalizeUnit($unit);
        return self::toInches(self::parseInch($value), $unit);
    }

    /**
     * Convert a parsed carton number to inches.
     */
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
        $inset = self::WINDOW_INSET_IN;
        $maxW = $box['w'] - 2 * $inset;
        $maxH = $box['h'] - 2 * $inset;
        [$pt, $fitted] = self::fitLines($lines, $maxW, $maxH, $maxPt);
        $lh = 1.00;
        $html = [];
        foreach ($fitted as $ln) {
            $html[] = self::esc($ln);
        }
        $inner = implode('<br />', $html);
        $padL = $box['left'] + $inset;
        $padR = $pageW - $box['left'] - $box['w'] + $inset;
        $h = static fn(float $n): string => sprintf('%.4fin', $n);

        return '<td height="' . sprintf('%.2f', $box['h'] * 72) . '" align="left" valign="top" style="'
            . 'height:' . $h($box['h']) . ';'
            . 'padding-left:' . $h($padL) . ';'
            . 'padding-right:' . $h($padR) . ';'
            . 'padding-top:' . $h($inset) . ';'
            . 'padding-bottom:0;'
            . 'text-align:left;vertical-align:top;'
            . 'font-family:' . self::WINDOW_FONT . ';font-size:' . sprintf('%.2fpt', $pt) . ';'
            . 'line-height:' . $lh . ';">'
            . $inner
            . '</td>';
    }

    /**
     * HTML-escape one address line for mPDF.
     */
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
                if (self::textWidthIn($word, $pt) > $maxWidthIn) {
                    if ($cur !== '') {
                        $out[] = $cur;
                        $cur = '';
                    }
                    foreach (self::splitToken($word, $maxWidthIn, $pt) as $piece) {
                        $out[] = $piece;
                    }
                    continue;
                }
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

    /**
     * @return list<string>
     */
    private static function splitToken(string $word, float $maxWidthIn, float $pt): array
    {
        $out = [];
        $cur = '';
        foreach (mb_str_split($word, 1, 'UTF-8') as $ch) {
            $try = $cur . $ch;
            if ($cur !== '' && self::textWidthIn($try, $pt) > $maxWidthIn) {
                $out[] = $cur;
                $cur = $ch;
            } else {
                $cur = $try;
            }
        }
        if ($cur !== '') {
            $out[] = $cur;
        }
        return $out !== [] ? $out : [$word];
    }

    /**
     * Wrap and clip monospace lines at 10 cpi / 6 lpi. Prefer the last line.
     *
     * @param list<string> $lines
     * @return list<string>
     */
    private static function fitTextWindow(array $lines, float $widthIn, float $heightIn): array
    {
        $maxChars = (int) floor(($widthIn * 10.0) + 1e-9);
        $maxLines = (int) floor(($heightIn * 6.0) + 1e-9);
        if ($maxChars < 1 || $maxLines < 1) {
            return [];
        }
        $wrapped = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            foreach (self::wrapMonospace($line, $maxChars) as $piece) {
                $wrapped[] = $piece;
            }
        }
        return self::keepLastLines($wrapped, $maxLines);
    }

    /**
     * @return list<string>
     */
    private static function wrapMonospace(string $line, int $maxChars): array
    {
        $words = preg_split('/\s+/u', $line) ?: [$line];
        $out = [];
        $cur = '';
        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }
            if (mb_strlen($word, 'UTF-8') > $maxChars) {
                if ($cur !== '') {
                    $out[] = $cur;
                    $cur = '';
                }
                $chars = mb_str_split($word, 1, 'UTF-8');
                $piece = '';
                foreach ($chars as $ch) {
                    if ($piece !== '' && mb_strlen($piece, 'UTF-8') >= $maxChars) {
                        $out[] = $piece;
                        $piece = $ch;
                    } else {
                        $piece .= $ch;
                    }
                }
                $out[] = $piece;
                continue;
            }
            $try = ($cur === '') ? $word : ($cur . ' ' . $word);
            if (mb_strlen($try, 'UTF-8') <= $maxChars) {
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
        return $out;
    }

    /**
     * @param list<string> $lines
     * @return list<string>
     */
    private static function keepLastLines(array $lines, int $maxLines): array
    {
        if ($maxLines < 1) {
            return [];
        }
        if (count($lines) <= $maxLines) {
            return $lines;
        }
        $last = $lines[array_key_last($lines)];
        if ($maxLines === 1) {
            return [$last];
        }
        $head = array_slice($lines, 0, $maxLines - 1);
        $head[] = $last;
        return $head;
    }

    /**
     * Pad $out with newlines so the next content starts at 0-based $line.
     */
    private static function padTextToLine(string $out, int $line): string
    {
        return $out . str_repeat("\n", max(0, $line - substr_count($out, "\n")));
    }

    /**
     * Keep whole wrapped lines that fit $maxH at $pt. Prefer the last line.
     *
     * @param list<string> $lines
     * @return list<string>
     */
    private static function clipLines(
        array $lines,
        float $maxH,
        float $pt,
        float $lineHeight
    ): array {
        if ($lines === []) {
            return $lines;
        }
        $lineIn = ($pt / 72.0) * $lineHeight;
        if ($lineIn <= 0.0) {
            return $lines;
        }
        $maxLines = (int) floor(($maxH / $lineIn) + 1e-9);
        return self::keepLastLines($lines, $maxLines);
    }

    /**
     * String width in inches at $pt, from the font the window actually embeds.
     */
    private static function textWidthIn(string $s, float $pt): float
    {
        if ($s === '') {
            return 0.0;
        }
        $pdf = self::measurePdf(self::sheetFormat());
        $pdf->SetFont(self::WINDOW_FONT, '', $pt);
        // mPDF reports the current user unit, which is millimetres.
        $widthMm = $pdf->GetStringWidth($s);
        if (!is_int($widthMm) && !is_float($widthMm)) {
            return 0.0;
        }
        return $widthMm / 25.4;
    }

    /**
     * Paper name safe to put in CSS. Falls back to letter when unset.
     */
    public static function sheetFormat(): string
    {
        $name = 'LETTER';
        try {
            $raw = OEGlobalsBag::getInstance()->get('pdf_size');
            if (is_string($raw) && preg_match('/^[A-Za-z0-9]+$/', $raw) === 1) {
                $name = $raw;
            }
        } catch (\RuntimeException) {
            $name = 'LETTER';
        }
        return $name;
    }

    /**
     * Width and height of the PDF paper, in inches.
     *
     * @return array{0: float, 1: float}
     */
    private static function pageSizeInches(): array
    {
        $pdf = self::measurePdf(self::sheetFormat());
        $widthMm = $pdf->w;
        $heightMm = $pdf->h;
        if (
            (!is_int($widthMm) && !is_float($widthMm))
            || (!is_int($heightMm) && !is_float($heightMm))
            || $widthMm <= 0
            || $heightMm <= 0
        ) {
            return [8.5, 11.0];
        }
        return [$widthMm / 25.4, $heightMm / 25.4];
    }

    /**
     * One mPDF instance per paper size, used only to measure type.
     */
    private static function measurePdf(string $format): \Mpdf\Mpdf
    {
        /** @var array<string, \Mpdf\Mpdf> $cache */
        static $cache = [];
        $cache[$format] ??= new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => $format,
            'default_font' => self::WINDOW_FONT,
            'tempDir' => sys_get_temp_dir(),
        ]);
        return $cache[$format];
    }
}
