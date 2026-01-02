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

namespace OpenEMR\Tests\Setting\Service\Global;

use OpenEMR\Services\Globals\GlobalSettingSection;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Setting\Service\Global\GlobalSettingSectionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('setting')]
#[CoversClass(GlobalSettingSectionService::class)]
#[CoversMethod(GlobalSettingSectionService::class, 'slugify')]
#[CoversMethod(GlobalSettingSectionService::class, 'deslugify')]
class GlobalSettingSectionServiceTest extends TestCase
{
    private function getGlobalSettingSectionService(): GlobalSettingSectionService
    {
        $globalsService = $this->createMock(GlobalsService::class);
        $globalsService->method('getAllSections')->willReturn(GlobalSettingSection::ALL_SECTIONS);

        return new GlobalSettingSectionService($globalsService);
    }

    #[Test]
    #[DataProvider('slugifyDataProvider')]
    public function slugifyTest(
        string $sectionName,
        string $expectedSlugifiedSectionName
    ): void {
        $this->assertEquals(
            $expectedSlugifiedSectionName,
            $this->getGlobalSettingSectionService()->slugify($sectionName)
        );
    }

    public static function slugifyDataProvider(): iterable
    {
        yield 'Empty' => ['', ''];

        yield [GlobalSettingSection::PDF, 'pdf'];
        yield [GlobalSettingSection::PATIENT_BANNER_BAR, 'patient-banner-bar'];
        yield [GlobalSettingSection::ENCOUNTER_FORM, 'encounter-form'];
        yield [GlobalSettingSection::E_SIGN, 'e-sign'];
        yield [GlobalSettingSection::LOGIN_PAGE, 'login-page'];
    }

    #[Test]
    #[DataProvider('deslugifyDataProvider')]
    public function deslugifyTest(
        string $slugifiedSectionName,
        string $expectedRestoredSectionName
    ): void {
        $this->assertEquals(
            $expectedRestoredSectionName,
            $this->getGlobalSettingSectionService()->deslugify($slugifiedSectionName)
        );
    }

    public static function deslugifyDataProvider(): iterable
    {
        yield ['pdf', GlobalSettingSection::PDF];
        yield ['patient-banner-bar', GlobalSettingSection::PATIENT_BANNER_BAR];
        yield ['encounter-form', GlobalSettingSection::ENCOUNTER_FORM];
        yield ['e-sign', GlobalSettingSection::E_SIGN];
        yield ['login-page', GlobalSettingSection::LOGIN_PAGE];
    }
}
