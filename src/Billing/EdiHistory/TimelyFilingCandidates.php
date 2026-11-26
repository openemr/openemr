<?php

/**
 * Encounters a payer denied for timely filing, on a posting or a remittance.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

use OpenEMR\Billing\ParseERA;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use RuntimeException;

/**
 * Adjustment reason 29 is timely filing (#15530 step 4). A posting says it in
 * ar_activity.reason_code; a remittance says it in a CAS the CSV columns do not carry,
 * so the file is read. Only claims whose reference is pid-encounter can be listed.
 */
final class TimelyFilingCandidates
{
    public const TIMELY_FILING = '29';

    /**
     * @param array<string, mixed> $eraClaim one claim payment from ParseERA
     */
    public static function hasTimelyFiling(array $eraClaim): bool
    {
        foreach ((array) ($eraClaim['svc'] ?? []) as $service) {
            if (!is_array($service)) {
                continue;
            }
            foreach ((array) ($service['adj'] ?? []) as $adjustment) {
                if (!is_array($adjustment)) {
                    continue;
                }
                if (trim((string) ($adjustment['reason_code'] ?? '')) === self::TIMELY_FILING) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return list<array{pid: int, encounter: int, payer: string, date: string, source: string}>
     */
    public static function fromPostings(string $fromDate = '', string $toDate = '', string $payer = ''): array
    {
        $where = 'a.deleted IS NULL AND a.reason_code = ?';
        $binds = [self::TIMELY_FILING];
        if ($fromDate !== '') {
            $where .= ' AND DATE(COALESCE(a.post_date, a.post_time)) >= ?';
            $binds[] = $fromDate;
        }
        if ($toDate !== '') {
            $where .= ' AND DATE(COALESCE(a.post_date, a.post_time)) <= ?';
            $binds[] = $toDate;
        }
        if ($payer !== '') {
            $where .= ' AND ic.name LIKE ?';
            $binds[] = '%' . $payer . '%';
        }
        try {
            $rows = QueryUtils::fetchRecords(
                'SELECT a.pid, a.encounter, a.post_date, a.post_time, COALESCE(ic.name, \'\') AS payer'
                . ' FROM `ar_activity` a'
                . ' LEFT JOIN `ar_session` s ON s.session_id = a.session_id'
                . ' LEFT JOIN `insurance_companies` ic ON ic.id = s.payer_id'
                . ' WHERE ' . $where . ' ORDER BY a.encounter, a.sequence_no',
                $binds
            );
        } catch (SqlQueryException) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            $date = self::date((string) ($row['post_date'] ?? ''));
            if ($date === '') {
                $date = self::date(substr((string) ($row['post_time'] ?? ''), 0, 10));
            }
            $out[] = [
                'pid' => (int) ($row['pid'] ?? 0),
                'encounter' => (int) ($row['encounter'] ?? 0),
                'payer' => (string) ($row['payer'] ?? ''),
                'date' => $date,
                'source' => 'posting',
            ];
        }
        return $out;
    }

    /**
     * @param list<array<string, mixed>> $csvRows  claims_f835.csv rows
     * @param list<array<string, mixed>> $fileRows files_f835.csv rows, for the remittance date
     * @param ?callable                  $parser   fn(string $path, callable $onClaim): void
     * @return list<array{pid: int, encounter: int, payer: string, date: string, source: string}>
     */
    public static function fromRemittances(array $csvRows, array $fileRows, string $directory, ?callable $parser = null): array
    {
        $parser ??= static function (string $path, callable $onClaim): void {
            ob_start();
            try {
                ParseERA::parseERA($path, static function (array $claim) use ($onClaim): void {
                    $onClaim($claim);
                });
            } finally {
                ob_end_clean();
            }
        };

        $dates = [];
        foreach ($fileRows as $row) {
            $name = (string) ($row['FileName'] ?? '');
            if ($name !== '') {
                $dates[$name] = self::date((string) ($row['Date'] ?? ''));
            }
        }

        $byReference = [];
        $files = [];
        foreach ($csvRows as $row) {
            $reference = trim((string) ($row['CLM01'] ?? ''));
            $name = (string) ($row['FileName'] ?? '');
            if ($reference === '' || $name === '') {
                continue;
            }
            $byReference[$reference] = $row;
            $files[$name] = true;
        }

        $found = [];
        foreach (array_keys($files) as $name) {
            $path = $directory . DIRECTORY_SEPARATOR . $name;
            if (!is_file($path)) {
                continue;
            }
            $onClaim = static function (array $claim) use (&$found, $byReference, $name, $dates): void {
                if (!self::hasTimelyFiling($claim)) {
                    return;
                }
                $reference = trim((string) ($claim['our_claim_id'] ?? ''));
                if ($reference === '' || !isset($byReference[$reference])) {
                    return;
                }
                if (preg_match('/^(\d+)-(\d+)$/', $reference, $m) !== 1) {
                    return;
                }
                $key = $m[1] . '-' . $m[2];
                if (isset($found[$key])) {
                    return;
                }
                $found[$key] = [
                    'pid' => (int) $m[1],
                    'encounter' => (int) $m[2],
                    'payer' => (string) ($byReference[$reference]['Payer'] ?? ''),
                    'date' => $dates[$name] ?? '',
                    'source' => 'remittance',
                ];
            };
            try {
                $parser($path, $onClaim);
            } catch (RuntimeException) {
                continue;
            }
        }
        return array_values($found);
    }

    private static function date(string $value): string
    {
        $value = trim($value);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) === 1) {
            return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $value : '';
        }
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $value, $m) === 1) {
            return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $m[1] . '-' . $m[2] . '-' . $m[3] : '';
        }
        return '';
    }
}