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
use OpenEMR\Setting\Service\Factory\SettingServiceFactory;
use OpenEMR\Setting\Service\User\UserSpecificSettingService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('setting')]
#[CoversClass(UserSpecificSettingService::class)]
#[CoversMethod(UserSpecificSettingService::class, 'getSettingKeysBySectionName')]
class UserSpecificSettingServiceTest extends TestCase
{
    #[Test]
    public function dataProviderConsistencyTest(): void
    {
        $this->assertEquals(
            GlobalSettingSection::USER_SPECIFIC_SECTIONS,
            array_map(
                static fn ($data): string => $data[0],
                iterator_to_array(
                    self::getSettingKeysBySectionNameDataProvider()
                )
            )
        );
    }

    #[Test]
    #[DataProvider('getSettingKeysBySectionNameDataProvider')]
    public function getSettingKeysBySectionNameTest(
        string $sectionName,
        array $expectedSectionSessionKeys
    ): void {
        $settingService = SettingServiceFactory::createUserSpecificByUserId(1);
        $reflection = new \ReflectionClass($settingService);
        $method = $reflection->getMethod('getSettingKeysBySectionName');

        $this->assertEquals(
            $expectedSectionSessionKeys,
            $method->invokeArgs($settingService, [$sectionName])
        );
    }

    /**
     * @see GlobalSettingSection::USER_SPECIFIC_SECTIONS
     */
    public static function getSettingKeysBySectionNameDataProvider(): iterable
    {
        yield [
            GlobalSettingSection::APPEARANCE,
            [
                'theme_tabs_layout',
                'css_header',
                'enable_compact_mode',
                'search_any_patient',
                'default_encounter_view',
                'gbl_pt_list_page_size',
                'gbl_pt_list_new_window',
            ]
        ];

        yield [
            GlobalSettingSection::BILLING,
            [
                'posting_adj_disable',
            ]
        ];

        yield [
            GlobalSettingSection::CALENDAR,
            [
                'calendar_view_type',
                'event_color',
                'ptkr_visit_reason',
                'ptkr_date_range',
                'ptkr_start_date',
                'ptkr_end_date',
                'pat_trkr_timer',
                'checkout_roll_off',
            ]
        ];

        yield [
            GlobalSettingSection::CARE_COORDINATION,
            [
                'ccda_view_max_sections',
                'ccda_ccd_section_sort_order',
                'ccda_referral_section_sort_order',
                'ccda_toc_section_sort_order',
                'ccda_careplan_section_sort_order',
                'ccda_default_section_sort_order',
            ]
        ];

        yield [
            GlobalSettingSection::CDR,
            [
                'patient_birthday_alert',
                'patient_birthday_alert_manual_off',
            ]
        ];

        yield [
            GlobalSettingSection::CONNECTORS,
            [
                'erx_import_status_message',
            ]
        ];

        yield [
            GlobalSettingSection::FEATURES,
            [
                'text_templates_enabled',
                'enable_help',
                'messages_due_date',
                'expand_form',
            ]
        ];

        yield [
            GlobalSettingSection::LOCALE,
            [
                'units_of_measurement',
                'us_weight_format',
                'date_display_format',
                'time_display_format',
            ]
        ];

        yield [
            GlobalSettingSection::QUESTIONNAIRES,
            [
                'questionnaire_display_LOINCnote',
                'questionnaire_display_style',
                'questionnaire_display_fullscreen',
            ]
        ];

        yield [
            GlobalSettingSection::REPORT,
            [
                'ledger_begin_date',
                'print_next_appointment_on_ledger',
            ]
        ];
    }
}
