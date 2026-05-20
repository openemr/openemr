<?php

/**
 * Isolated tests for PaymentAdvicePostingService pure helpers.
 *
 * The full posting flow needs a database, but the four pure helpers below
 * pin down behaviors the reviewer flagged as required pre-merge:
 *
 *   - buildIdempotencyReference():   fixed format used as the dedup key in
 *                                    ar_session.reference; if this format
 *                                    drifts, isAlreadyPosted() will silently
 *                                    stop matching prior postings and the
 *                                    same payment_advice_id can be posted
 *                                    twice. Pinning it down here means a
 *                                    test fails before the bug ships.
 *   - parsePatientControlNumber():   the integration boundary where the PCN
 *                                    string from ClaimRev is parsed into
 *                                    pid/encounter. Bad parsing posts to the
 *                                    wrong patient.
 *   - getClaimStatusLabel():         835 CLP02 → human label. Used for UI;
 *                                    a missing entry gives operators a blank
 *                                    column instead of the raw code.
 *   - sumServiceAmounts():           decimal-cents integrity for the
 *                                    billed/paid/adjusted totals computed
 *                                    in preview(). Asserted as string with
 *                                    sprintf("%.2f", …) so float drift on
 *                                    accumulated cents fails the test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\PaymentAdvicePostingService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

// The ClaimRev module isn't registered in the root composer autoloader; pull
// the source files needed by these helpers in directly.
require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/TypeCoerce.php';
require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-claimrev-connect/src/PaymentAdvicePostingService.php';

class PaymentAdvicePostingServiceTest extends TestCase
{
    // ---------------------------------------------------------------
    // buildIdempotencyReference()
    // ---------------------------------------------------------------

    public function testIdempotencyReferenceUsesFixedPrefix(): void
    {
        $this->assertSame(
            'ClaimRev-abc-123',
            PaymentAdvicePostingService::buildIdempotencyReference('abc-123')
        );
    }

    public function testIdempotencyReferenceIsDeterministic(): void
    {
        // Posting the same paymentAdviceId twice must produce the same
        // reference — the dedup query is `WHERE reference LIKE ?` against
        // this string, so any drift breaks idempotency.
        $first = PaymentAdvicePostingService::buildIdempotencyReference('PA-2026-04-15-001');
        $second = PaymentAdvicePostingService::buildIdempotencyReference('PA-2026-04-15-001');
        $this->assertSame($first, $second);
    }

    public function testDistinctPaymentAdviceIdsProduceDistinctReferences(): void
    {
        $a = PaymentAdvicePostingService::buildIdempotencyReference('PA-1');
        $b = PaymentAdvicePostingService::buildIdempotencyReference('PA-2');
        $this->assertNotSame($a, $b);
    }

    public function testReferencePrefixHasExpectedShape(): void
    {
        // The constant is part of the public contract; isAlreadyPosted()
        // uses LIKE '%' . prefix . id, so consumers may key off it too.
        // We assert the shape (must start with 'ClaimRev' and end with
        // a separator) rather than the exact value so future renaming
        // remains a deliberate decision rather than a one-line typo.
        $prefix = PaymentAdvicePostingService::REFERENCE_PREFIX;
        $this->assertStringStartsWith('ClaimRev', $prefix);
        $this->assertStringEndsWith('-', $prefix);
    }

    // ---------------------------------------------------------------
    // parsePatientControlNumber()
    // ---------------------------------------------------------------

    /**
     * @return array<string, array{string, ?array{pid: int, encounter: int}}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function pcnProvider(): array
    {
        return [
            'dash separator'              => ['42-101', ['pid' => 42, 'encounter' => 101]],
            'space separator'             => ['42 101', ['pid' => 42, 'encounter' => 101]],
            'multi-digit pid + encounter' => ['12345-99887', ['pid' => 12345, 'encounter' => 99887]],
            'extra trailing parts'        => ['42-101-extra', ['pid' => 42, 'encounter' => 101]],
            'empty string'                => ['', null],
            'no separator'                => ['42101', null],
            'zero pid'                    => ['0-101', null],
            'zero encounter'              => ['42-0', null],
            'negative pid'                => ['-1-101', null], // first preg_split chunk is empty → pid=0
            'non-numeric pid'             => ['abc-101', null], // (int)'abc' === 0
            'whitespace only'             => ['   ', null],
        ];
    }

    /**
     * @param array{pid: int, encounter: int}|null $expected
     */
    #[DataProvider('pcnProvider')]
    public function testParsePatientControlNumber(string $input, ?array $expected): void
    {
        $this->assertSame(
            $expected,
            PaymentAdvicePostingService::parsePatientControlNumber($input)
        );
    }

    // ---------------------------------------------------------------
    // getClaimStatusLabel()
    // ---------------------------------------------------------------

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function claimStatusLabelProvider(): array
    {
        return [
            'primary'   => ['1', 'Processed as Primary'],
            'secondary' => ['2', 'Processed as Secondary'],
            'tertiary'  => ['3', 'Processed as Tertiary'],
            'denied'    => ['4', 'Denied'],
            'pended'    => ['5', 'Pended'],
            'reversal'  => ['22', 'Reversal of Previous Payment'],
            // Unknown codes pass through so operators see something.
            'unknown'      => ['99', '99'],
            'empty string' => ['', ''],
        ];
    }

    #[DataProvider('claimStatusLabelProvider')]
    public function testGetClaimStatusLabel(string $code, string $expected): void
    {
        $this->assertSame(
            $expected,
            PaymentAdvicePostingService::getClaimStatusLabel($code)
        );
    }

    // ---------------------------------------------------------------
    // sumServiceAmounts() — decimal-cents integrity
    // ---------------------------------------------------------------

    public function testSumServiceAmountsEmpty(): void
    {
        $sums = PaymentAdvicePostingService::sumServiceAmounts([]);
        $this->assertSame('0.00', sprintf('%.2f', $sums['billed']));
        $this->assertSame('0.00', sprintf('%.2f', $sums['paid']));
        $this->assertSame('0.00', sprintf('%.2f', $sums['adjusted']));
    }

    public function testSumServiceAmountsSingleLine(): void
    {
        $sums = PaymentAdvicePostingService::sumServiceAmounts([
            ['chargeAmount' => 150.00, 'paymentAmount' => 120.00, 'adjustmentGroups' => [
                ['adjustments' => [['adjustmentAmount' => 30.00]]],
            ]],
        ]);
        $this->assertSame('150.00', sprintf('%.2f', $sums['billed']));
        $this->assertSame('120.00', sprintf('%.2f', $sums['paid']));
        $this->assertSame('30.00', sprintf('%.2f', $sums['adjusted']));
    }

    public function testSumServiceAmountsAccumulatesAcrossLinesAndGroups(): void
    {
        $sums = PaymentAdvicePostingService::sumServiceAmounts([
            ['chargeAmount' => 100.00, 'paymentAmount' => 80.00, 'adjustmentGroups' => [
                ['adjustments' => [
                    ['adjustmentAmount' => 15.00],
                    ['adjustmentAmount' => 5.00],
                ]],
            ]],
            ['chargeAmount' => 50.00, 'paymentAmount' => 40.00, 'adjustmentGroups' => [
                ['adjustments' => [['adjustmentAmount' => 10.00]]],
            ]],
        ]);
        $this->assertSame('150.00', sprintf('%.2f', $sums['billed']));
        $this->assertSame('120.00', sprintf('%.2f', $sums['paid']));
        $this->assertSame('30.00', sprintf('%.2f', $sums['adjusted']));
    }

    /**
     * Edge cents that classically drift under repeated float addition.
     * Asserting via sprintf("%.2f", …) catches drift at the cent level —
     * if the sum loses precision, the formatted result won't match.
     */
    public function testSumServiceAmountsHoldsPrecisionAcrossCentEdges(): void
    {
        // 0.10 + 0.20 = 0.30 (the canonical IEEE-754 stumbling block);
        // 0.01 + 0.02 + ... + 0.10 = 0.55; etc.
        $servicePaymentInfos = [];
        $expectedPaidCents = 0;
        for ($i = 1; $i <= 100; $i++) {
            // 100 lines of $0.0i (i pennies). Sum should be 0.01 + 0.02 + ... + 1.00 = $50.50
            $cents = $i;
            $servicePaymentInfos[] = [
                'chargeAmount' => 0.0,
                'paymentAmount' => $cents / 100.0,
                'adjustmentGroups' => [],
            ];
            $expectedPaidCents += $cents;
        }
        $sums = PaymentAdvicePostingService::sumServiceAmounts($servicePaymentInfos);
        // 100 * 101 / 200 == 50.50
        $expected = sprintf('%.2f', $expectedPaidCents / 100.0);
        $this->assertSame($expected, sprintf('%.2f', $sums['paid']));
        $this->assertSame('50.50', $expected); // sanity check on the expected value
    }

    public function testSumServiceAmountsTreatsMissingFieldsAsZero(): void
    {
        $sums = PaymentAdvicePostingService::sumServiceAmounts([
            ['chargeAmount' => 100.00], // no paymentAmount, no adjustmentGroups
            ['paymentAmount' => 50.00], // no chargeAmount
            [], // empty entry
        ]);
        $this->assertSame('100.00', sprintf('%.2f', $sums['billed']));
        $this->assertSame('50.00', sprintf('%.2f', $sums['paid']));
        $this->assertSame('0.00', sprintf('%.2f', $sums['adjusted']));
    }

    public function testSumServiceAmountsHandlesMalformedAdjustmentShape(): void
    {
        // Defensive: adjustmentGroups not an array, adjustments not an array
        $sums = PaymentAdvicePostingService::sumServiceAmounts([
            ['chargeAmount' => 10.00, 'paymentAmount' => 8.00, 'adjustmentGroups' => 'oops'],
            ['chargeAmount' => 20.00, 'paymentAmount' => 15.00, 'adjustmentGroups' => [
                ['adjustments' => 'oops'],
            ]],
        ]);
        $this->assertSame('30.00', sprintf('%.2f', $sums['billed']));
        $this->assertSame('23.00', sprintf('%.2f', $sums['paid']));
        $this->assertSame('0.00', sprintf('%.2f', $sums['adjusted']));
    }
}
