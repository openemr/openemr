<?php

/**
 * Database tests for the payer claim control number (CLP07) on ERA postings.
 *
 * A claim the payer paid nothing on posts only adjustments, so unless the
 * adjustment rows carry CLP07 the encounter has no prior-payer claim control
 * number for its secondary claim (837P 2330B REF*F8).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated The SUT is deprecated
 */
class SLEOBPayerClaimNumberTest extends TestCase
{
    /**
     * Synthetic ids, clear of the demo data. ar_activity has no foreign keys, so
     * no patient, encounter or session rows are needed.
     */
    private const PID = 99990011;
    private const ENC = 99990111;
    private const SESSION_ID = 99990211;

    protected function setUp(): void
    {
        SessionWrapperFactory::getInstance()->getActiveSession()->set(
            'authUserID',
            QueryUtils::fetchSingleValue('SELECT id FROM users ORDER BY id LIMIT 1', 'id')
        );
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testAdjustmentStoresThePayerClaimNumber(): void
    {
        SLEOB::arPostAdjustment(
            patient_id: (string) self::PID,
            encounter_id: (string) self::ENC,
            session_id: (string) self::SESSION_ID,
            amount: '80.00',
            code: '99213',
            payer_type: '1',
            reason: 'Adjust code 45',
            codetype: 'CPT4',
            payer_claim_number: 'ICNTEST0000001',
        );

        $this->assertSame(['ICNTEST0000001'], $this->payerClaimNumbers());
    }

    public function testZeroPaidClaimStillRecordsThePayerClaimNumber(): void
    {
        // What the ERA posting writes for a claim paid 0: a zero-dollar
        // patient-responsibility note and a contractual adjustment, no payment.
        SLEOB::arPostAdjustment(
            patient_id: (string) self::PID,
            encounter_id: (string) self::ENC,
            session_id: (string) self::SESSION_ID,
            amount: '0',
            code: '99213',
            payer_type: '1',
            reason: 'Ins1 dedbl: 20.00',
            codetype: 'CPT4',
            payer_claim_number: 'ICNTEST0000002',
        );
        SLEOB::arPostAdjustment(
            patient_id: (string) self::PID,
            encounter_id: (string) self::ENC,
            session_id: (string) self::SESSION_ID,
            amount: '80.00',
            code: '99213',
            payer_type: '1',
            reason: 'Adjust code 45',
            codetype: 'CPT4',
            payer_claim_number: 'ICNTEST0000002',
        );

        $this->assertSame(['ICNTEST0000002', 'ICNTEST0000002'], $this->payerClaimNumbers());

        // The lookup Claim::otherPayerClaimControlNumber() makes for a secondary claim.
        $this->assertSame('ICNTEST0000002', QueryUtils::fetchSingleValue(
            "SELECT payer_claim_number FROM ar_activity WHERE pid = ? AND encounter = ? AND payer_type = 1 " .
            "AND payer_claim_number IS NOT NULL AND payer_claim_number != '' AND deleted IS NULL " .
            "ORDER BY post_time DESC, sequence_no DESC LIMIT 1",
            'payer_claim_number',
            [self::PID, self::ENC]
        ));
    }

    public function testAdjustmentWithoutAPayerClaimNumberStoresNull(): void
    {
        SLEOB::arPostAdjustment(
            patient_id: (string) self::PID,
            encounter_id: (string) self::ENC,
            session_id: (string) self::SESSION_ID,
            amount: '5.00',
            code: '99213',
            payer_type: '1',
            reason: 'manual',
        );

        $this->assertSame([null], $this->payerClaimNumbers());
    }

    /**
     * @return list<?string>
     */
    private function payerClaimNumbers(): array
    {
        $values = [];
        foreach (
            QueryUtils::fetchRecords(
                'SELECT payer_claim_number FROM ar_activity WHERE pid = ? AND encounter = ? ORDER BY sequence_no',
                [self::PID, self::ENC]
            ) as $row
        ) {
            $value = $row['payer_claim_number'] ?? null;
            $values[] = is_string($value) ? $value : null;
        }
        return $values;
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM ar_activity WHERE pid = ?', [self::PID]);
    }
}
