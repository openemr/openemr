<?php

/**
 * What a statements page does after a run: write the batch, send it on, and the biller's message.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\Statement;

use OpenEMR\Billing\Statement\StatementDelivery;
use OpenEMR\Billing\Statement\StatementOutput;
use OpenEMR\Billing\Statement\StatementRun;
use OpenEMR\Billing\Statement\StatementRunHandler;
use OpenEMR\Billing\Statement\StatementRunSteps;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class StatementRunHandlerTest extends TestCase
{
    private const TODAY = '2026-11-02';

    private string $batchFile;
    private HandlerTestSteps $steps;
    private RecordingStatementOutput $output;

    private bool $translationWas;

    protected function setUp(): void
    {
        // The messages go through xl(); with translation off it needs no database.
        $this->translationWas = OEGlobalsBag::getInstance()->getBoolean('disable_translation');
        OEGlobalsBag::getInstance()->set('disable_translation', true);
        $file = tempnam(sys_get_temp_dir(), 'stmt');
        $this->assertIsString($file);
        $this->batchFile = $file;
        $this->steps = new HandlerTestSteps();
        $this->output = new RecordingStatementOutput();
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set('disable_translation', $this->translationWas);
        @unlink($this->batchFile);
    }

    public function testAPrintRunPrintsTheBatchAndCountsTheStatements(): void
    {
        $message = $this->handle([self::invoice(1, 11), self::invoice(2, 21)], StatementDelivery::Print);

        $this->assertSame('Now printing 2 statements and updating invoices.', $message);
        $this->assertSame('statement 1|statement 2|', file_get_contents($this->batchFile));
        $this->assertSame([['print', $this->batchFile]], $this->output->calls);
        $this->assertSame([[11], [21]], $this->steps->recorded);
    }

    public function testAFailedPrintSaysSoAndWhereTheStatementsAre(): void
    {
        $this->output->printError = 'lpr: The printer or class does not exist.';

        $message = $this->handle([self::invoice(1, 11)], StatementDelivery::Print);

        $this->assertSame('Printing failed: lpr: The printer or class does not exist. The statements are in '
            . $this->batchFile . '. The invoices were updated.', $message);
        $this->assertSame([[11]], $this->steps->recorded);
    }

    public function testAFailedPrintWithoutUpdateLeavesOutTheInvoices(): void
    {
        $this->output->printError = 'lpr exited with status 1';
        $this->steps->emptyFor = [2];

        $message = $this->handle([self::invoice(1, 11), self::invoice(2, 21)], StatementDelivery::Print, withoutUpdate: true);

        $this->assertSame('Printing failed: lpr exited with status 1 The statements are in ' . $this->batchFile
            . '. No statement for Ann Patient2: the amount due does not meet the minimum amount to print set in Globals.', $message);
    }

    public function testWithoutUpdateSaysTheInvoicesWontBeUpdated(): void
    {
        $message = $this->handle([self::invoice(1, 11)], StatementDelivery::Print, withoutUpdate: true);

        $this->assertSame('Now printing 1 statements; invoices will not be updated.', $message);
        $this->assertSame([], $this->steps->recorded);
    }

    public function testTestModePrintsAndRecordsNothing(): void
    {
        $message = $this->handle([self::invoice(1, 11)], StatementDelivery::Print, test: true);

        $this->assertSame('Printing skipped; see test output in ' . $this->batchFile, $message);
        $this->assertSame([], $this->output->calls);
        $this->assertSame([], $this->steps->recorded);
    }

    public function testDownloadsHandTheBatchOn(): void
    {
        $this->handle([self::invoice(1, 11)], StatementDelivery::Download);
        $this->handle([self::invoice(1, 11)], StatementDelivery::Pdf);

        $this->assertSame([
            ['download', $this->batchFile],
            ['downloadPdf', $this->batchFile, ['firstName' => 'Ann', 'pid' => 1]],
        ], $this->output->calls);
    }

    public function testAPrintRunNamesThePatientsLeftWithNothingDue(): void
    {
        $this->steps->owed = [101 => -5.0];

        $message = $this->handle([self::invoice(1, 11, 101), self::invoice(2, 21)], StatementDelivery::Print, skipNothingDue: true);

        $this->assertSame('Now printing 1 statements and updating invoices. No statement for Ann Patient1: nothing is due after their credits.', $message);
    }

    public function testWhenEveryPatientHasNothingDueNothingIsSentOn(): void
    {
        $this->steps->owed = [101 => -5.0];

        $message = $this->handle([self::invoice(1, 11, 101)], StatementDelivery::Download, skipNothingDue: true);

        $this->assertSame('No statement for Ann Patient1: nothing is due after their credits.', $message);
        $this->assertSame([], $this->output->calls);
        $this->assertSame('', file_get_contents($this->batchFile));
    }

    public function testAPrintRunNamesThePatientsUnderThePrintMinimum(): void
    {
        $this->steps->emptyFor = [1];

        $message = $this->handle([self::invoice(1, 11), self::invoice(2, 21)], StatementDelivery::Print);

        $this->assertSame('Now printing 1 statements and updating invoices. No statement for Ann Patient1: the amount due does not meet the minimum amount to print set in Globals.', $message);
        $this->assertSame('statement 2|', file_get_contents($this->batchFile));
    }

    public function testWhenEveryPatientIsUnderThePrintMinimumNothingIsSentOn(): void
    {
        $this->steps->owed = [101 => -5.0];
        $this->steps->emptyFor = [2];

        $message = $this->handle([self::invoice(1, 11, 101), self::invoice(2, 21)], StatementDelivery::Pdf, skipNothingDue: true);

        $this->assertSame('No statement for Ann Patient1: nothing is due after their credits. No statement for Ann Patient2: the amount due does not meet the minimum amount to print set in Globals.', $message);
        $this->assertSame([], $this->output->calls);
    }

    public function testAnEmailRunIsPrintedTooAndItsFailuresAreReplacedByThePrintMessage(): void
    {
        $this->steps->emailFails = true;

        $message = $this->handle([self::invoice(1, 11)], StatementDelivery::Email);

        $this->assertSame('Now printing 1 statements and updating invoices.', $message);
        $this->assertSame([['print', $this->batchFile]], $this->output->calls);
    }

    public function testAPortalRun(): void
    {
        $this->assertSame('Sending Invoice to Patient Portal Completed', $this->handle([self::invoice(1, 11)], StatementDelivery::Portal));

        $this->steps->portalAuthorized = false;
        $this->assertSame('Notification FAILED: Not Portal Authorized', $this->handle([self::invoice(1, 11)], StatementDelivery::Portal));

        $this->steps->portalAuthorized = true;
        $this->steps->portalFails = true;
        $this->assertSame('Notification FAILED', $this->handle([self::invoice(1, 11)], StatementDelivery::Portal));
        $this->assertSame([], $this->output->calls);
    }

    /**
     * @param list<array<string, mixed>> $invoices
     */
    private function handle(
        array $invoices,
        StatementDelivery $delivery,
        bool $withoutUpdate = false,
        bool $test = false,
        bool $skipNothingDue = false,
    ): string {
        $handler = new StatementRunHandler(new StatementRun($this->steps, new NullLogger()), $this->output, $this->batchFile);
        return $handler->handle($invoices, $delivery, $withoutUpdate, $test, $skipNothingDue, self::TODAY);
    }

    /**
     * @return array<string, mixed>
     */
    private static function invoice(int $pid, int $id, ?int $encounter = null): array
    {
        return [
            'id' => $id, 'date' => '2026-09-01 00:00:00', 'pid' => $pid, 'encounter' => $encounter ?? $id * 10,
            'stmt_count' => 0, 'last_level_billed' => 1, 'last_level_closed' => 1, 'enc_billing_note' => '',
            'provider_id' => 1, 'fname' => 'Ann', 'mname' => '', 'lname' => 'Patient' . $pid,
            'street' => '', 'street_line_2' => '', 'city' => '', 'state' => '', 'postal_code' => '',
            'pat_billing_note' => '', 'guardiansname' => '', 'guardianaddress' => '', 'guardiancity' => '',
            'guardianstate' => '', 'guardianpostalcode' => '',
        ];
    }
}

/**
 * Each invoice's encounter owes $owed[encounter] (10.00 when not set).
 */
final class HandlerTestSteps implements StatementRunSteps
{
    /** @var array<int, float> */
    public array $owed = [];
    public bool $emailFails = false;
    public bool $portalAuthorized = true;
    public bool $portalFails = false;
    /** @var list<int> pids whose statement the print rules leave empty */
    public array $emptyFor = [];
    /** @var list<list<int>> */
    public array $recorded = [];

    public function appendInvoice(array &$stmt, int $pid, int $encounter, string $serviceDate, int $notice): void
    {
        $amount = is_numeric($stmt['amount'] ?? null) ? (float) $stmt['amount'] : 0.0;
        $stmt['amount'] = sprintf('%.2f', $amount + ($this->owed[$encounter] ?? 10.0));
    }

    public function creditEncounters(int $pid, array $excludedEncounterIds): array
    {
        return [];
    }

    public function buildStatement(array $stmt): string
    {
        $pid = is_numeric($stmt['pid'] ?? null) ? (int) $stmt['pid'] : 0;
        return in_array($pid, $this->emptyFor, true) ? '' : 'statement ' . $pid . '|';
    }

    public function saveDocument(int $pid, string $statement): void
    {
    }

    public function email(int $pid, string $statement): void
    {
        if ($this->emailFails) {
            throw new \RuntimeException('Patient email address is invalid');
        }
    }

    public function isPortalAuthorized(int $pid): bool
    {
        return $this->portalAuthorized;
    }

    public function notifyPortal(int $pid, array $invoices, string $statement, string $invoiceId): bool
    {
        return !$this->portalFails;
    }

    public function recordStatementRun(array $encounterIds): void
    {
        $this->recorded[] = $encounterIds;
    }
}

final class RecordingStatementOutput implements StatementOutput
{
    /** @var list<list<mixed>> */
    public array $calls = [];

    public function download(string $batchFile): void
    {
        $this->calls[] = ['download', $batchFile];
    }

    public function downloadPdf(string $batchFile, ?array $onlyPatient): void
    {
        $this->calls[] = ['downloadPdf', $batchFile, $onlyPatient];
    }

    public ?string $printError = null;

    public function print(string $batchFile): ?string
    {
        $this->calls[] = ['print', $batchFile];
        return $this->printError;
    }
}
