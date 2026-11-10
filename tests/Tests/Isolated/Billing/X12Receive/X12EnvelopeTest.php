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
