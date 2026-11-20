<?php

/**
 * Database tests for EraCheckPosting::denialRecorded(): a re-posted ERA must
 * not record the same denial again.
 *
 * Posting a denied claim (CLP02 4) writes a new claim version with status 7
 * and the reason codes in process_file, and nothing to ar_activity, so the
 * already-posted check can't see it. Each re-post used to add another denied
 * version. The denials here are written by BillingUtilities::updateClaim()
 * with the arguments sl_eob_process.php uses.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\EraCheckPosting;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class EraDenialRecordedTest extends TestCase
{
    /**
     * Synthetic ids, clear of the demo data. claims has no foreign keys, so no
     * patient or encounter rows are needed.
     */
    private const PID = 99990021;
    private const ENC = 99990121;
    private const PAYER = '99990221';
    private const REASONS = '99213__CO_197,99213__CO_45';

    protected function setUp(): void
    {
        $this->cleanUp();
        // The claim as billed to the primary payer.
        BillingUtilities::updateClaim(true, self::PID, self::ENC, self::PAYER, 1, 2, 2, 'batch.txt');
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testABilledClaimHasNoDenialRecorded(): void
    {
        $this->assertFalse($this->posting()->denialRecorded(self::PID, self::ENC, 1, self::REASONS));
    }

    public function testTheSameDenialIsRecordedOnce(): void
    {
        // The ERA posted twice, with sl_eob_process.php's guard.
        foreach ([1, 2] as $post) {
            if (!$this->posting()->denialRecorded(self::PID, self::ENC, 1, self::REASONS)) {
                $this->recordDenial(self::REASONS);
            }
        }

        $this->assertTrue($this->posting()->denialRecorded(self::PID, self::ENC, 1, self::REASONS));
        $this->assertSame(2, $this->versions(), 'The billed version and one denied version');
    }

    public function testADenialWithOtherReasonsOrAtAnotherLevelIsNew(): void
    {
        $this->recordDenial(self::REASONS);

        $this->assertFalse($this->posting()->denialRecorded(self::PID, self::ENC, 1, '99213__CO_27'));
        $this->assertFalse($this->posting()->denialRecorded(self::PID, self::ENC, 2, self::REASONS));
        $this->assertFalse((new EraCheckPosting('99990222'))->denialRecorded(self::PID, self::ENC, 1, self::REASONS));
    }

    public function testADenialAfterTheClaimWasRebilledIsNew(): void
    {
        $this->recordDenial(self::REASONS);
        BillingUtilities::updateClaim(true, self::PID, self::ENC, self::PAYER, 1, 2, 2, 'batch2.txt');

        $this->assertFalse($this->posting()->denialRecorded(self::PID, self::ENC, 1, self::REASONS));
    }

    public function testReasonsLongerThanTheColumnStillMatch(): void
    {
        $reasons = implode(',', array_fill(0, 20, '99213_25_CO_197'));
        $this->assertGreaterThan(255, strlen($reasons));
        $this->recordDenial($reasons);

        $this->assertTrue($this->posting()->denialRecorded(self::PID, self::ENC, 1, $reasons));
    }

    private function posting(): EraCheckPosting
    {
        return new EraCheckPosting(self::PAYER);
    }

    /**
     * As sl_eob_process.php records a denial from the primary payer.
     */
    private function recordDenial(string $reasons): void
    {
        BillingUtilities::updateClaim(true, self::PID, self::ENC, self::PAYER, '1', 7, 0, $reasons);
    }

    private function versions(): int
    {
        $count = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS n FROM claims WHERE patient_id = ? AND encounter_id = ?',
            'n',
            [self::PID, self::ENC]
        );
        $this->assertIsNumeric($count);
        return (int) $count;
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM claims WHERE patient_id = ? AND encounter_id = ?',
            [self::PID, self::ENC]
        );
    }
}
