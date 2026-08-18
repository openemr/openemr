<?php

/**
 * Tests for CodingUtils.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Utils;

use OpenEMR\Common\Utils\CodingUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CodingUtilsTest extends TestCase
{
    #[DataProvider('activeCheckboxProvider')]
    public function testIsActiveCheckboxChecked(mixed $active, mixed $mode, bool $expected): void
    {
        $this->assertSame($expected, CodingUtils::isActiveCheckboxChecked($active, $mode));
    }

    /**
     * @return array<string, array{mixed, mixed, bool}>
     */
    public static function activeCheckboxProvider(): array
    {
        return [
            'external modify with false' => [false, 'modify', false],
            'external modify with empty string' => ['', 'modify', false],
            'external modify with empty array' => [[], 'modify', false],
            'external modify with integer zero' => [0, 'modify', false],
            'external modify with string zero' => ['0', 'modify', false],
            'external modify with null' => [null, 'modify', true],
            'external modify with active integer' => [1, 'modify', true],
            'external modify with non-empty string' => ['active', 'modify', true],
            'external modify with non-empty array' => [[1], 'modify', true],
            'local edit with false' => [false, 'edit', false],
            'local edit with empty string' => ['', 'edit', false],
            'local edit with empty array' => [[], 'edit', false],
            'local edit with integer zero' => [0, 'edit', false],
            'local edit with string zero' => ['0', 'edit', false],
            'local edit with null' => [null, 'edit', false],
            'local edit with active integer' => [1, 'edit', true],
            'local edit with non-empty string' => ['active', 'edit', true],
            'local edit with non-empty array' => [[1], 'edit', true],
        ];
    }
}
