<?php

/**
 * The assessment workspace: one questionnaire rendered by the native runtime inside the
 * Questionnaire Assessments page's frame.
 *
 * ?questionnaire_id= starts a new response to an active repository questionnaire;
 * ?response_id= reopens one of this patient's patient-level responses with the questionnaire
 * it was answered against. Saving is AssessmentSaveController, reached by a message from the
 * page's Save button. The markup is templates/patient/assessment/workspace.html.twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientFile\Assessment;

use JsonException;
use OpenEMR\Services\NativeQuestionnaireResponseProcessor;
use OpenEMR\Services\Questionnaire\AssessmentStore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final readonly class AssessmentWorkspaceController
{
    public const TEMPLATE = 'patient/assessment/workspace.html.twig';

    /** Safe to print inside a script element. */
    private const SCRIPT_JSON = JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR;

    /**
     * @param int $pid the session patient; the entry point has already refused a request without one
     * @param array{stylesheet: string, script: string, version: string, strings: string} $runtime
     */
    public function __construct(
        private Environment $twig,
        private AssessmentStore $store,
        private NativeQuestionnaireResponseProcessor $processor,
        private int $pid,
        private string $csrfToken,
        private string $saveUrl,
        private array $runtime,
    ) {
    }

    /**
     * Show the questionnaire, or say in the frame why it cannot be opened.
     */
    public function dispatchAction(Request $request): Response
    {
        try {
            $view = $this->buildView(
                AssessmentValues::positiveInt(AssessmentValues::input($request->query, 'questionnaire_id')),
                trim(AssessmentValues::input($request->query, 'response_id'))
            );
            $status = Response::HTTP_OK;
        } catch (AssessmentUnavailable $e) {
            $view = ['runtime' => $this->runtime, 'title' => xl('Questionnaire Assessment'), 'error' => $e->getMessage()];
            $status = Response::HTTP_BAD_REQUEST;
        }

        return new Response(
            $this->twig->render(self::TEMPLATE, $view),
            $status,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /**
     * The template's variables for a new response or a saved one.
     *
     * @return array<string, mixed>
     * @throws AssessmentUnavailable when the questionnaire or response cannot be opened here
     */
    public function buildView(?int $questionnaireId, string $responseId): array
    {
        [$questionnaireId, $questionnaireJson, $responseJson] = $responseId !== ''
            ? $this->savedResponse($responseId)
            : $this->newResponse($questionnaireId);

        $questionnaire = self::decode($questionnaireJson, 'Questionnaire', xl('The repository record does not contain a FHIR Questionnaire.'));
        $response = $responseJson !== ''
            ? self::decode($responseJson, 'QuestionnaireResponse', xl('The saved response is not a FHIR QuestionnaireResponse.'))
            : null;
        $title = $questionnaire['title'] ?? $questionnaire['name'] ?? null;

        try {
            return [
                'runtime' => $this->runtime,
                'title' => is_string($title) && $title !== '' ? $title : xl('Questionnaire Assessment'),
                'error' => '',
                'questionnaireJson' => json_encode($questionnaire, self::SCRIPT_JSON),
                'responseJson' => json_encode($response, self::SCRIPT_JSON),
                'questionnaireId' => $questionnaireId,
                'responseId' => $responseId,
                'saveUrl' => $this->saveUrl,
                'csrfToken' => $this->csrfToken,
            ];
        } catch (JsonException $e) {
            throw new AssessmentUnavailable(xl('The repository record does not contain a FHIR Questionnaire.'), 0, $e);
        }
    }

    /**
     * A saved response of this patient's, outside any encounter, with the questionnaire it was
     * answered against (the repository's current one if the response did not keep it).
     *
     * @return array{int, string, string} questionnaire id, questionnaire JSON, response JSON
     */
    private function savedResponse(string $responseId): array
    {
        $response = $this->store->response($responseId);
        if ($response === []) {
            throw new AssessmentUnavailable(xl('Questionnaire response was not found.'));
        }
        if ($this->processor->normalizeNonNegativeInt($response['patient_id'] ?? null) !== $this->pid) {
            throw new AssessmentUnavailable(xl('Questionnaire response does not belong to the selected patient.'));
        }
        // NULL (saved through the FHIR API, e.g. by a SMART app) and 0 (saved here) are both
        // patient-level; Available Assessments lists both.
        if (!$this->processor->isPatientLevelEncounter($response['encounter'] ?? null)) {
            throw new AssessmentUnavailable(xl('Encounter questionnaires cannot be edited from the patient assessment workspace.'));
        }
        $questionnaireId = AssessmentValues::positiveInt($response['questionnaire_foreign_id'] ?? null);
        if ($questionnaireId === null) {
            throw new AssessmentUnavailable(xl('Questionnaire repository link is missing from the response.'));
        }

        $questionnaireJson = AssessmentValues::responseQuestionnaire($this->store, $response, $questionnaireId);

        return [$questionnaireId, $questionnaireJson, AssessmentValues::text($response['questionnaire_response'] ?? null)];
    }

    /**
     * An active repository questionnaire, for a new response.
     *
     * @return array{int, string, string} questionnaire id, questionnaire JSON, empty response JSON
     */
    private function newResponse(?int $questionnaireId): array
    {
        if ($questionnaireId === null) {
            throw new AssessmentUnavailable(xl('A Questionnaire repository id is required.'));
        }
        $record = $this->store->questionnaire($questionnaireId);
        if ($record === [] || $this->processor->normalizeNonNegativeInt($record['active'] ?? null) !== 1) {
            throw new AssessmentUnavailable(xl('The selected Questionnaire is not active or was not found.'));
        }

        return [$questionnaireId, AssessmentValues::text($record['questionnaire'] ?? null), ''];
    }

    /**
     * Decode a FHIR resource and check its type.
     *
     * @return array<mixed>
     */
    private static function decode(string $json, string $resourceType, string $refusal): array
    {
        try {
            $resource = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new AssessmentUnavailable($refusal, 0, $e);
        }
        if (!is_array($resource) || ($resource['resourceType'] ?? null) !== $resourceType) {
            throw new AssessmentUnavailable($refusal);
        }

        return $resource;
    }
}
