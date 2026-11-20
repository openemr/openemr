<?php

/**
 * Database-backed coverage of the 837P that X125010837P::genX12837P() writes for one claim: the
 * transaction's envelope and segment count, the payer loops (2010BB for the payer billed, 2330B for
 * the other payer) and the claim and service lines. There was no test of the 837P generator before.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\X125010837P;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class X125010837PTest extends TestCase
{
    private const PID = 99990081;
    private const ENC = 99990181;
    private const PRIMARY = 99990281;
    private const SECONDARY = 99990282;
    private const FACILITY = 99990381;
    private const PARTNER = 99990481;
    private const ADDRESS_PRIMARY = 99990581;
    private const ADDRESS_SECONDARY = 99990582;
    private const SESSION = 99990681;
    private const MSP_TYPE_LOG = '*** Medicare is not the primary payer and its Secondary Medicare Type is empty.';

    /** What the last generate() call wrote to the billing log. */
    private string $log = '';

    protected function setUp(): void
    {
        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO facility (id, name, street, city, state, postal_code, phone, attn, federal_ein, facility_npi, pos_code, " .
            "billing_location, accepts_assignment) " .
            "VALUES (?, 'TEST CLINIC', '1 MAIN ST', 'TEST CITY', 'CA', '90210', '2025550100', 'BILLING', '123456789', '1234567893', '11', 1, 1)",
            [self::FACILITY]
        );
        foreach (
            [
                [self::PRIMARY, 'PRIMARY PAYER', 'PP001', self::ADDRESS_PRIMARY, '100 PAYER WAY', 'ALBANY', 'NY', '12207'],
                [self::SECONDARY, 'SECONDARY PAYER', 'SP002', self::ADDRESS_SECONDARY, '200 OTHER RD', 'BOSTON', 'MA', '02110'],
            ] as [$id, $name, $cmsId, $addressId, $street, $city, $state, $zip]
        ) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO insurance_companies (id, name, cms_id, ins_type_code) VALUES (?, ?, ?, 2)",
                [$id, $name, $cmsId]
            );
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO addresses (id, line1, city, state, zip, foreign_id) VALUES (?, ?, ?, ?, ?, ?)",
                [$addressId, $street, $city, $state, $zip, $id]
            );
        }
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, street, city, state, postal_code, date) " .
            "VALUES (?, ?, 'JANE', 'TESTPATIENT', '1950-01-01', 'Female', '5 ELM ST', 'TEST CITY', 'CA', '90210', NOW())",
            [self::PID, (string) self::PID]
        );
        foreach ([['primary', self::PRIMARY, 'TEST123'], ['secondary', self::SECONDARY, 'SEC456']] as [$type, $provider, $policy]) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO insurance_data (pid, type, provider, policy_number, date, subscriber_relationship, " .
                "subscriber_fname, subscriber_lname, subscriber_DOB, subscriber_sex, subscriber_street, subscriber_city, " .
                "subscriber_state, subscriber_postal_code) " .
                "VALUES (?, ?, ?, ?, '2020-01-01', 'self', 'JANE', 'TESTPATIENT', '1950-01-01', 'Female', '5 ELM ST', " .
                "'TEST CITY', 'CA', '90210')",
                [self::PID, $type, $provider, $policy]
            );
        }
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_encounter (pid, encounter, date, reason, facility_id, billing_facility, provider_id, pos_code) " .
            "VALUES (?, ?, '2026-09-01', 'test', ?, ?, 1, 11)",
            [self::PID, self::ENC, self::FACILITY, self::FACILITY]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO billing (date, code_type, code, pid, encounter, provider_id, user, groupname, authorized, " .
            "activity, billed, units, fee, code_text, justify, payer_id) " .
            "VALUES (NOW(), 'ICD10', 'I10', ?, ?, 1, 1, 'Default', 1, 1, 0, 1, 0, 'Hypertension', '', ?), " .
            "(NOW(), 'CPT4', '99213', ?, ?, 1, 1, 'Default', 1, 1, 0, 1, 100.00, 'Office visit', 'ICD10|I10:', ?)",
            [self::PID, self::ENC, self::PRIMARY, self::PID, self::ENC, self::PRIMARY]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO x12_partners (id, name, x12_sender_id, x12_receiver_id, processing_format) " .
            "VALUES (?, 'TEST PARTNER', 'SENDER', 'RECEIVER', 'standard')",
            [self::PARTNER]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testThePrimaryClaimIsAWellFormedTransaction(): void
    {
        [$segments, $edicount] = $this->generate();

        $this->assertTransactionIsWellFormed($segments, $edicount);
        $this->assertSame(['CLM', self::PID . '-' . self::ENC, '100.00'], array_slice($this->elements($segments, 'CLM')[0], 0, 3));
        $this->assertSame(['SV1', 'HC:99213', '100.00', 'UN', '1'], array_slice($this->elements($segments, 'SV1')[0], 0, 5));
        $this->assertSame(['HI', 'ABK:I10'], $this->elements($segments, 'HI')[0]);
    }

    public function testTheServiceLineCarriesItsBillingIdAsTheLineItemControlNumber(): void
    {
        $billingId = QueryUtils::fetchSingleValue(
            "SELECT id FROM billing WHERE pid = ? AND encounter = ? AND code = '99213'",
            'id',
            [self::PID, self::ENC]
        );
        $this->assertIsNumeric($billingId);

        [$segments, $edicount] = $this->generate();

        $this->assertTransactionIsWellFormed($segments, $edicount);
        // Loop 2400: the REF follows the service date.
        $this->assertSame(
            ['DTP*472*D8*20260901', 'REF*6R*' . $billingId],
            $this->loopAfter($segments, 'DTP*472*', 2)
        );
        $this->assertCount(1, $this->elements($segments, 'REF*6R'), 'one control number for the one service line');
    }

    public function testTheLineItemControlNumberComesBeforeTheLineNote(): void
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE billing SET notecodes = 'LINE NOTE' WHERE pid = ? AND encounter = ? AND code = '99213'",
            [self::PID, self::ENC]
        );

        [$segments, $edicount] = $this->generate();

        $this->assertTransactionIsWellFormed($segments, $edicount);
        $this->assertSame(
            $this->position($segments, 'REF*6R*') + 1,
            $this->position($segments, 'NTE*ADD*'),
            'loop 2400 lists its REF segments before its NTE'
        );
    }

    public function testAPrimaryClaimNamesThePayerIn2010BBAndTheSecondaryIn2330B(): void
    {
        [$segments] = $this->generate();

        // 2010BB: the payer billed.
        $this->assertSame(
            ['NM1*PR*2*PRIMARY PAYER*****PI*PP001', 'N3*100 PAYER WAY', 'N4*ALBANY*NY*12207'],
            $this->loopAfter($segments, 'NM1*PR*2*PRIMARY PAYER*', 3)
        );
        // 2330B: the secondary, as the other payer, after its subscriber (2330A).
        $this->assertSame(
            ['NM1*PR*2*SECONDARY PAYER*****PI*SP002', 'N3*200 OTHER RD', 'N4*BOSTON*MA*02110'],
            $this->loopAfter($segments, 'NM1*PR*2*SECONDARY PAYER*', 3)
        );
        $this->assertLessThan(
            $this->position($segments, 'NM1*PR*2*SECONDARY PAYER*'),
            $this->position($segments, 'NM1*PR*2*PRIMARY PAYER*'),
            'the payer billed (2010BB) comes before the other payer (2330B)'
        );
        $this->assertSame(['SBR', 'P'], array_slice($this->elements($segments, 'SBR')[0], 0, 2));
        $this->assertSame(['SBR', 'S'], array_slice($this->elements($segments, 'SBR')[1], 0, 2));
    }

    public function testASecondaryClaimNamesTheOtherPayerWithItsAddressInLoop2330B(): void
    {
        $this->postThePrimarysPayment();

        [$segments, $edicount] = $this->generate(self::SECONDARY);

        $this->assertTransactionIsWellFormed($segments, $edicount);
        // 2010BB is now the secondary, the payer billed.
        $this->assertSame(
            ['NM1*PR*2*SECONDARY PAYER*****PI*SP002', 'N3*200 OTHER RD', 'N4*BOSTON*MA*02110'],
            $this->loopAfter($segments, 'NM1*PR*2*SECONDARY PAYER*', 3)
        );
        // 2330B: the primary, as the other payer.
        $this->assertSame(
            ['NM1*PR*2*PRIMARY PAYER*****PI*PP001', 'N3*100 PAYER WAY', 'N4*ALBANY*NY*12207'],
            $this->loopAfter($segments, 'NM1*PR*2*PRIMARY PAYER*', 3)
        );
        $this->assertLessThan(
            $this->position($segments, 'NM1*PR*2*PRIMARY PAYER*'),
            $this->position($segments, 'NM1*PR*2*SECONDARY PAYER*'),
            'the payer billed (2010BB) comes before the other payer (2330B)'
        );
        // 2000B is the secondary's subscriber, 2320 the primary's.
        $this->assertSame(['SBR', 'S'], array_slice($this->elements($segments, 'SBR')[0], 0, 2));
        $this->assertSame(['SBR', 'P'], array_slice($this->elements($segments, 'SBR')[1], 0, 2));
        // 2320 (from the primary's SBR to the other payer's NM1): what the primary paid on the claim.
        $loop2320 = array_slice(
            $segments,
            $this->position($segments, 'SBR*P*'),
            $this->position($segments, 'NM1*PR*2*PRIMARY PAYER*') - $this->position($segments, 'SBR*P*')
        );
        $this->assertContains('AMT*D*60.00', $loop2320);
        // 2430, right after the line's SVD: what the primary paid on the line, its write-off, and
        // what is left to the patient (100.00 - 60.00 - 20.00, sent as coinsurance).
        $this->assertSame(
            ['SVD*PP001*60.00*HC:99213**1', 'CAS*CO*45*20.00', 'CAS*PR*2*20.00', 'DTP*573*D8*20260915'],
            $this->loopAfter($segments, 'SVD*', 4)
        );
        $this->assertCount(2, $this->elements($segments, 'CAS'), 'no CAS outside loop 2430');
    }

    public function testAMedicareSecondaryClaimWithoutItsTypeIsLogged(): void
    {
        // Both test payers are Medicare Part B (ins_type_code 2); neither policy has a type.
        $this->postThePrimarysPayment();
        [$segments] = $this->generate(self::SECONDARY);

        // 2000B: the SBR05 a payer other than primary requires goes out empty.
        $this->assertSame(['SBR', 'S', '18', '', '', ''], array_slice($this->elements($segments, 'SBR')[0], 0, 6));
        $this->assertStringContainsString(self::MSP_TYPE_LOG, $this->log);
    }

    public function testAMedicareSecondaryClaimWithItsTypeSendsItAndIsNotLogged(): void
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE insurance_data SET policy_type = '12' WHERE pid = ? AND type = 'secondary'",
            [self::PID]
        );

        $this->postThePrimarysPayment();
        [$segments] = $this->generate(self::SECONDARY);

        $this->assertSame('12', $this->elements($segments, 'SBR')[0][5]);
        $this->assertStringNotContainsString(self::MSP_TYPE_LOG, $this->log);
    }

    public function testAPrimaryClaimLogsAMedicareSecondaryWithoutItsTypeInLoop2320(): void
    {
        // Billing the primary: the secondary is the other subscriber (2320), and its SBR05 is empty too.
        [$segments] = $this->generate();

        $this->assertSame(['SBR', 'S'], array_slice($this->elements($segments, 'SBR')[1], 0, 2));
        $this->assertSame('', $this->elements($segments, 'SBR')[1][5]);
        $this->assertStringContainsString(self::MSP_TYPE_LOG, $this->log);
    }

    public function testASecondaryThatIsNotMedicareIsNotLogged(): void
    {
        // ins_type_code 16: commercial insurance (CI).
        QueryUtils::sqlStatementThrowException(
            "UPDATE insurance_companies SET ins_type_code = 16 WHERE id = ?",
            [self::SECONDARY]
        );

        $this->postThePrimarysPayment();
        $this->generate(self::SECONDARY);
        $secondaryLog = $this->log;
        $this->generate();

        $this->assertStringNotContainsString(self::MSP_TYPE_LOG, $secondaryLog);
        $this->assertStringNotContainsString(self::MSP_TYPE_LOG, $this->log);
    }

    public function testAPayerWithoutAnAddressGetsNoN3N4InLoops2010BBAnd2330B(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM addresses WHERE id IN (?, ?)',
            [self::ADDRESS_PRIMARY, self::ADDRESS_SECONDARY]
        );

        [$segments, $edicount] = $this->generate();

        $this->assertTransactionIsWellFormed($segments, $edicount);
        foreach (['NM1*PR*2*PRIMARY PAYER*', 'NM1*PR*2*SECONDARY PAYER*'] as $payerLoop) {
            $next = $segments[$this->position($segments, $payerLoop) + 1];
            $this->assertStringStartsNotWith('N3*', $next, 'no empty N3 after the payer name');
            $this->assertStringStartsNotWith('N4*', $next, 'no empty N4 after the payer name');
        }
        // The biller is told which fields are missing, and that the pair was left out.
        foreach (['payer', 'other insco'] as $who) {
            $this->assertStringContainsString("*** Missing $who street.", $this->log);
            $this->assertStringContainsString("*** Missing $who city.", $this->log);
        }
        $this->assertStringContainsString('*** Payer address is incomplete', $this->log);
        $this->assertStringContainsString('*** Other payer address is incomplete', $this->log);
    }

    public function testAPayerAddressWithABadZipIsLeftOutAndTheZipIsLogged(): void
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE addresses SET zip = '1234' WHERE id = ?",
            [self::ADDRESS_PRIMARY]
        );

        [$segments] = $this->generate();

        $this->assertStringStartsNotWith(
            'N3*',
            $segments[$this->position($segments, 'NM1*PR*2*PRIMARY PAYER*') + 1]
        );
        $this->assertStringContainsString('*** Payer zip is not 5 or 9 digits.', $this->log);
        $this->assertStringNotContainsString('*** Missing payer street.', $this->log);
        // The secondary's address is complete and still goes out.
        $this->assertSame(
            ['NM1*PR*2*SECONDARY PAYER*****PI*SP002', 'N3*200 OTHER RD', 'N4*BOSTON*MA*02110'],
            $this->loopAfter($segments, 'NM1*PR*2*SECONDARY PAYER*', 3)
        );
    }

    private function postThePrimarysPayment(): void
    {
        // The primary has paid 60.00 and written off 20.00, as its remit would have posted.
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO ar_session (session_id, payer_id, user_id, reference, check_date, deposit_date, pay_total, " .
            "payment_type, adjustment_code, post_to_date, patient_id, payment_method) " .
            "VALUES (?, ?, 1, 'TESTERA', '2026-09-15', '2026-09-15', 60.00, 'insurance', 'insurance_payment', " .
            "'2026-09-15', ?, 'electronic')",
            [self::SESSION, self::PRIMARY, self::PID]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, " .
            "post_user, session_id, pay_amount, adj_amount, memo, account_code, post_date) " .
            "VALUES (?, ?, 1, 'CPT4', '99213', '', 1, NOW(), 1, ?, 60.00, 0, '', 'IPP', '2026-09-15'), " .
            "(?, ?, 2, 'CPT4', '99213', '', 1, NOW(), 1, ?, 0, 20.00, 'Ins1 adjust code 45', '', '2026-09-15')",
            [self::PID, self::ENC, self::SESSION, self::PID, self::ENC, self::SESSION]
        );
    }

    /**
     * @return array{list<string>, int} the segments without their terminator, and the generator's segment count
     */
    private function generate(?int $payerId = null): array
    {
        $log = '';
        $hlCount = 1;
        $edicount = 0;
        // SEFLAG true: write the SE trailer even when gen_x12_based_on_ins_co is on (which otherwise
        // leaves it to the batch). The payer ID is numeric, as BillingClaim::getSelectedPayerId() passes it.
        $out = X125010837P::genX12837P(
            self::PID,
            self::ENC,
            self::PARTNER,
            $log,
            false,
            true,
            $hlCount,
            $edicount,
            payerId: $payerId === null ? null : (string) $payerId,
        );
        $this->assertIsInt($edicount);
        $this->assertIsString($log);
        $this->log = $log;
        $segments = array_values(array_filter(
            array_map(trim(...), explode('~', $out)),
            static fn(string $s): bool => $s !== ''
        ));
        return [$segments, $edicount];
    }

    /**
     * ST through SE: the counts agree, the control numbers match, and no segment is written without
     * data elements.
     *
     * @param list<string> $segments
     */
    private function assertTransactionIsWellFormed(array $segments, int $edicount): void
    {
        $st = array_search(true, array_map(static fn(string $s): bool => str_starts_with($s, 'ST*'), $segments), true);
        $se = array_search(true, array_map(static fn(string $s): bool => str_starts_with($s, 'SE*'), $segments), true);
        $this->assertIsInt($st, 'the claim has an ST segment');
        $this->assertIsInt($se, 'the claim has an SE segment');
        $transaction = array_slice($segments, $st, $se - $st + 1);
        $seElements = explode('*', $segments[$se]);
        $stElements = explode('*', $segments[$st]);
        $this->assertSame((string) count($transaction), $seElements[1], 'SE01 is the number of segments from ST to SE');
        $this->assertSame($stElements[2], $seElements[2], 'SE02 repeats ST02');
        foreach ($transaction as $segment) {
            $this->assertStringContainsString('*', $segment, "segment '$segment' has data elements");
        }
        $this->assertGreaterThan(0, $edicount);
    }

    /**
     * @param list<string> $segments
     * @return list<list<string>> the elements of every segment with this id
     */
    private function elements(array $segments, string $id): array
    {
        $found = [];
        foreach ($segments as $segment) {
            if (str_starts_with($segment, $id . '*')) {
                $found[] = explode('*', $segment);
            }
        }
        $this->assertNotSame([], $found, "the claim has a $id segment");
        return $found;
    }

    /**
     * @param list<string> $segments
     * @return int the index of the first segment starting with $prefix
     */
    private function position(array $segments, string $prefix): int
    {
        foreach ($segments as $i => $segment) {
            if (str_starts_with($segment, $prefix)) {
                return $i;
            }
        }
        $this->fail("no segment starts with '$prefix'");
    }

    /**
     * @param list<string> $segments
     * @return list<string> the first segment starting with $prefix and the ones after it, $count in all
     */
    private function loopAfter(array $segments, string $prefix, int $count): array
    {
        foreach ($segments as $i => $segment) {
            if (str_starts_with($segment, $prefix)) {
                return array_slice($segments, $i, $count);
            }
        }
        $this->fail("no segment starts with '$prefix'");
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM ar_activity WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM ar_session WHERE session_id = ?', [self::SESSION]);
        QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM addresses WHERE id IN (?, ?)', [self::ADDRESS_PRIMARY, self::ADDRESS_SECONDARY]);
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_companies WHERE id IN (?, ?)', [self::PRIMARY, self::SECONDARY]);
        QueryUtils::sqlStatementThrowException('DELETE FROM facility WHERE id = ?', [self::FACILITY]);
        QueryUtils::sqlStatementThrowException('DELETE FROM x12_partners WHERE id = ?', [self::PARTNER]);
    }
}
