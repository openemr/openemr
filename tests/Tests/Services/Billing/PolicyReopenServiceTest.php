<?php

/**
 * Database tests for the encounters offered for reopening after an insurance
 * policy is saved, and for reopening them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\Reopen\PolicyReopenService;
use OpenEMR\Billing\Reopen\ReopenCandidate;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\TestCase;

class PolicyReopenServiceTest extends TestCase
{
    private const PID = 99990041;
    private const OTHER_PID = 99990042;
    private const OLD_PAYER = 99990051;
    private const NEW_PAYER = 99990052;
    private const SECONDARY_PAYER = 99990053;
    private const ENC = 99990141;
    private const SESSION_ID = 99990241;

    private int $facilityId;
    private int $providerId;

    protected function setUp(): void
    {
        $this->cleanUp();

        $this->facilityId = $this->firstId('facility');
        $this->providerId = $this->firstId('users');

        $this->statement(
            'INSERT INTO insurance_companies (id, name) VALUES (?, ?), (?, ?), (?, ?)',
            [
                self::OLD_PAYER, 'TEST OLD PAYER',
                self::NEW_PAYER, 'TEST NEW PAYER',
                self::SECONDARY_PAYER, 'TEST SECONDARY PAYER',
            ]
        );
        foreach ([self::PID, self::OTHER_PID] as $pid) {
            $this->statement(
                "INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) "
                . "VALUES (?, ?, 'JANE', 'TESTREOPEN', '1950-01-01', 'Female', NOW())",
                [$pid, (string) $pid]
            );
        }
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testAnEncounterBilledToTheOldPayerIsOfferedForTheNewPrimary(): void
    {
        // The old payer's coverage ended; the new payer was entered after the visit was billed.
        $this->policy('primary', self::OLD_PAYER, '2020-01-01', '2026-06-30');
        $newPolicy = $this->policy('primary', self::NEW_PAYER, '2026-07-01');
        $this->billedEncounter(self::ENC, '2026-09-01', self::OLD_PAYER, 1, 1);

        $candidates = (new PolicyReopenService())->candidates(self::PID, $newPolicy);

        $this->assertEquals(
            [new ReopenCandidate(self::ENC, '2026-09-01', 'TEST OLD PAYER', '100.00')],
            $candidates
        );
        $this->assertSame(
            ['encounter' => self::ENC, 'date' => '2026-09-01', 'last_billed_to' => 'TEST OLD PAYER', 'balance' => '100.00'],
            $candidates[0]->toArray()
        );
    }

    public function testAnEncounterAlreadyBilledToThePolicysPayerIsNotOffered(): void
    {
        $policy = $this->policy('primary', self::NEW_PAYER, '2026-07-01');
        $this->billedEncounter(self::ENC, '2026-09-01', self::NEW_PAYER, 1, 0);

        $this->assertSame([], (new PolicyReopenService())->candidates(self::PID, $policy));
    }

    public function testAnEncounterOutsideThePolicysDatesIsNotOffered(): void
    {
        $this->policy('primary', self::OLD_PAYER, '2020-01-01', '2026-06-30');
        $newPolicy = $this->policy('primary', self::NEW_PAYER, '2026-07-01');
        // Seen while the old payer was in force.
        $this->billedEncounter(self::ENC, '2026-05-01', self::OLD_PAYER, 1, 1);

        $this->assertSame([], (new PolicyReopenService())->candidates(self::PID, $newPolicy));
    }

    public function testAnUnbilledEncounterIsNotOffered(): void
    {
        $policy = $this->policy('primary', self::NEW_PAYER, '2026-07-01');
        $this->billedEncounter(self::ENC, '2026-09-01', self::OLD_PAYER, 1, 1);
        $this->statement('UPDATE billing SET billed = 0 WHERE pid = ?', [self::PID]);

        $this->assertSame([], (new PolicyReopenService())->candidates(self::PID, $policy));
    }

    public function testAnEncounterPaidInFullIsNotOffered(): void
    {
        $policy = $this->policy('primary', self::NEW_PAYER, '2026-07-01');
        $this->billedEncounter(self::ENC, '2026-09-01', self::OLD_PAYER, 1, 1);
        $this->activity(self::ENC, 1, 80.00, 20.00);

        $this->assertSame([], (new PolicyReopenService())->candidates(self::PID, $policy));
    }

    public function testANewSecondaryIsOfferedOnceThePrimaryIsClosed(): void
    {
        $this->policy('primary', self::OLD_PAYER, '2020-01-01');
        $secondary = $this->policy('secondary', self::SECONDARY_PAYER, '2026-07-01');
        // Still out to the primary: its remittance will send the claim on to the new secondary.
        $this->billedEncounter(self::ENC, '2026-09-01', self::OLD_PAYER, 1, 0);
        // The primary paid and the rest went to the patient before the secondary was entered.
        $this->billedEncounter(self::ENC + 1, '2026-09-08', self::OLD_PAYER, 1, 1);
        $this->activity(self::ENC + 1, 1, 60.00, 15.00);

        $this->assertEquals(
            [new ReopenCandidate(self::ENC + 1, '2026-09-08', 'TEST OLD PAYER', '25.00')],
            (new PolicyReopenService())->candidates(self::PID, $secondary)
        );
    }

    public function testASecondaryWithNoPrimaryInForceIsNotOffered(): void
    {
        // The Billing Manager offers no secondary when the date of service has no primary.
        $secondary = $this->policy('secondary', self::SECONDARY_PAYER, '2026-07-01');
        $this->billedEncounter(self::ENC, '2026-09-01', self::OLD_PAYER, 1, 1);

        $this->assertSame([], (new PolicyReopenService())->candidates(self::PID, $secondary));
    }

    public function testAnEncounterMarkedBilledWithNoPayerHasNoLastBilledTo(): void
    {
        $policy = $this->policy('primary', self::NEW_PAYER, '2026-07-01');
        $this->billedEncounter(self::ENC, '2026-09-01', 0, 0, 0);

        $this->assertEquals(
            [new ReopenCandidate(self::ENC, '2026-09-01', null, '100.00')],
            (new PolicyReopenService())->candidates(self::PID, $policy)
        );
    }

    public function testAnotherPatientsPolicyOffersNothing(): void
    {
        $policy = $this->policy('primary', self::NEW_PAYER, '2026-07-01');
        $this->billedEncounter(self::ENC, '2026-09-01', self::OLD_PAYER, 1, 1);
        $service = new PolicyReopenService();

        $this->assertSame([], $service->candidates(self::OTHER_PID, $policy));
        $this->assertSame([], $service->candidates(self::PID, 'not-a-uuid'));
        $this->assertSame([], $service->reopen(self::OTHER_PID, $policy, [self::ENC]));
        $this->assertSame(1, $this->encounterColumn(self::ENC, 'last_level_billed'));
    }

    public function testReopeningForAPrimaryPutsTheEncounterBackToUnbilled(): void
    {
        $policy = $this->policy('primary', self::NEW_PAYER, '2026-07-01');
        $this->billedEncounter(self::ENC, '2026-09-01', self::OLD_PAYER, 1, 1);
        $this->billedEncounter(self::ENC + 1, '2026-09-08', self::OLD_PAYER, 1, 1);
        $this->statement(
            "UPDATE form_encounter SET stmt_count = 2, last_stmt_date = '2026-10-01' WHERE pid = ?",
            [self::PID]
        );

        // The second encounter is a candidate too, but was not chosen; the third is not this patient's.
        $reopened = (new PolicyReopenService())->reopen(self::PID, $policy, [self::ENC, self::ENC + 2]);

        $this->assertSame([self::ENC], $reopened);
        $this->assertSame(0, $this->billedFlag(self::ENC));
        $this->assertSame(0, $this->encounterColumn(self::ENC, 'last_level_billed'));
        $this->assertSame(0, $this->encounterColumn(self::ENC, 'last_level_closed'));
        $this->assertSame(0, $this->encounterColumn(self::ENC, 'stmt_count'));
        $this->assertNull(QueryUtils::fetchSingleValue(
            'SELECT last_stmt_date FROM form_encounter WHERE pid = ? AND encounter = ?',
            'last_stmt_date',
            [self::PID, self::ENC]
        ));
        $this->assertSame(1, $this->billedFlag(self::ENC + 1));
        $this->assertSame(2, $this->encounterColumn(self::ENC + 1, 'stmt_count'));
        // Reopened, the encounter is no longer offered.
        $this->assertEquals(
            [new ReopenCandidate(self::ENC + 1, '2026-09-08', 'TEST OLD PAYER', '100.00')],
            (new PolicyReopenService())->candidates(self::PID, $policy)
        );
    }

    public function testReopeningForASecondaryKeepsThePrimaryBilledAndClosed(): void
    {
        $this->policy('primary', self::OLD_PAYER, '2020-01-01');
        $secondary = $this->policy('secondary', self::SECONDARY_PAYER, '2026-07-01');
        $this->billedEncounter(self::ENC, '2026-09-01', self::OLD_PAYER, 1, 1);
        $this->activity(self::ENC, 1, 60.00, 15.00);

        $this->assertSame([self::ENC], (new PolicyReopenService())->reopen(self::PID, $secondary, [self::ENC]));

        $this->assertSame(0, $this->billedFlag(self::ENC));
        // The Billing Manager selects the payer at index last_level_closed: the secondary.
        $this->assertSame(1, $this->encounterColumn(self::ENC, 'last_level_billed'));
        $this->assertSame(1, $this->encounterColumn(self::ENC, 'last_level_closed'));
    }

    /**
     * @return string the policy's uuid as text
     */
    private function policy(string $type, int $company, string $start, ?string $end = null, int $pid = self::PID): string
    {
        $uuid = (new UuidRegistry(['table_name' => 'insurance_data']))->createUuid();
        $this->statement(
            "INSERT INTO insurance_data (uuid, pid, type, provider, policy_number, date, date_end, subscriber_relationship) "
            . "VALUES (?, ?, ?, ?, 'TESTREOPEN', ?, ?, 'self')",
            [$uuid, $pid, $type, $company, $start, $end]
        );
        return UuidRegistry::uuidToString($uuid);
    }

    /**
     * An encounter with one 100.00 charge marked billed, and the claim that billed it.
     */
    private function billedEncounter(int $encounter, string $date, int $payer, int $levelBilled, int $levelClosed): void
    {
        $this->statement(
            "INSERT INTO form_encounter (pid, encounter, date, reason, facility_id, provider_id, last_level_billed, "
            . "last_level_closed) VALUES (?, ?, ?, 'test', ?, ?, ?, ?)",
            [self::PID, $encounter, $date, $this->facilityId, $this->providerId, $levelBilled, $levelClosed]
        );
        $this->statement(
            "INSERT INTO billing (date, code_type, code, pid, encounter, provider_id, user, groupname, authorized, "
            . "activity, billed, bill_date, units, fee, code_text, payer_id) "
            . "VALUES (NOW(), 'CPT4', '99213', ?, ?, ?, 1, 'Default', 1, 1, 1, NOW(), 1, 100.00, 'Office visit', ?)",
            [self::PID, $encounter, $this->providerId, $payer]
        );
        $this->statement(
            'INSERT INTO claims (patient_id, encounter_id, version, payer_id, status, payer_type, bill_process, bill_time) '
            . 'VALUES (?, ?, 1, ?, 2, ?, 0, NOW())',
            [self::PID, $encounter, $payer, $levelBilled]
        );
    }

    private function activity(int $encounter, int $payerType, float $paid, float $adjusted): void
    {
        $this->statement(
            "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, "
            . "post_user, session_id, modified_time, pay_amount, adj_amount, memo, account_code, follow_up, "
            . "follow_up_note, reason_code) "
            . "VALUES (?, ?, 1, 'CPT4', '99213', '', ?, NOW(), 1, ?, NOW(), ?, ?, 'test', '', '', '', '')",
            [self::PID, $encounter, $payerType, self::SESSION_ID, $paid, $adjusted]
        );
    }

    private function billedFlag(int $encounter): int
    {
        return $this->intValue('SELECT billed AS v FROM billing WHERE pid = ? AND encounter = ?', $encounter);
    }

    private function encounterColumn(int $encounter, string $column): int
    {
        return $this->intValue("SELECT `$column` AS v FROM form_encounter WHERE pid = ? AND encounter = ?", $encounter);
    }

    private function intValue(string $sql, int $encounter): int
    {
        $value = QueryUtils::fetchSingleValue($sql, 'v', [self::PID, $encounter]);
        if (!is_numeric($value)) {
            throw new \RuntimeException('No value for encounter ' . $encounter);
        }
        return (int) $value;
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
     * @param list<int|float|string|null> $binds
     */
    private function statement(string $sql, array $binds): void
    {
        QueryUtils::sqlStatementThrowException($sql, $binds);
    }

    private function cleanUp(): void
    {
        foreach ([self::PID, self::OTHER_PID] as $pid) {
            $this->statement('DELETE FROM ar_activity WHERE pid = ?', [$pid]);
            $this->statement('DELETE FROM claims WHERE patient_id = ?', [$pid]);
            $this->statement('DELETE FROM billing WHERE pid = ?', [$pid]);
            $this->statement('DELETE FROM form_encounter WHERE pid = ?', [$pid]);
            $this->statement(
                'DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM insurance_data WHERE pid = ?)',
                [$pid]
            );
            $this->statement('DELETE FROM insurance_data WHERE pid = ?', [$pid]);
            $this->statement('DELETE FROM patient_data WHERE pid = ?', [$pid]);
        }
        $this->statement(
            'DELETE FROM insurance_companies WHERE id IN (?, ?, ?)',
            [self::OLD_PAYER, self::NEW_PAYER, self::SECONDARY_PAYER]
        );
    }
}
