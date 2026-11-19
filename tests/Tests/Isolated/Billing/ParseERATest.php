<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\ParseERA;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ParseERA float type correctness.
 *
 * These tests specifically cover the fix where chg/paid values were stored as
 * strings ('0') rather than floats (0.0), which caused the strict === 0.0
 * comparison in sl_eob_process.php to be permanently dead code.
 */
class ParseERATest extends TestCase
{
    // -------------------------------------------------------------------------
    // Fixtures
    // -------------------------------------------------------------------------

    /** What the last parseFixture() call's parseERA() returned: empty unless it rejected the file. */
    private string $parseError = '';

    /**
     * Minimal 835 with zero paid and a non-contractual adjustment (OA*96).
     */
    private function getZeroPaidNonContractualFixture(): string
    {
        return implode("\n", [
            'ISA*00*          *00*          *ZZ*PAYER          *ZZ*RECEIVER       *260101*1200*^*00501*000000001*0*P*:~',
            'GS*HP*PAYER*RECEIVER*20260101*1200*1*X*005010X221A1~',
            'ST*835*0001~',
            'BPR*I*0*C*NON************20260101~',
            'TRN*1*12345*1234567890~',
            'DTM*405*20260101~',
            'N1*PR*TEST PAYER~',
            'N1*PE*TEST PROVIDER*XX*1234567890~',
            'LX*1~',
            'CLP*CLAIM001*1*100.00*0*0*MC*12345~',
            'NM1*QC*1*DOE*JOHN****MI*123456789~',
            'SVC*HC:99213*100.00*0~',
            'DTM*472*20260101~',
            'CAS*OA*96*100.00~',
            'SE*15*0001~',
            'GE*1*1~',
            'IEA*1*000000001~',
        ]);
    }

    /**
     * Minimal 835 with zero paid and a contractual writeoff (CO*45).
     */
    private function getZeroPaidContractualWriteoffFixture(): string
    {
        return implode("\n", [
            'ISA*00*          *00*          *ZZ*PAYER          *ZZ*RECEIVER       *260101*1200*^*00501*000000001*0*P*:~',
            'GS*HP*PAYER*RECEIVER*20260101*1200*1*X*005010X221A1~',
            'ST*835*0001~',
            'BPR*I*0*C*NON************20260101~',
            'TRN*1*12345*1234567890~',
            'DTM*405*20260101~',
            'N1*PR*TEST PAYER~',
            'N1*PE*TEST PROVIDER*XX*1234567890~',
            'LX*1~',
            'CLP*CLAIM001*1*100.00*0*0*MC*12345~',
            'NM1*QC*1*DOE*JOHN****MI*123456789~',
            'SVC*HC:99213*100.00*0~',
            'DTM*472*20260101~',
            'CAS*CO*45*100.00~',
            'SE*15*0001~',
            'GE*1*1~',
            'IEA*1*000000001~',
        ]);
    }

    /**
     * Minimal 835 with a non-zero paid amount.
     */
    private function getNonZeroPaidFixture(): string
    {
        return implode("\n", [
            'ISA*00*          *00*          *ZZ*PAYER          *ZZ*RECEIVER       *260101*1200*^*00501*000000001*0*P*:~',
            'GS*HP*PAYER*RECEIVER*20260101*1200*1*X*005010X221A1~',
            'ST*835*0001~',
            'BPR*I*75.00*C*NON************20260101~',
            'TRN*1*12345*1234567890~',
            'DTM*405*20260101~',
            'N1*PR*TEST PAYER~',
            'N1*PE*TEST PROVIDER*XX*1234567890~',
            'LX*1~',
            'CLP*CLAIM001*1*100.00*75.00*0*MC*12345~',
            'NM1*QC*1*DOE*JOHN****MI*123456789~',
            'SVC*HC:99213*100.00*75.00~',
            'DTM*472*20260101~',
            'CAS*CO*45*25.00~',
            'SE*15*0001~',
            'GE*1*1~',
            'IEA*1*000000001~',
        ]);
    }

    /**
     * Minimal 835 whose 1000A payer loop is built from the given N1*PR segment
     * and an optional REF segment, for exercising payer identification.
     */
    private function getPayerLoopFixture(string $n1pr, ?string $ref = null): string
    {
        $segments = [
            'ISA*00*          *00*          *ZZ*PAYER          *ZZ*RECEIVER       *260101*1200*^*00501*000000001*0*P*:~',
            'GS*HP*PAYER*RECEIVER*20260101*1200*1*X*005010X221A1~',
            'ST*835*0001~',
            'BPR*I*75.00*C*NON************20260101~',
            'TRN*1*12345*1234567890~',
            'DTM*405*20260101~',
            $n1pr,
        ];

        if ($ref !== null) {
            $segments[] = $ref;
        }

        return implode("\n", array_merge($segments, [
            'N1*PE*TEST PROVIDER*XX*1234567890~',
            'LX*1~',
            'CLP*CLAIM001*1*100.00*75.00*0*MC*12345~',
            'NM1*QC*1*DOE*JOHN****MI*123456789~',
            'SVC*HC:99213*100.00*75.00~',
            'DTM*472*20260101~',
            'CAS*CO*45*25.00~',
            sprintf('SE*%d*0001~', $ref === null ? 13 : 14),
            'GE*1*1~',
            'IEA*1*000000001~',
        ]));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Write fixture to a temp file, parse it, clean up, return $out.
     *
     * @return array<int|string, mixed>
     */
    private function parseFixture(string $content): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'era_test_');
        file_put_contents($tmp, $content);

        /** @var array<int|string, mixed> $out */
        $out = [];
        $cb = function (array &$o) use (&$out): void {
            $out = $o;
        };

        $this->parseError = ParseERA::parseERA($tmp, $cb);
        unlink($tmp);

        return $out;
    }

    /**
     * Extract first svc row from parsed output.
     *
     * @param array<int|string, mixed> $out
     * @return array<string, mixed>
     */
    private function firstSvc(array $out): array
    {
        /** @var array<int, mixed> $svcs */
        $svcs = $out['svc'];
        /** @var array<string, mixed> $svc */
        $svc = $svcs[0];
        return $svc;
    }

    /**
     * Extract first adj row from a svc row.
     *
     * @param array<string, mixed> $svc
     * @return array<string, mixed>
     */
    private function firstAdj(array $svc): array
    {
        /** @var array<int, mixed> $adjs */
        $adjs = $svc['adj'];
        /** @var array<string, mixed> $adj */
        $adj = $adjs[0];
        return $adj;
    }

    // -------------------------------------------------------------------------
    // Type tests — chg/paid are floats, not strings
    // -------------------------------------------------------------------------

    public function testPaidIsFloat(): void
    {
        $svc = $this->firstSvc($this->parseFixture($this->getNonZeroPaidFixture()));
        $this->assertIsFloat($svc['paid'], 'paid should be float, not string');
    }

    public function testChgIsFloat(): void
    {
        $svc = $this->firstSvc($this->parseFixture($this->getNonZeroPaidFixture()));
        $this->assertIsFloat($svc['chg'], 'chg should be float, not string');
    }

    public function testPaidValueParsedCorrectly(): void
    {
        $svc = $this->firstSvc($this->parseFixture($this->getNonZeroPaidFixture()));
        $this->assertSame(75.0, $svc['paid']);
    }

    public function testChgValueParsedCorrectly(): void
    {
        $svc = $this->firstSvc($this->parseFixture($this->getNonZeroPaidFixture()));
        $this->assertSame(100.0, $svc['chg']);
    }

    // -------------------------------------------------------------------------
    // The quiet bug fix: '0' string vs 0.0 float strict comparison
    // -------------------------------------------------------------------------

    /**
     * Core regression test.
     *
     * Old behavior: paid was stored as string '0', so ($svc['paid'] === 0.0)
     * was always false — error detection in sl_eob_process was dead code.
     *
     * New behavior: paid is float 0.0, so the comparison is live.
     */
    public function testZeroPaidIsFloatNotString(): void
    {
        $svc = $this->firstSvc($this->parseFixture($this->getZeroPaidNonContractualFixture()));
        $this->assertIsFloat($svc['paid'], 'zero paid should be float 0.0, not string "0"');
        $this->assertSame(0.0, $svc['paid']);
    }

    // -------------------------------------------------------------------------
    // Contractual writeoff (CO*45) — paid 0.0 but should NOT be flagged
    // -------------------------------------------------------------------------

    public function testContractualWriteoffGroupCode(): void
    {
        $svc = $this->firstSvc($this->parseFixture($this->getZeroPaidContractualWriteoffFixture()));
        $adj = $this->firstAdj($svc);

        $this->assertSame(0.0, $svc['paid']);
        $this->assertSame('CO', $adj['group_code']);
        $this->assertSame('45', $adj['reason_code']);
    }

    public function testContractualWriteoffAmountIsFloat(): void
    {
        $svc = $this->firstSvc($this->parseFixture($this->getZeroPaidContractualWriteoffFixture()));
        $adj = $this->firstAdj($svc);

        $this->assertIsFloat($adj['amount']);
        $this->assertSame(100.0, $adj['amount']);
    }

    // -------------------------------------------------------------------------
    // Non-contractual adjustment (OA*96) — zero paid, should be detectable
    // -------------------------------------------------------------------------

    public function testNonContractualAdjGroupCode(): void
    {
        $svc = $this->firstSvc($this->parseFixture($this->getZeroPaidNonContractualFixture()));
        $adj = $this->firstAdj($svc);

        $this->assertSame('OA', $adj['group_code']);
        $this->assertSame('96', $adj['reason_code']);
    }

    public function testNonContractualAdjAmountIsFloat(): void
    {
        $svc = $this->firstSvc($this->parseFixture($this->getZeroPaidNonContractualFixture()));
        $adj = $this->firstAdj($svc);

        $this->assertIsFloat($adj['amount']);
        $this->assertSame(100.0, $adj['amount']);
    }

    // -------------------------------------------------------------------------
    // Payer identification — REF*2U overrides N104
    // -------------------------------------------------------------------------

    /**
     * With no REF*2U present, payer_id comes from N104.
     */
    public function testPayerIdFallsBackToN104(): void
    {
        $out = $this->parseFixture($this->getPayerLoopFixture('N1*PR*TEST PAYER*XV*N104ID~'));
        $this->assertSame('N104ID', $out['payer_id']);
    }

    /**
     * REF*2U overwrites the N104 value, as the N1*PR handler documents.
     */
    public function testRef2uOverridesN104(): void
    {
        $out = $this->parseFixture(
            $this->getPayerLoopFixture('N1*PR*TEST PAYER*XV*N104ID~', 'REF*2U*REFPAYERID~')
        );
        $this->assertSame('REFPAYERID', $out['payer_id']);
    }

    /**
     * Regression test.
     *
     * The qualifier check was written as trim($seg[1] == '2U'), so trim()
     * received the boolean result of the comparison rather than the segment
     * value. A padded qualifier compares false under PHP 8 string semantics,
     * trim(false) is '', and the override silently never happened — leaving
     * whatever N104 carried as the payer id.
     */
    public function testPaddedRef2uQualifierStillOverridesN104(): void
    {
        $out = $this->parseFixture(
            $this->getPayerLoopFixture('N1*PR*TEST PAYER*XV*N104ID~', 'REF* 2U *REFPAYERID~')
        );
        $this->assertSame('REFPAYERID', $out['payer_id']);
    }

    /**
     * A REF carrying some other qualifier is ignored, leaving N104 in place.
     * REF*EV is deliberately not used here: it is claimed by an earlier branch
     * that rejects it inside a loop.
     */
    public function testNonPayerRefQualifierIsIgnored(): void
    {
        $out = $this->parseFixture(
            $this->getPayerLoopFixture('N1*PR*TEST PAYER*XV*N104ID~', 'REF*TJ*SOMEOTHERID~')
        );
        $this->assertSame('N104ID', $out['payer_id']);
    }

    // -------------------------------------------------------------------------
    // Line item control number — REF*6R in loop 2110

    /**
     * Minimal 835 with two service lines for the same code; each line ends with the given
     * segments (after its DTM).
     *
     * @param list<string> $first
     * @param list<string> $second
     */
    private function getTwoServiceLineFixture(array $first, array $second): string
    {
        return implode("\n", [
            'ISA*00*          *00*          *ZZ*PAYER          *ZZ*RECEIVER       *260101*1200*^*00501*000000001*0*P*:~',
            'GS*HP*PAYER*RECEIVER*20260101*1200*1*X*005010X221A1~',
            'ST*835*0001~',
            'BPR*I*60.00*C*NON************20260101~',
            'TRN*1*12345*1234567890~',
            'DTM*405*20260101~',
            'N1*PR*TEST PAYER~',
            'N1*PE*TEST PROVIDER*XX*1234567890~',
            'LX*1~',
            'CLP*CLAIM001*1*200.00*60.00*0*MC*12345~',
            'NM1*QC*1*DOE*JOHN****MI*123456789~',
            'SVC*HC:70471:26*100.00*40.00~',
            'DTM*472*20260101~',
            ...$first,
            'SVC*HC:70471:26*100.00*20.00~',
            'DTM*472*20260101~',
            ...$second,
            'SE*' . (14 + count($first) + count($second)) . '*0001~',
            'GE*1*1~',
            'IEA*1*000000001~',
        ]);
    }

    /**
     * @param array<int|string, mixed> $out
     * @return list<?string> line_ctrl of each service line, null where the remit gave none
     */
    private function lineControlNumbers(array $out): array
    {
        $this->assertSame('', $this->parseError, 'the parser accepts the fixture');
        $this->assertIsArray($out['svc']);
        $found = [];
        foreach ($out['svc'] as $svc) {
            $this->assertIsArray($svc);
            $lineCtrl = $svc['line_ctrl'] ?? null;
            $this->assertTrue($lineCtrl === null || is_string($lineCtrl));
            $found[] = $lineCtrl;
        }
        return $found;
    }

    /**
     * Each service line keeps the control number that follows it.
     */
    public function testRef6rIsKeptOnItsServiceLine(): void
    {
        $out = $this->parseFixture($this->getTwoServiceLineFixture(['REF*6R*501~'], ['REF*6R*502~']));
        $this->assertSame(['501', '502'], $this->lineControlNumbers($out));
    }

    /**
     * A line without REF*6R has no line_ctrl, and a later line's number does not move up to it.
     */
    public function testServiceLineWithoutRef6rHasNoControlNumber(): void
    {
        $out = $this->parseFixture($this->getTwoServiceLineFixture([], ['REF* 6R * 502 ~']));
        $this->assertSame([null, '502'], $this->lineControlNumbers($out));
    }

    /**
     * Other service-level REFs (here REF*LU, a location number) are
     * still ignored and do not stand in for REF*6R.
     */
    public function testOtherServiceLevelRefIsNotAControlNumber(): void
    {
        $out = $this->parseFixture($this->getTwoServiceLineFixture(['REF*LU*11~'], ['REF*LU*11~', 'REF*6R*502~']));
        $this->assertSame([null, '502'], $this->lineControlNumbers($out));
    }

    /**
     * @return list<array<mixed>> the provider-level adjustments seen by the last claim of each check
     */
    private function plbByCheck(string $content): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'era_plb_');
        $this->assertNotFalse($tmp);
        file_put_contents($tmp, $content);
        $byCheck = [];
        try {
            $error = ParseERA::parseERA($tmp, static function (array $out) use (&$byCheck): void {
                $check = is_string($out['check_number'] ?? null) ? $out['check_number'] : '';
                $byCheck[$check] = is_array($out['plb'] ?? null) ? $out['plb'] : [];
            });
        } finally {
            unlink($tmp);
        }
        $this->assertSame('', $error);
        return array_values($byCheck);
    }

    private function getTwoCheckFixture(string $firstPlb): string
    {
        return "ISA*00*          *00*          *ZZ*PAYER          *ZZ*RECEIVER       *261020*1200*^*00501*000000042*0*P*:~"
            . "GS*HP*PAYER*RECEIVER*20261020*1200*1*X*005010X221A1~"
            . "ST*835*0001~BPR*I*60.00*C*ACH************20261020~TRN*1*CHK1*1234567890~N1*PR*TEST PAYER~"
            . "N1*PE*TEST PROVIDER*XX*1234567890~LX*1~CLP*10-20*1*100.00*70.00*0*MC*ICN1~NM1*QC*1*DOE*JOHN~"
            . "SVC*HC:99213*100.00*70.00~" . $firstPlb . "SE*" . ($firstPlb === '' ? 10 : 11) . "*0001~"
            . "ST*835*0002~BPR*I*25.00*C*ACH************20261020~TRN*1*CHK2*1234567890~N1*PR*TEST PAYER~"
            . "N1*PE*TEST PROVIDER*XX*1234567890~LX*1~CLP*11-21*1*100.00*25.00*0*MC*ICN2~NM1*QC*1*DOE*JANE~"
            . "SVC*HC:99213*100.00*25.00~SE*10*0002~"
            . "GE*2*1~IEA*1*000000042~";
    }

    /**
     * Each PLB adjustment is kept with its reason code (without the reference
     * that follows it in the composite) and amount, for the check it is in only.
     */
    public function testProviderLevelAdjustmentsAreKeptForTheirCheck(): void
    {
        $plb = $this->plbByCheck($this->getTwoCheckFixture('PLB*1234567890*20261231*WO:ICN9*12.50*L6*-2.50~'));
        $this->assertSame(
            [[['reason' => 'WO', 'amount' => 12.5], ['reason' => 'L6', 'amount' => -2.5]], []],
            $plb
        );
    }

    public function testACheckWithoutProviderLevelAdjustmentsHasNone(): void
    {
        $this->assertSame([[], []], $this->plbByCheck($this->getTwoCheckFixture('')));
    }
}
