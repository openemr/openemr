<?php

/**
 * Isolated tests for the end of the 837P CLM segment (CLM10, CLM11).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\X125010837P;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class X125010837PClaimSegmentTest extends TestCase
{
    #[DataProvider('relatedCauseProvider')]
    public function testSignatureSourceOnlyForWorkersComp(bool $employment, bool $auto, bool $other, string $expected): void
    {
        $this->assertSame($expected, X125010837P::claimSignatureAndRelatedCauses($employment, $auto, $other));
    }

    /**
     * @return array<string, array{bool, bool, bool, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function relatedCauseProvider(): array
    {
        return [
            'no related cause: segment ends at CLM09' => [false, false, false, ''],
            'workers comp: CLM10 P, CLM11 EM' => [true, false, false, '*P*EM'],
            'auto accident: empty CLM10, CLM11 AA' => [false, true, false, '**AA'],
            'other accident: empty CLM10, CLM11 OA' => [false, false, true, '**OA'],
            'employment takes precedence' => [true, true, false, '*P*EM'],
        ];
    }
}
