<?php

/**
 * Isolated tests for the mammography certification number a claim sends (837P loop 2300
 * REF*EW): the service facility's FDA number, only when the claim bills a mammogram.
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
use ReflectionClass;

#[Group('isolated')]
class ClaimMammographyCertTest extends TestCase
{
    /**
     * @param array<string, mixed> $facility
     * @param list<string> $codes
     */
    #[DataProvider('claimProvider')]
    public function testTheNumberIsSentOnlyForAMammogramAtACertifiedFacility(array $facility, array $codes, string $expected): void
    {
        $this->assertSame($expected, $this->claimFor($facility, $codes)->mammographyCertNumber());
    }

    /**
     * @return array<string, array{array<string, mixed>, list<string>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function claimProvider(): array
    {
        $certified = ['mammography_cert_number' => '123456'];
        return [
            'screening mammogram' => [$certified, ['77067'], '123456'],
            'diagnostic mammogram among other lines' => [$certified, ['76641', '77065'], '123456'],
            'tomosynthesis add-on by HCPCS, lower case' => [$certified, ['g0279'], '123456'],
            'number is trimmed and cleaned for X12' => [['mammography_cert_number' => ' ab*123456 '], ['77066'], 'AB123456'],
            'no mammogram on the claim' => [$certified, ['71046', '76641'], ''],
            'no service lines' => [$certified, [], ''],
            'facility has no number' => [['mammography_cert_number' => ''], ['77067'], ''],
            'facility number is null' => [['mammography_cert_number' => null], ['77067'], ''],
            'facility row predates the column' => [[], ['77067'], ''],
        ];
    }

    public function testHasMammographyReadsEveryServiceLine(): void
    {
        $this->assertTrue($this->claimFor([], ['99213', ' 77063 '])->hasMammography());
        $this->assertFalse($this->claimFor([], ['99213', '77080'])->hasMammography());
    }

    /**
     * A Claim with only its service facility and service lines set; the constructor needs a database.
     *
     * @param array<string, mixed> $facility
     * @param list<string> $codes
     */
    private function claimFor(array $facility, array $codes): Claim
    {
        $claim = (new ReflectionClass(Claim::class))->newInstanceWithoutConstructor();
        $claim->facility = $facility;
        $claim->procs = array_map(static fn(string $code): array => ['code' => $code], $codes);
        return $claim;
    }
}
