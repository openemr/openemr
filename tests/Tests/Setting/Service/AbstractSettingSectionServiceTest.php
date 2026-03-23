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

namespace OpenEMR\Tests\Setting\Service;

use OpenEMR\Setting\Service\AbstractSettingSectionService;
use OpenEMR\Setting\Service\SettingSectionServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[Group('setting')]
#[CoversClass(AbstractSettingSectionService::class)]
#[CoversMethod(AbstractSettingSectionService::class, 'deslugify')]
class AbstractSettingSectionServiceTest extends TestCase
{
    private function getAbstractSettingSectionService(array $sectionNames): SettingSectionServiceInterface
    {
        $service = $this->getMockBuilder(AbstractSettingSectionService::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getSectionNames',
            ])
            ->getMock();
        $service->method('getSectionNames')->willReturn($sectionNames);
        return $service;
    }

    #[Test]
    #[DataProvider('deslugifyFailedDataProvider')]
    public function deslugifyFailedTest(
        array $sectionNames,
        string $slugifiedSectionName,
        string $expectedExceptionMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->getAbstractSettingSectionService($sectionNames)->deslugify($slugifiedSectionName);
    }

    public static function deslugifyFailedDataProvider(): iterable
    {
        yield [['A', 'B'], 'c', 'Section "c" does not exist. Possible ones: "a", "b"'];
    }
}
