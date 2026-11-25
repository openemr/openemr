<?php

/**
 * Isolated tests for ClaimStatusReader and the claim status report it returns.
 *
 * The 277 files are built here from made-up names, numbers and amounts, to keep PHI out of
 * the repository. They follow the layout a clearinghouse returns for an 837: one transaction
 * in which the clearinghouse is the information source and one in which the payer is.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\ClaimStatusReader;
use OpenEMR\Billing\EdiHistory\ClaimStatusReport;
use OpenEMR\Billing\EdiHistory\ClaimStatusSourceKind;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClaimStatusReaderTest extends TestCase
{
    private const ISA = 'ISA*00*          *00*          *ZZ*CLEARINGHOUSE  *ZZ*SUBMITTER      '
        . '*260127*0412*^*00501*000000101*0*P*:~';

    /**
     * The segments above the claims of one 277CA transaction.
     *
     * @param string $source the NM1 and following segments of the information source
     */
    private static function transaction(string $controlNumber, string $source, string $claims): string
    {
        return 'ST*277*' . $controlNumber . '*005010X214~'
            . 'BHT*0085*08*' . $controlNumber . '*20260127*0412*TH~'
            . 'HL*1**20*1~' . $source
            . 'HL*2*1*21*1~NM1*41*2*EXAMPLE CLINIC*****46*555~TRN*2*BATCH1~STC*A1:19*20260127*WQ*477~'
            . 'HL*3*2*19*1~NM1*85*2*EXAMPLE CLINIC*****XX*1999999999~TRN*1*PROVIDER1~STC*A1:19*20260127*WQ*477~'
            . $claims
            . 'SE*99*' . $controlNumber . '~';
    }

    private static function interchange(string ...$transactions): string
    {
        return self::ISA
            . 'GS*HN*CLEARINGHOUSE*SUBMITTER*20260127*0412*101*X*005010X214~'
            . implode('', $transactions)
            . 'GE*' . count($transactions) . '*101~IEA*1*000000101~';
    }

    private const CLEARINGHOUSE = 'NM1*AY*2*EXAMPLE EDI*****46*123456789~TRN*1*777~DTP*050*D8*20260127~';
    private const PAYER = 'NM1*PR*2*EXAMPLE HEALTH PLAN*****PI*54321~TRN*1*888~DTP*050*D8*20260128~';

    /**
     * A clearinghouse transaction and a payer transaction about claim 100-200, as returned
     * the day the claim was sent.
     */
    private static function acknowledgment(): string
    {
        return self::interchange(
            self::transaction(
                '0001',
                self::CLEARINGHOUSE,
                'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~'
                . 'TRN*2*100-200~STC*A0:19*20260127*WQ*257~REF*D9*TRACE123~DTP*472*D8*20260120~'
            ),
            self::transaction(
                '0002',
                self::PAYER,
                'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~'
                . 'TRN*2*100-200~'
                . 'STC*A1:19*20260128*WQ*257******** Payer has acknowledged receipt.~'
                . 'STC*A2:20:PR*20260128*WQ*257~'
                . 'REF*1K*PAYERCLAIM99~DTP*472*RD8*20260120-20260122~'
            ),
        );
    }

    /**
     * @return array{ClaimStatusReport, ClaimStatusReport} the clearinghouse's report, then the payer's
     */
    private static function acknowledgmentReports(): array
    {
        $reports = ClaimStatusReader::read(self::acknowledgment());
        self::assertCount(2, $reports);
        return [$reports[0], $reports[1]];
    }

    public function testClearinghouseReportSaysTheClaimWasForwarded(): void
    {
        [$report] = self::acknowledgmentReports();

        $this->assertSame('100-200', $report->claimReference);
        $this->assertSame(ClaimStatusSourceKind::Clearinghouse, $report->source->kind());
        $this->assertSame('EXAMPLE EDI', $report->source->name);
        $this->assertSame('46', $report->source->identifierQualifier);
        $this->assertSame('123456789', $report->source->identifier);
        $this->assertSame('2026-01-27', $report->sourceReceivedDate?->format('Y-m-d'));
        $this->assertSame(['TRACE123'], $report->clearinghouseTraceNumbers());
        $this->assertSame([], $report->payerClaimControlNumbers());

        $this->assertCount(1, $report->statuses);
        $status = $report->statuses[0];
        $this->assertSame('A0', $status->category);
        $this->assertSame('19', $status->code);
        $this->assertSame('', $status->entityCode);
        $this->assertSame('2026-01-27', $status->effectiveDate?->format('Y-m-d'));
        $this->assertSame('257', $status->amount);
        $this->assertTrue($status->isAccepted());
        $this->assertFalse($status->isRejected());
        $this->assertFalse($status->isForServiceLine());
    }

    public function testPayerReportKeepsEveryStatusAndThePayerClaimNumber(): void
    {
        [, $report] = self::acknowledgmentReports();

        $this->assertSame(ClaimStatusSourceKind::Payer, $report->source->kind());
        $this->assertSame('EXAMPLE HEALTH PLAN', $report->source->name);
        $this->assertSame('PI', $report->source->identifierQualifier);
        $this->assertSame('54321', $report->source->identifier);
        $this->assertSame('2026-01-28', $report->sourceReceivedDate?->format('Y-m-d'));
        $this->assertSame(['PAYERCLAIM99'], $report->payerClaimControlNumbers());
        $this->assertSame([], $report->clearinghouseTraceNumbers());

        $this->assertCount(2, $report->statuses);
        [$received, $accepted] = $report->statuses;
        $this->assertSame('A1', $received->category);
        $this->assertSame('Payer has acknowledged receipt.', $received->text);
        $this->assertSame('A2', $accepted->category);
        $this->assertSame('20', $accepted->code);
        $this->assertSame('PR', $accepted->entityCode);
        $this->assertSame('', $accepted->text);
        $this->assertTrue($accepted->isAccepted());
    }

    public function testReportCarriesTheEnvelopeThePatientAndTheServiceDates(): void
    {
        [$fromClearinghouse, $fromPayer] = self::acknowledgmentReports();

        $this->assertSame('000000101', $fromClearinghouse->interchangeControlNumber);
        $this->assertSame('2026-01-27', $fromClearinghouse->interchangeDate?->format('Y-m-d'));
        $this->assertSame('0001', $fromClearinghouse->transactionControlNumber);
        $this->assertSame('0002', $fromPayer->transactionControlNumber);
        $this->assertSame('DOE', $fromPayer->patientLastName);
        $this->assertSame('JANE', $fromPayer->patientFirstName);

        $this->assertSame('2026-01-20', $fromClearinghouse->serviceDateFrom?->format('Y-m-d'));
        $this->assertSame('2026-01-20', $fromClearinghouse->serviceDateTo?->format('Y-m-d'));
        $this->assertSame('2026-01-20', $fromPayer->serviceDateFrom?->format('Y-m-d'));
        $this->assertSame('2026-01-22', $fromPayer->serviceDateTo?->format('Y-m-d'));
    }

    public function testClaimsUnderOnePatientAreReportedSeparately(): void
    {
        $reports = ClaimStatusReader::read(self::interchange(self::transaction(
            '0001',
            self::PAYER,
            'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~'
            . 'TRN*2*100-200~STC*A2:20*20260128*WQ*257~REF*1K*FIRST~'
            . 'TRN*2*100-201~STC*A7:562:85*20260128*U*220~REF*1K*SECOND~'
            . 'HL*5*3*PT~NM1*QC*1*ROE*RICHARD****MI*MEMBER2~'
            . 'TRN*2*300-400~STC*A1:19*20260128*WQ*90~'
        )));

        $this->assertSame(['100-200', '100-201', '300-400'], array_column($reports, 'claimReference'));
        $this->assertSame(['FIRST'], $reports[0]->payerClaimControlNumbers());
        $this->assertSame(['SECOND'], $reports[1]->payerClaimControlNumbers());
        $this->assertSame('257', $reports[0]->statuses[0]->amount);
        $this->assertSame('220', $reports[1]->statuses[0]->amount);
        $this->assertSame('DOE', $reports[1]->patientLastName);
        $this->assertSame('ROE', $reports[2]->patientLastName);
        $this->assertSame([], $reports[2]->payerClaimControlNumbers());
    }

    public function testRejectionIsSeenFromTheActionOrTheCategory(): void
    {
        $reports = ClaimStatusReader::read(self::interchange(self::transaction(
            '0001',
            self::PAYER,
            'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~'
            . 'TRN*2*100-200~STC*A7:562:85*20260128*U*257~STC*A3:21*20260128**257~STC*A1:19*20260128*15*257~'
        )));

        $this->assertCount(1, $reports);
        [$byAction, $byCategory, $neither] = $reports[0]->statuses;
        $this->assertTrue($byAction->isRejected());
        $this->assertFalse($byAction->isAccepted());
        $this->assertSame('85', $byAction->entityCode);
        $this->assertTrue($byCategory->isRejected());
        $this->assertFalse($neither->isRejected());
        $this->assertFalse($neither->isAccepted());
    }

    public function testServiceLineStatusesNameTheirProcedure(): void
    {
        $reports = ClaimStatusReader::read(self::interchange(self::transaction(
            '0001',
            self::PAYER,
            'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~'
            . 'TRN*2*100-200~STC*A3:21*20260128*U*257~DTP*472*D8*20260120~'
            . 'SVC*HC:92014*200*0~STC*A7:454*20260128*U*200~DTP*472*D8*20260121~'
            . 'SVC*HC:92015*57*0~STC*A2:20*20260128*WQ*57~'
        )));

        $this->assertCount(1, $reports);
        [$claim, $firstLine, $secondLine] = $reports[0]->statuses;
        $this->assertFalse($claim->isForServiceLine());
        $this->assertSame('HC:92014', $firstLine->serviceLine);
        $this->assertTrue($firstLine->isForServiceLine());
        $this->assertSame('HC:92015', $secondLine->serviceLine);
        $this->assertTrue($secondLine->isAccepted());
        // The service line's own date does not replace the claim's.
        $this->assertSame('2026-01-20', $reports[0]->serviceDateFrom?->format('Y-m-d'));
    }

    public function testSeveralStatusesInOneSegmentAreAllKept(): void
    {
        $reports = ClaimStatusReader::read(self::interchange(self::transaction(
            '0001',
            self::PAYER,
            'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~'
            . 'TRN*2*100-200~STC*A7:562:85*20260128*U*257******A7:128:IL*A7:153*Three things are wrong.~'
        )));

        $statuses = $reports[0]->statuses;
        $this->assertSame(['562', '128', '153'], array_column($statuses, 'code'));
        $this->assertSame(['85', 'IL', ''], array_column($statuses, 'entityCode'));
        $this->assertSame(['Three things are wrong.', '', ''], array_column($statuses, 'text'));
        $this->assertSame(['U', 'U', 'U'], array_column($statuses, 'actionCode'));
    }

    public function testStatusesAboveTheClaimLevelAreNotReported(): void
    {
        // transaction() gives the receiver and the provider a TRN and an STC of their own.
        $reports = ClaimStatusReader::read(self::interchange(self::transaction('0001', self::PAYER, '')));

        $this->assertSame([], $reports);
    }

    public function testClaimStatusResponseIsReadAtTheSubscriberLevel(): void
    {
        $text = self::ISA
            . 'GS*HN*PAYER*SUBMITTER*20260127*0412*101*X*005010X212~'
            . 'ST*277*0001*005010X212~BHT*0010*08*REQUEST1*20260127*0412*DG~'
            . 'HL*1**20*1~NM1*PR*2*EXAMPLE HEALTH PLAN*****PI*54321~'
            . 'HL*2*1*21*1~NM1*41*2*EXAMPLE CLINIC*****46*555~'
            . 'HL*3*2*19*1~NM1*1P*2*EXAMPLE CLINIC*****XX*1999999999~'
            . 'HL*4*3*22*0~NM1*IL*1*DOE*JANE****MI*MEMBER1~'
            . 'TRN*2*REQUEST1~STC*F1:65*20260210**257*200~REF*1K*PAYERCLAIM99~REF*EJ*100-200~DTP*472*D8*20260120~'
            . 'SE*13*0001~GE*1*101~IEA*1*000000101~';

        $reports = ClaimStatusReader::read($text);

        $this->assertCount(1, $reports);
        $this->assertSame('REQUEST1', $reports[0]->claimReference);
        $this->assertSame('F1', $reports[0]->statuses[0]->category);
        $this->assertNull($reports[0]->sourceReceivedDate);
        $this->assertTrue($reports[0]->isAbout('100-200'));
        $this->assertSame(['1K' => ['PAYERCLAIM99'], 'EJ' => ['100-200']], $reports[0]->references);
    }

    public function testIsAboutMatchesTheWholeReferenceOnly(): void
    {
        [, $report] = self::acknowledgmentReports();

        $this->assertTrue($report->isAbout('100-200'));
        $this->assertTrue($report->isAbout(' payerclaim99 '));
        $this->assertFalse($report->isAbout('00-20'));
        $this->assertFalse($report->isAbout('1100-2000'));
        $this->assertFalse($report->isAbout(''));
    }

    public function testLineBreaksAfterSegmentsAreIgnored(): void
    {
        $withLineBreaks = str_replace('~', "~\r\n", self::acknowledgment());

        $this->assertEquals(
            ClaimStatusReader::read(self::acknowledgment()),
            ClaimStatusReader::read($withLineBreaks)
        );
    }

    public function testSeparatorsComeFromTheInterchangeHeader(): void
    {
        $otherSeparators = strtr(self::acknowledgment(), ['*' => '|', ':' => '>', '~' => "\n"]);

        $this->assertEquals(
            ClaimStatusReader::read(self::acknowledgment()),
            ClaimStatusReader::read($otherSeparators)
        );
    }

    #[DataProvider('lineBreakProvider')]
    public function testSegmentsMayEndWithALineBreakOnly(string $lineBreak): void
    {
        $endedByLineBreaks = str_replace('~', $lineBreak, self::acknowledgment());

        $this->assertEquals(
            ClaimStatusReader::read(self::acknowledgment()),
            ClaimStatusReader::read($endedByLineBreaks)
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function lineBreakProvider(): array
    {
        return [
            'LF' => ["\n"],
            'CRLF' => ["\r\n"],
            'CR' => ["\r"],
        ];
    }

    public function testEachInterchangeIsReadWithItsOwnSeparators(): void
    {
        $first = self::acknowledgment();
        $second = strtr($first, ['*' => '|', ':' => '>', '~' => '!']);

        $reports = ClaimStatusReader::read($first . "\n" . $second);

        $expected = ClaimStatusReader::read($first);
        $this->assertCount(2, $expected);
        $this->assertEquals([...$expected, ...$expected], $reports);
        $this->assertSame('PR', $reports[3]->statuses[1]->entityCode);
    }

    public function testServiceLineReferencesAreNotClaimReferences(): void
    {
        $reports = ClaimStatusReader::read(self::interchange(self::transaction(
            '0001',
            self::PAYER,
            'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~'
            . 'TRN*2*100-200~STC*A2:20*20260128*WQ*257~REF*1K*PAYERCLAIM99~'
            . 'SVC*HC:92014*257*0~STC*A2:20*20260128*WQ*257~REF*FJ*1~'
        )));

        $this->assertSame(['1K' => ['PAYERCLAIM99']], $reports[0]->references);
        $this->assertFalse($reports[0]->isAbout('1'));
    }

    public function testDatesThatAreNotDatesAreLeftEmpty(): void
    {
        $reports = ClaimStatusReader::read(self::interchange(self::transaction(
            '0001',
            'NM1*PR*2*EXAMPLE HEALTH PLAN*****PI*54321~DTP*050*D8*20261340~',
            'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~'
            . 'TRN*2*100-200~STC*A1:19*TODAY*WQ*257~STC*A1:19**WQ*257~DTP*472*D8*2026012~'
        )));

        $this->assertNull($reports[0]->sourceReceivedDate);
        $this->assertNull($reports[0]->statuses[0]->effectiveDate);
        $this->assertNull($reports[0]->statuses[1]->effectiveDate);
        $this->assertNull($reports[0]->serviceDateFrom);
        $this->assertNull($reports[0]->serviceDateTo);
    }

    #[DataProvider('notClaimStatusProvider')]
    public function testTextWithNoClaimStatusGivesNoReports(string $text): void
    {
        $this->assertSame([], ClaimStatusReader::read($text));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function notClaimStatusProvider(): array
    {
        return [
            'empty' => [''],
            'not X12' => [str_repeat('This is a letter, not an X12 file. ', 5)],
            'shorter than an interchange header' => ['ISA*00*'],
            'letters where the separators belong' => ['ISA' . str_repeat('A', 110)],
            'a remittance' => [
                self::ISA
                . 'GS*HP*PAYER*SUBMITTER*20260127*0412*101*X*005010X221A1~'
                . 'ST*835*0001~BPR*I*0*C*NON~TRN*1*CHECK1*1~'
                . 'LX*1~CLP*100-200*4*257*0**MC*PAYERCLAIM99~'
                . 'SE*6*0001~GE*1*101~IEA*1*000000101~',
            ],
            'an interchange with no transaction' => [self::ISA . 'IEA*0*000000101~'],
        ];
    }

    #[DataProvider('sourceKindProvider')]
    public function testSourceKindComesFromTheEntityCode(string $entityCode, ClaimStatusSourceKind $expected): void
    {
        $this->assertSame($expected, ClaimStatusSourceKind::fromEntityCode($entityCode));
    }

    /**
     * @return array<string, array{string, ClaimStatusSourceKind}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function sourceKindProvider(): array
    {
        return [
            'payer' => ['PR', ClaimStatusSourceKind::Payer],
            'clearinghouse' => ['AY', ClaimStatusSourceKind::Clearinghouse],
            'another entity' => ['FI', ClaimStatusSourceKind::Other],
            'no entity code' => ['', ClaimStatusSourceKind::Other],
        ];
    }
}
