<?php

/**
 * The heading and width of the patient dashboard's Questionnaire Assessments page.
 *
 * Uses the standard OemrUI expand icon: the page switches between the full window
 * (container-fluid) and the centered Bootstrap container (container), and remembers the
 * choice per user. Until a user chooses, the questionnaire_display_fullscreen global decides,
 * as it did before the icon.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\OeUI\OemrUI;
use OpenEMR\Services\Globals\UserSettingsService;

final class AssessmentPageLayout
{
    /** The user setting OemrUI keeps the width in; its _xpd suffix is OemrUI's convention. */
    public const EXPAND_SETTING = 'fhir_assessments_xpd';

    /** The user setting that remembers whether the SMART test launches card is folded. */
    public const SMART_LAUNCHES_SECTION = 'questionnaire_smart_launches_expand';

    /**
     * Which administration tabs the user may open, and where each screen lives.
     *
     * Each tab shows only with the permission its embedded screen also enforces. Managing where
     * questionnaires are offered changes it for every patient and every user, so Availability
     * needs admin/super. The document page is the portal's, which admits staff only with the
     * portal ACL.
     *
     * @return array{canUseTemplates: bool, canManageAvailability: bool, canImport: bool, templatesUrl: string, availabilityUrl: string, importUrl: string}
     */
    public static function adminTabs(bool $authorized, int $pid, string $webRoot): array
    {
        $manager = $webRoot . '/interface/super/manage_questionnaire_availability.php';

        return [
            'canUseTemplates' => $authorized
                && AclMain::aclCheckCore('patients', 'docs')
                && AclMain::aclCheckCore('patientportal', 'portal'),
            'canManageAvailability' => $authorized && AclMain::aclCheckCore('admin', 'super'),
            'canImport' => $authorized && AclMain::aclCheckCore('admin', 'forms'),
            'templatesUrl' => $webRoot . '/portal/patient/onsitedocuments?' . http_build_query(['pid' => $pid, 'is_module' => 1]),
            'availabilityUrl' => $manager,
            'importUrl' => $manager . '?view=import',
        ];
    }

    /**
     * Build the page's OemrUI, seeding the user's width from the questionnaire global the
     * first time.
     *
     * Only the user's own row counts: saving a width also stores a default for everyone, and
     * taking that would hand one user's choice to users who never made it.
     */
    public static function create(OEGlobalsBag $globals): OemrUI
    {
        $user = filter_var(UserSettingsService::effectiveUser(null), FILTER_VALIDATE_INT);
        if ($user !== false && UserSettingsService::getUserSetting(self::EXPAND_SETTING, $user, $user) === null) {
            UserSettingsService::setUserSetting(
                self::EXPAND_SETTING,
                self::defaultWidth($globals->getBoolean('questionnaire_display_fullscreen')),
                $user,
                false
            );
        }

        return new OemrUI([
            'heading_title' => xl('Questionnaire Assessments'),
            'include_patient_name' => false,
            'expandable' => true,
            'expandable_files' => [self::EXPAND_SETTING],
            'action' => '',
            'action_title' => '',
            'action_href' => '',
            'show_help_icon' => false,
            'help_file_name' => '',
        ]);
    }

    /**
     * The width a user starts with: '1' for the full window, '0' for the centered container.
     * The global is worded as turning full screen off.
     */
    public static function defaultWidth(bool $fullScreenTurnedOff): string
    {
        return $fullScreenTurnedOff ? '0' : '1';
    }
}
