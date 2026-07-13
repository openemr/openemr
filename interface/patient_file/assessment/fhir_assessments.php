<?php

/**
 * Patient FHIR assessment launcher.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once(dirname(__FILE__, 3) . "/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\Menu\PatientMenuRole;

$pid = PatientSessionUtil::getPid();
$authorized = AclMain::aclCheckCore('patients', 'med');
$webRoot = OEGlobalsBag::getInstance()->getWebRoot();
$container = OEGlobalsBag::getInstance()->getBoolean('questionnaire_display_fullscreen')
    ? 'container'
    : 'container-fluid';

$smartAssessmentClients = [];
if ($authorized && OEGlobalsBag::getInstance()->getBoolean('rest_fhir_api')) {
    $clientRepository = new ClientRepository();
    foreach ($clientRepository->listClientEntities() as $client) {
        if (
            $client->isEnabled()
            && $client->hasScope(SmartLaunchController::CLIENT_APP_REQUIRED_LAUNCH_SCOPE)
        ) {
            $smartAssessmentClients[] = $client;
        }
    }
}
$smartLaunchCsrfToken = CsrfUtils::collectCsrfToken(SessionWrapperFactory::getInstance()->getActiveSession());

/**
 * @var Closure(mixed): string $assessmentString
 */
$assessmentString = static function (mixed $value): string {
    if (is_string($value)) {
        return $value;
    }

    if (is_int($value) || is_float($value)) {
        return (string)$value;
    }

    return '';
};

$assessmentPositiveInt = static function (mixed $value): ?int {
    if (is_int($value)) {
        return $value > 0 ? $value : null;
    }

    if (!is_string($value) || $value === '' || !ctype_digit($value)) {
        return null;
    }

    $validated = filter_var($value, FILTER_VALIDATE_INT);
    return is_int($validated) && $validated > 0 ? $validated : null;
};

/**
 * @var Closure(array<mixed>): string $assessmentDescription
 */
$assessmentDescription = static function (array $questionnaireRecord) use ($assessmentString): string {
    $questionnaireJson = $assessmentString($questionnaireRecord['questionnaire'] ?? null);
    if ($questionnaireJson === '') {
        return '';
    }

    $questionnaire = json_decode($questionnaireJson, true);
    if (!is_array($questionnaire)) {
        return '';
    }

    $description = $questionnaire['description'] ?? null;
    if (is_string($description) && $description !== '') {
        return $description;
    }

    $purpose = $questionnaire['purpose'] ?? null;
    return is_string($purpose) ? $purpose : '';
};

$assessmentStatusClass = (static fn(string $status): string => match ($status) {
    'completed' => 'badge-success',
    'in-progress' => 'badge-warning',
    'amended' => 'badge-info',
    'stopped', 'entered-in-error' => 'badge-danger',
    default => 'badge-secondary',
});

$questionnairesByCategory = [];
$assessmentHistory = [];
$latestAssessmentByQuestionnaire = [];

if ($authorized && $pid > 0) {
    $questionnaireRecords = QueryUtils::fetchRecordsNoLog(
        "SELECT
            qr.id,
            qr.name,
            qr.version,
            qr.profile,
            qr.type,
            qr.code_display,
            qr.category,
            qr.questionnaire,
            lo.title AS category_title
         FROM questionnaire_repository qr
         LEFT JOIN list_options lo
            ON lo.list_id = 'Observation_Types'
            AND lo.option_id = qr.category
            AND lo.activity = 1
         WHERE qr.active = 1
         ORDER BY COALESCE(lo.seq, 999999), COALESCE(lo.title, qr.category), qr.name"
    );

    foreach ($questionnaireRecords as $questionnaireRecord) {
        $questionnaireId = $assessmentPositiveInt($questionnaireRecord['id']);
        if ($questionnaireId === null) {
            continue;
        }

        $categoryTitle = $assessmentString($questionnaireRecord['category_title'] ?? null);
        $category = $assessmentString($questionnaireRecord['category'] ?? null);
        if ($categoryTitle === '') {
            $categoryTitle = $category !== ''
                ? ucwords(str_replace(['_', '-'], ' ', $category))
                : xl('Unassigned');
        }

        $questionnairesByCategory[$categoryTitle][] = $questionnaireRecord;
    }

    $assessmentHistory = QueryUtils::fetchRecordsNoLog(
        "SELECT
            qr.id AS questionnaire_response_id,
            qr.questionnaire_foreign_id,
            qr.response_id,
            qr.questionnaire_name,
            qr.encounter,
            qr.create_time,
            qr.last_updated,
            qr.status,
            qr.version
         FROM questionnaire_response qr
         WHERE qr.patient_id = ?
            AND COALESCE(qr.encounter, 0) = 0
         ORDER BY qr.last_updated DESC, qr.id DESC",
        [$pid]
    );

    foreach ($assessmentHistory as $assessmentRecord) {
        $questionnaireForeignId = $assessmentPositiveInt($assessmentRecord['questionnaire_foreign_id'] ?? null);
        if ($questionnaireForeignId === null || isset($latestAssessmentByQuestionnaire[$questionnaireForeignId])) {
            continue;
        }

        $latestAssessmentByQuestionnaire[$questionnaireForeignId] = $assessmentRecord;
    }
}

$nativeQuestionnaireUrl = $webRoot . '/interface/forms/questionnaire_assessments/native_questionnaire.php';
$smartLaunchUrl = $webRoot . '/interface/smart/ehr-launch-client.php';
$selfUrl = $webRoot . '/interface/patient_file/assessment/fhir_assessments.php';
?>
<!doctype html>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('FHIR Assessments'); ?></title>
    <style>
        #assessment-frame {
            border: 0;
            min-height: 72vh;
            width: 100%;
        }
    </style>
</head>
<body class="body_top">
    <div class="<?php echo attr($container); ?> mt-3">
        <h4><?php echo xlt('FHIR Assessments'); ?></h4>
        <div class="row">
            <div class="col-sm-12">
                <?php
                $menuPatient = new PatientMenuRole();
                $menuPatient->displayHorizNavBarMenu();
                ?>
            </div>
        </div>

        <?php if (!$authorized) : ?>
            <div class="alert alert-warning mt-3"><?php echo xlt('Not authorized'); ?></div>
        <?php elseif ($pid < 1) : ?>
            <div class="alert alert-info mt-3"><?php echo xlt('No patient selected.'); ?></div>
        <?php else : ?>
            <div id="assessment-workspace" class="card my-3 d-none">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span id="assessment-title" class="font-weight-bold"></span>
                    <div class="btn-group btn-group-sm">
                        <button type="button" id="assessment-save" class="btn btn-primary">
                            <?php echo xlt('Save Assessment'); ?>
                        </button>
                        <button type="button" id="assessment-close" class="btn btn-secondary">
                            <?php echo xlt('Close'); ?>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <iframe id="assessment-frame" title="<?php echo xla('FHIR Assessment'); ?>"></iframe>
                </div>
            </div>

            <div id="assessment-catalog" class="my-3">
                <ul class="nav nav-tabs" id="assessment-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a
                            class="nav-link active"
                            id="available-assessments-tab"
                            data-toggle="tab"
                            href="#available-assessments"
                            role="tab"
                            aria-controls="available-assessments"
                            aria-selected="true"
                        >
                            <?php echo xlt('Available Assessments'); ?>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a
                            class="nav-link"
                            id="assessment-history-tab"
                            data-toggle="tab"
                            href="#assessment-history"
                            role="tab"
                            aria-controls="assessment-history"
                            aria-selected="false"
                        >
                            <?php echo xlt('Assessment History'); ?>
                            <?php if ($assessmentHistory !== []) : ?>
                                <span class="badge badge-secondary ml-1"><?php echo text((string)count($assessmentHistory)); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
                <div class="tab-content border-left border-right border-bottom p-3 bg-light" id="assessment-tabs-content">
                    <div
                        class="tab-pane fade show active"
                        id="available-assessments"
                        role="tabpanel"
                        aria-labelledby="available-assessments-tab"
                    >
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1"><?php echo xlt('Available Assessments'); ?></h5>
                                <div class="small text-muted">
                                    <?php echo xlt('Active patient-context FHIR Questionnaires available from the repository.'); ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($questionnairesByCategory === []) : ?>
                            <div class="text-muted"><?php echo xlt('No active FHIR Questionnaires are available.'); ?></div>
                        <?php else : ?>
                            <?php foreach ($questionnairesByCategory as $categoryTitle => $questionnaires) : ?>
                                <h5 class="mt-2 mb-3"><?php echo text($categoryTitle); ?></h5>
                                <div class="row">
                                    <?php foreach ($questionnaires as $questionnaire) : ?>
                                        <?php
                                        $questionnaireId = $assessmentPositiveInt($questionnaire['id']);
                                        if ($questionnaireId === null) {
                                            continue;
                                        }

                                        $name = $assessmentString($questionnaire['name'] ?? null);
                                        $name = $name !== '' ? $name : xl('Unnamed Questionnaire');
                                        $description = $assessmentDescription($questionnaire);
                                        $codeDisplay = $assessmentString($questionnaire['code_display'] ?? null);
                                        $profile = $assessmentString($questionnaire['profile'] ?? null);
                                        $version = $assessmentString($questionnaire['version'] ?? null);
                                        $latestAssessment = $latestAssessmentByQuestionnaire[$questionnaireId] ?? null;
                                        $latestStatus = is_array($latestAssessment)
                                            ? $assessmentString($latestAssessment['status'] ?? null)
                                            : '';
                                        $lastUpdated = is_array($latestAssessment)
                                            ? $assessmentString($latestAssessment['last_updated'] ?? null)
                                            : '';
                                        if ($lastUpdated === '' && is_array($latestAssessment)) {
                                            $lastUpdated = $assessmentString($latestAssessment['create_time'] ?? null);
                                        }
                                        $latestResponseId = is_array($latestAssessment)
                                            ? $assessmentString($latestAssessment['response_id'] ?? null)
                                            : '';
                                        $latestQuestionnaireResponseId = is_array($latestAssessment)
                                            ? $assessmentPositiveInt($latestAssessment['questionnaire_response_id'] ?? null)
                                            : null;
                                        $isContinuable = $latestResponseId !== '' && $latestStatus === 'in-progress';
                                        $smartQuestionnaireResponseId = $isContinuable
                                            ? $latestQuestionnaireResponseId
                                            : null;
                                        $launchUrl = $nativeQuestionnaireUrl . '?' . http_build_query(
                                                $isContinuable
                                                    ? ['response_id' => $latestResponseId]
                                                    : ['questionnaire_id' => $questionnaireId]
                                            );
                                        ?>
                                        <div class="col-12 col-lg-6 col-xl-4 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body d-flex flex-column">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title font-weight-bold mb-0"><?php echo text($name); ?></h6>
                                                        <?php if ($latestStatus !== '') : ?>
                                                            <span class="badge <?php echo attr($assessmentStatusClass($latestStatus)); ?> ml-2">
                                                            <?php echo text($latestStatus); ?>
                                                        </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($description !== '') : ?>
                                                        <p class="card-text mb-2"><?php echo text($description); ?></p>
                                                    <?php elseif ($codeDisplay !== '') : ?>
                                                        <p class="card-text mb-2"><?php echo text($codeDisplay); ?></p>
                                                    <?php endif; ?>
                                                    <div class="small text-muted mb-3">
                                                        <?php if ($version !== '') : ?>
                                                            <?php echo xlt('Version'); ?>: <?php echo text($version); ?>
                                                        <?php endif; ?>
                                                        <?php if (str_contains($profile, '/uv/sdc/')) : ?>
                                                            <span class="badge badge-info ml-1"><?php echo xlt('FHIR SDC'); ?></span>
                                                        <?php else : ?>
                                                            <span class="badge badge-secondary ml-1"><?php echo xlt('FHIR R4'); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($lastUpdated !== '') : ?>
                                                        <div class="small text-muted mb-2">
                                                            <?php echo xlt('Last assessed'); ?>: <?php echo text($lastUpdated); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="mt-auto">
                                                        <button
                                                            type="button"
                                                            class="btn <?php echo attr($isContinuable ? 'btn-warning' : 'btn-primary'); ?>
                                                            btn-sm assessment-launch"
                                                            data-assessment-url="<?php echo attr($launchUrl); ?>"
                                                            data-assessment-title="<?php echo attr($name); ?>"
                                                        >
                                                            <?php echo $isContinuable ? xlt('Continue') : xlt('Start Assessment'); ?>
                                                        </button>
                                                        <?php if ($smartAssessmentClients !== []) : ?>
                                                            <div class="btn-group ml-1">
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                                                    data-toggle="dropdown"
                                                                    aria-haspopup="true"
                                                                    aria-expanded="false"
                                                                >
                                                                    <?php echo xlt('Launch SMART App'); ?>
                                                                </button>
                                                                <div class="dropdown-menu">
                                                                    <?php foreach ($smartAssessmentClients as $smartClient) : ?>
                                                                        <button
                                                                            type="button"
                                                                            class="dropdown-item assessment-smart-launch"
                                                                            data-smart-name="<?php echo attr($smartClient->getName()); ?>"
                                                                            data-client-id="<?php echo attr($assessmentString($smartClient->getIdentifier())); ?>"
                                                                            data-questionnaire-id="<?php echo attr((string)$questionnaireId); ?>"
                                                                            data-questionnaire-response-id="<?php echo attr((string)($smartQuestionnaireResponseId ?? '')); ?>"
                                                                        >
                                                                            <?php echo text($smartClient->getName()); ?>
                                                                        </button>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div
                        class="tab-pane fade"
                        id="assessment-history"
                        role="tabpanel"
                        aria-labelledby="assessment-history-tab"
                    >
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1"><?php echo xlt('Assessment History'); ?></h5>
                                <div class="small text-muted">
                                    <?php echo xlt('Prior patient-level QuestionnaireResponses saved outside an encounter.'); ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($assessmentHistory === []) : ?>
                            <div class="text-muted"><?php echo xlt('No FHIR assessment history found.'); ?></div>
                        <?php else : ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                    <tr>
                                        <th><?php echo xlt('Assessment'); ?></th>
                                        <th><?php echo xlt('Status'); ?></th>
                                        <th><?php echo xlt('Last Updated'); ?></th>
                                        <th><?php echo xlt('Context'); ?></th>
                                        <th><?php echo xlt('Version'); ?></th>
                                        <th class="text-right"><?php echo xlt('Action'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($assessmentHistory as $assessment) : ?>
                                        <?php
                                        $status = $assessmentString($assessment['status'] ?? null);
                                        $questionnaireName = $assessmentString($assessment['questionnaire_name'] ?? null);
                                        $lastUpdated = $assessmentString($assessment['last_updated'] ?? null);
                                        if ($lastUpdated === '') {
                                            $lastUpdated = $assessmentString($assessment['create_time'] ?? null);
                                        }
                                        $contextLabel = xl('Patient');
                                        $historyVersion = $assessmentString($assessment['version'] ?? null);
                                        $responseId = $assessmentString($assessment['response_id'] ?? null);
                                        $historyQuestionnaireId = $assessmentPositiveInt($assessment['questionnaire_foreign_id'] ?? null);
                                        $historyQuestionnaireResponseId = $assessmentPositiveInt($assessment['questionnaire_response_id'] ?? null);
                                        $editUrl = $responseId !== ''
                                            ? $nativeQuestionnaireUrl . '?' . http_build_query(['response_id' => $responseId])
                                            : '';
                                        ?>
                                        <tr>
                                            <td><?php echo text($questionnaireName); ?></td>
                                            <td>
                                            <span class="badge <?php echo attr($assessmentStatusClass($status)); ?>">
                                                <?php echo text($status !== '' ? $status : xl('Unknown')); ?>
                                            </span>
                                            </td>
                                            <td><?php echo text($lastUpdated); ?></td>
                                            <td><?php echo text($contextLabel); ?></td>
                                            <td><?php echo text($historyVersion); ?></td>
                                            <td class="text-right">
                                                <?php if ($editUrl !== '') : ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-primary btn-sm assessment-launch"
                                                        data-assessment-url="<?php echo attr($editUrl); ?>"
                                                        data-assessment-title="<?php echo attr($questionnaireName); ?>"
                                                    >
                                                        <?php echo xlt('View / Edit'); ?>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (
                                                    $smartAssessmentClients !== []
                                                    && $historyQuestionnaireId !== null
                                                    && $historyQuestionnaireResponseId !== null
                                                ) : ?>
                                                    <div class="btn-group ml-1">
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                                            data-toggle="dropdown"
                                                            aria-haspopup="true"
                                                            aria-expanded="false"
                                                        >
                                                            <?php echo xlt('Launch SMART App'); ?>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <?php foreach ($smartAssessmentClients as $smartClient) : ?>
                                                                <button
                                                                    type="button"
                                                                    class="dropdown-item assessment-smart-launch"
                                                                    data-smart-name="<?php echo attr($smartClient->getName()); ?>"
                                                                    data-client-id="<?php echo attr($assessmentString($smartClient->getIdentifier())); ?>"
                                                                    data-questionnaire-id="<?php echo attr((string)$historyQuestionnaireId); ?>"
                                                                    data-questionnaire-response-id="<?php echo attr((string)$historyQuestionnaireResponseId); ?>"
                                                                >
                                                                    <?php echo text($smartClient->getName()); ?>
                                                                </button>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($authorized && $pid > 0) : ?>
        <script>
            const assessmentWorkspace = document.getElementById('assessment-workspace')
            const assessmentCatalog = document.getElementById('assessment-catalog')
            const assessmentFrame = document.getElementById('assessment-frame')
            const assessmentTitle = document.getElementById('assessment-title')
            const assessmentSaveButton = document.getElementById('assessment-save')
            const assessmentLauncherUrl = <?php echo js_escape($selfUrl); ?>;
            const smartLaunchUrl = <?php echo js_escape($smartLaunchUrl); ?>;
            const smartLaunchCsrfToken = <?php echo js_escape($smartLaunchCsrfToken); ?>;
            const smartLaunchIntent = <?php echo js_escape(
                \OpenEMR\FHIR\SMART\SMARTLaunchToken::INTENT_QUESTIONNAIRE_ASSESSMENT
                                      ); ?>;
            let assessmentSavePending = false

            function openAssessment (url, title) {
                top.restoreSession()
                assessmentSavePending = false
                assessmentSaveButton.disabled = false
                assessmentTitle.textContent = title
                assessmentCatalog.classList.add('d-none')
                assessmentWorkspace.classList.remove('d-none')
                assessmentFrame.src = url
                window.scrollTo({ top: 0, behavior: 'smooth' })
            }

            function closeAssessment () {
                assessmentSavePending = false
                assessmentSaveButton.disabled = false
                assessmentFrame.src = 'about:blank'
                assessmentWorkspace.classList.add('d-none')
                assessmentCatalog.classList.remove('d-none')
            }

            document.querySelectorAll('.assessment-launch').forEach((button) => {
                button.addEventListener('click', () => {
                    openAssessment(button.dataset.assessmentUrl, button.dataset.assessmentTitle)
                })
            })

            document.querySelectorAll('.assessment-smart-launch').forEach((button) => {
                button.addEventListener('click', () => {
                    const clientId = button.dataset.clientId || ''
                    const questionnaireId = button.dataset.questionnaireId || ''
                    const questionnaireResponseId = button.dataset.questionnaireResponseId || ''
                    if (clientId === '' || questionnaireId === '') {
                        return
                    }

                    top.restoreSession()
                    const params = new URLSearchParams({
                        client_id: clientId,
                        csrf_token: smartLaunchCsrfToken,
                        intent: smartLaunchIntent,
                        questionnaire_id: questionnaireId
                    })
                    if (questionnaireResponseId !== '') {
                        params.set('questionnaire_response_id', questionnaireResponseId)
                    }

                    const title = button.dataset.smartName || <?php echo xlj('SMART Assessment App'); ?>;
                    const height = window.top.innerHeight
                    dlgopen(
                        smartLaunchUrl + '?' + params.toString(),
                        '_blank',
                        'modal-full',
                        height,
                        '',
                        title,
                        { allowExternal: true }
                    )
                })
            })

            assessmentSaveButton.addEventListener('click', () => {
                if (assessmentSavePending) {
                    return
                }
                top.restoreSession()
                assessmentSavePending = true
                assessmentSaveButton.disabled = true
                assessmentFrame.contentWindow.postMessage({ submitForm: true }, window.location.origin)
            })

            document.getElementById('assessment-close').addEventListener('click', closeAssessment)

            window.addEventListener('message', (event) => {
                if (event.origin !== window.location.origin || event.source !== assessmentFrame.contentWindow) {
                    return
                }

                if (event.data?.assessmentSaved === true) {
                    window.location.href = assessmentLauncherUrl
                    return
                }

                if (event.data?.assessmentValidationFailed === true || event.data?.assessmentSaveFailed === true) {
                    assessmentSavePending = false
                    assessmentSaveButton.disabled = false
                }
            })
        </script>
    <?php endif; ?>
</body>
</html>
