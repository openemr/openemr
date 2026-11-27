<?php

/**
 * Tests for the row-per-claim fix in edih_277_csv_data(), without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\X12File;
use PHPUnit\Framework\TestCase;

class Edih277CsvDataTest extends TestCase
{
    private const ISA = 'ISA*00*          *00*          *ZZ*CLEARINGHOUSE  *ZZ*SUBMITTER      '
        . '*260127*0412*^*00501*000000101*0*P*:~';

    private const PAYER = 'NM1*PR*2*EXAMPLE HEALTH PLAN*****PI*54321~';

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/edih_csv_stubs.php';
        require_once __DIR__ . '/../../../../../library/edihistory/edih_csv_parse.php';
    }

    private static function ca(string $controlNumber, string $claims): string
    {
        return 'ST*277*' . $controlNumber . '*005010X214~'
            . 'BHT*0085*08*' . $controlNumber . '*20260127*0412*TH~'
            . 'HL*1**20*1~' . self::PAYER
            . 'HL*2*1*21*1~NM1*41*2*EXAMPLE CLINIC*****46*555~'
            . 'HL*3*2*19*1~NM1*85*2*EXAMPLE CLINIC*****XX*1999999999~'
            . $claims
            . 'SE*99*' . $controlNumber . '~';
    }

    private static function interchange(string $gs01, string ...$transactions): string
    {
        return self::ISA
            . 'GS*' . $gs01 . '*CLEARINGHOUSE*SUBMITTER*20260127*0412*101*X*005010X214~'
            . implode('', $transactions)
            . 'GE*' . count($transactions) . '*101~IEA*1*000000101~';
    }

    private function parse(string $text): array
    {
        $path = sys_get_temp_dir() . '/edih277_' . getmypid() . '_' . mt_rand() . '.277';
        file_put_contents($path, $text);
        try {
            return edih_277_csv_data(new X12File($path));
        } finally {
            unlink($path);
        }
    }

    private function claims(array $ret): array
    {
        return $ret['000000101']['claim'];
    }

    public function testSeveralClaimsUnderOnePatientEachKeepTheirOwnRow(): void
    {
        $ret = $this->parse(self::interchange(
            'HN',
            self::ca(
                '0001',
                'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~'
                . 'TRN*2*100-200~STC*A1:19*20260128*WQ*257~DTP*472*D8*20260120~'
                . 'TRN*2*100-201~STC*A7:562:85*20260128*U*220~DTP*472*D8*20260121~'
                . 'HL*5*3*PT~NM1*QC*1*ROE*RICHARD****MI*MEMBER2~'
                . 'TRN*2*300-400~STC*A2:20*20260128*WQ*90~DTP*472*D8*20260122~'
            )
        ));

        $rows = $this->claims($ret);
        $this->assertCount(3, $rows);
        $this->assertSame(['100-200', '100-201', '300-400'], array_column($rows, 'CLM01'));
        $this->assertSame(['A1:19', 'A7:562:85', 'A2:20'], array_column($rows, 'Status'));
        $this->assertSame(['20260120', '20260121', '20260122'], array_column($rows, 'SvcDate'));
        $this->assertSame('DOE, JANE', $rows[0]['PtName']);
        $this->assertSame('DOE, JANE', $rows[1]['PtName']);
        $this->assertSame('ROE, RICHARD', $rows[2]['PtName']);
        $this->assertSame('0001', $rows[0]['BHT03']);
        $this->assertSame('0001', $rows[2]['BHT03']);
        $this->assertSame('EXAMPLE HEALTH PLAN', $rows[0]['Payer']);
        $this->assertSame('f277', $ret['000000101']['type']);
    }

    public function testBatchOnlyRejectionStillGetsOneRow(): void
    {
        $ret = $this->parse(self::interchange(
            'HN',
            'ST*277*0001*005010X214~BHT*0085*08*0001*20260127*0412*TH~'
            . 'HL*1**20*1~' . self::PAYER
            . 'HL*2*1*21*1~NM1*41*2*EXAMPLE CLINIC*****46*555~STC*A3:21*20260127*U*477~'
            . 'SE*7*0001~'
        ));

        $rows = $this->claims($ret);
        $this->assertCount(1, $rows);
        $this->assertSame('A3:21', $rows[0]['Status']);
        $this->assertSame('', $rows[0]['CLM01']);
        $this->assertSame('0001', $rows[0]['BHT03']);
    }

    public function testResponseTo276KeepsTraceAndPayerNumberPerClaim(): void
    {
        $ret = $this->parse(self::interchange(
            'HN',
            'ST*277*0001*005010X212~BHT*0010*08*0001*20260127*0412*DG~'
            . 'HL*1**20*1~' . self::PAYER
            . 'HL*2*1*21*1~NM1*41*2*EXAMPLE CLINIC*****46*555~'
            . 'HL*3*2*22*0~NM1*IL*1*DOE*JANE****MI*MEMBER1~'
            . 'TRN*2*100-200~STC*F1:65*20260210**257~REF*EJ*PAYER99~'
            . 'TRN*2*100-201~STC*F2:88*20260210**220~REF*EJ*PAYER100~'
            . 'SE*11*0001~'
        ));

        $rows = $this->claims($ret);
        $this->assertCount(2, $rows);
        $this->assertSame(['100-200', '100-201'], array_column($rows, 'Trace'));
        $this->assertSame(['PAYER99', 'PAYER100'], array_column($rows, 'CLM01'));
        $this->assertSame(['F1:65', 'F2:88'], array_column($rows, 'Status'));
    }

    public function testFileTotalsComeFromTheReceiverLevel(): void
    {
        $ret = $this->parse(self::interchange(
            'HN',
            'ST*277*0001*005010X214~BHT*0085*08*0001*20260127*0412*TH~'
            . 'HL*1**20*1~' . self::PAYER
            . 'HL*2*1*21*1~NM1*41*2*EXAMPLE CLINIC*****46*555~'
            . 'QTY*90*3~QTY*AA*1~AMT*YU*700~AMT*YY*220~'
            . 'HL*3*2*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~TRN*2*100-200~STC*A2:20*20260128*WQ*257~'
            . 'SE*12*0001~'
        ));

        $file = $ret['000000101']['file'][0];
        $this->assertSame(3, $file['Accept']);
        $this->assertSame(1, $file['Reject']);
        $this->assertSame(700, $file['AccAmt']);
        $this->assertSame(220, $file['RejAmt']);
    }

    public function testTwoTransactionsAccumulateRows(): void
    {
        $ret = $this->parse(self::interchange(
            'HN',
            self::ca('0001', 'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~TRN*2*100-200~STC*A1:19*20260128*WQ*257~'),
            self::ca('0002', 'HL*4*3*PT~NM1*QC*1*ROE*RICHARD****MI*MEMBER2~TRN*2*300-400~STC*A2:20*20260128*WQ*90~')
        ));

        $rows = $this->claims($ret);
        $this->assertCount(2, $rows);
        $this->assertSame(['0001', '0002'], array_column($rows, 'BHT03'));
        $this->assertSame(['100-200', '300-400'], array_column($rows, 'CLM01'));
    }

    public function test276RequestStillMakesOneRowPerTransaction(): void
    {
        $ret = $this->parse(self::interchange(
            'HR',
            'ST*276*0001*005010X212~BHT*0010*13*REQ1*20260127*0412~'
            . 'HL*1**20*1~' . self::PAYER
            . 'HL*2*1*21*1~NM1*41*2*EXAMPLE CLINIC*****46*555~'
            . 'HL*3*2*22*0~NM1*IL*1*DOE*JANE****MI*MEMBER1~TRN*2*100-200~REF*1K*PAYER99~'
            . 'SE*9*0001~'
        ));

        $rows = $this->claims($ret);
        $this->assertCount(1, $rows);
        $this->assertSame('100-200', $rows[0]['Trace']);
        $this->assertSame('PAYER99', $rows[0]['ClaimID']);
        $this->assertSame('0000001010001', $rows[0]['BHT03']);
        $this->assertSame('f276', $ret['000000101']['type']);
    }
}