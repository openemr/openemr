<?php

/**
 * Isolated tests for Claim::x12SignatureAndRelatedCauses(), the end of the 837P
 * CLM segment (CLM10, CLM11), built from the claim's misc billing options.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\Claim;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class ClaimSignatureAndRelatedCausesTest extends TestCase
{
    #[DataProvider('relatedCauseProvider')]
    public function testSignatureSourceAndRelatedCauses(
        bool $employment,
        bool $auto,
        bool $other,
        string $state,
        string $expected
    ): void {
        $claim = $this->claim([
            'employment_related' => $employment ? 1 : 0,
            'auto_accident' => $auto ? 1 : 0,
            'other_accident' => $other ? 1 : 0,
            'accident_state' => $state,
        ]);
        $this->assertSame($expected, $claim->x12SignatureAndRelatedCauses());
    }

    public function testNoMiscBillingOptionsEndsAtClm09(): void
    {
        $this->assertSame('', $this->claim([])->x12SignatureAndRelatedCauses());
    }

    /**
     * @param array<string, int|string> $billingOptions
     */
    private function claim(array $billingOptions): Claim
    {
        // The constructor loads the encounter from the database; these methods
        // read only the misc billing options.
        $claim = (new \ReflectionClass(Claim::class))->newInstanceWithoutConstructor();
        $claim->billing_options = $billingOptions;
        return $claim;
    }

    /**
     * @return array<string, array{bool, bool, bool, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function relatedCauseProvider(): array
    {
        return [
            'no related cause: segment ends at CLM09' => [false, false, false, '', ''],
            'workers comp: CLM10 P, CLM11 EM' => [true, false, false, '', '*P*EM'],
            'auto accident: state in CLM11-4' => [false, true, false, 'VT', '**AA:::VT'],
            'auto accident without a state: AA alone' => [false, true, false, '', '**AA'],
            'other accident: empty CLM10, CLM11 OA' => [false, false, true, '', '**OA'],
            'state uppercased' => [false, true, false, ' vt ', '**AA:::VT'],
            'state ignored without an auto accident' => [false, false, true, 'VT', '**OA'],
            'workers comp and auto: EM, AA, state' => [true, true, false, 'NH', '*P*EM:AA::NH'],
            'auto and other: AA, OA, state' => [false, true, true, 'VT', '**AA:OA::VT'],
            'all three: first two causes, state' => [true, true, true, 'VT', '*P*EM:AA::VT'],
            'workers comp and other: EM, OA' => [true, false, true, '', '*P*EM:OA'],
        ];
    }
}
