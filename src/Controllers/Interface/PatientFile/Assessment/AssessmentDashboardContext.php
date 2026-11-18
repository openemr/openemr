<?php

/**
 * Who is looking at the Questionnaire Assessments page, for which patient, and what the
 * page links to. Built by AssessmentEntryPoint from the session, ACL and globals.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientFile\Assessment;

final readonly class AssessmentDashboardContext
{
    /**
     * @param bool $authorized the user has patients/med
     * @param int $pid the session patient, 0 when none is selected
     * @param ?int $encounter the session encounter, null when none
     * @param list<SmartApp> $smartApps enabled apps that may take a questionnaire launch
     * @param array{canUseTemplates: bool, canManageAvailability: bool, canImport: bool, templatesUrl: string, availabilityUrl: string, importUrl: string} $adminTabs
     */
    public function __construct(
        public bool $authorized,
        public int $pid,
        public ?int $encounter,
        public ?int $facility,
        public array $smartApps,
        public bool $showSmartContextTestLaunches,
        public string $csrfToken,
        public array $adminTabs,
        public string $webRoot,
    ) {
    }

    public function workspaceUrl(): string
    {
        return $this->webRoot . '/interface/forms/questionnaire_assessments/native_questionnaire.php';
    }

    public function launcherUrl(): string
    {
        return $this->webRoot . '/interface/patient_file/assessment/fhir_assessments.php';
    }

    public function smartLaunchUrl(): string
    {
        return $this->webRoot . '/interface/smart/ehr-launch-client.php';
    }
}
