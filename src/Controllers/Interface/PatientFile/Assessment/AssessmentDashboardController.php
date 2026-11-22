<?php

/**
 * The patient dashboard's Questionnaire Assessments page.
 *
 * Available Assessments lists the questionnaires offered on the dashboard for this patient,
 * grouped by category, each with the state of its latest patient-level response. Assessment
 * History lists those responses. Either opens a questionnaire in the workspace frame, or in a
 * SMART app when an availability rule allows that app. The administration tabs are embedded
 * screens with their own controllers. The markup is templates/patient/assessment/dashboard.html.twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientFile\Assessment;

use Closure;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use OpenEMR\Services\Globals\CollapsedSections;
use OpenEMR\Services\Questionnaire\AssessmentCategoryGroups;
use OpenEMR\Services\Questionnaire\AssessmentPageLayout;
use OpenEMR\Services\Questionnaire\AssessmentStore;
use OpenEMR\Services\Questionnaire\AvailabilityContext;
use OpenEMR\Services\Questionnaire\QuestionnaireSurface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * @phpstan-type DashboardView array{
 *     chrome: AssessmentPageChrome,
 *     authorized: bool,
 *     hasPatient: bool,
 *     csrfToken: string,
 *     adminTabs: array{canUseTemplates: bool, canManageAvailability: bool, canImport: bool, templatesUrl: string, availabilityUrl: string, importUrl: string},
 *     launcherUrl: string,
 *     smartLaunchUrl: string,
 *     smartLaunchesSection: string,
 *     smartTestApps: list<SmartApp>,
 *     smartLaunchesCollapsed: bool,
 *     currentEncounterId: ?int,
 *     intents: array{patient: string, encounter: string, mainTab: string, appointment: string, questionnaire: string},
 *     categories: list<AssessmentCategory>,
 *     history: list<AssessmentHistoryRow>,
 * }
 */
final readonly class AssessmentDashboardController
{
    public const TEMPLATE = 'patient/assessment/dashboard.html.twig';

    /**
     * @param Closure(list<string>): CollapsedSections $sections reads which of these sections the user folded
     */
    public function __construct(
        private Environment $twig,
        private AssessmentStore $store,
        private AssessmentDashboardContext $context,
        private AssessmentPageChrome $chrome,
        private Closure $sections,
    ) {
    }

    /**
     * Render the page.
     */
    public function dispatchAction(): Response
    {
        return new Response(
            $this->twig->render(self::TEMPLATE, $this->buildView()),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /**
     * The template's variables. Without patients/med or a selected patient the page shows
     * only a notice, so nothing is read.
     *
     * @return DashboardView
     */
    public function buildView(): array
    {
        $context = $this->context;
        $smartSection = AssessmentPageLayout::SMART_LAUNCHES_SECTION;
        $view = [
            'chrome' => $this->chrome,
            'authorized' => $context->authorized,
            'hasPatient' => $context->pid > 0,
            'csrfToken' => $context->csrfToken,
            'adminTabs' => $context->adminTabs,
            'launcherUrl' => $context->launcherUrl(),
            'smartLaunchUrl' => $context->smartLaunchUrl(),
            'smartLaunchesSection' => $smartSection,
            'smartTestApps' => $context->showSmartContextTestLaunches ? $context->smartApps : [],
            'smartLaunchesCollapsed' => false,
            'currentEncounterId' => $context->encounter !== null && $context->encounter > 0 ? $context->encounter : null,
            'intents' => [
                'patient' => SMARTLaunchToken::INTENT_PATIENT_DEMOGRAPHICS_DIALOG,
                'encounter' => SMARTLaunchToken::INTENT_ENCOUNTER_DIALOG,
                'mainTab' => SMARTLaunchToken::INTENT_MAIN_TAB,
                'appointment' => SMARTLaunchToken::INTENT_APPOINTMENT_DIALOG,
                'questionnaire' => SMARTLaunchToken::INTENT_QUESTIONNAIRE_ASSESSMENT,
            ],
            'categories' => [],
            'history' => [],
        ];
        if (!$context->authorized || $context->pid < 1) {
            return $view;
        }

        // Which questionnaires belong on this dashboard is an assignment question, not a
        // property of the repository row, so it is answered in one place for every surface.
        $groups = AssessmentCategoryGroups::fromRecords(
            $this->store->availableQuestionnaires(new AvailabilityContext(
                QuestionnaireSurface::Dashboard,
                pid: $context->pid,
                encounter: $context->encounter,
                facility: $context->facility,
            )),
            xl('Unassigned')
        );
        $history = $this->store->patientHistory($context->pid);
        $latest = self::latestByQuestionnaire($history);
        $smartApps = $this->smartAppsByQuestionnaire();
        $collapsed = ($this->sections)([$smartSection, ...array_keys($groups)]);

        $categories = [];
        foreach ($groups as $section => $group) {
            $cards = [];
            foreach ($group['records'] as $record) {
                $card = $this->card($record, $latest, $smartApps);
                if ($card !== null) {
                    $cards[] = $card;
                }
            }
            $categories[] = new AssessmentCategory($section, $group['title'], $collapsed->isCollapsed($section), $cards);
        }

        $view['smartLaunchesCollapsed'] = $collapsed->isCollapsed($smartSection);
        $view['categories'] = $categories;
        $view['history'] = array_map(fn(array $response): AssessmentHistoryRow => $this->historyRow($response, $smartApps), $history);
        return $view;
    }

    /**
     * Each questionnaire's newest response; the history arrives newest first.
     *
     * @param list<array<mixed>> $history
     * @return array<int, array<mixed>>
     */
    private static function latestByQuestionnaire(array $history): array
    {
        $latest = [];
        foreach ($history as $response) {
            $questionnaireId = AssessmentValues::positiveInt($response['questionnaire_foreign_id'] ?? null);
            if ($questionnaireId !== null && !isset($latest[$questionnaireId])) {
                $latest[$questionnaireId] = $response;
            }
        }

        return $latest;
    }

    /**
     * The SMART apps each questionnaire may be launched into.
     *
     * Asked once per app, because a SMART rule can name the app it allows. Empty unless a
     * SMART rule reaches the questionnaire, so the launch menu is offered only for
     * questionnaires an administrator selected. The launch endpoint asks the same question,
     * so a hand-built launch URL is refused too.
     *
     * @return array<int, list<SmartApp>>
     */
    private function smartAppsByQuestionnaire(): array
    {
        $byQuestionnaire = [];
        foreach ($this->context->smartApps as $app) {
            $ids = $this->store->availableIds(new AvailabilityContext(
                QuestionnaireSurface::Smart,
                pid: $this->context->pid,
                facility: $this->context->facility,
                clientId: $app->clientId,
            ));
            foreach ($ids as $id) {
                $byQuestionnaire[$id][] = $app;
            }
        }

        return $byQuestionnaire;
    }

    /**
     * A questionnaire's card. An in-progress latest response is continued rather than a new
     * one started, in the workspace and in a SMART app alike.
     *
     * @param array<string, mixed> $record
     * @param array<int, array<mixed>> $latest
     * @param array<int, list<SmartApp>> $smartApps
     */
    private function card(array $record, array $latest, array $smartApps): ?AssessmentCard
    {
        $questionnaireId = AssessmentValues::positiveInt($record['id'] ?? null);
        if ($questionnaireId === null) {
            return null;
        }

        $name = AssessmentValues::text($record['name'] ?? null);
        $summary = AssessmentValues::description($record);
        $response = $latest[$questionnaireId] ?? [];
        $status = AssessmentValues::text($response['status'] ?? null);
        $responseId = AssessmentValues::text($response['response_id'] ?? null);
        $continuable = $responseId !== '' && $status === 'in-progress';

        return new AssessmentCard(
            questionnaireId: $questionnaireId,
            name: $name !== '' ? $name : xl('Unnamed Questionnaire'),
            summary: $summary !== '' ? $summary : AssessmentValues::text($record['code_display'] ?? null),
            version: AssessmentValues::text($record['version'] ?? null),
            sdc: str_contains(AssessmentValues::text($record['profile'] ?? null), '/uv/sdc/'),
            latestStatus: $status,
            lastAssessed: AssessmentValues::lastSaved($response),
            continuable: $continuable,
            launchUrl: $this->workspaceUrl($continuable ? ['response_id' => $responseId] : ['questionnaire_id' => $questionnaireId]),
            smartApps: $smartApps[$questionnaireId] ?? [],
            smartResponseId: $continuable ? AssessmentValues::positiveInt($response['questionnaire_response_id'] ?? null) : null,
        );
    }

    /**
     * @param array<mixed> $response
     * @param array<int, list<SmartApp>> $smartApps
     */
    private function historyRow(array $response, array $smartApps): AssessmentHistoryRow
    {
        $responseId = AssessmentValues::text($response['response_id'] ?? null);
        $questionnaireId = AssessmentValues::positiveInt($response['questionnaire_foreign_id'] ?? null);

        return new AssessmentHistoryRow(
            name: AssessmentValues::text($response['questionnaire_name'] ?? null),
            status: AssessmentValues::text($response['status'] ?? null),
            lastUpdated: AssessmentValues::lastSaved($response),
            version: AssessmentValues::text($response['version'] ?? null),
            editUrl: $responseId !== '' ? $this->workspaceUrl(['response_id' => $responseId]) : '',
            questionnaireId: $questionnaireId,
            responseRecordId: AssessmentValues::positiveInt($response['questionnaire_response_id'] ?? null),
            smartApps: $questionnaireId !== null ? ($smartApps[$questionnaireId] ?? []) : [],
        );
    }

    /**
     * @param array<string, int|string> $query
     */
    private function workspaceUrl(array $query): string
    {
        return $this->context->workspaceUrl() . '?' . http_build_query($query);
    }
}
