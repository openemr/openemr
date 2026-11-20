<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\UserTaxonomyService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class UserTaxonomyServiceTest extends TestCase
{
    /**
     * @codeCoverageIgnore
     * @return array<string, array{string, ?string}>
     */
    public static function codes(): array
    {
        return [
            'internal medicine' => ['207R00000X', '207R00000X'],
            'cardiology, lower case and padded' => [' 207rc0000x ', '207RC0000X'],
            'group code starting with digits' => ['193200000X', '193200000X'],
            'too short' => ['207R0000X', null],
            'too long' => ['207R000000X', null],
            'does not end in X' => ['207R000001', null],
            'letters first' => ['ABCR00000X', null],
            'a list in one field' => ['207R00000X;207RC0000X', null],
            'empty' => ['', null],
        ];
    }

    #[DataProvider('codes')]
    public function testNormalize(string $raw, ?string $expected): void
    {
        $this->assertSame($expected, UserTaxonomyService::normalize($raw));
    }

    public function testCleanListKeepsOrderAndSkipsBlanks(): void
    {
        $result = UserTaxonomyService::cleanList(['207RC0000X', '', '  ', '208D00000X']);
        $this->assertSame(['207RC0000X', '208D00000X'], $result['codes']);
        $this->assertSame([], $result['invalid']);
    }

    public function testCleanListDropsPrimaryAndDuplicates(): void
    {
        $result = UserTaxonomyService::cleanList(['207R00000X', '207rc0000x', '207RC0000X'], '207r00000x');
        $this->assertSame(['207RC0000X'], $result['codes']);
    }

    public function testCleanListReportsInvalidCodes(): void
    {
        $result = UserTaxonomyService::cleanList(['207RC0000X', 'cardiology', '12345']);
        $this->assertSame(['207RC0000X'], $result['codes']);
        $this->assertSame(['cardiology', '12345'], $result['invalid']);
    }

    public function testCleanListIgnoresNonScalarValues(): void
    {
        $result = UserTaxonomyService::cleanList([['207RC0000X'], null, '207RC0000X']);
        $this->assertSame(['207RC0000X'], $result['codes']);
    }
}
