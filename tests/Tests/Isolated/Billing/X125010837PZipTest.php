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

    private function generator(string $log): HoldZipFixture
    {
        $probe = new HoldZipGenerator('validate');
        $probe->renderedLog = $log;
        $batch = new BillingClaimBatch('.txt', [
            'claims' => [(object) ['action' => 'validate']],
        ]);
        $probe->useBatch($batch);
        $claim = $this->createMock(BillingClaim::class);
        $claim->action = 'validate';
        $claim->method('getId')->willReturn('9-9');

        return new HoldZipFixture($probe, $batch, $claim);
    }
}

final class HoldZipFixture
{
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

    public ?BillingClaim $seen = null;

    public function printToScreen(mixed $message): void
    {
        $this->screen[] = is_string($message) ? $message : '';
    }

    public function appendToLog(mixed $message): void
    {
        if (!is_string($message)) {
            return;
        }
    }

    public function useBatch(BillingClaimBatch $batch): void
    {
        $this->batch = $batch;
    }

    /**
     * @return array{string, list<string>}
     */
    protected function renderedClaim(BillingClaim $claim): array
    {
        $this->seen = $claim;

        return [$this->renderedLog, ['']];
    }

    protected function rememberPayer(BillingClaim $claim): void
    {
        $this->seen = $claim;
        $this->calls[] = 'remember';
    }

    protected function markBilledNew(BillingClaim $claim): void
    {
        $this->seen = $claim;
        $this->calls[] = 'mark-new';
    }

    protected function markBilledExisting(BillingClaim $claim): void
    {
        $this->seen = $claim;
        $this->calls[] = 'mark-existing';
    }
}
