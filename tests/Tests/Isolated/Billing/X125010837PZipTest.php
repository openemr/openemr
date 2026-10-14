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

use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingProcessor\Tasks\GeneratorX12;
use OpenEMR\Billing\BillingProcessor\Tasks\GeneratorX12Direct;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\FacilityZipDenial;
use OpenEMR\Billing\X125010837P;
use OpenEMR\Core\OEGlobalsBag;
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
     * Global on leaves a denying claim out of the batch and does not mark it billed.
     */
    public function testGlobalOnHoldsAShortZipAndSkipsAnEmptyFile(): void
    {
        $this->withGlobals(true, function (): void {
            $checked = $this->generator(X125010837P::SERVICE_ZIP_LOG);
            $checked->probe->validateAndClear($checked->claim);
            $screen = implode("\n", $checked->probe->screen);

            $this->assertSame(['remember'], $checked->probe->calls);
            $this->assertSame([], $checked->batch->getClaims());
            $this->assertStringContainsString('MA114', $screen);
            $this->assertStringNotContainsString('Successfully', $screen);

            $validated = $this->generator(X125010837P::BILLING_ZIP_LOG);
            $validated->probe->validateOnly($validated->claim);
            $this->assertSame([], $validated->probe->calls);
            $this->assertSame([], $validated->batch->getClaims());

            $generated = $this->generator(X125010837P::BILLING_ZIP_LOG);
            $generated->probe->generate($generated->claim);
            $this->assertSame(['remember'], $generated->probe->calls);
            $generated->probe->completeToScreen([]);
            $generated->probe->completeToFile([]);
            $this->assertSame([], $generated->batch->getClaims());
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

            $this->assertSame(['remember', 'mark-existing'], $gen->probe->calls);
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
                [false, 4],
            ], $probe->writes);
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
                [false, 4],
            ], $probe->writes);
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

            $this->assertSame(['remember', 'mark-existing'], $gen->probe->calls);
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

            $this->assertSame(['remember', 'mark-existing'], $gen->probe->calls);
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

            $this->assertSame(['remember'], $gen->probe->calls);
            $this->assertSame([], $gen->batch->getClaims());
            $this->assertContains(FacilityZipDenial::LEFT_OUT_NOT_SAVED, $gen->probe->screen);
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
     * Record that the payer was stored and the claim left unbilled.
     */
    protected function rememberPayer(BillingClaim $claim): bool
    {
        $this->seen = $claim;
        $this->calls[] = 'remember';

        return $this->payerStored;
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

    /**
     * Point the probe at the batch the case built.
     */
    public function useBatch(BillingClaimBatch $batch): void
    {
        $this->batch = $batch;
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

        return $newversion ? 4 : 1;
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

    /**
     * @return array{string, non-empty-list<string>, FacilityZipDenial}
     */
    protected function renderedClaim(BillingClaim $claim): array
    {
        return ['X12 generate patient on file.', [''], new FacilityZipDenial()];
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

        return $newversion ? 4 : 1;
    }

    /**
     * Skip claim text. An accepted claim still marks the stored version billed.
     *
     * @return BillingClaimBatch
     */
    protected function updateBatchFile(BillingClaim $claim, bool $billIfAccepted = false)
    {
        if ($billIfAccepted) {
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
