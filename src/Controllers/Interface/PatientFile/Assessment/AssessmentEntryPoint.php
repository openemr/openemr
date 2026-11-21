<?php

/**
 * Wires the Questionnaire Assessments page, its workspace and the workspace's save.
 *
 * The session, ACL, globals and legacy page helpers are read here, so the controllers take
 * only typed values and can be tested without them. Each method backs one entry script:
 * interface/patient_file/assessment/fhir_assessments.php and
 * interface/forms/questionnaire_assessments/native_questionnaire.php and native_save.php.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientFile\Assessment;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\Services\Globals\CollapsedSections;
use OpenEMR\Services\NativeQuestionnaireResponseProcessor;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Questionnaire\AssessmentPageLayout;
use OpenEMR\Services\Questionnaire\AssessmentRepository;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityService;
use OpenEMR\Services\Questionnaire\QuestionnaireRuntimeAssets;
use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AssessmentEntryPoint
{
    private const ACL_SECTION = 'patients';
    private const ACL_VALUE = 'med';

    /**
     * The Questionnaire Assessments page.
     */
    public function dashboard(): Response
    {
        $globals = OEGlobalsBag::getInstance();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $authorized = AclMain::aclCheckCore(self::ACL_SECTION, self::ACL_VALUE);
        $pid = PatientSessionUtil::getPid();
        $encounter = $session->get('encounter');
        $webRoot = $globals->getWebRoot();

        $context = new AssessmentDashboardContext(
            authorized: $authorized,
            pid: $pid,
            encounter: is_numeric($encounter) ? (int)$encounter : null,
            facility: AssessmentValues::positiveInt($session->get('facilityId')),
            smartApps: $authorized && $globals->getBoolean('rest_fhir_api') ? self::smartApps() : [],
            showSmartContextTestLaunches: $globals->getBoolean('smart_context_test_launches'),
            csrfToken: CsrfUtils::collectCsrfToken($session),
            adminTabs: AssessmentPageLayout::adminTabs($authorized, $pid, $webRoot),
            webRoot: $webRoot,
        );

        return (new AssessmentDashboardController(
            ServiceContainer::getTwig(),
            self::store(),
            $context,
            self::chrome($globals),
            CollapsedSections::forCurrentUser(...),
        ))->dispatchAction();
    }

    /**
     * The workspace frame. Refused outright without a patient or patients/med.
     */
    public function workspace(Request $request): Response
    {
        $pid = self::requirePatientAccess();
        $globals = OEGlobalsBag::getInstance();

        return (new AssessmentWorkspaceController(
            ServiceContainer::getTwig(),
            self::store(),
            new NativeQuestionnaireResponseProcessor(),
            $pid,
            CsrfUtils::collectCsrfToken(SessionWrapperFactory::getInstance()->getActiveSession()),
            $globals->getWebRoot() . '/interface/forms/questionnaire_assessments/native_save.php',
            (new QuestionnaireRuntimeAssets($globals))->describe(),
        ))->dispatchAction($request);
    }

    /**
     * The workspace's save. Answers in JSON, a refusal included, so the workspace can show it.
     */
    public function save(Request $request): JsonResponse
    {
        return (new AssessmentSaveController(
            self::store(),
            new NativeQuestionnaireResponseProcessor(),
            SessionWrapperFactory::getInstance()->getActiveSession(),
            ServiceContainer::getLogger(),
            PatientSessionUtil::getPid(),
            // Saving a new response takes write or addonly; amending a saved one takes write.
            AclMain::aclCheckCore(self::ACL_SECTION, self::ACL_VALUE, '', ['write', 'addonly']),
            AclMain::aclCheckCore(self::ACL_SECTION, self::ACL_VALUE, '', ['write']),
        ))->dispatchAction($request);
    }

    private static function store(): AssessmentRepository
    {
        return new AssessmentRepository(
            new QuestionnaireAvailabilityService(),
            new QuestionnaireService(),
            new QuestionnaireResponseService(),
            new PatientService(),
        );
    }

    /**
     * The enabled SMART apps registered with the launch scope a questionnaire launch needs.
     *
     * @return list<SmartApp>
     */
    private static function smartApps(): array
    {
        $apps = [];
        foreach ((new ClientRepository())->listClientEntities() as $client) {
            $clientId = AssessmentValues::text($client->getIdentifier());
            if ($clientId === '' || !$client->isEnabled() || !$client->hasScope(SmartLaunchController::CLIENT_APP_REQUIRED_LAUNCH_SCOPE)) {
                continue;
            }
            $apps[] = new SmartApp($clientId, AssessmentValues::text($client->getName()));
        }

        return $apps;
    }

    /**
     * The OemrUI width and expand script, and the patient menu bar with the OemrUI heading
     * controls at its end, which the legacy helpers echo rather than return. The tab row names
     * the section, so there is no separate page title.
     */
    private static function chrome(OEGlobalsBag $globals): AssessmentPageChrome
    {
        $layout = AssessmentPageLayout::create($globals);

        return new AssessmentPageChrome(
            $layout->oeContainer(),
            self::captured($layout->oeBelowContainerDiv(...)),
            self::captured(static fn() => (new PatientMenuRole())->displayHorizNavBarMenu($layout->tabRowControls())),
        );
    }

    /**
     * What a helper echoes.
     */
    private static function captured(callable $echo): string
    {
        ob_start();
        $echo();
        $output = ob_get_clean();

        return is_string($output) ? $output : '';
    }

    /**
     * The session patient, after refusing a request without one or without patients/med.
     */
    private static function requirePatientAccess(): int
    {
        $pid = PatientSessionUtil::getPid();
        if ($pid < 1) {
            AccessDeniedHelper::denyWithTemplate('No patient selected.', xl('Questionnaire Assessment'));
        }
        if (!AclMain::aclCheckCore(self::ACL_SECTION, self::ACL_VALUE)) {
            AccessDeniedHelper::denyWithTemplate('ACL check failed for patient assessments.', xl('Questionnaire Assessment'));
        }

        return $pid;
    }
}
