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
     * What was posted, from ar_activity joined to ar_session. The caller passes active
     * rows only (deleted IS NULL); a date is the posting date, or the post time's day.
     *
     * @param list<array<string, mixed>> $rows
     * @return list<EncounterEvent>
     */
    public static function posted(array $rows): array
    {
        $events = [];
        foreach ($rows as $row) {
            $date = self::date((string) ($row['post_date'] ?? ''));
            if ($date === '') {
                $date = self::date(substr((string) ($row['post_time'] ?? ''), 0, 10));
            }
            $level = (string) ($row['payer_type'] ?? '');
            $title = 'Posted';
            if ($level === '0') {
                $title = 'Posted to patient';
            } elseif ($level !== '') {
                $title = 'Posted to insurance level ' . $level;
            }
            // ar_activity.code is empty for a claim-level entry.
            $code = (string) ($row['code'] ?? '');
            $detail = ($code === '' ? 'Claim level' : 'Code ' . $code)
                . ', paid ' . (string) ($row['pay_amount'] ?? '')
                . ', adjustment ' . (string) ($row['adj_amount'] ?? '');
            $reason = (string) ($row['reason_code'] ?? '');
            if ($reason !== '') {
                $detail .= ', reason ' . $reason;
            }
            $memo = (string) ($row['memo'] ?? '');
            if ($memo !== '') {
                $detail .= ', memo ' . $memo;
            }
            $check = (string) ($row['reference'] ?? '');
            if ($check !== '') {
                $detail .= ', check ' . $check;
            }
            $payerClaim = (string) ($row['payer_claim_number'] ?? '');
            if ($payerClaim !== '') {
                $detail .= ', payer claim number ' . $payerClaim;
            }
            $events[] = new EncounterEvent(
                $date,
                EncounterHistory::KIND_POSTED,
                $title,
                $detail,
                '',
                $payerClaim
            );
        }
        return $events;
    }

    /**
     * The adjustments and remark codes a remittance states for one claim, as ParseERA
     * reads them: each service line's adjustments in file order, its remarks once.
     *
     * @param array<string, mixed> $eraClaim one claim payment from ParseERA
     */
    public static function adjustmentsAndRemarks(array $eraClaim): string
    {
        $adjustments = [];
        $remarks = [];
        foreach ((array) ($eraClaim['svc'] ?? []) as $service) {
            foreach ((array) ($service['adj'] ?? []) as $adjustment) {
                $group = (string) ($adjustment['group_code'] ?? '');
                $reason = (string) ($adjustment['reason_code'] ?? '');
                $amount = (string) ($adjustment['amount'] ?? '');
                $label = trim($group . '-' . $reason, '-');
                $text = trim($label . ' ' . $amount);
                if ($text !== '') {
                    $adjustments[] = $text;
                }
            }
            $remark = (string) ($service['remark'] ?? '');
            if ($remark !== '' && !in_array($remark, $remarks, true)) {
                $remarks[] = $remark;
            }
        }
        $parts = [];
        if ($adjustments !== []) {
            $parts[] = 'adjustments ' . implode(', ', $adjustments);
        }
        if ($remarks !== []) {
            $parts[] = 'remarks ' . implode(', ', $remarks);
        }
        return implode('; ', $parts);
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