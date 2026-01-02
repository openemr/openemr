<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\Globals;

use OpenEMR\Services\Globals\GlobalsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[Group('isolated')]
#[Group('setting')]
#[CoversClass(GlobalsService::class)]
#[CoversMethod(GlobalsService::class, 'isSectionExists')]
#[CoversMethod(GlobalsService::class, 'createSection')]
class GlobalsServiceIsolatedTest extends TestCase
{
    #[Test]
    #[DataProvider('isSectionExistsDataProvider')]
    public function isSectionExistsTest(
        array $metadata,
        string $sectionName,
        bool $expected,
    ): void {
        $this->assertEquals(
            $expected,
            (new GlobalsService($metadata))->isSectionExists($sectionName),
        );
    }

    public static function isSectionExistsDataProvider(): iterable
    {
        yield 'Empty' => [
            [],
            'First',
            false,
        ];

        yield 'Existing' => [
            [
                'First' => [],
            ],
            'First',
            true,
        ];
    }

    #[Test]
    #[DataProvider('createSectionFailedDataProvider')]
    public function createSectionFailedTest(
        array $metadata,
        string $sectionName,
        ?string $beforeSectionName,
        string $expectedExceptionMessage,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $globalsService = new GlobalsService($metadata);
        $globalsService->createSection($sectionName, $beforeSectionName);
    }

    public static function createSectionFailedDataProvider(): iterable
    {
        yield 'Create already existing section' => [
            [
                'First' => [],
            ],
            'First',
            null,
            'Section First already exists',
        ];

        yield 'Create after non-existent' => [
            [],
            'Second',
            'First',
            'Section First does not exist',
        ];
    }

    #[Test]
    #[DataProvider('createSectionDataProvider')]
    public function createSectionTest(
        array $metadata,
        string $sectionName,
        ?string $beforeSectionName,
        array $expectedGlobalsMetadata,
    ): void {
        $globalsService = new GlobalsService($metadata);
        $globalsService->createSection($sectionName, $beforeSectionName);

        $this->assertEquals(
            $expectedGlobalsMetadata,
            $globalsService->getGlobalsMetadata(),
        );
    }

    public static function createSectionDataProvider(): iterable
    {
        yield 'Create first' => [
            [],
            'First',
            null,
            [
                'First' => [],
            ],
        ];

        yield 'Create second at the end' => [
            [
                'First' => [],
            ],
            'Second',
            null,
            [
                'First' => [],
                'Second' => [],
            ],
        ];

        yield 'Create third at the end' => [
            [
                'First' => [],
                'Second' => [],
            ],
            'Third',
            null,
            [
                'First' => [],
                'Second' => [],
                'Third' => [],
            ],
        ];

        yield 'Create after first' => [
            [
                'First' => [],
                'Second' => [],
            ],
            'Third',
            'First',
            [
                'First' => [],
                'Third' => [],
                'Second' => [],
            ],
        ];

        yield 'Create after last' => [
            [
                'First' => [],
                'Second' => [],
            ],
            'Third',
            'Second',
            [
                'First' => [],
                'Second' => [],
                'Third' => [],
            ],
        ];
    }
}
