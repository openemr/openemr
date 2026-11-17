<?php

/**
 * Posting's payer at each level (SLEOB::arGetPayerID(): ERA and manual
 * posting, the secondary claim, batch payments, collections) and the ERA
 * reversal's payers (EraReversal): the policy InsuranceService has in force
 * at the level on the date of service.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\EraReversal;
use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PostingPayerLevelTest extends TestCase
{
    private const PID = 99995501;
    private const ENC = 99995511;
    private const DOS = '2026-09-15';
    private const PRIMARY = 99995521;
    private const SECONDARY = 99995522;
    private const OTHER = 99995523;

    protected function setUp(): void
    {
        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO form_encounter (pid, encounter, date, last_level_billed, last_level_closed) VALUES (?, ?, ?, 1, 1)',
            [self::PID, self::ENC, self::DOS]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    /**
     * A 0000-00-00 end (what sql_mode = '' stores for a mistyped date) is open,
     * as the coverage rule reads it: the policy is still the payer.
     */
    #[Test]
    public function aZeroEndDateIsOpen(): void
    {
        $this->addPolicy('primary', self::PRIMARY, '2020-01-01', '0000-00-00');

        self::assertEquals(self::PRIMARY, SLEOB::arGetPayerID(self::PID, self::DOS, 1));
    }

    /**
     * Of two undated policies at a level, the later entry is in force.
     */
    #[Test]
    public function ofTwoUndatedPoliciesTheLaterEntryIsThePayer(): void
    {
        $this->addPolicy('primary', self::OTHER, null, null);
        $this->addPolicy('primary', self::PRIMARY, null, null);

        self::assertEquals(self::PRIMARY, SLEOB::arGetPayerID(self::PID, self::DOS, 1));
        self::assertEquals(self::PRIMARY, SLEOB::arGetPayerID(self::PID, self::DOS, '1'), 'the level as ERA posting passes it');
    }

    /**
     * A level with nothing in force, a level other than 1 to 3, or a date of
     * service that can't be read has no payer.
     */
    #[Test]
    public function withNothingToAnswerThereIsNoPayer(): void
    {
        $this->addPolicy('primary', self::PRIMARY, '2020-01-01', '2026-03-31');
        $this->addPolicy('secondary', self::SECONDARY, null, null);

        self::assertSame(0, SLEOB::arGetPayerID(self::PID, self::DOS, 1), 'the primary ended');
        self::assertEquals(self::SECONDARY, SLEOB::arGetPayerID(self::PID, self::DOS, 2));
        self::assertSame(0, SLEOB::arGetPayerID(self::PID, self::DOS, 3));
        self::assertSame(0, SLEOB::arGetPayerID(self::PID, self::DOS, 4));
        self::assertSame(0, SLEOB::arGetPayerID(self::PID, self::DOS, '2.5'));
        self::assertSame(0, SLEOB::arGetPayerID(self::PID, '', 2));
    }

    /**
     * A reversal from the secondary is found at the secondary's level, though
     * the primary ended before the date of service.
     */
    #[Test]
    public function theReversalFindsACurrentSecondaryBehindAnEndedPrimary(): void
    {
        $this->addPolicy('primary', self::PRIMARY, '2020-01-01', '2026-03-31');
        $this->addPolicy('secondary', self::SECONDARY, '2020-01-01', null);

        self::assertSame(2, (new EraReversal((string) self::SECONDARY))->reversedLevel(self::PID, self::ENC, self::DOS, ''));
        self::assertSame(0, (new EraReversal((string) self::PRIMARY))->reversedLevel(self::PID, self::ENC, self::DOS, ''));
    }

    private function addPolicy(string $type, int $company, ?string $start, ?string $end): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_data (uuid, pid, type, provider, date, date_end, plan_name) VALUES (?, ?, ?, ?, ?, ?, 'test-fixture')",
            [(new UuidRegistry(['table_name' => 'insurance_data']))->createUuid(), self::PID, $type, (string) $company, $start, $end]
        );
    }

    private function cleanUp(): void
    {
        foreach (QueryUtils::fetchTableColumn('SELECT uuid FROM insurance_data WHERE pid = ? AND uuid IS NOT NULL', 'uuid', [self::PID]) as $uuid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'insurance_data' AND uuid = ?", [$uuid]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE pid = ?', [self::PID]);
    }
}
