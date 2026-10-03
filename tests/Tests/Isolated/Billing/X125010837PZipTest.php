<?php

/**
 * Isolated tests for the facility ZIP log lines and the opt-in hold.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\BatchFilePublisher;
use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingProcessor\Tasks\GeneratorX12;
use OpenEMR\Billing\BillingProcessor\Tasks\GeneratorX12Direct;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\FacilityZipDenial;
use OpenEMR\Billing\UnbilledFileDecision;
use OpenEMR\Billing\X125010837P;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class X125010837PZipTest extends TestCase
{
    /**
     * A short ZIP is still sent. Billing names the 277CA. Service names that edit and MA114.
     */
    public function testLogNamesMedicareConsequenceForEachLoop(): void
    {
        $billing = X125010837P::BILLING_ZIP_LOG;
        $this->assertStringContainsString('9 digits', $billing);
        $this->assertStringContainsString('277CA', $billing);
        $this->assertStringContainsString('CSC 500', $billing);
        $this->assertStringContainsString('country code', $billing);
        $this->assertStringNotContainsString('MA114', $billing);
        $this->assertStringNotContainsString('Rejecting claim', $billing);

        $service = X125010837P::SERVICE_ZIP_LOG;
        $this->assertStringContainsString('9 digits', $service);
        $this->assertStringContainsString('277CA', $service);
        $this->assertStringContainsString('CSC 500', $service);
        $this->assertStringContainsString('MA114', $service);
        $this->assertStringContainsString('country code', $service);
        $this->assertStringNotContainsString('Rejecting claim', $service);
    }

    /**
     * A pay-to warning is not a denial hold. The billing and service ZIP lines are.
     */
    public function testLogShowsDenialForBillingAndService(): void
    {
        $this->assertFalse(X125010837P::logShowsDenial("*** Pay to provider zip is not 9 digits.\n"));
        $this->assertTrue(X125010837P::logShowsDenial(X125010837P::BILLING_ZIP_LOG));
        $this->assertTrue(X125010837P::logShowsDenial(X125010837P::SERVICE_ZIP_LOG));
    }

    /**
     * Global off keeps the claim, including a short ZIP, and marks it billed.
     */
    public function testGlobalOffDoesNotDropAShortZip(): void
    {
        $this->withGlobals(false, function (): void {
            $gen = $this->generator(X125010837P::BILLING_ZIP_LOG);
            $gen->probe->validateAndClear($gen->claim);
            $screen = implode("\n", $gen->probe->screen);

            $this->assertSame(['mark-new'], $gen->probe->calls);
            $this->assertCount(1, $gen->batch->getClaims());
            $this->assertStringContainsString('Successfully marked claim', $screen);
            $this->assertStringNotContainsString('MA114', $screen);
        });
    }

    /**
     * Global on leaves a denying claim out of the batch and stores no version.
     */
    public function testGlobalOnHoldsAShortZipAndSkipsAnEmptyFile(): void
    {
        $this->withGlobals(true, function (): void {
            $checked = $this->generator(X125010837P::SERVICE_ZIP_LOG);
            $checked->probe->validateAndClear($checked->claim);
            $screen = implode("\n", $checked->probe->screen);

            $this->assertSame(['bind'], $checked->probe->calls);
            $this->assertSame([], $checked->batch->getClaims());
            $this->assertStringContainsString('MA114', $screen);
            $this->assertStringNotContainsString('Successfully', $screen);

            $validated = $this->generator(X125010837P::BILLING_ZIP_LOG);
            $validated->probe->validateOnly($validated->claim);
            $this->assertSame([], $validated->probe->calls);
            $this->assertSame([], $validated->batch->getClaims());

            $generated = $this->generator(X125010837P::BILLING_ZIP_LOG);
            $generated->probe->generate($generated->claim);
            $this->assertSame(['bind'], $generated->probe->calls);
            $generated->probe->completeToScreen([]);
            $generated->probe->completeToFile([]);
            $this->assertSame([], $generated->batch->getClaims());
            $this->assertContains('No claims were added to the batch.', $generated->probe->screen);
            $this->assertContains('No claim file was written.', $generated->probe->screen);
        });
    }

    /**
     * Global on still sends a claim the log does not flag, and marks it billed.
     */
    public function testGlobalOnKeepsANineDigitZip(): void
    {
        $this->withGlobals(true, function (): void {
            $gen = $this->generator('X12 validate patient on 2026-09-29.');
            $gen->probe->validateAndClear($gen->claim);

            $this->assertSame(['bind', 'remember', 'mark-existing'], $gen->probe->calls);
            $this->assertCount(1, $gen->batch->getClaims());
            $this->assertStringContainsString('Successfully marked claim', implode("\n", $gen->probe->screen));
        });
    }

    /**
     * The billed update names the version this run inserted.
     */
    public function testBilledUpdateTargetsTheInsertedVersion(): void
    {
        $this->assertSame(
            ['ORDER BY version DESC LIMIT 1', []],
            BillingUtilities::existingClaimVersionSql(null)
        );
        $this->assertSame(
            ['ORDER BY version DESC LIMIT 1', []],
            BillingUtilities::existingClaimVersionSql(0)
        );
        $this->assertSame(['AND version = ? ', [4]], BillingUtilities::existingClaimVersionSql(4));
        $this->assertSame(4, BillingUtilities::insertedClaimVersion(4));
        $this->assertSame(4, BillingUtilities::insertedClaimVersion('4'));
        $this->assertSame(0, BillingUtilities::insertedClaimVersion('4abc'));
        $this->assertSame(0, BillingUtilities::insertedClaimVersion(null));
    }

    #[DataProvider('billedUpdateStoredProvider')]
    public function testBilledUpdateStoredRequiresTheBilledRow(mixed $row, bool $stored): void
    {
        $this->assertSame($stored, BillingUtilities::billedUpdateStored($row));
    }

    /**
     * @return array<string, array{mixed, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function billedUpdateStoredProvider(): array
    {
        return [
            'missing row' => [false, false],
            'billed int' => [['status' => 2], true],
            'billed string' => [['status' => '2'], true],
            'still unbilled' => [['status' => 1], false],
            'unbilled string' => [['status' => '1'], false],
            'no status' => [[], false],
        ];
    }

    #[DataProvider('unbilledClaimVersionProvider')]
    public function testUnbilledClaimVersionReadsTheStoredVersion(mixed $row, ?int $version): void
    {
        $this->assertSame($version, BillingUtilities::unbilledClaimVersion($row));
    }

    /**
     * @return array<string, array{mixed, ?int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unbilledClaimVersionProvider(): array
    {
        return [
            'missing row' => [false, null],
            'version int' => [['version' => 4], 4],
            'version string' => [['version' => '4'], 4],
            'zero' => [['version' => 0], null],
            'junk' => [['version' => '4abc'], null],
            'empty' => [[], null],
        ];
    }

    /**
     * Hold on keeps the inserted version through the billed update and the filename.
     */
    public function testHoldKeepsTheInsertedVersionOnTheBilledUpdate(): void
    {
        $this->withGlobals(true, function (): void {
            $probe = $this->versionProbe();
            $probe->generate($this->versionClaim());

            $this->assertSame([
                [true, null],
                [false, 4],
            ], $probe->writes);
            $this->assertSame([1, 1], $probe->writeStatus);
            $probe->fileOnDisk = true;
            $probe->completeToFile([]);

            $this->assertSame([
                [true, null],
                [false, 4],
                [false, 4],
            ], $probe->writes);
            $this->assertSame([1, 1, 2], $probe->writeStatus);
        });
    }

    /**
     * The next claim on the same run does not reuse the previous version.
     */
    public function testHoldOffStillUpdatesTheLatestVersion(): void
    {
        $this->withGlobals(true, function (): void {
            $probe = $this->versionProbe();
            $probe->generate($this->versionClaim());
            $probe->writes = [];
            OEGlobalsBag::getInstance()->set('gbl_hold_claims_that_will_deny', false);
            $probe->generate($this->versionClaim());

            $this->assertSame([
                [true, null],
                [false, null],
            ], $probe->writes);
        });
    }

    /**
     * X12 Direct keeps the inserted version on the billed update and the filename.
     */
    public function testDirectHoldKeepsTheInsertedVersion(): void
    {
        $this->withGlobals(true, function (): void {
            $probe = new DirectVersionProbe('generate');
            $probe->generate($this->versionClaim());

            $this->assertSame([
                [true, null],
                [false, 4],
            ], $probe->writes);
            $this->assertSame([1, 1], $probe->writeStatus);
        });
    }

    /**
     * A failed file-name write leaves that version. The next run names it again.
     */
    public function testRetryBillsTheUnbilledVersion(): void
    {
        $this->withGlobals(true, function (): void {
            $probe = $this->versionProbe();
            $probe->billedUpdateResult = 0;
            $probe->generate($this->versionClaim());

            $this->assertSame([
                [true, null],
                [false, 4],
            ], $probe->writes);
            $this->assertSame([], $probe->storedClaims());

            $probe->writes = [];
            $probe->writeStatus = [];
            $probe->billedUpdateResult = 1;
            $probe->openUnbilled = 4;
            $probe->generate($this->versionClaim());

            $this->assertSame([
                [false, 4],
            ], $probe->writes);
            $this->assertSame([1], $probe->writeStatus);
            $this->assertCount(1, $probe->storedClaims());
            $probe->fileOnDisk = true;
            $probe->completeToFile([]);

            $this->assertSame([
                [false, 4],
                [false, 4],
            ], $probe->writes);
            $this->assertSame([1, 2], $probe->writeStatus);
        });
    }

    /**
     * X12 Direct bills the unbilled version instead of inserting another.
     */
    public function testDirectBillsTheUnbilledVersion(): void
    {
        $this->withGlobals(true, function (): void {
            $probe = new DirectVersionProbe('generate');
            $probe->openUnbilled = 7;
            $probe->generate($this->versionClaim());

            $this->assertSame([
                [false, 7],
            ], $probe->writes);
            $this->assertSame([1], $probe->writeStatus);
        });
    }

    /**
     * The hold reads the ZIP flags. Text in the log is not a denial.
     */
    public function testPatientNameDoesNotHoldTheClaim(): void
    {
        $this->withGlobals(true, function (): void {
            $gen = $this->generator(
                'X12 validate ' . X125010837P::BILLING_ZIP_LOG . ' ' . X125010837P::SERVICE_ZIP_LOG,
                new FacilityZipDenial()
            );
            $gen->probe->validateAndClear($gen->claim);

            $this->assertSame(['bind', 'remember', 'mark-existing'], $gen->probe->calls);
            $this->assertCount(1, $gen->batch->getClaims());
            $this->assertStringContainsString('Successfully marked claim', implode("\n", $gen->probe->screen));
        });
    }

    /**
     * A failed billed update keeps the accepted claim out of the batch.
     */
    public function testAcceptedClaimStaysOutWhenTheBilledUpdateFails(): void
    {
        $this->withGlobals(true, function (): void {
            $gen = $this->generator('X12 validate patient on 2026-09-29.');
            $gen->probe->billedUpdateLands = false;
            $gen->probe->validateAndClear($gen->claim);
            $screen = implode("\n", $gen->probe->screen);

            $this->assertSame(['bind', 'remember', 'mark-existing'], $gen->probe->calls);
            $this->assertSame([], $gen->batch->getClaims());
            $this->assertStringContainsString(FacilityZipDenial::LEFT_OUT_NOT_BILLED, $screen);
            $this->assertStringNotContainsString('Successfully', $screen);
        });
    }

    /**
     * A failed insert does not send the claim or update another version.
     */
    public function testClaimStaysOutWhenTheInsertFails(): void
    {
        $this->withGlobals(true, function (): void {
            $gen = $this->generator('X12 validate patient on 2026-09-29.');
            $gen->probe->payerStored = false;
            $gen->probe->generate($gen->claim);

            $this->assertSame(['bind', 'remember'], $gen->probe->calls);
            $this->assertSame([], $gen->batch->getClaims());
            $this->assertContains(FacilityZipDenial::LEFT_OUT_NOT_SAVED, $gen->probe->screen);
        });
    }

    /**
     * A second run of a held denial still stores no claim version.
     */
    public function testHeldDenialDoesNotStoreAnotherVersionOnRetry(): void
    {
        $this->withGlobals(true, function (): void {
            $gen = $this->generator(X125010837P::BILLING_ZIP_LOG);
            $gen->probe->generate($gen->claim);
            $gen->probe->generate($gen->claim);

            $this->assertSame(['bind', 'bind'], $gen->probe->calls);
            $this->assertSame([], $gen->batch->getClaims());
        });
    }

    /**
     * An accepted claim enters the batch only after the billed write lands.
     */
    public function testClaimEntersBatchOnlyAfterTheBilledWriteLands(): void
    {
        $probe = new InclusionProbe('validate');
        $none = new FacilityZipDenial();
        $billing = new FacilityZipDenial(true, false);
        $service = new FacilityZipDenial(false, true);

        $this->assertFalse($probe->enters(true, $billing, true, true));
        $this->assertFalse($probe->enters(true, $service, false, false));
        $this->assertFalse($probe->enters(true, $none, true, false));
        $this->assertTrue($probe->enters(true, $none, true, true));
        $this->assertTrue($probe->enters(true, $none, false, false));
        $this->assertTrue($probe->enters(false, $billing, false, false));
    }

    /**
     * A pay-to warning does not hold the claim when the denial hold is on.
     */
    public function testGlobalOnDoesNotHoldAPayToWarning(): void
    {
        $this->withGlobals(true, function (): void {
            $gen = $this->generator("*** Pay to provider zip is not 9 digits.\n");
            $gen->probe->validateOnly($gen->claim);

            $this->assertSame([], $gen->probe->calls);
            $this->assertCount(1, $gen->batch->getClaims());
            $this->assertStringContainsString('Successfully validated claim', implode("\n", $gen->probe->screen));
        });
    }

    /**
     * @param callable(): void $run
     */
    private function withGlobals(bool $hold, callable $run): void
    {
        $bag = OEGlobalsBag::getInstance();
        $keys = ['OE_SITE_DIR', 'temp_skip_translations', 'gbl_hold_claims_that_will_deny'];
        $saved = [];
        $present = [];
        foreach ($keys as $key) {
            $present[$key] = $bag->has($key);
            $saved[$key] = $present[$key] ? $bag->get($key) : null;
        }

        try {
            $bag->set('OE_SITE_DIR', sys_get_temp_dir());
            $bag->set('temp_skip_translations', true);
            $bag->set('gbl_hold_claims_that_will_deny', $hold);
            $run();
        } finally {
            foreach ($keys as $key) {
                if ($present[$key]) {
                    $bag->set($key, $saved[$key]);
                } else {
                    $bag->remove($key);
                    unset($GLOBALS[$key]);
                }
            }
        }
    }

    /**
     * Standard generator, batch, and claim for one hold case.
     */
    private function generator(string $log, ?FacilityZipDenial $denial = null): HoldZipFixture
    {
        $probe = new HoldZipGenerator('validate');
        $probe->renderedLog = $log;
        $probe->denial = $denial ?? new FacilityZipDenial(
            str_contains($log, X125010837P::BILLING_ZIP_LOG),
            str_contains($log, X125010837P::SERVICE_ZIP_LOG),
        );
        $batch = new BillingClaimBatch('.txt', [
            'claims' => [(object) ['action' => 'validate']],
        ]);
        $probe->useBatch($batch);
        $claim = $this->createMock(BillingClaim::class);
        $claim->action = 'validate';
        $claim->method('getId')->willReturn('9-9');

        return new HoldZipFixture($probe, $batch, $claim);
    }

    /**
     * Standard generator whose claim writes are recorded instead of stored.
     */
    private function versionProbe(): VersionHoldProbe
    {
        $probe = new VersionHoldProbe('generate');
        $probe->useBatch(new BillingClaimBatch('.txt', [
            'claims' => [(object) ['action' => 'validate']],
        ]));

        return $probe;
    }

    /**
     * Claim double for a version-tracking case. The constructor reads the database.
     */
    private function versionClaim(): BillingClaim
    {
        $claim = $this->createMock(BillingClaim::class);
        $claim->action = 'generate';
        $claim->method('getId')->willReturn('9-9');
        $claim->method('getPid')->willReturn('9');
        $claim->method('getEncounter')->willReturn('9');
        $claim->method('getPayorId')->willReturn('12');
        $claim->method('getPayorType')->willReturn(1);
        $claim->method('getTarget')->willReturn('standard');
        $claim->method('getPartner')->willReturn('3');

        return $claim;
    }

    /**
     * A held last claim drops the SE trailer. Earlier claims in the batch still need it.
     */
    public function testDirectWritesSeWhenTheHeldClaimWasLast(): void
    {
        $probe = new DirectSeProbe('validate');
        $batch = $this->batchWithPriorClaim();
        $this->assertSame(13, $probe->seal($batch, 12));
        $this->assertStringContainsString('SE*13*0001', $batch->getBatContent());

        $only = $this->batchWithPriorClaim();
        $only->setClaims([]);
        $this->assertSame(12, $probe->seal($only, 12));
        $this->assertStringNotContainsString('SE*', $only->getBatContent());
    }

    /**
     * Batch that already holds one claim, so a held last claim still closes SE.
     */
    private function batchWithPriorClaim(): BillingClaimBatch
    {
        $batch = new BillingClaimBatch('.txt', [
            'claims' => [(object) ['action' => 'validate']],
        ]);
        $content = new \ReflectionProperty(BillingClaimBatch::class, 'bat_content');
        $content->setValue($batch, 'ISA~');
        $transaction = new \ReflectionProperty(BillingClaimBatch::class, 'bat_stcount');
        $transaction->setValue($batch, 1);
        $batch->setClaims([new \stdClass()]);

        return $batch;
    }

    /**
     * A file that is already on disk is marked billed and stays out of the new batch.
     */
    public function testWrittenFileIsMarkedBilledWithoutAnotherBatch(): void
    {
        $this->withGlobals(true, function (): void {
            $probe = $this->versionProbe();
            $probe->openUnbilled = 4;
            $probe->storedFile = 'old-batch.txt';
            $probe->fileOnDisk = true;
            $probe->generate($this->versionClaim());

            $this->assertSame([[false, 4]], $probe->writes);
            $this->assertSame([2], $probe->writeStatus);
            $this->assertSame([], $probe->storedClaims());
            $this->assertContains(UnbilledFileDecision::ALREADY_WRITTEN, $probe->screen);
        });
    }

    /**
     * With the hold off, a file already on disk is billed and is not written again.
     */
    public function testHoldOffSettlesAPublishedFile(): void
    {
        $this->withGlobals(false, function (): void {
            $probe = $this->versionProbe();
            $probe->openUnbilled = 4;
            $probe->storedFile = 'old-batch.txt';
            $probe->fileOnDisk = true;
            $probe->generate($this->versionClaim());

            $this->assertSame([[false, 4]], $probe->writes);
            $this->assertSame([2], $probe->writeStatus);
            $this->assertSame([], $probe->storedClaims());
            $this->assertContains(UnbilledFileDecision::ALREADY_WRITTEN, $probe->screen);
        });
    }

    /**
     * A published file that cannot be billed stays out of a second batch.
     */
    public function testHoldOffLeavesAPublishedFileOutWhenSettlementFails(): void
    {
        $this->withGlobals(false, function (): void {
            $probe = $this->versionProbe();
            $probe->openUnbilled = 4;
            $probe->storedFile = 'old-batch.txt';
            $probe->fileOnDisk = true;
            $probe->billedUpdateResult = 0;
            $probe->generate($this->versionClaim());

            $this->assertSame([[false, 4]], $probe->writes);
            $this->assertSame([], $probe->storedClaims());
            $this->assertContains(FacilityZipDenial::LEFT_OUT_NOT_BILLED, $probe->screen);
        });
    }

    /**
     * A stored file name with no file is cleared, and the claim is generated again.
     */
    public function testMissingFileIsGeneratedAgain(): void
    {
        $this->withGlobals(true, function (): void {
            $probe = $this->versionProbe();
            $probe->openUnbilled = 4;
            $probe->storedFile = 'missing-batch.txt';
            $probe->fileOnDisk = false;
            $probe->generate($this->versionClaim());

            $this->assertSame([4], $probe->cleared);
            $this->assertSame(['missing-batch.txt'], $probe->clearedNames);
            $this->assertSame([[false, 4]], $probe->writes);
            $this->assertSame([1], $probe->writeStatus);
            $this->assertCount(1, $probe->storedClaims());
            $this->assertContains(UnbilledFileDecision::FILE_WAS_MISSING, $probe->screen);
        });
    }

    /**
     * A failed file write clears the name and does not mark the claim billed.
     */
    public function testFailedFileWriteClearsTheName(): void
    {
        $this->withGlobals(true, function (): void {
            $probe = $this->versionProbe();
            $probe->generate($this->versionClaim());
            $probe->fileStored = false;
            $probe->fileOnDisk = false;
            $probe->completeToFile([]);

            $this->assertSame([4], $probe->cleared);
            $this->assertCount(1, $probe->clearedNames);
            $this->assertStringContainsString('-batch', $probe->clearedNames[0]);
            $this->assertSame([1, 1], $probe->writeStatus);
            $this->assertContains('Error Generating Batch File', $probe->screen);
        });
    }

    /**
     * @return array<string, array{string, bool, UnbilledFileDecision}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unbilledFileDecisionProvider(): array
    {
        return [
            'no name yet' => ['', true, UnbilledFileDecision::None],
            'name and file' => ['batch.txt', true, UnbilledFileDecision::Present],
            'name without file' => ['batch.txt', false, UnbilledFileDecision::Missing],
        ];
    }

    #[DataProvider('unbilledFileDecisionProvider')]
    public function testUnbilledFileDecision(string $storedFile, bool $fileExists, UnbilledFileDecision $decision): void
    {
        $this->assertSame($decision, UnbilledFileDecision::fromStoredFile($storedFile, $fileExists));
    }


/**
     * A published batch matches its completion note. A short file does not.
     */
    public function testACompleteBatchIsTheOnlyFileThatCounts(): void
    {
        $directory = sys_get_temp_dir() . '/openemr-batch-' . bin2hex(random_bytes(4));
        mkdir($directory);
        try {
            file_put_contents($directory . '/short.txt', 'ISA');
            $this->assertFalse(BatchFilePublisher::isPublished($directory, 'short.txt'));
            file_put_contents($directory . '/batch.txt', 'OLD');
            $this->assertTrue(BatchFilePublisher::publish($directory, 'batch.txt', 'GS~'));
            $this->assertSame('GS~', file_get_contents($directory . '/batch.txt'));
            $this->assertTrue(BatchFilePublisher::isPublished($directory, 'batch.txt'));
            $this->assertFileDoesNotExist($directory . '/batch.txt.partial');
            $this->assertFalse(BatchFilePublisher::publish($directory, 'batch.txt', 'ISA~GS~'));
            $this->assertSame('GS~', file_get_contents($directory . '/batch.txt'));
            $link = $directory . '/link.txt';
            if (symlink($directory . '/batch.txt', $link)) {
                $this->assertFalse(BatchFilePublisher::publish($directory, 'link.txt', 'ISA~'));
            }
            $this->assertFalse(BatchFilePublisher::publish($directory, '../batch.txt', 'ISA~'));
            $this->assertFalse(BatchFilePublisher::publish($directory, 'batch.txt', ''));
            $blocked = $directory . '/blocked.txt.complete';
            mkdir($blocked);
            $this->assertFalse(BatchFilePublisher::publish($directory, 'blocked.txt', 'GS~'));
            $this->assertFileDoesNotExist($directory . '/blocked.txt');
            rmdir($blocked);
        } finally {
            foreach (glob($directory . '/*') ?: [] as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }


    /**
     * Direct validation says when every claim was held out of the batch.
     */
    public function testDirectValidationSaysWhenNoClaimsWereAdded(): void
    {
        $this->withGlobals(true, function (): void {
            $probe = new DirectEmptyScreenProbe('validate');
            $probe->completeToScreen([]);

            $this->assertContains('No claims were added to the batch.', $probe->screen);
        });
    }

    /**
     * A missing file stays assigned when this run does not hold the claim.
     */
    public function testMissingFileIsLeftAloneWithoutTheFence(): void
    {
        $this->withGlobals(true, function (): void {
            $probe = $this->versionProbe();
            $probe->openUnbilled = 4;
            $probe->storedFile = 'missing-batch.txt';
            $probe->fileOnDisk = false;
            $batch = (new \ReflectionProperty(GeneratorX12::class, 'batch'))->getValue($probe);
            $decision = (new \ReflectionMethod(VersionHoldProbe::class, 'previousFileDecision'))
                ->invoke($probe, $this->versionClaim(), $batch);

            $this->assertSame(UnbilledFileDecision::Busy, $decision);
            $this->assertSame([], $probe->cleared);
        });
    }

    /**
     * Another run does not clear a file name it does not own, and does not generate again.
     */
    public function testAnotherRunDoesNotTakeAFileStillBeingWritten(): void
    {
        $this->withGlobals(true, function (): void {
            $probe = $this->versionProbe();
            $probe->generationAllowed = false;
            $probe->openUnbilled = 4;
            $probe->storedFile = 'missing-batch.txt';
            $probe->fileOnDisk = false;
            $probe->generate($this->versionClaim());

            $this->assertSame([], $probe->cleared);
            $this->assertSame([], $probe->writes);
            $this->assertSame([], $probe->storedClaims());
            $this->assertContains(UnbilledFileDecision::STILL_BEING_WRITTEN, $probe->screen);
        });
    }

    /**
     * The batch is not published when the claim no longer names the file.
     */
    public function testBatchIsNotPublishedWhenTheClaimIsNoLongerOwned(): void
    {
        $this->withGlobals(true, function (): void {
            $directory = sys_get_temp_dir() . '/openemr-own-' . bin2hex(random_bytes(4));
            mkdir($directory);
            try {
                $batch = $this->ownedBatch($directory);
                $batch->requireGenerationOwner(fn (string $phase): bool => $phase === 'allow');

                $this->assertFalse($batch->write_batch_file());
                $this->assertFileDoesNotExist($directory . '/owned-batch.txt');
            } finally {
                $this->removeDirectory($directory);
            }
        });
    }

    /**
     * A file published after ownership was lost is removed and is not left to send.
     */
    public function testPublishedBatchIsDroppedWhenOwnershipIsLost(): void
    {
        $this->withGlobals(true, function (): void {
            $directory = sys_get_temp_dir() . '/openemr-own-' . bin2hex(random_bytes(4));
            mkdir($directory);
            try {
                $batch = $this->ownedBatch($directory);
                $calls = 0;
                $batch->requireGenerationOwner(function (string $phase) use (&$calls): bool {
                    $calls++;

                    return $calls === 1 && $phase !== '';
                });

                $this->assertFalse($batch->write_batch_file());
                $this->assertSame(2, $calls);
                $this->assertFileDoesNotExist($directory . '/owned-batch.txt');
                $this->assertFileDoesNotExist($directory . '/owned-batch.txt.complete');
            } finally {
                $this->removeDirectory($directory);
            }
        });
    }

    /**
     * @return array<string, array{mixed, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function billingSettlementLandedProvider(): array
    {
        return [
            'no billing rows' => [[], 'batch.txt', true],
            'billed with this file' => [[['billed' => 1, 'process_file' => 'batch.txt']], 'batch.txt', true],
            'billed string' => [[['billed' => '1', 'process_file' => 'batch.txt']], 'batch.txt', true],
            'still unbilled' => [[['billed' => 0, 'process_file' => 'batch.txt']], 'batch.txt', false],
            'other file' => [[['billed' => 1, 'process_file' => 'other.txt']], 'batch.txt', false],
            'one row left open' => [[
                ['billed' => 1, 'process_file' => 'batch.txt'],
                ['billed' => 0, 'process_file' => 'batch.txt'],
            ], 'batch.txt', false],
            'not a list' => ['nope', 'batch.txt', false],
            'row is not an array' => [['row'], 'batch.txt', false],
        ];
    }

    /**
     * A published file is removed when the claim is lost before it is queued.
     */
    public function testPublishedBatchIsDroppedBeforeItIsQueued(): void
    {
        $this->withGlobals(true, function (): void {
            OEGlobalsBag::getInstance()->set('auto_sftp_claims_to_x12_partner', true);
            $directory = sys_get_temp_dir() . '/openemr-own-' . bin2hex(random_bytes(4));
            mkdir($directory);
            try {
                $batch = $this->ownedBatch($directory);
                $batch->requireGenerationOwner(
                    fn (string $phase): bool => $phase !== 'before-queue' && $phase !== ''
                );

                $this->assertFalse($batch->write_batch_file());
                $this->assertFileDoesNotExist($directory . '/owned-batch.txt');
                $this->assertFileDoesNotExist($directory . '/owned-batch.txt.complete');
            } finally {
                $this->removeDirectory($directory);
            }
        });
    }

    /**
     * @return array<string, array{mixed, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function billingFileNamedProvider(): array
    {
        return [
            'no billing rows' => [[], 'batch.txt', true],
            'named with this file' => [[['process_file' => 'batch.txt']], 'batch.txt', true],
            'other file' => [[['process_file' => 'other.txt']], 'batch.txt', false],
            'one row left unnamed' => [[
                ['process_file' => 'batch.txt'],
                ['process_file' => ''],
            ], 'batch.txt', false],
            'not a list' => ['nope', 'batch.txt', false],
            'row is not an array' => [['row'], 'batch.txt', false],
            'blank name' => [[[]], '', false],
        ];
    }

    /**
     * Billing rows agree with the assigned file only when every active row names it.
     */
    #[DataProvider('billingFileNamedProvider')]
    public function testBillingFileNamed(mixed $rows, string $filename, bool $named): void
    {
        $this->assertSame($named, BillingUtilities::billingFileNamed($rows, $filename));
    }

    /**
     * Billing rows agree with the claim file only when every active row is billed to it.
     */
    #[DataProvider('billingSettlementLandedProvider')]
    public function testBillingSettlementLanded(mixed $rows, string $filename, bool $landed): void
    {
        $this->assertSame($landed, BillingUtilities::billingSettlementLanded($rows, $filename));
    }

    /**
     * @return array<string, array{mixed, int|null}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function billedEncounterLevelProvider(): array
    {
        return [
            'primary' => [1, 1],
            'secondary' => [2, 2],
            'primary string' => ['1', 1],
            'zero' => [0, null],
            'zero string' => ['0', null],
            'negative' => [-1, null],
            'blank' => ['', null],
            'not digits' => ['1a', null],
            'missing' => [null, null],
        ];
    }

    /**
     * A billed claim stores a positive payer level and leaves the others alone.
     */
    #[DataProvider('billedEncounterLevelProvider')]
    public function testBilledEncounterLevel(mixed $payerType, ?int $level): void
    {
        $this->assertSame($level, BillingUtilities::billedEncounterLevel($payerType));
    }

    /**
     * Batch whose content is ready to publish into a temporary directory.
     */
    private function ownedBatch(string $directory): BillingClaimBatch
    {
        $batch = new BillingClaimBatch('.txt', [
            'claims' => [(object) ['action' => 'validate']],
        ]);
        $batch->setBatFiledir($directory);
        $batch->setBatFilename('owned-batch.txt');
        (new \ReflectionProperty(BillingClaimBatch::class, 'bat_content'))->setValue($batch, 'ISA~');

        return $batch;
    }

    /**
     * Remove a temporary batch directory.
     */
    private function removeDirectory(string $directory): void
    {
        foreach (glob($directory . '/*') ?: [] as $file) {
            if (is_file($file) || is_link($file)) {
                unlink($file);
            }
        }
        if (is_dir($directory)) {
            rmdir($directory);
        }
    }

}

final class HoldZipFixture
{
    /**
     * Probe, batch, and claim for one hold case.
     */
    public function __construct(
        public HoldZipGenerator $probe,
        public BillingClaimBatch $batch,
        public BillingClaim $claim,
    ) {
    }

}

final class DirectEmptyScreenProbe extends GeneratorX12Direct
{
    /** @var list<string> */
    public array $screen = [];

    public function printToScreen(mixed $message): void
    {
        $this->screen[] = is_string($message) ? $message : '';
    }

}

final class HoldZipGenerator extends GeneratorX12
{
    /** @var list<string> */
    public array $screen = [];

    /** @var list<string> */
    public array $calls = [];

    public string $renderedLog = '';

    public FacilityZipDenial $denial;

    public bool $payerStored = true;

    public bool $billedUpdateLands = true;

    public ?BillingClaim $seen = null;

    /**
     * Screen lines the generator prints during the case.
     */
    public function printToScreen(mixed $message): void
    {
        $this->screen[] = is_string($message) ? $message : '';
    }

    /**
     * Accept a log string. The parent records it.
     */
    public function appendToLog(mixed $message): void
    {
        if (!is_string($message)) {
            return;
        }
    }

    /**
     * Point the probe at the batch the case built.
     */
    public function useBatch(BillingClaimBatch $batch): void
    {
        $this->batch = $batch;
    }

    /**
     * @return array{string, non-empty-list<string>, FacilityZipDenial}
     */
    protected function renderedClaim(BillingClaim $claim): array
    {
        $this->seen = $claim;

        return [$this->renderedLog, [''], $this->denial];
    }

    /**
     * Record the payer selected before the 837 is rendered.
     */
    protected function bindSelectedPayer(BillingClaim $claim): void
    {
        $this->calls[] = 'bind';
    }

    /**
     * Record that the payer was stored and the claim left unbilled.
     */
    protected function rememberPayer(BillingClaim $claim): bool
    {
        $this->seen = $claim;
        $this->calls[] = 'remember';

        return $this->payerStored;
    }

    /**
     * The screen cases do not read a stored file name.
     */
    protected function openUnbilledFile(BillingClaim $claim): string
    {
        return '';
    }

    /**
     * The screen cases do not read an unbilled row.
     *
     * @return array{version: int, process_file: string}|null
     */
    protected function openUnbilledAssignment(BillingClaim $claim): ?array
    {
        return null;
    }

    /**
     * Record the billed row written before the 837.
     */
    protected function markBilledNew(BillingClaim $claim): void
    {
        $this->seen = $claim;
        $this->calls[] = 'mark-new';
    }

    /**
     * Record the billed update of the row stored for this claim.
     */
    protected function markBilledExisting(BillingClaim $claim): bool
    {
        $this->seen = $claim;
        $this->calls[] = 'mark-existing';

        return $this->billedUpdateLands;
    }

    /**
     * Screen cases do not take a database fence.
     */
    protected function holdGeneration(BillingClaim $claim): bool
    {
        return true;
    }

    /**
     * Screen cases do not release a database fence.
     */
    protected function releaseGenerationFences(): void
    {
    }
}

final class DirectSeProbe extends GeneratorX12Direct
{
    /**
     * Call the SE trailer helper with the segment count from the held claim.
     */
    public function seal(BillingClaimBatch $batch, int $segmentCount): int
    {
        return $this->appendSeForHeldLastClaim($batch, $segmentCount);
    }
}

final class VersionHoldProbe extends GeneratorX12
{
    /** @var list<array{0: bool, 1: ?int}> */
    public array $writes = [];

    public ?int $openUnbilled = null;

    public string $storedFile = '';

    public bool $fileOnDisk = false;

    public bool $fileStored = true;

    public bool $generationAllowed = true;

    /** @var list<int> */
    public array $writeStatus = [];

    /** @var list<int> */
    public array $cleared = [];

    /** @var list<string> */
    public array $clearedNames = [];

    /** @var list<string> */
    public array $screen = [];

    public int $billedUpdateResult = 1;

    /**
     * Point the probe at the batch the case built.
     */
    public function useBatch(BillingClaimBatch $batch): void
    {
        $this->batch = $batch;
    }

    /**
     * Claims this run put in the batch.
     *
     * @return array<mixed>
     */
    public function storedClaims(): array
    {
        return $this->batch->getClaims();
    }

    /**
     * Record each claim write. An insert returns version 4.
     */
    protected function writeClaimRow(
        mixed $newversion,
        mixed $patientId,
        mixed $encounterId,
        mixed $payerId = -1,
        mixed $payerType = -1,
        mixed $status = -1,
        mixed $billProcess = -1,
        string $processFile = '',
        string $target = '',
        mixed $partnerId = -1,
        ?int $claimVersion = null
    ): mixed {
        $this->writes[] = [(bool) $newversion, $claimVersion];
        $this->writeStatus[] = is_int($status) ? $status : 0;

        return $newversion ? 4 : $this->billedUpdateResult;
    }

    /**
     * The version case does not read the stored file name.
     */
    protected function openUnbilledFile(BillingClaim $claim): string
    {
        return $this->storedFile;
    }

    /**
     * Version and file name from the same stand-in row.
     *
     * @return array{version: int, process_file: string}|null
     */
    protected function openUnbilledAssignment(BillingClaim $claim): ?array
    {
        if ($this->openUnbilled === null) {
            return null;
        }

        return [
            'version' => $this->openUnbilled,
            'process_file' => $this->storedFile,
        ];
    }

    /**
     * The version case decides whether the named file is present.
     */
    protected function claimFileLanded(BillingClaimBatch $batch, string $filename): bool
    {
        return $this->fileOnDisk;
    }

    /**
     * Record a cleared file name instead of updating claims.
     */
    protected function clearClaimFile(BillingClaim $claim, int $version, string $filename = ''): void
    {
        $this->cleared[] = $version;
        $this->clearedNames[] = $filename;
    }

    /**
     * The screen cases do not update the billing payer.
     */
    protected function bindSelectedPayer(BillingClaim $claim): void
    {
    }

    /**
     * Record the file name through the claim-write probe.
     */
    protected function stampClaimFile(BillingClaim $claim, int $version, string $filename): bool
    {
        return $this->landedClaimWrite($this->writeClaimRow(
            false,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            BillingClaim::STATUS_LEAVE_UNBILLED,
            BillingClaim::BILL_PROCESS_IN_PROGRESS,
            $filename,
            $claim->getTarget(),
            $claim->getPartner(),
            $version
        )) !== null;
    }

    /**
     * Record the billed update through the claim-write probe.
     */
    protected function settleClaimFile(BillingClaim $claim, int $version, string $filename): bool
    {
        return $this->landedClaimWrite($this->writeClaimRow(
            false,
            $claim->getPid(),
            $claim->getEncounter(),
            -1,
            -1,
            BillingClaim::STATUS_MARK_AS_BILLED,
            BillingClaim::BILL_PROCESS_BILLED,
            $filename,
            '',
            -1,
            $version
        )) !== null;
    }

    /**
     * Skip the edi directory.
     */
    protected function storeBatchFile(BillingClaimBatch $batch): bool
    {
        return $this->fileStored;
    }

    /**
     * The version case does not read claims. Null means this run inserts.
     */
    protected function openUnbilledVersion(BillingClaim $claim): ?int
    {
        return $this->openUnbilled;
    }

    /**
     * Screen lines are unused in the version case.
     */
    public function printToScreen(mixed $message): void
    {
        $this->screen[] = is_string($message) ? $message : '';
    }

    /**
     * The version case does not write the billing log.
     */
    public function appendToLog(mixed $message): void
    {
    }

    /**
     * @return array{string, non-empty-list<string>, FacilityZipDenial}
     */
    protected function renderedClaim(BillingClaim $claim): array
    {
        return ['X12 generate patient on file.', [''], new FacilityZipDenial()];
    }

    /**
     * Record the fence without taking a database lock.
     */
    protected function holdGeneration(BillingClaim $claim): bool
    {
        if (!$this->generationAllowed) {
            return false;
        }
        $name = $this->generationFenceName($claim);
        if ($name === null) {
            return false;
        }
        if (!in_array($name, $this->generationFences, true)) {
            $this->generationFences[] = $name;
        }

        return true;
    }

    /**
     * Drop recorded fences without a database call.
     */
    protected function releaseGenerationFences(): void
    {
        $this->generationFences = [];
    }
}

final class InclusionProbe extends GeneratorX12
{
    /**
     * Expose the batch inclusion rule for one set of flags.
     */
    public function enters(
        bool $hold,
        FacilityZipDenial $denial,
        bool $billIfAccepted,
        bool $billedWriteLanded
    ): bool {
        return $this->claimEntersBatch($hold, $denial, $billIfAccepted, $billedWriteLanded);
    }
}

final class DirectVersionProbe extends GeneratorX12Direct
{
    /** @var list<array{0: bool, 1: ?int}> */
    public array $writes = [];

    public ?int $openUnbilled = null;

    /** @var list<int> */
    public array $writeStatus = [];

    /**
     * Record each claim write. An insert returns version 4.
     */
    protected function writeClaimRow(
        mixed $newversion,
        mixed $patientId,
        mixed $encounterId,
        mixed $payerId = -1,
        mixed $payerType = -1,
        mixed $status = -1,
        mixed $billProcess = -1,
        string $processFile = '',
        string $target = '',
        mixed $partnerId = -1,
        ?int $claimVersion = null
    ): mixed {
        $this->writes[] = [(bool) $newversion, $claimVersion];
        $this->writeStatus[] = is_int($status) ? $status : 0;

        return $newversion ? 4 : 1;
    }

    /**
     * The version case does not read claims. Null means this run inserts.
     */
    protected function openUnbilledVersion(BillingClaim $claim): ?int
    {
        return $this->openUnbilled;
    }

    /**
     * Record the file name through the claim-write probe.
     */
    protected function stampClaimFile(BillingClaim $claim, int $version, string $filename): bool
    {
        return $this->landedClaimWrite($this->writeClaimRow(
            false,
            $claim->getPid(),
            $claim->getEncounter(),
            $claim->getPayorId(),
            $claim->getPayorType(),
            BillingClaim::STATUS_LEAVE_UNBILLED,
            BillingClaim::BILL_PROCESS_IN_PROGRESS,
            $filename,
            $claim->getTarget(),
            $claim->getPartner(),
            $version
        )) !== null;
    }

    /**
     * Skip claim text. An accepted claim still marks the stored version billed.
     *
     * @return BillingClaimBatch
     */
    protected function updateBatchFile(BillingClaim $claim, bool $billIfAccepted = false)
    {
        if ($billIfAccepted && $this->rememberPayer($claim)) {
            if ($this->billWhenTheFileLands) {
                $batch = new BillingClaimBatch('.txt', [
                    'claims' => [(object) ['action' => 'validate']],
                ]);
                $this->noteClaimFile($claim, $batch);

                return $batch;
            }
            $this->markBilledExisting($claim);
        }

        return new BillingClaimBatch('.txt', [
            'claims' => [(object) ['action' => 'validate']],
        ]);
    }

    /**
     * Screen lines are unused in the version case.
     */
    public function printToScreen(mixed $message): void
    {
    }

    /**
     * The version case does not write the billing log.
     */
    public function appendToLog(mixed $message): void
    {
    }
}
