<?php

/**
 * Recorder Tests
 *
 * Covers ar_activity sequence_no allocation through the public recordActivity()
 * seam. getNextSequenceNumber() is private and returns whatever the driver hands
 * back for IFNULL(MAX(sequence_no),0) + 1, so these assertions are only
 * meaningful against a real database. That is why this lives in the services
 * suite: tests/Tests/Unit/PaymentProcessing is routed to phpunit-isolated.xml,
 * which runs without database initialization.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@open-emr.org>
 * @author    AI Generated - Claude (Anthropic)
 * @copyright Copyright (c) 2026 - Public Domain for AI generated content
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\PaymentProcessing;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\PaymentProcessing\Recorder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RecorderTest extends TestCase
{
    /**
     * Synthetic ids, well clear of the demo data and the fixture managers.
     *
     * ar_activity declares no foreign keys - PRIMARY KEY (pid, encounter,
     * sequence_no) and KEY session_id - so no patient, encounter or session rows
     * need to exist for these inserts to be valid.
     */
    private const PID = '99990001';
    private const ENC = '99990101';
    private const ENC_2 = '99990102';
    private const SESSION_ID = '99990201';

    private Recorder $recorder;

    protected function setUp(): void
    {
        $this->recorder = new Recorder();
        // In case a previous run failed partway through.
        $this->removeActivityFixtures();
    }

    protected function tearDown(): void
    {
        $this->removeActivityFixtures();
    }

    /**
     * The first recordActivity() call is the regression guard on its own: with
     * declare(strict_types=1) an un-cast int return from getNextSequenceNumber()
     * raises a TypeError before anything is written, so this test fails without
     * needing to assert anything about the type directly.
     */
    #[Test]
    public function testSequenceNumbersIncrementWithinAnEncounter(): void
    {
        $this->recorder->recordActivity($this->activity());
        $this->recorder->recordActivity($this->activity());
        $this->recorder->recordActivity($this->activity());

        $this->assertSame([1, 2, 3], $this->sequenceNumbersFor(self::ENC));
    }

    #[Test]
    public function testSequenceNumbersAreScopedToTheEncounter(): void
    {
        $this->recorder->recordActivity($this->activity());
        $this->recorder->recordActivity($this->activity());
        $this->recorder->recordActivity($this->activity(['encounterId' => self::ENC_2]));

        $this->assertSame([1, 2], $this->sequenceNumbersFor(self::ENC));
        $this->assertSame([1], $this->sequenceNumbersFor(self::ENC_2));
    }

    /**
     * recordActivity() hand-orders 20 bind params against a 20 column list, so a
     * transposition is a live risk. Distinct values per column catch it.
     */
    #[Test]
    public function testRecordActivityWritesEachValueToItsOwnColumn(): void
    {
        $this->recorder->recordActivity($this->activity([
            'codeType' => 'HCPCS',
            'code' => 'Z0001',
            'modifier' => 'ZZ',
            'payerType' => '1',
            'payAmount' => '73.45',
            'adjustmentAmount' => '12.55',
            'memo' => 'Ins adjust Ins1',
            'accountCode' => 'IPP',
            'followUp' => true,
            'followUpNote' => 'awaiting secondary',
            'reasonCode' => 'ZZ999',
            'postDate' => '2026-08-04',
            'payerClaimNumber' => 'CLM0000123456',
        ]));

        $row = QueryUtils::querySingleRow(
            'SELECT * FROM ar_activity WHERE pid = ? AND encounter = ? AND sequence_no = ?',
            [self::PID, self::ENC, 1],
        );
        // querySingleRow is annotated array<mixed>|false; throwing here narrows it
        // so the offset reads below stay clean at PHPStan level 10.
        if (!is_array($row)) {
            throw new \RuntimeException('recordActivity() did not write an ar_activity row');
        }

        $this->assertSame('HCPCS', $row['code_type']);
        $this->assertSame('Z0001', $row['code']);
        $this->assertSame('ZZ', $row['modifier']);
        $this->assertSame('Ins adjust Ins1', $row['memo']);
        $this->assertSame('IPP', $row['account_code']);
        $this->assertSame('y', $row['follow_up']);
        $this->assertSame('awaiting secondary', $row['follow_up_note']);
        $this->assertSame('ZZ999', $row['reason_code']);
        $this->assertSame('2026-08-04', $row['post_date']);
        $this->assertSame('CLM0000123456', $row['payer_claim_number']);

        // Loose comparison on the numeric columns on purpose: whether payer_type
        // arrives as int and the decimals as string is a driver detail, and
        // pinning it down is what produced the bug this class is being tested for.
        $this->assertEquals(1, $row['payer_type']);
        $this->assertEquals(self::SESSION_ID, $row['session_id']);
        $this->assertEquals('73.45', $row['pay_amount']);
        $this->assertEquals('12.55', $row['adj_amount']);

        $this->assertNotEmpty($row['post_time']);
        $this->assertNotEmpty($row['modified_time']);
        $this->assertNull($row['deleted']);
    }

    #[Test]
    public function testFollowUpDefaultsToNotFlagged(): void
    {
        $this->recorder->recordActivity($this->activity());

        // follow_up is char(1) NOT NULL, so it is a string on any driver.
        $this->assertSame('', QueryUtils::fetchSingleValue(
            'SELECT follow_up FROM ar_activity WHERE pid = ? AND encounter = ?',
            'follow_up',
            [self::PID, self::ENC],
        ));
    }

    /**
     * Base payload for recordActivity(). Every value is a string, matching the
     * array shape documented on the method.
     *
     * The code values are deliberately synthetic. No real CPT appears here since
     * that set is AMA-licensed and is not distributed with OpenEMR, and no real
     * CARC in reason_code either. ar_activity.code_type and code are plain
     * varchars with no foreign key to code_types, so nothing validates them and
     * these tests only need the values to differ from one another.
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function activity(array $overrides = []): array
    {
        return array_merge([
            'patientId' => self::PID,
            'encounterId' => self::ENC,
            'codeType' => 'HCPCS',
            'code' => 'Z0001',
            'modifier' => '',
            'payerType' => '0',
            'postUser' => '1',
            'sessionId' => self::SESSION_ID,
            'payAmount' => '25.00',
            'adjustmentAmount' => '0',
            'memo' => '',
            'accountCode' => 'PP',
        ], $overrides);
    }

    /**
     * Normalizing here rather than asserting on the raw column values on purpose:
     * whether sequence_no arrives as int or string is a driver detail. is_numeric()
     * narrows the mixed element type so the cast is not a cast from mixed, which
     * keeps this file out of the PHPStan baseline.
     *
     * @return list<int>
     */
    private function sequenceNumbersFor(string $encounterId): array
    {
        $sequenceNumbers = [];
        $column = QueryUtils::fetchTableColumn(
            'SELECT sequence_no FROM ar_activity WHERE pid = ? AND encounter = ? ORDER BY sequence_no',
            'sequence_no',
            [self::PID, $encounterId],
        );
        foreach ($column as $value) {
            if (!is_numeric($value)) {
                throw new \RuntimeException(
                    'ar_activity.sequence_no was not numeric: ' . get_debug_type($value)
                );
            }
            $sequenceNumbers[] = (int) $value;
        }
        return $sequenceNumbers;
    }

    private function removeActivityFixtures(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM ar_activity WHERE pid = ?',
            [self::PID],
        );
    }
}
