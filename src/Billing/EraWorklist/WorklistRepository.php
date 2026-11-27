<?php

/**
 * Reads the ERA worklist from what receiving and unattended posting recorded, and stores
 * its "worked" marks.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

use OpenEMR\Billing\EraAutoPost\AutoPostFileStatus;
use OpenEMR\Billing\EraAutoPost\CheckStatus;
use OpenEMR\Billing\EraAutoPost\ClaimOutcome;
use OpenEMR\Billing\EraAutoPost\HoldReason;
use OpenEMR\Billing\X12Receive\ReceivedStatus;
use OpenEMR\Billing\X12Receive\X12FileType;
use OpenEMR\Common\Database\QueryUtils;

final class WorklistRepository implements Worklist
{
    /**
     * A stored 835 a person still has to look at: unattended posting did not finish it
     * (it was off, has not run yet, could not read the file or was stopped), or finished
     * it and held one of its checks. Binds: self::fileBinds().
     */
    private const FILE_ON_WORKLIST = "r.`file_type` = ? AND r.`status` = ? AND r.`stored_name` != ''"
        . ' AND (r.`auto_post_status` != ? OR EXISTS (SELECT 1 FROM `era_auto_post_check` AS h'
        . ' WHERE h.`received_id` = r.`id` AND h.`status` = ?))';

    public function reviewClaims(WorklistFilter $filter, int $limit): WorklistPage
    {
        [$where, $binds] = self::reviewWhere($filter);
        $from = ' FROM `era_auto_post_claim` AS c JOIN `era_auto_post_check` AS k ON k.`id` = c.`check_id`';
        $rows = QueryUtils::fetchRecords(
            'SELECT c.`id`, c.`our_claim_id`, c.`payer_claim_id`, c.`patient_name`, c.`pid`, c.`encounter`,'
            . ' c.`payer_level`, c.`claim_status_code`, c.`reasons`, c.`claim_payment`, c.`payments_posted`,'
            . ' c.`adjustments_posted`, c.`worked_by`, c.`worked_at`, k.`check_number`, k.`check_date`,'
            . ' k.`payer_name`, k.`processed_at`, fe.`id` AS invoice_id,'
            . ' u.`fname` AS worked_fname, u.`lname` AS worked_lname, u.`username` AS worked_username'
            . $from
            . ' LEFT JOIN `form_encounter` AS fe ON fe.`pid` = c.`pid` AND fe.`encounter` = c.`encounter` AND c.`pid` > 0'
            . ' LEFT JOIN `users` AS u ON u.`id` = c.`worked_by` AND c.`worked_by` > 0'
            . ' WHERE ' . $where
            // An integer the page sets, not request text; the driver cannot bind LIMIT.
            . ' ORDER BY k.`processed_at` DESC, k.`id` DESC, c.`sequence` LIMIT ' . $limit,
            $binds
        );
        $claims = [];
        foreach ($rows as $row) {
            $claims[] = new ReviewClaim(
                id: self::int($row, 'id'),
                checkNumber: self::text($row, 'check_number'),
                checkDate: self::checkDate(self::text($row, 'check_date')),
                payerName: self::text($row, 'payer_name'),
                processedAt: self::time($row, 'processed_at'),
                patientName: self::text($row, 'patient_name'),
                pid: self::int($row, 'pid'),
                encounter: self::int($row, 'encounter'),
                invoiceId: self::int($row, 'invoice_id'),
                ourClaimId: self::text($row, 'our_claim_id'),
                payerClaimId: self::text($row, 'payer_claim_id'),
                payerLevel: self::int($row, 'payer_level'),
                claimStatusCode: self::text($row, 'claim_status_code'),
                reasons: self::reasons($row),
                claimPayment: self::amount($row, 'claim_payment'),
                paymentsPosted: self::amount($row, 'payments_posted'),
                adjustmentsPosted: self::amount($row, 'adjustments_posted'),
                worked: self::worked($row),
            );
        }
        return new WorklistPage($claims, self::count('SELECT COUNT(*) AS n' . $from . ' WHERE ' . $where, $binds));
    }

    public function postedChecks(WorklistFilter $filter, int $limit): WorklistPage
    {
        [$checkWhere, $binds] = self::checkWhere($filter);
        $where = 'k.`status` = ?' . $checkWhere;
        array_unshift($binds, CheckStatus::Posted->value);
        $from = ' FROM `era_auto_post_check` AS k JOIN `x12_remote_received` AS r ON r.`id` = k.`received_id`';
        $rows = QueryUtils::fetchRecords(
            'SELECT k.`id`, k.`check_number`, k.`check_date`, k.`payer_name`, k.`check_amount`, k.`claim_total`,'
            . ' k.`plb_total`, k.`plb_reasons`, k.`session_id`, k.`processed_at`, r.`stored_name`'
            . $from . ' WHERE ' . $where
            // An integer the page sets, not request text; the driver cannot bind LIMIT.
            . ' ORDER BY k.`processed_at` DESC, k.`id` DESC LIMIT ' . $limit,
            $binds
        );
        $claims = self::claimsOf($rows);
        $checks = [];
        foreach ($rows as $row) {
            $id = self::int($row, 'id');
            $checks[] = new PostedCheck(
                id: $id,
                checkNumber: self::text($row, 'check_number'),
                checkDate: self::checkDate(self::text($row, 'check_date')),
                payerName: self::text($row, 'payer_name'),
                checkAmount: self::amount($row, 'check_amount'),
                claimTotal: self::amount($row, 'claim_total'),
                plbTotal: self::amount($row, 'plb_total'),
                plbReasons: self::text($row, 'plb_reasons'),
                sessionId: self::int($row, 'session_id'),
                processedAt: self::time($row, 'processed_at'),
                fileName: self::text($row, 'stored_name'),
                claims: $claims[$id] ?? [],
            );
        }
        return new WorklistPage($checks, self::count('SELECT COUNT(*) AS n' . $from . ' WHERE ' . $where, $binds));
    }

    public function receivedFiles(WorklistFilter $filter, int $limit): WorklistPage
    {
        $where = self::FILE_ON_WORKLIST;
        $binds = self::fileBinds();
        if (!$filter->showWorked) {
            $where .= ' AND r.`worked_by` = 0';
        }
        if ($filter->from !== null) {
            $where .= ' AND r.`received_at` >= ?';
            $binds[] = $filter->from->format('Y-m-d 00:00:00');
        }
        if ($filter->to !== null) {
            $where .= ' AND r.`received_at` < ?';
            $binds[] = $filter->to->modify('+1 day')->format('Y-m-d 00:00:00');
        }
        $rows = QueryUtils::fetchRecords(
            'SELECT r.`id`, r.`remote_name`, r.`stored_name`, r.`received_at`, r.`auto_post_status`, r.`worked_by`,'
            . ' r.`worked_at`, p.`name` AS partner_name,'
            . ' u.`fname` AS worked_fname, u.`lname` AS worked_lname, u.`username` AS worked_username'
            . ' FROM `x12_remote_received` AS r'
            . ' LEFT JOIN `x12_partners` AS p ON p.`id` = r.`x12_partner_id`'
            . ' LEFT JOIN `users` AS u ON u.`id` = r.`worked_by` AND r.`worked_by` > 0'
            . ' WHERE ' . $where
            // An integer the page sets, not request text; the driver cannot bind LIMIT.
            . ' ORDER BY r.`received_at` DESC, r.`id` DESC LIMIT ' . $limit,
            $binds
        );
        $held = self::heldChecksOf($rows);
        $files = [];
        foreach ($rows as $row) {
            $id = self::int($row, 'id');
            $files[] = new ReceivedFile(
                id: $id,
                partnerName: self::text($row, 'partner_name'),
                remoteName: self::text($row, 'remote_name'),
                storedName: self::text($row, 'stored_name'),
                receivedAt: self::time($row, 'received_at'),
                autoPostStatus: AutoPostFileStatus::tryFrom(self::text($row, 'auto_post_status')),
                heldChecks: $held[$id] ?? [],
                worked: self::worked($row),
            );
        }
        return new WorklistPage(
            $files,
            self::count('SELECT COUNT(*) AS n FROM `x12_remote_received` AS r WHERE ' . $where, $binds)
        );
    }

    public function payerNames(): array
    {
        $names = QueryUtils::fetchTableColumn(
            "SELECT DISTINCT `payer_name` FROM `era_auto_post_check` WHERE `payer_name` != '' ORDER BY `payer_name`",
            'payer_name'
        );
        return array_values(array_filter($names, is_string(...)));
    }

    public function openCounts(): OpenCounts
    {
        return new OpenCounts(
            self::count(
                'SELECT COUNT(*) AS n FROM `era_auto_post_claim` AS c JOIN `era_auto_post_check` AS k ON k.`id` = c.`check_id`'
                . ' WHERE c.`outcome` = ? AND k.`status` = ? AND c.`worked_by` = 0',
                [ClaimOutcome::Review->value, CheckStatus::Posted->value]
            ),
            self::count(
                'SELECT COUNT(*) AS n FROM `x12_remote_received` AS r WHERE ' . self::FILE_ON_WORKLIST . ' AND r.`worked_by` = 0',
                self::fileBinds()
            ),
        );
    }

    public function markClaimWorked(int $claimId, WorklistUser $by, \DateTimeImmutable $at): bool
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `era_auto_post_claim` SET `worked_by` = ?, `worked_at` = ?'
            . ' WHERE `id` = ? AND `outcome` = ? AND `worked_by` = 0',
            [$by->id, $at->format('Y-m-d H:i:s'), $claimId, ClaimOutcome::Review->value]
        );
        return self::changedOne();
    }

    public function clearClaimWorked(int $claimId): bool
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `era_auto_post_claim` SET `worked_by` = 0, `worked_at` = NULL WHERE `id` = ? AND `worked_by` != 0',
            [$claimId]
        );
        return self::changedOne();
    }

    public function takeFromUnattended(int $receivedId, AutoPostFileStatus $seen): bool
    {
        $becomes = match ($seen) {
            AutoPostFileStatus::Pending => '',
            AutoPostFileStatus::Running => AutoPostFileStatus::Interrupted->value,
            AutoPostFileStatus::Done, AutoPostFileStatus::Unreadable, AutoPostFileStatus::Interrupted => null,
        };
        if ($becomes === null) {
            return false;
        }
        // The state is checked in the statement that changes it, as the run's own take is:
        // of a run and a person reaching for a waiting file, only one gets it.
        QueryUtils::sqlStatementThrowException(
            'UPDATE `x12_remote_received` SET `auto_post_status` = ?'
            . ' WHERE `id` = ? AND `file_type` = ? AND `status` = ? AND `auto_post_status` = ?',
            [$becomes, $receivedId, X12FileType::Remittance835->value, ReceivedStatus::Stored->value, $seen->value]
        );
        return self::changedOne();
    }

    public function markFileWorked(int $receivedId, WorklistUser $by, \DateTimeImmutable $at): bool
    {
        // Not while a posting run is still to take the file or has it: what the run finds
        // (a check it holds, an error) is for a person to see, and a mark would hide it.
        QueryUtils::sqlStatementThrowException(
            'UPDATE `x12_remote_received` AS r SET r.`worked_by` = ?, r.`worked_at` = ?'
            . ' WHERE r.`id` = ? AND r.`worked_by` = 0 AND r.`auto_post_status` NOT IN (?, ?) AND ' . self::FILE_ON_WORKLIST,
            [
                $by->id,
                $at->format('Y-m-d H:i:s'),
                $receivedId,
                AutoPostFileStatus::Pending->value,
                AutoPostFileStatus::Running->value,
                ...self::fileBinds(),
            ]
        );
        return self::changedOne();
    }

    public function clearFileWorked(int $receivedId): bool
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `x12_remote_received` SET `worked_by` = 0, `worked_at` = NULL WHERE `id` = ? AND `worked_by` != 0',
            [$receivedId]
        );
        return self::changedOne();
    }

    /**
     * @return list<string> the binds of self::FILE_ON_WORKLIST
     */
    private static function fileBinds(): array
    {
        return [
            X12FileType::Remittance835->value,
            ReceivedStatus::Stored->value,
            AutoPostFileStatus::Done->value,
            CheckStatus::Held->value,
        ];
    }

    /**
     * @return array{string, list<string>} the condition on claim c of check k, and its binds
     */
    private static function reviewWhere(WorklistFilter $filter): array
    {
        // A held check's claim payments were never looked up: the check is worked from its file.
        $where = 'c.`outcome` = ? AND k.`status` = ?';
        $binds = [ClaimOutcome::Review->value, CheckStatus::Posted->value];
        if (!$filter->showWorked) {
            $where .= ' AND c.`worked_by` = 0';
        }
        if ($filter->reason !== null) {
            $where .= ' AND FIND_IN_SET(?, c.`reasons`) > 0';
            $binds[] = $filter->reasonValue();
        }
        [$checkWhere, $checkBinds] = self::checkWhere($filter);
        return [$where . $checkWhere, [...$binds, ...$checkBinds]];
    }

    /**
     * @return array{string, list<string>} conditions on check k, each led by AND, and their binds
     */
    private static function checkWhere(WorklistFilter $filter): array
    {
        $where = '';
        $binds = [];
        if ($filter->payerName !== '') {
            $where .= ' AND k.`payer_name` = ?';
            $binds[] = $filter->payerName;
        }
        if ($filter->checkNumber !== '') {
            $where .= ' AND LOCATE(?, k.`check_number`) > 0';
            $binds[] = $filter->checkNumber;
        }
        if ($filter->from !== null) {
            $where .= ' AND k.`processed_at` >= ?';
            $binds[] = $filter->from->format('Y-m-d 00:00:00');
        }
        if ($filter->to !== null) {
            $where .= ' AND k.`processed_at` < ?';
            $binds[] = $filter->to->modify('+1 day')->format('Y-m-d 00:00:00');
        }
        return [$where, $binds];
    }

    /**
     * The claim payments of these checks, with the lines each check's payment session
     * holds for the claim's encounter.
     *
     * @param list<array<mixed>> $checkRows rows of era_auto_post_check with id and session_id
     * @return array<int, list<PostedClaim>> keyed by check id
     */
    private static function claimsOf(array $checkRows): array
    {
        if ($checkRows === []) {
            return [];
        }
        $sessions = [];
        foreach ($checkRows as $row) {
            $sessions[self::int($row, 'id')] = self::int($row, 'session_id');
        }
        $lines = self::linesOf(array_values(array_unique(array_filter($sessions, static fn(int $id): bool => $id > 0))));
        $rows = QueryUtils::fetchRecords(
            'SELECT c.`check_id`, c.`payer_claim_id`, c.`patient_name`, c.`pid`, c.`encounter`, c.`payer_level`,'
            . ' c.`claim_status_code`, c.`outcome`, c.`reasons`, c.`claim_payment`, c.`payments_posted`,'
            . ' c.`adjustments_posted`, fe.`id` AS invoice_id'
            . ' FROM `era_auto_post_claim` AS c'
            . ' LEFT JOIN `form_encounter` AS fe ON fe.`pid` = c.`pid` AND fe.`encounter` = c.`encounter` AND c.`pid` > 0'
            . ' WHERE c.`check_id` IN (' . self::placeholders(count($sessions)) . ')'
            . ' ORDER BY c.`check_id`, c.`sequence`',
            array_keys($sessions)
        );
        $claims = [];
        foreach ($rows as $row) {
            $outcome = ClaimOutcome::tryFrom(self::text($row, 'outcome'));
            if ($outcome === null) {
                continue;
            }
            $checkId = self::int($row, 'check_id');
            $pid = self::int($row, 'pid');
            $encounter = self::int($row, 'encounter');
            // The session's lines for an encounter are listed once, under the first of the
            // check's claim payments for it.
            $key = self::lineKey($sessions[$checkId] ?? 0, $pid, $encounter);
            $claimLines = $pid > 0 ? ($lines[$key] ?? []) : [];
            unset($lines[$key]);
            $claims[$checkId][] = new PostedClaim(
                patientName: self::text($row, 'patient_name'),
                pid: $pid,
                encounter: $encounter,
                invoiceId: self::int($row, 'invoice_id'),
                payerClaimId: self::text($row, 'payer_claim_id'),
                payerLevel: self::int($row, 'payer_level'),
                claimStatusCode: self::text($row, 'claim_status_code'),
                outcome: $outcome,
                reasons: self::reasons($row),
                claimPayment: self::amount($row, 'claim_payment'),
                paymentsPosted: self::amount($row, 'payments_posted'),
                adjustmentsPosted: self::amount($row, 'adjustments_posted'),
                lines: $claimLines,
            );
        }
        return $claims;
    }

    /**
     * @param list<int> $sessionIds
     * @return array<string, list<PostedLine>> keyed by self::lineKey()
     */
    private static function linesOf(array $sessionIds): array
    {
        if ($sessionIds === []) {
            return [];
        }
        $rows = QueryUtils::fetchRecords(
            'SELECT `session_id`, `pid`, `encounter`, `code`, `modifier`, `pay_amount`, `adj_amount`, `memo`'
            . ' FROM `ar_activity` WHERE `session_id` IN (' . self::placeholders(count($sessionIds)) . ')'
            . ' AND `deleted` IS NULL ORDER BY `pid`, `encounter`, `sequence_no`',
            $sessionIds
        );
        $lines = [];
        foreach ($rows as $row) {
            $key = self::lineKey(self::int($row, 'session_id'), self::int($row, 'pid'), self::int($row, 'encounter'));
            $lines[$key][] = new PostedLine(
                code: self::text($row, 'code'),
                modifier: self::text($row, 'modifier'),
                paid: self::amount($row, 'pay_amount'),
                adjusted: self::amount($row, 'adj_amount'),
                memo: self::text($row, 'memo'),
            );
        }
        return $lines;
    }

    /**
     * @param list<array<mixed>> $fileRows rows of x12_remote_received with id
     * @return array<int, list<HeldCheck>> keyed by received id
     */
    private static function heldChecksOf(array $fileRows): array
    {
        if ($fileRows === []) {
            return [];
        }
        $ids = array_map(static fn(array $row): int => self::int($row, 'id'), $fileRows);
        $rows = QueryUtils::fetchRecords(
            'SELECT k.`received_id`, k.`check_number`, k.`check_date`, k.`payer_name`, k.`check_amount`,'
            . ' k.`claim_total`, k.`plb_total`, k.`hold_reason`,'
            . ' (SELECT COUNT(*) FROM `era_auto_post_claim` AS c WHERE c.`check_id` = k.`id`) AS claims'
            . ' FROM `era_auto_post_check` AS k'
            . ' WHERE k.`received_id` IN (' . self::placeholders(count($ids)) . ') AND k.`status` = ?'
            . ' ORDER BY k.`received_id`, k.`sequence`',
            [...$ids, CheckStatus::Held->value]
        );
        $held = [];
        foreach ($rows as $row) {
            $held[self::int($row, 'received_id')][] = new HeldCheck(
                checkNumber: self::text($row, 'check_number'),
                checkDate: self::checkDate(self::text($row, 'check_date')),
                payerName: self::text($row, 'payer_name'),
                checkAmount: self::amount($row, 'check_amount'),
                claimTotal: self::amount($row, 'claim_total'),
                plbTotal: self::amount($row, 'plb_total'),
                reason: HoldReason::tryFrom(self::text($row, 'hold_reason')),
                claims: self::int($row, 'claims'),
            );
        }
        return $held;
    }

    private static function lineKey(int $sessionId, int $pid, int $encounter): string
    {
        return $sessionId . '|' . $pid . '|' . $encounter;
    }

    /**
     * @param positive-int $count
     */
    private static function placeholders(int $count): string
    {
        return implode(', ', array_fill(0, $count, '?'));
    }

    /**
     * @param list<int|string> $binds
     */
    private static function count(string $sql, array $binds): int
    {
        $count = QueryUtils::fetchSingleValue($sql, 'n', $binds);
        return is_numeric($count) ? (int) $count : 0;
    }

    private static function changedOne(): bool
    {
        $changed = QueryUtils::affectedRows();
        return is_numeric($changed) && (int) $changed === 1;
    }

    /**
     * @param string $stored BPR16 as stored, yyyymmdd
     * @return string yyyy-mm-dd, or '' when it is not a date
     */
    private static function checkDate(string $stored): string
    {
        $date = \DateTimeImmutable::createFromFormat('!Ymd', $stored);
        return $date !== false && $date->format('Ymd') === $stored ? $date->format('Y-m-d') : '';
    }

    /**
     * @param array<mixed> $row
     */
    private static function worked(array $row): ?WorkedMark
    {
        $at = $row['worked_at'] ?? null;
        if (self::int($row, 'worked_by') <= 0 || !is_string($at)) {
            return null;
        }
        $name = trim(self::text($row, 'worked_fname') . ' ' . self::text($row, 'worked_lname'));
        return new WorkedMark($name !== '' ? $name : self::text($row, 'worked_username'), new \DateTimeImmutable($at));
    }

    /**
     * @param array<mixed> $row
     * @return list<string>
     */
    private static function reasons(array $row): array
    {
        $reasons = self::text($row, 'reasons');
        return $reasons === '' ? [] : explode(',', $reasons);
    }

    /**
     * @param array<mixed> $row
     */
    private static function text(array $row, string $column): string
    {
        $value = $row[$column] ?? null;
        return is_string($value) ? $value : (is_int($value) || is_float($value) ? (string) $value : '');
    }

    /**
     * @param array<mixed> $row
     */
    private static function int(array $row, string $column): int
    {
        $value = $row[$column] ?? null;
        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @param array<mixed> $row
     */
    private static function amount(array $row, string $column): float
    {
        $value = $row[$column] ?? null;
        return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * @param array<mixed> $row
     */
    private static function time(array $row, string $column): \DateTimeImmutable
    {
        $value = $row[$column] ?? null;
        return new \DateTimeImmutable(is_string($value) ? $value : '@0');
    }
}
