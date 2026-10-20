<?php

/**
 * Database tests for building a claim for a payer other than the one on the
 * encounter's billing rows, as the billing manager's Validate Only does.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\Claim;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class ClaimSelectedPayerTest extends TestCase
{
    private const PID = 99990021;
    private const ENC = 99990121;
    private const PRIMARY = 99990031;
    private const SECONDARY = 99990032;
    private const SESSION_ID = 99990221;

    protected function setUp(): void
    {
        $this->cleanUp();

        $facilityId = $this->firstId('facility');
        $providerId = $this->firstId('users');

        $this->insert(
            'INSERT INTO insurance_companies (id, name, cms_id) VALUES (?, ?, ?), (?, ?, ?)',
            [self::PRIMARY, 'TEST PRIMARY PAYER', 'TP001', self::SECONDARY, 'TEST SECONDARY PAYER', 'TS002']
        );
        $this->insert(
            "INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, 'JANE', 'TESTPATIENT', '1950-01-01', 'Female', NOW())",
            [self::PID, (string) self::PID]
        );
        $this->insert(
            "INSERT INTO insurance_data (pid, type, provider, policy_number, date, subscriber_relationship) VALUES " .
            "(?, 'primary', ?, 'PRI123', '2020-01-01', 'self'), (?, 'secondary', ?, 'SEC456', '2020-01-01', 'self')",
            [self::PID, self::PRIMARY, self::PID, self::SECONDARY]
        );
        $this->insert(
            "INSERT INTO form_encounter (pid, encounter, date, reason, facility_id, provider_id) VALUES (?, ?, '2026-09-01', 'test', ?, ?)",
            [self::PID, self::ENC, $facilityId, $providerId]
        );
        // The encounter was billed to the primary; its billing row still says so.
        $this->insert(
            "INSERT INTO billing (date, code_type, code, pid, encounter, provider_id, user, groupname, authorized, " .
            "activity, billed, units, fee, code_text, payer_id) " .
            "VALUES (NOW(), 'CPT4', '99213', ?, ?, ?, 1, 'Default', 1, 1, 1, 1, 100.00, 'Office visit', ?)",
            [self::PID, self::ENC, $providerId, self::PRIMARY]
        );
        // The primary's remit, with its claim control number.
        $this->insert(
            "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, " .
            "post_user, session_id, modified_time, pay_amount, adj_amount, memo, account_code, follow_up, " .
            "follow_up_note, reason_code, payer_claim_number) " .
            "VALUES (?, ?, 1, 'CPT4', '99213', '', 1, NOW(), 1, ?, NOW(), 0, 80.00, 'Adjust code 45', '', '', '', '', 'ICNTEST0000021')",
            [self::PID, self::ENC, self::SESSION_ID]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testWithoutAPayerTheClaimIsForTheBillingRowsPayer(): void
    {
        $claim = new Claim(self::PID, self::ENC, false);

        $this->assertSame('P', $claim->payerSequence());
        $this->assertSame(1, $claim->payerLevel());
    }

    public function testASelectedSecondaryBuildsTheSecondaryClaim(): void
    {
        $claim = new Claim(self::PID, self::ENC, false, (string) self::SECONDARY);

        $this->assertSame('S', $claim->payerSequence());
        $this->assertSame(2, $claim->payerLevel());
        // The prior payer, at index 1, is the primary, and its claim control
        // number is what loop 2330B REF*F8 sends.
        $this->assertSame('P', $claim->payerSequence(1));
        $this->assertSame('ICNTEST0000021', $claim->otherPayerClaimControlNumber(1));
    }

    public function testBuildingForASelectedPayerSavesNothing(): void
    {
        new Claim(self::PID, self::ENC, false, (string) self::SECONDARY);

        // Loose on purpose: whether the driver hands back int or string is not the point.
        $this->assertEquals(
            self::PRIMARY,
            QueryUtils::fetchSingleValue(
                'SELECT payer_id FROM billing WHERE pid = ? AND encounter = ?',
                'payer_id',
                [self::PID, self::ENC]
            )
        );
    }

    private function firstId(string $table): int
    {
        $id = QueryUtils::fetchSingleValue("SELECT id FROM `$table` ORDER BY id LIMIT 1", 'id');
        if (!is_numeric($id)) {
            throw new \RuntimeException("No row in $table to build the test encounter with");
        }
        return (int) $id;
    }

    /**
     * @param list<int|string|null> $binds
     */
    private function insert(string $sql, array $binds): void
    {
        QueryUtils::sqlStatementThrowException($sql, $binds);
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM ar_activity WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM insurance_companies WHERE id IN (?, ?)',
            [self::PRIMARY, self::SECONDARY]
        );
    }
}
