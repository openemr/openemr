<?php

/**
 * Turns what EDI History holds into the dated facts of an encounter's history.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

/**
 * Each method states only what its rows say (#15530 step 3). A date that is not a real
 * date is left empty, an amount stays as written, and a remittance carries a dedupe key
 * of its file content and claim so the same remittance stored twice is listed once.
 */
final class EncounterHistorySources
{
    /**
     * @param list<array<string, mixed>> $claimRows claims_f837.csv rows
     * @param list<array<string, mixed>> $fileRows  files_f837.csv rows, for the batch date
     * @return list<EncounterEvent>
     */
    public static function billed(array $claimRows, array $fileRows): array
    {
        $dates = self::fileDates($fileRows);
        $events = [];
        foreach ($claimRows as $row) {
            $file = (string) ($row['FileName'] ?? '');
            $reference = (string) ($row['CLM01'] ?? '');
            $events[] = new EncounterEvent(
                self::date($dates[$file] ?? ''),
                EncounterHistory::KIND_BILLED,
                'Claim billed',
                'Claim ' . $reference . ', charge ' . (string) ($row['Fee'] ?? ''),
                $file,
                $reference
            );
        }
        return $events;
    }

    /**
     * @param list<array<string, mixed>> $claimRows claims_f997.csv rows
     * @return list<EncounterEvent>
     */
    public static function acknowledged(array $claimRows): array
    {
        $events = [];
        foreach ($claimRows as $row) {
            $trace = (string) ($row['Trace'] ?? '');
            $events[] = new EncounterEvent(
                self::date((string) ($row['RspDate'] ?? '')),
                EncounterHistory::KIND_ACK,
                'Acknowledgment ' . (string) ($row['RspType'] ?? ''),
                'Status ' . (string) ($row['Status'] ?? '') . ', trace ' . $trace,
                (string) ($row['FileName'] ?? ''),
                $trace
            );
        }
        return $events;
    }

    /**
     * @param list<array<string, mixed>> $ackRows indexed acknowledgments, each with its
     *                                            source, result and payer claim numbers
     * @return list<EncounterEvent>
     */
    public static function statuses(array $ackRows): array
    {
        $events = [];
        foreach ($ackRows as $row) {
            $reference = (string) ($row['claim_reference'] ?? '');
            $name = (string) ($row['source_name'] ?? '');
            $kind = (string) ($row['source_kind'] ?? '');
            $detail = 'Claim ' . $reference;
            $result = (string) ($row['result'] ?? '');
            if ($result !== '') {
                $detail .= ', ' . $result;
            }
            $numbers = (string) ($row['payer_numbers'] ?? '');
            if ($numbers !== '') {
                $detail .= ', payer claim number ' . $numbers;
            }
            $events[] = new EncounterEvent(
                self::date((string) ($row['source_received_date'] ?? '')),
                EncounterHistory::KIND_STATUS,
                'Claim status from ' . ($name !== '' ? $name : 'the source')
                    . ($kind !== '' ? ' (' . $kind . ')' : ''),
                $detail,
                (string) ($row['file_name'] ?? ''),
                $reference
            );
        }
        return $events;
    }

    /**
     * @param list<array<string, mixed>> $claimRows claims_f835.csv rows
     * @param list<array<string, mixed>> $fileRows  files_f835.csv rows, each optionally
     *                                            carrying Sha256 of the stored file
     * @return list<EncounterEvent>
     */
    public static function remittances(array $claimRows, array $fileRows): array
    {
        $dates = self::fileDates($fileRows);
        $sha = [];
        foreach ($fileRows as $row) {
            $name = (string) ($row['FileName'] ?? '');
            if ($name !== '') {
                $sha[$name] = (string) ($row['Sha256'] ?? '');
            }
        }
        $events = [];
        foreach ($claimRows as $row) {
            $file = (string) ($row['FileName'] ?? '');
            $reference = (string) ($row['CLM01'] ?? '');
            $detail = 'Claim ' . $reference
                . ', status ' . (string) ($row['Status'] ?? '')
                . ', paid ' . (string) ($row['Pmt'] ?? '')
                . ', patient responsibility ' . (string) ($row['PtResp'] ?? '');
            $payerClaim = (string) ($row['ClaimID'] ?? '');
            if ($payerClaim !== '') {
                $detail .= ', payer claim number ' . $payerClaim;
            }
            $payer = (string) ($row['Payer'] ?? '');
            if ($payer !== '') {
                $detail .= ', payer ' . $payer;
            }
            $content = $sha[$file] ?? '';
            $events[] = new EncounterEvent(
                self::date($dates[$file] ?? ''),
                EncounterHistory::KIND_REMITTANCE,
                'Remittance',
                $detail,
                $file,
                $reference,
                $content !== '' ? $content . '|' . $reference : ''
            );
        }
        return $events;
    }

    /**
     * @param list<array<string, mixed>> $fileRows
     * @return array<string, string>
     */
    private static function fileDates(array $fileRows): array
    {
        $dates = [];
        foreach ($fileRows as $row) {
            $name = (string) ($row['FileName'] ?? '');
            if ($name !== '') {
                $dates[$name] = (string) ($row['Date'] ?? '');
            }
        }
        return $dates;
    }

    /**
     * Ymd as the CSV tables hold it, or Y-m-d as the index holds it; '' when not a date.
     */
    private static function date(string $value): string
    {
        $value = trim($value);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) === 1) {
            return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $value : '';
        }
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $value, $m) === 1) {
            return checkdate((int) $m[2], (int) $m[3], (int) $m[1])
                ? $m[1] . '-' . $m[2] . '-' . $m[3] : '';
        }
        return '';
    }
}