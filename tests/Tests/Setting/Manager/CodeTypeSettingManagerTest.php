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

namespace OpenEMR\Tests\Setting\Manager;

use OpenEMR\Common\Database\Repository\Settings\CodeTypeRepository;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\Globals\GlobalsServiceFactory;
use OpenEMR\Setting\Driver\GlobalSettingDriver;
use OpenEMR\Setting\Driver\SettingDriverInterface;
use OpenEMR\Setting\Manager\CodeTypeSettingManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[Group('setting')]
#[Group('code-type')]
#[CoversClass(CodeTypeSettingManager::class)]
#[CoversMethod(CodeTypeSettingManager::class, 'isDataTypeSupported')]
#[CoversMethod(CodeTypeSettingManager::class, 'validateSettingValue')]
#[CoversMethod(CodeTypeSettingManager::class, 'normalizeSetting')]
class CodeTypeSettingManagerTest extends TestCase
{
    private const ALL_CODE_TYPES = [
        ['ct_key' => 'CPT4', 'ct_label' => 'CPT4 Procedure/Service'],
        ['ct_key' => 'CPTII', 'ct_label' => 'CPTII Performance Measures'],
        ['ct_key' => 'CVX', 'ct_label' => 'CVX Immunization'],
        ['ct_key' => 'DSMIV', 'ct_label' => 'DSMIV Diagnosis'],
        ['ct_key' => 'HCPCS', 'ct_label' => 'HCPCS Procedure/Service'],
        ['ct_key' => 'ICD10', 'ct_label' => 'ICD10 Diagnosis'],
        ['ct_key' => 'ICD10-PCS', 'ct_label' => 'ICD10 Procedure/Service'],
        ['ct_key' => 'ICD9', 'ct_label' => 'ICD9 Diagnosis'],
        ['ct_key' => 'ICD9-SG', 'ct_label' => 'ICD9 Procedure/Service'],
        ['ct_key' => 'LOINC', 'ct_label' => 'LOINC'],
        ['ct_key' => 'NCI-CONCEPT-ID', 'ct_label' => 'NCI CONCEPT ID'],
        ['ct_key' => 'OID', 'ct_label' => 'OID Valueset'],
        ['ct_key' => 'PHIN Questions', 'ct_label' => 'PHIN Questions'],
        ['ct_key' => 'RXCUI', 'ct_label' => 'RXCUI Medication'],
        ['ct_key' => 'SNOMED', 'ct_label' => 'SNOMED Diagnosis'],
        ['ct_key' => 'SNOMED-CT', 'ct_label' => 'SNOMED Clinical Term'],
        ['ct_key' => 'SNOMED-PR', 'ct_label' => 'SNOMED Procedure'],
        ['ct_key' => 'VALUESET', 'ct_label' => 'CQM Valueset'],
    ];

    #[Test]
    #[DataProvider('isDataTypeSupportedDataProvider')]
    public function isDataTypeSupportedTest(
        string $dataType,
        bool $expected,
    ): void {
        $manager = new CodeTypeSettingManager(
            $this->createMock(CodeTypeRepository::class),
            $this->createMock(SettingDriverInterface::class),
            $this->createMock(GlobalsService::class),
        );

        $this->assertEquals(
            $expected,
            $manager->isDataTypeSupported($dataType),
        );
    }

    public static function isDataTypeSupportedDataProvider(): iterable
    {
        yield [GlobalSetting::DATA_TYPE_ADDRESS_BOOK, false];
        yield [GlobalSetting::DATA_TYPE_BOOL, false];
        yield 'Supported DATA_TYPE_CODE_TYPES' => [GlobalSetting::DATA_TYPE_CODE_TYPES, true];
        yield [GlobalSetting::DATA_TYPE_COLOR_CODE, false];
        yield [GlobalSetting::DATA_TYPE_CSS, false];
        yield [GlobalSetting::DATA_TYPE_DEFAULT_RANDOM_UUID, false];
        yield [GlobalSetting::DATA_TYPE_DEFAULT_VISIT_CATEGORY, false];
        yield [GlobalSetting::DATA_TYPE_ENCRYPTED, false];
        yield [GlobalSetting::DATA_TYPE_ENCRYPTED_HASH, false];
        yield [GlobalSetting::DATA_TYPE_ENUM, false];
        yield [GlobalSetting::DATA_TYPE_HOUR, false];
        yield [GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION, false];
        yield [GlobalSetting::DATA_TYPE_LANGUAGE, false];
        yield [GlobalSetting::DATA_TYPE_MULTI_DASHBOARD_CARDS, false];
        yield [GlobalSetting::DATA_TYPE_MULTI_LANGUAGE_SELECT, false];
        yield [GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR, false];
        yield [GlobalSetting::DATA_TYPE_NUMBER, false];
        yield [GlobalSetting::DATA_TYPE_PASS, false];
        yield [GlobalSetting::DATA_TYPE_TABS_CSS, false];
    }

    #[Test]
    #[DataProvider('validateSettingValueFailedDataProvider')]
    public function validateSettingValueFailedTest(
        string $settingValue,
        string $expectedExceptionMessage,
    ): void {
        $manager = new CodeTypeSettingManager(
            $this->getRepositoryMock(),
            $this->createMock(SettingDriverInterface::class),
            $this->createMock(GlobalsService::class),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $manager->validateSettingValue('default_search_code_type', $settingValue);
    }

    public static function validateSettingValueFailedDataProvider(): iterable
    {
        yield 'Empty' => ['', 'Setting "default_search_code_type" can not accept value "". Expected one of: "CPT4" (CPT4 Procedure/Service), "CVX" (CVX Immunization), "DSMIV" (DSMIV Diagnosis)'];
        yield 'Random string' => ['Random string', 'Setting "default_search_code_type" can not accept value "Random string". Expected one of: "CPT4" (CPT4 Procedure/Service), "CVX" (CVX Immunization), "DSMIV" (DSMIV Diagnosis)'];
    }

    #[Test]
    #[DataProvider('normalizeSettingDataProvider')]
    public function normalizeSettingTest(
        string $settingKey,
        string $settingDefaultValue,
        ?string $settingValue,
        array $expectedNormalizedSetting,
    ): void {
        $globalSettingDriver = $this->createMock(GlobalSettingDriver::class);
        $globalSettingDriver->method('getSettingDefaultValue')->with($settingKey)->willReturn($settingDefaultValue);
        $globalSettingDriver->method('getSettingValue')->with($settingKey)->willReturn($settingValue);

        $manager = new CodeTypeSettingManager(
            $this->getRepositoryMock(),
            $globalSettingDriver,
            GlobalsServiceFactory::getInstance(),
        );

        $this->assertEquals(
            $expectedNormalizedSetting,
            iterator_to_array(
                $manager->normalizeSetting($settingKey)
            )
        );
    }

    public static function normalizeSettingDataProvider(): iterable
    {
        $valueOptions = [[
            'option_value' => 'CPT4',
            'option_label' => 'CPT4 Procedure/Service',
        ],[
            'option_value' => 'CVX',
            'option_label' => 'CVX Immunization',
        ],[
            'option_value' => 'DSMIV',
            'option_label' => 'DSMIV Diagnosis',
        ],[
            'option_value' => 'ICD10',
            'option_label' => 'ICD10 Diagnosis',
        ]];

        yield 'Value NOT set' => [
            'default_search_code_type',
            'DSMIV',
            null,
            [
                'setting_key' => 'default_search_code_type',
                'setting_name' => 'Default Search Code Type',
                'setting_description' => 'The default code type to search for in the Fee Sheet.',
                'setting_default_value' => 'DSMIV',
                'setting_is_default_value' => true,
                'setting_value' => null,
                'setting_value_options' => $valueOptions,
            ],
        ];

        yield 'Value SET (default)' => [
            'default_search_code_type',
            'DSMIV',
            'DSMIV',
            [
                'setting_key' => 'default_search_code_type',
                'setting_name' => 'Default Search Code Type',
                'setting_description' => 'The default code type to search for in the Fee Sheet.',
                'setting_default_value' => 'DSMIV',
                'setting_is_default_value' => true,
                'setting_value' => 'DSMIV',
                'setting_value_options' => $valueOptions,
            ],
        ];

        yield 'Value SET (non-default)' => [
            'default_search_code_type',
            'DSMIV',
            'CPT4',
            [
                'setting_key' => 'default_search_code_type',
                'setting_name' => 'Default Search Code Type',
                'setting_description' => 'The default code type to search for in the Fee Sheet.',
                'setting_default_value' => 'DSMIV',
                'setting_is_default_value' => false,
                'setting_value' => 'CPT4',
                'setting_value_options' => $valueOptions,
            ],
        ];
    }

    private function getRepositoryMock(): CodeTypeRepository
    {
        $repository = $this->createMock(CodeTypeRepository::class);
        $repository->method('findAll')->willReturn([
            ['ct_key' => 'CPT4', 'ct_label' => 'CPT4 Procedure/Service'],
            ['ct_key' => 'CVX', 'ct_label' => 'CVX Immunization'],
            ['ct_key' => 'DSMIV', 'ct_label' => 'DSMIV Diagnosis'],
            ['ct_key' => 'ICD10', 'ct_label' => 'ICD10 Diagnosis'],
        ]);

        return $repository;
    }
}
