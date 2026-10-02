<?php

/**
 * Held settlement writes the encounter's billed payer level.
 *
 * The generate path marks the claim billed after the file lands.
 * That write stores the level updateClaim() stores for the same type.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class HeldClaimSettlementTest extends TestCase
{
    private int $pid = 0;

    private int $encounter = 0;

    private int $billingId = 0;

    /**
     * Remove only the rows this test inserted.
     */
    protected function tearDown(): void
    {
        $this->removeCreatedRows();
        parent::tearDown();
    }

    /**
     * A positive payer type is stored on the encounter when the file is billed.
     */
    public function testSettlementWritesTheEncounterLevel(): void
    {
        $filename = $this->insertOpenClaim(1, 0);

        $this->assertTrue(BillingUtilities::billUnbilledClaimFile($this->pid, $this->encounter, 1, $filename));
        $this->assertSame(1, $this->storedLevel());
        $this->assertSame(BillingClaim::STATUS_MARK_AS_BILLED, $this->storedClaimStatus());
        $this->assertSame(1, $this->storedBilledFlag());
        $this->assertSame($filename, $this->storedBillingFile());
    }

    /**
     * A non-positive payer type leaves the encounter level where it was.
     */
    public function testSettlementLeavesTheLevelWhenThePayerTypeIsNotPositive(): void
    {
        $filename = $this->insertOpenClaim(0, 3);

        $this->assertTrue(BillingUtilities::billUnbilledClaimFile($this->pid, $this->encounter, 1, $filename));
        $this->assertSame(3, $this->storedLevel());
        $this->assertSame(BillingClaim::STATUS_MARK_AS_BILLED, $this->storedClaimStatus());
    }

    /**
     * Another file name does not bill the claim or change the encounter level.
     */
    public function testSettlementDoesNotBillADifferentFile(): void
    {
        $filename = $this->insertOpenClaim(1, 0);

        $this->assertFalse(
            BillingUtilities::billUnbilledClaimFile($this->pid, $this->encounter, 1, $filename . '-other')
        );
        $this->assertSame(0, $this->storedLevel());
        $this->assertSame(BillingClaim::STATUS_LEAVE_UNBILLED, $this->storedClaimStatus());
        $this->assertSame(0, $this->storedBilledFlag());
    }

    /**
     * Insert an unbilled claim that names this file and return the file name.
     */
    private function insertOpenClaim(int $payerType, int $startingLevel): string
    {
        $suffix = bin2hex(random_bytes(4));
        $filename = 'held-settlement-' . $suffix . '.txt';
        QueryUtils::sqlStatementThrowException(
            'LOCK TABLES patient_data WRITE, form_encounter WRITE'
        );
        try {
            $pid = $this->allocatePositiveId(
                'SELECT COALESCE(MAX(pid), 0) + 1 AS next_id FROM patient_data'
            );
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO patient_data (pid, fname, lname, mname, DOB, sex, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$pid, 'Hold', 'Settlement', '', '1980-01-01', 'Female', 'single']
            );
            $this->pid = $pid;
            $encounter = $this->allocatePositiveId(
                'SELECT COALESCE(MAX(encounter), 0) + 1 AS next_id FROM form_encounter'
            );
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO form_encounter (pid, encounter, date, facility_id, billing_facility,'
                . ' provider_id, last_level_billed, reason)'
                . ' VALUES (?, ?, ?, 0, 0, 0, ?, ?)',
                [$pid, $encounter, '2026-10-02 14:00:00', $startingLevel, 'held-settlement']
            );
            $this->encounter = $encounter;
        } finally {
            QueryUtils::sqlStatementThrowException('UNLOCK TABLES');
        }

        QueryUtils::sqlStatementThrowException(
            'INSERT INTO claims (patient_id, encounter_id, version, payer_id, status, payer_type,'
            . ' bill_process, process_file) VALUES (?, ?, 1, 1, ?, ?, ?, ?)',
            [
                $this->pid,
                $this->encounter,
                BillingClaim::STATUS_LEAVE_UNBILLED,
                $payerType,
                BillingClaim::BILL_PROCESS_IN_PROGRESS,
                $filename,
            ]
        );
        $billingId = QueryUtils::sqlInsert(
            'INSERT INTO billing (pid, encounter, code_type, code, code_text, billed, activity, fee)'
            . ' VALUES (?, ?, ?, ?, ?, 0, 1, ?)',
            [$this->pid, $this->encounter, 'CPT4', '99213', 'Office visit', '10.00']
        );
        if ($billingId <= 0) {
            $this->fail('Billing insert did not return an id');
        }
        $this->billingId = $billingId;

        return $filename;
    }

    /**
     * Read the next positive id from a MAX()+1 query.
     */
    private function allocatePositiveId(string $sql): int
    {
        $row = QueryUtils::querySingleRow($sql);
        $nextId = is_array($row) ? ($row['next_id'] ?? null) : null;
        if (is_string($nextId) && ctype_digit($nextId)) {
            $nextId = (int) $nextId;
        }
        if (!is_int($nextId) || $nextId <= 0) {
            $this->fail('Could not allocate an id');
        }

        return $nextId;
    }

    private function storedLevel(): int
    {
        return $this->storedInt(
            'SELECT last_level_billed FROM form_encounter WHERE pid = ? AND encounter = ?',
            [$this->pid, $this->encounter],
            'last_level_billed'
        );
    }

    private function storedClaimStatus(): int
    {
        return $this->storedInt(
            'SELECT status FROM claims WHERE patient_id = ? AND encounter_id = ? AND version = 1',
            [$this->pid, $this->encounter],
            'status'
        );
    }

    private function storedBilledFlag(): int
    {
        return $this->storedInt(
            'SELECT billed FROM billing WHERE id = ?',
            [$this->billingId],
            'billed'
        );
    }

    private function storedBillingFile(): string
    {
        $row = QueryUtils::querySingleRow(
            'SELECT process_file FROM billing WHERE id = ?',
            [$this->billingId]
        );
        $file = is_array($row) ? ($row['process_file'] ?? null) : null;
        if (!is_string($file)) {
            $this->fail('Billing file was not stored');
        }

        return $file;
    }

    /**
     * @param list<int|string> $params
     */
    private function storedInt(string $sql, array $params, string $column): int
    {
        $row = QueryUtils::querySingleRow($sql, $params);
        $value = is_array($row) ? ($row[$column] ?? null) : null;
        if (is_string($value) && ctype_digit($value)) {
            $value = (int) $value;
        }
        $this->assertIsInt($value);

        return $value;
    }

    /**
     * Delete the rows this test inserted, and nothing else.
     */
    private function removeCreatedRows(): void
    {
        if ($this->billingId > 0) {
            QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE id = ?', [$this->billingId]);
        }
        if ($this->pid > 0 && $this->encounter > 0) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM claims WHERE patient_id = ? AND encounter_id = ? AND version = 1',
                [$this->pid, $this->encounter]
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM form_encounter WHERE pid = ? AND encounter = ?',
                [$this->pid, $this->encounter]
            );
        }
        if ($this->pid > 0) {
            QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [$this->pid]);
        }
    }
}
