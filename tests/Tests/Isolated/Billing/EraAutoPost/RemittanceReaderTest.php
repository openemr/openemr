<?php

/**
 * Tests for RemittanceReader: an 835 file read into checks that can be balanced.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EraAutoPost;

use OpenEMR\Billing\EraAutoPost\RemittanceReader;
use PHPUnit\Framework\TestCase;

class RemittanceReaderTest extends TestCase
{
    private string $directory = '';

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/era_reader_' . bin2hex(random_bytes(6));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob($this->directory . '/*') ?: []);
        rmdir($this->directory);
    }

    public function testEachTransactionSetIsACheckWithItsClaimPayments(): void
    {
        $path = Era835::write($this->directory, 'two.edi', Era835::file(
            Era835::check('CHK1', '150.00', [['10-20', '1', '70.00'], ['11-21', '2', '80.00']]),
            Era835::check('CHK2', '25.00', [['12-22', '1', '25.00']], control: '0002'),
        ));

        $remittance = (new RemittanceReader())->read($path);

        $this->assertSame('', $remittance->error);
        $this->assertCount(2, $remittance->checks);
        [$first, $second] = $remittance->checks;
        $this->assertSame('CHK1', $first->checkNumber);
        $this->assertSame('20261020', $first->checkDate);
        $this->assertSame(150.0, $first->amount);
        $this->assertSame('TEST PAYER', $first->payerName);
        $this->assertSame(['10-20', '11-21'], array_map(static fn($claim): string => $claim->ourClaimId, $first->claims));
        $this->assertSame([70.0, 80.0], array_map(static fn($claim): float => $claim->paymentAmount, $first->claims));
        $this->assertSame(150.0, $first->claimTotal());
        $this->assertTrue($first->isBalanced());
        $this->assertSame(0.0, $first->plbTotal);
        $this->assertSame('CHK2', $second->checkNumber);
        $this->assertSame(['12-22'], array_map(static fn($claim): string => $claim->ourClaimId, $second->claims));
        $this->assertTrue($second->isBalanced());
    }

    public function testClaimPaymentsThatDoNotAddUpToTheCheckAreOutOfBalance(): void
    {
        $path = Era835::write($this->directory, 'short.edi', Era835::file(
            Era835::check('CHK1', '140.00', [['10-20', '1', '70.00'], ['11-21', '1', '80.00']]),
        ));

        $check = (new RemittanceReader())->read($path)->checks[0];

        $this->assertSame(150.0, $check->claimTotal());
        $this->assertFalse($check->isBalanced());
    }

    public function testProviderLevelAdjustmentsCountTowardTheBalance(): void
    {
        // 150.00 of claim payments, 12.50 kept back and 2.50 of interest paid: a check of 140.00.
        $path = Era835::write($this->directory, 'plb.edi', Era835::file(
            Era835::check('CHK1', '140.00', [['10-20', '1', '70.00'], ['11-21', '1', '80.00']], ['WO:ICN9*12.50', 'L6*-2.50']),
            Era835::check('CHK2', '25.00', [['12-22', '1', '25.00']], control: '0002'),
        ));

        [$first, $second] = (new RemittanceReader())->read($path)->checks;

        $this->assertSame(10.0, $first->plbTotal);
        $this->assertSame(['WO', 'L6'], $first->plbReasons);
        $this->assertTrue($first->isBalanced());
        // The next check does not inherit them.
        $this->assertSame(0.0, $second->plbTotal);
        $this->assertSame([], $second->plbReasons);
        $this->assertTrue($second->isBalanced());
    }

    public function testATransactionSetWithNoClaimsIsStillACheck(): void
    {
        $path = Era835::write($this->directory, 'plbonly.edi', Era835::file(
            Era835::check('CHK1', '70.00', [['10-20', '1', '70.00']]),
            Era835::check('PLBONLY', '10.00', [], ['L6*-10.00'], '0002'),
            Era835::check('CHK3', '25.00', [['12-22', '1', '25.00']], control: '0003'),
        ));

        $remittance = (new RemittanceReader())->read($path);

        $this->assertSame('', $remittance->error);
        $this->assertSame(['CHK1', 'PLBONLY', 'CHK3'], array_map(static fn($check): string => $check->checkNumber, $remittance->checks));
        $only = $remittance->checks[1];
        $this->assertSame([], $only->claims);
        $this->assertSame(10.0, $only->amount);
        $this->assertSame(-10.0, $only->plbTotal);
        $this->assertTrue($only->isBalanced());
        // The claims before it are not carried into the check after it.
        $this->assertSame(['12-22'], array_map(static fn($claim): string => $claim->ourClaimId, $remittance->checks[2]->claims));
    }

    public function testATransactionSetWithoutItsOwnPaymentOrTraceNumberIsAnError(): void
    {
        $second = Era835::check('CHK2', '70.00', [['12-22', '1', '70.00']], control: '0002');
        foreach (['BPR', 'TRN'] as $missing) {
            // Drop the segment and correct the set's segment count, so only the header is wrong.
            $headless = (string) preg_replace('/^' . $missing . '\*[^~]*~\n/m', '', $second);
            $headless = (string) preg_replace_callback('/SE\*(\d+)\*/', static fn(array $m): string => 'SE*' . ((int) $m[1] - 1) . '*', $headless);
            $path = Era835::write($this->directory, "no$missing.edi", Era835::file(
                Era835::check('CHK1', '70.00', [['10-20', '1', '70.00']]),
                $headless,
            ));

            $remittance = (new RemittanceReader())->read($path);

            // Otherwise the second set would be read as check CHK1 again, in balance.
            $this->assertSame(RemittanceReader::NO_CHECK_HEADER, $remittance->error, $missing);
        }
    }

    public function testATransactionSetWithoutItsEndIsAnError(): void
    {
        // CHK1 loses its SE: its 70.00 claim would be counted in CHK2, which then balances at 95.00.
        $first = (string) preg_replace('/^SE\*[^~]*~\n/m', '', Era835::check('CHK1', '70.00', [['10-20', '1', '70.00']]));
        $path = Era835::write($this->directory, 'open.edi', Era835::file(
            $first,
            Era835::check('CHK2', '95.00', [['12-22', '1', '25.00']], control: '0002'),
        ));

        $remittance = (new RemittanceReader())->read($path);

        $this->assertSame(RemittanceReader::SET_NOT_CLOSED, $remittance->error);
    }

    public function testAFileThatEndsEarlyReportsTheParsersError(): void
    {
        $whole = Era835::file(Era835::check('CHK1', '70.00', [['10-20', '1', '70.00']]));
        $path = Era835::write($this->directory, 'cut.edi', substr($whole, 0, (int) strpos($whole, 'GE*')));

        $remittance = (new RemittanceReader())->read($path);

        $this->assertSame('Premature end of ERA file', $remittance->error);
    }
}
