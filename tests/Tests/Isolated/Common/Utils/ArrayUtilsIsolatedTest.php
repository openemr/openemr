<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\ArrayUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[Group('isolated')]
#[Group('utils')]
#[CoversClass(ArrayUtils::class)]
#[CoversMethod(ArrayUtils::class, 'filter')]
class ArrayUtilsIsolatedTest extends TestCase
{
    #[Test]
    #[DataProvider('filterFailedDataProvider')]
    public function filterFailedTest(
        array $data,
        array $allowedFields,
        string $expectedExceptionMessage,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        ArrayUtils::filter($data, $allowedFields);
    }

    public static function filterFailedDataProvider(): iterable
    {
        yield 'Empty data' => [
            [],
            [
                'field_1',
            ],
            'Unknown allowed fields: field_1. Valid ones: .', // Ugly but it should never happen
        ];

        yield 'Single item data' => [
            [
                'field_a' => 'a',
            ],
            [
                'field_1',
            ],
            'Unknown allowed fields: field_1. Valid ones: field_a.',
        ];

        yield 'Multiple items data' => [
            [
                'field_a' => 'a',
                'field_b' => 'b',
            ],
            [
                'field_1',
            ],
            'Unknown allowed fields: field_1. Valid ones: field_a, field_b.',
        ];
    }

    #[Test]
    #[DataProvider('filterSucceededDataProvider')]
    public function filterSucceededTest(
        array $data,
        array $allowedFields,
        array $expected,
    ): void {
        $this->assertEquals(
            $expected,
            ArrayUtils::filter($data, $allowedFields),
        );
    }

    public static function filterSucceededDataProvider(): iterable
    {
        yield 'Empty' => [
            [],
            [],
            [],
        ];

        yield 'Empty allowed fields keeps data untouched' => [
            [
                'field_a' => 'a',
                'field_b' => 'b',
            ],
            [],
            [
                'field_a' => 'a',
                'field_b' => 'b',
            ],
        ];

        yield 'Single item allowed fields' => [
            [
                'field_a' => 'a',
                'field_b' => 'b',
            ],
            [
                'field_a',
            ],
            [
                'field_a' => 'a',
            ],
        ];

        yield 'Multiple items allowed fields' => [
            [
                'field_a' => 'a',
                'field_b' => 'b',
            ],
            [
                'field_a',
                'field_b',
            ],
            [
                'field_a' => 'a',
                'field_b' => 'b',
            ],
        ];
    }
}
