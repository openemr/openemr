<?php

/**
 * Segment renderers for the EDI 277 / 277CA claim-status HTML display.
 *
 * Each method turns one parsed X12 segment (already split on the element
 * delimiter) into the HTML rows it contributes, so the procedural
 * edih_277_transaction_html() loop stays thin state-machine glue. The
 * renderers hold no state of their own: every value a segment needs is
 * passed in, and everything it produces is returned, which keeps each
 * branch independently testable.
 *
 * Lifted from the case bodies of library/edihistory/edih_277_html.php.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin McCormick
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2016 Kevin McCormick
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

use edih_271_codes;

final class Claim277Renderer
{
    private function __construct()
    {
    }

    /**
     * Narrow a legacy 271-code lookup to a string.
     *
     * edih_271_codes::get_271_code() is untyped and so returns mixed; a code
     * lookup is always a string in practice, so anything else collapses to ''.
     */
    private static function code(edih_271_codes $cd, string $set, string $value): string
    {
        $result = $cd->get_271_code($set, $value);
        return is_string($result) ? $result : '';
    }

    /**
     * CSS row class for the loop the current segment belongs to.
     *
     * The class is fixed for the duration of an HL hierarchy loop and is a
     * pure function of the loop id's family letter (2000A / 2100A / 2200A ->
     * src, and so on), so it never needs to be carried as separate state.
     *
     * @param string $loopid current loop id, e.g. '2000A', '2200D', 'Heading'
     * @return string one of src|rcv|prv|sbr|dep, or '' outside an HL loop
     */
    public static function rowClass(string $loopid): string
    {
        return match (substr($loopid, -1)) {
            'A' => 'src',
            'B' => 'rcv',
            'C' => 'prv',
            'D' => 'sbr',
            'E' => 'dep',
            default => '',
        };
    }

    /**
     * Render the heading rows for a BHT (transaction header) segment.
     *
     * @param list<string> $sar segment split on the element delimiter
     * @return array{html: string, ref: string} rows plus the BHT03 reference
     */
    public static function bht(array $sar, edih_271_codes $cd): array
    {
        $bht01 = $sar[1] ?? null;
        $elem01 = match ($bht01) {
            '0010' => 'Src, Rcv, Prv, Sbr, Dep',
            '0085' => 'Src, Rcv, Prv, Pt',
            null => '',
            default => "Not determined ({$bht01})",
        };
        $elem02 = ($sar[2] ?? false) !== false ? self::code($cd,'BHT02', $sar[2]) : "";
        $elem03 = ($sar[3] ?? '') ?: "";
        $elem04 = ($sar[4] ?? '') ? EdiFormat::date($sar[4]) : "";
        $elem06 = ($sar[6] ?? '') ? self::code($cd,'BHT06', $sar[6]) : "";

        $e01Text = text($elem01);
        $e02Text = text($elem02);
        $e03Text = text($elem03);
        $e04Text = text($elem04);
        $e06Text = text($elem06);
        $e06Html = $elem06 ? "<tr><td>&gt;</td><td colspan=3><em>Type:</em> {$e06Text}</td></tr>" : '';
        $html = <<<HTML
            <tr><td colspan=2><em>Reference:</em> {$e03Text}</td><td colspan=2><em>Sequence:</em> {$e01Text}</td></tr>
            <tr><td colspan=2><em>Date:</em> {$e04Text}</td><td colspan=2><em>Type:</em> {$e02Text}</td>
            {$e06Html}
            HTML;

        return ['html' => $html, 'ref' => $elem03];
    }

    /**
     * Render the two identity rows for an NM1 segment.
     *
     * @param list<string> $sar
     * @return array{html: string, name: string} rows plus the assembled entity name
     */
    public static function nm1(array $sar, string $cls, edih_271_codes $cd): array
    {
        $nm101 = $sar[1] ?? '';
        $descr = ($nm101) ? self::code($cd,'NM101', $nm101) : "";

        $name = ($sar[3] ?? '') ?: "";
        $name .= ($sar[7] ?? '') ? " {$sar[7]}" : "";
        $name .= ($sar[4] ?? '') ? ", {$sar[4]}" : "";
        $name .= ($sar[5] ?? '') ? " {$sar[5]}" : "";
        $nm109 = ($sar[9] ?? '') ?: "";
        $nm108 = ($sar[8] ?? '') ? self::code($cd,'NM108', $sar[8]) : "";

        $descrAttr = attr($descr);
        $nameText = text($name);
        $nm108Text = text($nm108);
        $nm109Text = text($nm109);
        $html  = "<tr class='{$cls}'><td>&gt;</td><td colspan=3 title='{$descrAttr}'>{$nameText}</td></tr>";
        $html .= "<tr class='{$cls}'><td>&gt;</td><td colspan=3 title='{$descrAttr}'><em>{$nm108Text}</em> {$nm109Text}</td></tr>";

        return ['html' => $html, 'name' => $name];
    }

    /**
     * Render the contact row for a PER segment (2100A information-source contact).
     *
     * @param list<string> $sar
     */
    public static function per(array $sar, string $cls, edih_271_codes $cd): string
    {
        $elem02 = $sar[2] ?? '';
        $elem03 = (isset($sar[3])) ? self::code($cd,'PER03', $sar[3]) : "";
        $elem04 = $sar[4] ?? '';
        $elem05 = (isset($sar[5])) ? self::code($cd,'PER03', $sar[5]) : "";
        $elem06 = $sar[6] ?? '';
        $elem07 = (isset($sar[7])) ? self::code($cd,'PER03', $sar[7]) : "";
        $elem08 = $sar[8] ?? '';

        $elem02Text = text($elem02);
        $titleAttr = attr($elem03 . ' ' . $elem05 . ' ' . $elem07);
        $contactText = text($elem04 . ' ' . $elem06 . ' ' . $elem08);

        return "<tr class='{$cls}'><td colspan=2>{$elem02Text}</td><td colspan=2 title='{$titleAttr}'>{$contactText}</td></tr>";
    }

    /**
     * Render the trace row for a TRN segment.
     *
     * @param list<string> $sar
     * @return array{html: string, ref: string} row plus TRN02 (appended to the label)
     */
    public static function trn(array $sar, string $cls): array
    {
        $elem01 = ($sar[1] ?? '') == "1" ? "Transaction Ref" : "Trace";
        $elem02 = $sar[2] ?? '';
        $elem01Text = text($elem01);
        $elem02Text = text($elem02);
        $html = "<tr class='{$cls}'><td>&gt;</td><td colspan=3><em>{$elem01Text}</em> {$elem02Text}</td></tr>";

        return ['html' => $html, 'ref' => $elem02];
    }

    /**
     * Render the claim-status rows for an STC segment.
     *
     * All STC-local scratch (the composite sc1xx/sc2xx/sc3xx codes) stays
     * local to this call, so codes never leak into a later STC that lacks
     * that composite.
     *
     * @param list<string> $sar
     * @param non-empty-string $ds sub-element (composite) delimiter
     */
    public static function stc(array $sar, string $ds, string $cls, edih_271_codes $cd): string
    {
        // STC01 composite: claim status category : claim status : entity identifier
        $sc101 = $sc102 = $sc103 = null;
        if (isset($sar[1]) && strpos($sar[1], $ds)) {
            $scda = explode($ds, $sar[1]);
            $sc101 = $scda[0] ? self::code($cd,'HCCSCC', $scda[0]) : "";
            $sc102 = ($scda[1] ?? '') ? self::code($cd,'HCCSC', $scda[1]) : "";
            $sc103 = ($scda[2] ?? '') ? self::code($cd,'NM101', $scda[2]) : "";
        }

        $stc02 = ($sar[2] ?? '') ? EdiFormat::date($sar[2]) : "";  // status information date
        $stc03 = match ($sar[3] ?? null) {                          // action code
            'WQ' => 'Accepted',
            'F' => 'Final',
            '15' => 'Correct/Resubmit',
            'U' => 'Rejected',
            null => '',
            default => $sar[3],
        };
        $stc04 = ($sar[4] ?? '') ? EdiFormat::money($sar[4]) : "";  // billed amount
        $stc05 = ($sar[5] ?? '') ? EdiFormat::money($sar[5]) : "";  // paid amount
        $stc06 = ($sar[6] ?? '') ? EdiFormat::date($sar[6]) : "";   // payment date
        // $stc07 not used
        $stc08 = ($sar[8] ?? '') ? EdiFormat::date($sar[8]) : "";   // check issue date
        $stc09 = ($sar[9] ?? '') ?: "";                              // check or eft number

        // STC10 composite
        $sc201 = $sc202 = $sc203 = null;
        $sc204 = '';
        if (($sar[10] ?? false) && strpos($sar[10], $ds)) {
            $scda = explode($ds, $sar[10]);
            $sc201 = $scda[0] ? self::code($cd,'HCCSCC', $scda[0]) : "";
            $sc202 = ($scda[1] ?? '') ? self::code($cd,'HCCSC', $scda[1]) : "";
            $sc203 = ($scda[2] ?? '') ? self::code($cd,'NM101', $scda[2]) : "";
            $sc204 = ($scda[3] ?? '') === 'RA' ? "Rx Reject/Payment Codes" : "";
        }

        // STC11 composite
        $sc301 = $sc302 = $sc303 = null;
        $sc304 = '';
        if (($sar[11] ?? false) && strpos($sar[11], $ds)) {
            $scda = explode($ds, $sar[11]);
            $sc301 = $scda[0] ? self::code($cd,'HCCSCC', $scda[0]) : "";
            $sc302 = ($scda[1] ?? '') ? self::code($cd,'HCCSC', $scda[1]) : "";
            $sc303 = ($scda[2] ?? '') ? self::code($cd,'NM101', $scda[2]) : "";
            $sc304 = ($scda[3] ?? '') === 'RA' ? "Rx Reject/Payment Codes" : "";
        }

        $stc12 = ($sar[12] ?? '') ?: "";    // message

        // repeated STC row templates: a plain detail row, an "Entity" row, and a labeled row
        $row = fn(string $inner): string => "<tr class='$cls'><td>&gt;</td><td colspan=3>$inner</td></tr>";
        $entity = fn(string $inner): string => "<tr class='$cls'><td>&gt;</td><td colspan=3><em>Entity</em> $inner</td></tr>";
        $labeled = fn(string $label, string $inner): string => "<tr class='$cls'><td><em>$label</em></td><td colspan=3>$inner</td></tr>";

        $stc03Text = text($stc03);
        $sc101Text = text($sc101 ?? '');
        $stcHeadText = text($stc02 . ' ' . $stc04);
        $html  = ($sc101 !== null) ? "<tr class='{$cls}'><td>{$stc03Text}</td><td colspan=2>{$sc101Text}</td><td>{$stcHeadText}</td></tr>" : "";
        $html .= ($sc102 !== null) ? $row(text($sc102)) : "";
        $html .= ($sc103) ? $entity(text($sc103)) : "";
        $html .= ($stc05 || $stc06 || $stc08 || $stc09) ? $labeled('Payment', text($stc05 . " " . $stc06 . " " . $stc08 . " " . $stc09)) : "";
        $html .= ($sc201 !== null) ? $row(text($sc201 . " " . $sc204)) : "";
        $html .= ($sc202 !== null) ? $row(text($sc202)) : "";
        $html .= ($sc203) ? $entity(text($sc203)) : "";
        $html .= ($sc301 !== null) ? $row(text($sc301 . " " . $sc304)) : "";
        $html .= ($sc302 !== null) ? $row(text($sc302)) : "";
        $html .= ($sc303) ? $entity(text($sc303)) : "";
        $html .= ($stc12) ? $labeled('Message', text($stc12)) : "";

        return $html;
    }

    /**
     * Build the acknowledged/approved quantity prefix from a QTY segment.
     *
     * @param list<string> $sar
     */
    public static function qtyString(array $sar): string
    {
        $qtystr = match ($sar[1] ?? null) {
            '90' => 'Acknowledged Quantity ',
            'AA' => 'Unacknowledged Quantity ',
            'QA' => 'Quantity Approved ',
            'QC' => 'Quantity Disapproved ',
            null => '',
            default => 'Quantity ',
        };

        return $qtystr . (($sar[2] ?? '') ?: "");
    }

    /**
     * Render the amount row for an AMT segment (277CA), prefixed with the
     * carried QTY string.
     *
     * @param list<string> $sar
     * @param string $qtystr quantity prefix carried from the preceding QTY segment
     */
    public static function amt(array $sar, string $cls, string $qtystr): string
    {
        $amtstr = ($sar[1] ?? '') == 'YU' ? "Amt " : "Amt Rej ";
        $amtstr .= ($sar[2] ?? '') ? EdiFormat::money($sar[2]) : "";
        $amtText = text($qtystr . ' ' . $amtstr);

        return "<tr class='{$cls}'><td>&gt;</td><td colspan=3>{$amtText}</td></tr>";
    }

    /**
     * Render the reference row for a REF segment.
     *
     * @param list<string> $sar
     */
    public static function ref(array $sar, string $cls, edih_271_codes $cd): string
    {
        $elem01 = (isset($sar[1])) ? self::code($cd,'REF', $sar[1]) : '';
        $elem02 = $sar[2] ?? '';
        $elem03 = $sar[3] ?? '';
        $elem01Text = text($elem01);
        $elem02Text = text($elem02);
        $elem03Text = text($elem03);

        return "<tr class='{$cls}'><td>&gt;</td><td colspan=2><em>{$elem01Text}</em> {$elem02Text}</td><td>{$elem03Text}</td></tr>";
    }

    /**
     * Render the date row for a DTP segment.
     *
     * @param list<string> $sar
     */
    public static function dtp(array $sar, string $cls, edih_271_codes $cd): string
    {
        $elem01 = ($sar[1] ?? '') ? self::code($cd,'DTP', $sar[1]) : "";
        $elem02 = $sar[2] ?? '';
        $elem03 = $sar[3] ?? '';
        $var = match (true) {
            $elem02 == 'D8' && $elem03 => EdiFormat::date($elem03),
            $elem02 == 'RD8' && $elem03 => EdiFormat::date(substr($elem03, 0, 8)) . ' - ' . EdiFormat::date(substr($elem03, -8)),
            default => '',
        };
        $elem01Text = text($elem01);
        $varText = text($var);

        return "<tr class='{$cls}'><td>&gt;</td><td>{$elem01Text}</td><td colspan=2>{$varText}</td></tr>";
    }

    /**
     * Render the service rows for an SVC segment.
     *
     * @param list<string> $sar
     * @param non-empty-string $ds sub-element (composite) delimiter
     * @param bool $isRcv true in the 2200B receiver loop, which uses a 4-column row
     */
    public static function svc(array $sar, string $ds, string $cls, bool $isRcv, edih_271_codes $cd): string
    {
        $elem01 = '';                           // composite procedure code source:code:modifier:modifier
        if ($sar[1] ?? false) {
            // construct a code source code modifier string
            if (strpos($sar[1], $ds)) {
                $scda = explode($ds, $sar[1]);
                reset($scda);
                foreach ($scda as $key => $val) {
                    if ($key == 0 && $val) {
                        $elem01 = self::code($cd,'EB13', $val);
                    } else {
                        $elem01 .= " " . $val;
                    }
                }
            } else {
                $elem01 = $sar[1];
            }
        }

        $elem02 = ($sar[2] ?? '') ? EdiFormat::money($sar[2]) : "";  // billed amount
        $elem03 = ($sar[3] ?? '') ? EdiFormat::money($sar[3]) : "";  // paid amount
        $elem04 = ($sar[4] ?? '') ?: "";                              // revenue code
        // $elem05, $elem06 and $elem07 are not used

        $elem01Text = text($elem01);
        $elem02Text = text($elem02);
        $elem04Text = text($elem04);
        $svcText = text($elem02 . ' ' . $elem04);
        $html = $isRcv
            ? "<tr class='{$cls}'><td><em>Service</em></td><td>{$elem01Text}</td><td>{$elem02Text}</td><td>{$elem04Text}</td></tr>"
            : "<tr class='{$cls}'><td><em>Service</em></td><td>{$elem01Text}</td><td colspan=2>{$svcText}</td></tr>";
        $paidText = text($elem03 . ' ' . $elem04);
        $html .= ($elem03 || $elem04) ? "<tr class='{$cls}'><td>&gt;</td><td colspan=3>{$paidText}</td></tr>" : "";

        return $html;
    }
}
