<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Billing;

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

    /**
     * Minimal 835 with zero paid and a non-contractual adjustment (OA*96).
     * This exercises the path where $svc['paid'] === 0.0 should be TRUE,
     * meaning the error detection branch in sl_eob_process is reachable.
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

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Write fixture to a temp file, parse it, clean up, return $out.
     *
     * @return array<string, mixed>
     */
    private function parseFixture(string $content): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'era_test_');
        file_put_contents($tmp, $content);

        /** @var array<string, mixed> $out */
        $out = [];
        $cb = function (array &$o, string $action) use (&$out): void {
            $out = $o;
        };

        ParseERA::parseERA($tmp, $cb);
        unlink($tmp);

        return $out;
    }

    // -------------------------------------------------------------------------
    // Type tests — chg/paid are floats, not strings
    // -------------------------------------------------------------------------

    public function testPaidIsFloat(): void
    {
        $out = $this->parseFixture($this->getNonZeroPaidFixture());
        /** @var array<string, mixed> $svc */
        $svc = $out['svc'][0];
        $this->assertIsFloat($svc['paid'], 'paid should be float, not string');
    }

    public function testChgIsFloat(): void
    {
        $out = $this->parseFixture($this->getNonZeroPaidFixture());
        /** @var array<string, mixed> $svc */
        $svc = $out['svc'][0];
        $this->assertIsFloat($svc['chg'], 'chg should be float, not string');
    }

    public function testPaidValueParsedCorrectly(): void
    {
        $out = $this->parseFixture($this->getNonZeroPaidFixture());
        /** @var array<string, mixed> $svc */
        $svc = $out['svc'][0];
        $this->assertSame(75.0, $svc['paid']);
    }

    public function testChgValueParsedCorrectly(): void
    {
        $out = $this->parseFixture($this->getNonZeroPaidFixture());
        /** @var array<string, mixed> $svc */
        $svc = $out['svc'][0];
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
        $out = $this->parseFixture($this->getZeroPaidNonContractualFixture());
        /** @var array<string, mixed> $svc */
        $svc = $out['svc'][0];

        $paid = $svc['paid'];
        $this->assertIsFloat($paid, 'zero paid should be float 0.0, not string "0"');
        $this->assertSame(0.0, $paid);
    }

    // -------------------------------------------------------------------------
    // Contractual writeoff (CO*45) — paid 0.0 but should NOT be flagged
    // -------------------------------------------------------------------------

    public function testContractualWriteoffGroupCode(): void
    {
        $out = $this->parseFixture($this->getZeroPaidContractualWriteoffFixture());
        /** @var array<string, mixed> $svc */
        $svc = $out['svc'][0];
        /** @var array<string, mixed> $adj */
        $adj = $svc['adj'][0];

        $this->assertSame(0.0, $svc['paid']);
        $this->assertSame('CO', $adj['group_code']);
        $this->assertSame('45', $adj['reason_code']);
    }

    public function testContractualWriteoffAmountIsFloat(): void
    {
        $out = $this->parseFixture($this->getZeroPaidContractualWriteoffFixture());
        /** @var array<string, mixed> $svc */
        $svc = $out['svc'][0];
        /** @var array<string, mixed> $adj */
        $adj = $svc['adj'][0];

        $this->assertIsFloat($adj['amount']);
        $this->assertSame(100.0, $adj['amount']);
    }

    // -------------------------------------------------------------------------
    // Non-contractual adjustment (OA*96) — zero paid, should be detectable
    // -------------------------------------------------------------------------

    public function testNonContractualAdjGroupCode(): void
    {
        $out = $this->parseFixture($this->getZeroPaidNonContractualFixture());
        /** @var array<string, mixed> $svc */
        $svc = $out['svc'][0];
        /** @var array<string, mixed> $adj */
        $adj = $svc['adj'][0];

        $this->assertSame('OA', $adj['group_code']);
        $this->assertSame('96', $adj['reason_code']);
    }

    public function testNonContractualAdjAmountIsFloat(): void
    {
        $out = $this->parseFixture($this->getZeroPaidNonContractualFixture());
        /** @var array<string, mixed> $svc */
        $svc = $out['svc'][0];
        /** @var array<string, mixed> $adj */
        $adj = $svc['adj'][0];

        $this->assertIsFloat($adj['amount']);
        $this->assertSame(100.0, $adj['amount']);
    }

    // -------------------------------------------------------------------------
    // Artificial 'Claim' row — chg/paid initialized as 0.0 not '0'
    // -------------------------------------------------------------------------

    public function testArtificialClaimRowTypesAreFloat(): void
    {
        $out = $this->parseFixture($this->getZeroPaidNonContractualFixture());
        /** @var array<int, mixed> $svcs */
        $svcs = $out['svc'];

        foreach ($svcs as $svc) {
            /** @var array<string, mixed> $svc */
            if ($svc['code'] === 'Claim') {
                $this->assertIsFloat($svc['chg'], 'artificial Claim chg must be float');
                $this->assertIsFloat($svc['paid'], 'artificial Claim paid must be float');
                $this->assertSame(0.0, $svc['chg']);
                $this->assertSame(0.0, $svc['paid']);
            }
        }
    }
}
