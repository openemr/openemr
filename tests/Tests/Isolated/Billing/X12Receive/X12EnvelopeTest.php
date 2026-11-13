<?php

/**
 * Isolated tests for X12Envelope and X12FileType.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\X12Receive;

use OpenEMR\Billing\X12Receive\X12Envelope;
use OpenEMR\Billing\X12Receive\X12FileType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class X12EnvelopeTest extends TestCase
{
    private const ISA = 'ISA*00*          *00*          *ZZ*PAYER          *ZZ*RECEIVER       *260101*1200*^*00501*000000001*0*P*:~';

    /**
     * @return array<string, array{string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function headProvider(): array
    {
        return [
            '835 on one line' => [self::ISA . 'GS*HP*PAYER*RECEIVER*20260101*1200*1*X*005010X221A1~ST*835*0001~', 'HP'],
            '277 with line breaks' => [self::ISA . "\r\nGS*HN*PAYER*RECEIVER*20260101*1200*1*X*005010X214~\r\n", 'HN'],
            '999 acknowledgment' => [self::ISA . "\nGS*FA*PAYER*RECEIVER*20260101*1200*1*X*005010X231A1~", 'FA'],
            'other separator' => [str_replace('*', '|', self::ISA) . 'GS|HP|PAYER|RECEIVER|20260101|1200|1|X|005010X221A1~', 'HP'],
            'TA1 before the group' => [self::ISA . 'TA1*000000001*260101*1200*A*000~GS*FA*PAYER*RECEIVER*20260101*1200*1*X*005010X231A1~', 'FA'],
            'TA1 only' => [self::ISA . 'TA1*000000001*260101*1200*A*000~IEA*0*000000001~', 'TA1'],
            'plain text' => ['hello, not x12', null],
            'ISA only' => [self::ISA, null],
            'ISA then something else' => [self::ISA . 'ST*835*0001~BPR*I*0~', null],
            'empty group code' => [self::ISA . 'GS**PAYER*RECEIVER~', null],
            'empty' => ['', null],
        ];
    }

    #[DataProvider('headProvider')]
    public function testGroupCode(string $head, ?string $expected): void
    {
        $this->assertSame($expected, X12Envelope::groupCode($head));
    }

    /**
     * @return array<string, array{string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function tailProvider(): array
    {
        return [
            'ends with IEA' => ['SE*2*0001~GE*1*1~IEA*1*000000001~', true],
            'IEA then a line ending' => ["GE*1*1~\r\nIEA*1*000000001~\r\n", true],
            'cut off inside the IEA' => ['GE*1*1~IEA*1*0000', false],
            'cut off before the IEA' => ['SE*2*0001~GE*1*1~', false],
            'no terminator at all' => ['IEA', false],
            'empty' => ['', false],
        ];
    }

    #[DataProvider('tailProvider')]
    public function testIsComplete(string $tail, bool $expected): void
    {
        $this->assertSame($expected, X12Envelope::isComplete(self::ISA, $tail));
    }

    public function testTextThatIsNotX12IsNeverComplete(): void
    {
        $this->assertFalse(X12Envelope::isComplete('hello', 'IEA*1*000000001~'));
    }

    public function testAnAcknowledgmentFolderAlsoTakesAnInterchangeAckAndOthersDoNot(): void
    {
        $this->assertTrue(X12FileType::Acknowledgment999->accepts('FA'));
        $this->assertTrue(X12FileType::Acknowledgment999->accepts('TA1'));
        $this->assertFalse(X12FileType::ClaimStatus277->accepts('TA1'));
        $this->assertFalse(X12FileType::Remittance835->accepts(null));
        $both = [X12FileType::Remittance835, X12FileType::ClaimStatus277];
        $this->assertSame(X12FileType::ClaimStatus277, X12FileType::forGroupCode('HN', $both));
        $this->assertNull(X12FileType::forGroupCode('FA', $both));
    }

    public function testEachFileTypeHasItsGroupCodeFolderColumnAndLabel(): void
    {
        $this->assertSame('HP', X12FileType::Remittance835->groupCode());
        $this->assertSame('HN', X12FileType::ClaimStatus277->groupCode());
        $this->assertSame('FA', X12FileType::Acknowledgment999->groupCode());
        $this->assertSame('x12_sftp_receive_dir_835', X12FileType::Remittance835->partnerColumn());
        $this->assertSame('x12_sftp_receive_dir_277', X12FileType::ClaimStatus277->partnerColumn());
        $this->assertSame('x12_sftp_receive_dir_999', X12FileType::Acknowledgment999->partnerColumn());
        $this->assertSame(['835', '277', '999'], array_map(static fn(X12FileType $t): string => $t->label(), X12FileType::cases()));
    }
}
