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

namespace OpenEMR\Tests\Setting\Service\User;

use OpenEMR\Services\Globals\GlobalSettingSection;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Setting\Service\User\UserSpecificSettingSectionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('setting')]
#[CoversClass(UserSpecificSettingSectionService::class)]
#[CoversMethod(UserSpecificSettingSectionService::class, 'slugify')]
#[CoversMethod(UserSpecificSettingSectionService::class, 'deslugify')]
class UserSpecificSettingSectionServiceTest extends TestCase
{
    private function getUserSettingSectionService(): UserSpecificSettingSectionService
    {
        $globalsService = $this->createMock(GlobalsService::class);
        $globalsService->method('getUserSpecificSections')->willReturn(GlobalSettingSection::USER_SPECIFIC_SECTIONS);

        return new UserSpecificSettingSectionService($globalsService);
    }

    #[Test]
    #[DataProvider('slugifyDataProvider')]
    public function slugifyTest(
        string $sectionName,
        string $expectedSlugifiedSectionName
    ): void {
        $this->assertEquals(
            $expectedSlugifiedSectionName,
            $this->getUserSettingSectionService()->slugify($sectionName)
        );
    }

    public static function slugifyDataProvider(): iterable
    {
        yield 'Empty' => ['', ''];

        yield ['CDR', 'cdr'];
        yield ['Report', 'report'];
    }

    #[Test]
    #[DataProvider('deslugifyDataProvider')]
    public function deslugifyTest(
        string $slugifiedSectionName,
        string $expectedRestoredSectionName
    ): void {
        $this->assertEquals(
            $expectedRestoredSectionName,
            $this->getUserSettingSectionService()->deslugify($slugifiedSectionName)
        );
    }

    public static function deslugifyDataProvider(): iterable
    {
        yield ['cdr', 'CDR'];
        yield ['report', 'Report'];
    }
}
