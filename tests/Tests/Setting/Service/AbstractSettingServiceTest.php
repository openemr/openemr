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

use OpenEMR\Setting\Service\AbstractSettingService;
use OpenEMR\Setting\Service\Factory\SettingServiceFactory;
use OpenEMR\Setting\Service\SettingServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

// @todo Move initialization to classes
require_once(__DIR__ . "/../../../../library/globals.inc.php"); // As we need section names

#[Group('setting')]
#[CoversClass(AbstractSettingService::class)]
#[CoversMethod(AbstractSettingService::class, 'checkSectionHasSetting')]
class AbstractSettingServiceTest extends TestCase
{
    #[Test]
    #[DataProvider('checkSectionHasSettingDataProvider')]
    public function checkSectionHasSettingTest(
        SettingServiceInterface $settingService,
        string $sectionName,
        string $settingKey,
        string $expectedExceptionMessage,
    ): void {
        $reflection = new \ReflectionClass($settingService);
        $method = $reflection->getMethod('checkSectionHasSetting');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $method->invoke($settingService, $sectionName, $settingKey);
    }

    public static function checkSectionHasSettingDataProvider(): iterable
    {
        // Global
        yield 'Global | Non-existing Section' => [
            SettingServiceFactory::createGlobal(),
            'dummy',
            'enable_help',
            'Section "dummy" does not exist. Possible ones: "appearance", "billing", "branding", "calendar", "carecoordination", "cdr", "connectors", "documents", "e-sign", "encounter-form", "features", "insurance", "locale", "logging", "login-page", "miscellaneous", "notifications", "patient-banner-bar", "pdf", "portal", "questionnaires", "report", "rx", "security".',
        ];

        yield 'Global | Existing Section, but non-existing Setting' => [
            SettingServiceFactory::createGlobal(),
            'portal',
            'dummy',
            'Setting "dummy" does not exists under "portal" section. Possible section settings are: "portal_onsite_two_enable", "portal_onsite_two_address", "portal_css_header", "portal_force_credential_reset", "portal_onsite_two_basepath", "use_email_for_portal_username", "enforce_signin_email", "google_recaptcha_site_key", "google_recaptcha_secret_key", "portal_primary_menu_logo_height", "portal_onsite_two_register", "portal_two_pass_reset", "allow_portal_appointments", "allow_custom_report", "portal_two_ledger", "portal_two_payments", "portal_onsite_document_download", "allow_portal_uploads", "show_insurance_in_profile", "show_portal_primary_logo", "extra_portal_logo_login", "secondary_portal_logo_position".',
        ];

        yield 'Global | Existing Section, but Setting is from other Section' => [
            SettingServiceFactory::createGlobal(),
            'portal',
            'enable_help',
            'Setting "enable_help" does not exist under "portal" section. Did you mean "features" section?',
        ];

        // User-specific
        yield 'User-specific | Non-existing Section' => [
            SettingServiceFactory::createUserSpecificByUserId(1),
            'dummy',
            'enable_help',
            'Section "dummy" does not exist. Possible ones: "appearance", "billing", "calendar", "carecoordination", "cdr", "connectors", "features", "locale", "questionnaires", "report".'
        ];

        yield 'User-specific | Existing Section, but non-existing Setting' => [
            SettingServiceFactory::createUserSpecificByUserId(1),
            'locale',
            'dummy',
            'Setting "dummy" does not exists under "locale" section. Possible section settings are: "units_of_measurement", "us_weight_format", "date_display_format", "time_display_format".'
        ];

        yield 'User-specific | Existing Section, but Setting is from other Section' => [
            SettingServiceFactory::createUserSpecificByUserId(1),
            'locale',
            'enable_help',
            'Setting "enable_help" does not exist under "locale" section. Did you mean "features" section?'
        ];
    }
}
