<?php

/**
 * Tests for the EDI 277 / 277CA claim-status segment renderers.
 *
 * The renderers are pure functions over a split X12 segment: every value a
 * segment needs is passed in and everything it produces is returned, so each
 * branch is exercised in isolation. The code-lookup collaborator is the real
 * edih_271_codes class (a stateless value-lookup table), loaded here because
 * it lives outside the PSR-4 tree and is not autoloaded.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use edih_271_codes;
use OpenEMR\Billing\EdiHistory\Claim277Renderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Claim277RendererTest extends TestCase
{
    /** Sub-element (composite) delimiter used throughout the 277 fixtures. */
    private const DS = ':';

    private edih_271_codes $cd;

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../../library/edihistory/codes/edih_271_code_class.php';
    }

    protected function setUp(): void
    {
        // ds = sub-element delimiter, dr = repetition delimiter.
        $this->cd = new edih_271_codes(self::DS, '^');
    }

    #[DataProvider('rowClassProvider')]
    public function testRowClass(string $loopId, string $expected): void
    {
        self::assertSame($expected, Claim277Renderer::rowClass($loopId));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function rowClassProvider(): array
    {
        return [
            // The class depends only on the loop-id's trailing family letter,
            // so every loop in a family maps to the same class.
            '2000A source' => ['2000A', 'src'],
            '2100A source' => ['2100A', 'src'],
            '2200A source' => ['2200A', 'src'],
            '2000B receiver' => ['2000B', 'rcv'],
            '2100C provider' => ['2100C', 'prv'],
            '2000D subscriber' => ['2000D', 'sbr'],
            '2100E dependent' => ['2100E', 'dep'],
            'heading has no class' => ['Heading', ''],
            'empty has no class' => ['', ''],
        ];
    }

    public function testBhtRendersHeadingAndReturnsReference(): void
    {
        $result = Claim277Renderer::bht(
            ['BHT', '0010', '08', 'REF123', '20240115', '', '19'],
            $this->cd,
        );

        self::assertSame('REF123', $result['ref']);
        self::assertStringContainsString('<em>Reference:</em> REF123', $result['html']);
        self::assertStringContainsString('<em>Sequence:</em> Src, Rcv, Prv, Sbr, Dep', $result['html']);
        self::assertStringContainsString('<em>Date:</em> 2024-01-15', $result['html']);
        self::assertStringContainsString('<em>Type:</em> Status', $result['html']);
        // BHT06 present -> the transaction-type row is appended.
        self::assertStringContainsString('Response - further updates to follow', $result['html']);
    }

    #[DataProvider('bhtSequenceProvider')]
    public function testBhtSequenceLabel(?string $bht01, string $expectedSequence): void
    {
        $sar = ['BHT'];
        if ($bht01 !== null) {
            $sar[1] = $bht01;
        }

        $html = Claim277Renderer::bht($sar, $this->cd)['html'];

        self::assertStringContainsString('<em>Sequence:</em> ' . $expectedSequence . '</td>', $html);
    }

    /**
     * @return array<string, array{?string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function bhtSequenceProvider(): array
    {
        return [
            'code 0010' => ['0010', 'Src, Rcv, Prv, Sbr, Dep'],
            'code 0085' => ['0085', 'Src, Rcv, Prv, Pt'],
            'unknown code' => ['9999', 'Not determined (9999)'],
            'missing BHT01' => [null, ''],
        ];
    }

    public function testBhtMinimalSegmentReturnsEmptyReference(): void
    {
        $result = Claim277Renderer::bht(['BHT'], $this->cd);

        self::assertSame('', $result['ref']);
        // No BHT06 -> no trailing transaction-type row.
        self::assertStringNotContainsString('<em>Type:</em> ', str_replace('<em>Type:</em> </td>', '', $result['html']));
    }

    public function testNm1AssemblesNameAndEscapesHtml(): void
    {
        $result = Claim277Renderer::nm1(
            ['NM1', 'PR', '2', 'A & B <Co>'],
            'src',
            $this->cd,
        );

        // The returned name is the raw, unescaped value...
        self::assertSame('A & B <Co>', $result['name']);
        // ...but the HTML escapes it (text() uses ENT_NOQUOTES).
        self::assertStringContainsString('A &amp; B &lt;Co&gt;', $result['html']);
        self::assertStringContainsString("title='Payer'", $result['html']);
    }

    /**
     * @param list<string> $sar
     */
    #[DataProvider('nm1NameProvider')]
    public function testNm1NameAssembly(array $sar, string $expectedName): void
    {
        self::assertSame($expectedName, Claim277Renderer::nm1($sar, 'src', $this->cd)['name']);
    }

    /**
     * @return array<string, array{list<string>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nm1NameProvider(): array
    {
        return [
            'organization name only' => [['NM1', 'PR', '2', 'Acme Insurance'], 'Acme Insurance'],
            // Person: last (NM103), suffix (NM107), first (NM104), middle (NM105).
            'person full name' => [['NM1', '1P', '1', 'Smith', 'John', 'A', '', 'Jr'], 'Smith Jr, John A'],
            'person first and last' => [['NM1', '1P', '1', 'Smith', 'John'], 'Smith, John'],
            'empty name element' => [['NM1', 'PR', '2'], ''],
        ];
    }

    public function testNm1IncludesIdentificationRow(): void
    {
        $html = Claim277Renderer::nm1(
            ['NM1', 'PR', '2', 'Acme Insurance', '', '', '', '', 'PI', '12345'],
            'src',
            $this->cd,
        )['html'];

        // NM108 code translated, NM109 value appended.
        self::assertStringContainsString('<em>Payer ID</em> 12345', $html);
    }

    public function testPer(): void
    {
        $html = Claim277Renderer::per(
            ['PER', 'IC', 'Jane Doe', 'TE', '555-1212', 'FX', '555-9999'],
            'src',
            $this->cd,
        );

        self::assertSame(
            "<tr class='src'><td colspan=2>Jane Doe</td>"
            . "<td colspan=2 title='Telephone Facsimile '>555-1212 555-9999 </td></tr>",
            $html,
        );
    }

    #[DataProvider('trnProvider')]
    public function testTrn(string $trn01, string $expectedLabel): void
    {
        $result = Claim277Renderer::trn(['TRN', $trn01, 'TRACE99'], 'src');

        self::assertSame('TRACE99', $result['ref']);
        self::assertStringContainsString("<em>{$expectedLabel}</em> TRACE99", $result['html']);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function trnProvider(): array
    {
        return [
            'trace type 1 is a transaction reference' => ['1', 'Transaction Ref'],
            'other trace types are a trace' => ['2', 'Trace'],
        ];
    }

    public function testStcRendersStatusRowsFromComposite(): void
    {
        $html = Claim277Renderer::stc(
            ['STC', 'A1:19:PR', '20240115', 'WQ', '150.00', '100.00'],
            self::DS,
            'src',
            $this->cd,
        );

        // STC03 action code -> 'Accepted'; STC01 category code translated;
        // STC02 date and STC04 billed amount in the head row.
        self::assertStringContainsString('<td>Accepted</td>', $html);
        self::assertStringContainsString('The claim/encounter has been received.', $html);
        self::assertStringContainsString('2024-01-15 $150.00', $html);
        // STC05 paid amount surfaces in the labeled Payment row.
        self::assertStringContainsString('<em>Payment</em>', $html);
        self::assertStringContainsString('$100.00', $html);
    }

    public function testStcRendersAllCompositesAndMessage(): void
    {
        $html = Claim277Renderer::stc(
            ['STC', 'A1:19', '20240115', 'U', '150', '', '', '', '', '', 'A2:20:PR:RA', 'A3:21::RA', 'msg here'],
            self::DS,
            'sbr',
            $this->cd,
        );

        self::assertStringContainsString('<td>Rejected</td>', $html);
        // STC10 and STC11 composites both carry the 'RA' Rx flag.
        self::assertSame(2, substr_count($html, 'Rx Reject/Payment Codes'));
        // STC12 free-text message row.
        self::assertStringContainsString('<em>Message</em>', $html);
        self::assertStringContainsString('msg here', $html);
    }

    #[DataProvider('stcActionCodeProvider')]
    public function testStcActionCode(string $stc03, string $expected): void
    {
        $html = Claim277Renderer::stc(
            ['STC', 'A1:19:PR', '20240115', $stc03],
            self::DS,
            'src',
            $this->cd,
        );

        self::assertStringContainsString("<tr class='src'><td>{$expected}</td>", $html);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function stcActionCodeProvider(): array
    {
        return [
            'WQ accepted' => ['WQ', 'Accepted'],
            'F final' => ['F', 'Final'],
            '15 correct/resubmit' => ['15', 'Correct/Resubmit'],
            'U rejected' => ['U', 'Rejected'],
            'passthrough unknown code' => ['ZZ', 'ZZ'],
        ];
    }

    public function testStcWithoutCompositesRendersNothing(): void
    {
        // A follow-on STC that carries no composite of its own must render
        // nothing — the stateless renderer cannot leak a prior STC's codes.
        self::assertSame(
            '',
            Claim277Renderer::stc(['STC', '', '20240115', 'WQ'], self::DS, 'src', $this->cd),
        );
        self::assertSame(
            '',
            Claim277Renderer::stc(['STC'], self::DS, 'src', $this->cd),
        );
    }

    #[DataProvider('qtyProvider')]
    public function testQtyString(string $qty01, string $qty02, string $expected): void
    {
        self::assertSame($expected, Claim277Renderer::qtyString(['QTY', $qty01, $qty02]));
    }

    /**
     * @return array<string, array{string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function qtyProvider(): array
    {
        return [
            'acknowledged' => ['90', '5', 'Acknowledged Quantity 5'],
            'unacknowledged' => ['AA', '3', 'Unacknowledged Quantity 3'],
            'approved' => ['QA', '7', 'Quantity Approved 7'],
            'disapproved' => ['QC', '2', 'Quantity Disapproved 2'],
            'unknown qualifier' => ['ZZ', '1', 'Quantity 1'],
        ];
    }

    public function testQtyStringWithMissingQualifier(): void
    {
        self::assertSame('', Claim277Renderer::qtyString(['QTY']));
    }

    #[DataProvider('amtProvider')]
    public function testAmt(string $amt01, string $qtyPrefix, string $expectedBody): void
    {
        $html = Claim277Renderer::amt(['AMT', $amt01, '500.25'], 'src', $qtyPrefix);

        self::assertSame(
            "<tr class='src'><td>&gt;</td><td colspan=3>{$expectedBody}</td></tr>",
            $html,
        );
    }

    /**
     * @return array<string, array{string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function amtProvider(): array
    {
        return [
            'YU amount' => ['YU', 'Quantity Approved 5', 'Quantity Approved 5 Amt $500.25'],
            'other qualifier is a rejection' => ['ZZ', 'Q', 'Q Amt Rej $500.25'],
        ];
    }

    public function testRef(): void
    {
        $html = Claim277Renderer::ref(['REF', '1K', 'CLAIM99', 'extra'], 'prv', $this->cd);

        self::assertSame(
            "<tr class='prv'><td>&gt;</td><td colspan=2><em>Payer Control Number</em> CLAIM99</td>"
            . '<td>extra</td></tr>',
            $html,
        );
    }

    public function testDtpD8SingleDate(): void
    {
        $html = Claim277Renderer::dtp(['DTP', '472', 'D8', '20240115'], 'src', $this->cd);

        self::assertSame(
            "<tr class='src'><td>&gt;</td><td>Service</td><td colspan=2>2024-01-15</td></tr>",
            $html,
        );
    }

    public function testDtpRd8DateRange(): void
    {
        $html = Claim277Renderer::dtp(['DTP', '472', 'RD8', '20240101-20240131'], 'src', $this->cd);

        self::assertStringContainsString('2024-01-01 - 2024-01-31', $html);
    }

    public function testDtpUnknownFormatRendersNoDate(): void
    {
        $html = Claim277Renderer::dtp(['DTP', '472', 'XX', '20240115'], 'src', $this->cd);

        self::assertSame(
            "<tr class='src'><td>&gt;</td><td>Service</td><td colspan=2></td></tr>",
            $html,
        );
    }

    public function testSvcSubscriberLoopUsesThreeColumnRow(): void
    {
        $html = Claim277Renderer::svc(['SVC', 'HC:99213', '150', '100', '0300'], self::DS, 'src', false, $this->cd);

        self::assertSame(
            "<tr class='src'><td><em>Service</em></td><td>HCPCS Codes 99213</td>"
            . "<td colspan=2>$150.00 0300</td></tr>"
            . "<tr class='src'><td>&gt;</td><td colspan=3>$100.00 0300</td></tr>",
            $html,
        );
    }

    public function testSvcReceiverLoopUsesFourColumnRow(): void
    {
        $html = Claim277Renderer::svc(['SVC', 'HC:99213', '150', '100', '0300'], self::DS, 'rcv', true, $this->cd);

        self::assertStringContainsString(
            "<tr class='rcv'><td><em>Service</em></td><td>HCPCS Codes 99213</td><td>$150.00</td><td>0300</td></tr>",
            $html,
        );
    }

    public function testSvcProcedureCodeWithoutCompositeIsPassedThrough(): void
    {
        $html = Claim277Renderer::svc(['SVC', '99213', '150'], self::DS, 'src', false, $this->cd);

        self::assertStringContainsString('<td>99213</td>', $html);
        // No paid amount and no revenue code -> no second row.
        self::assertStringNotContainsString('&gt;', $html);
    }

    public function testConstructorIsPrivate(): void
    {
        $ctor = (new \ReflectionClass(Claim277Renderer::class))->getConstructor();
        self::assertNotNull($ctor);
        self::assertTrue($ctor->isPrivate());
    }
}
