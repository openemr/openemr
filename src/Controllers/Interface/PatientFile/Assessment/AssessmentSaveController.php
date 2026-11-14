<?php

/**
 * Saves a patient-level FHIR QuestionnaireResponse posted by the assessment workspace.
 *
 * The questionnaire it answers comes from the database, never the browser: the repository's
 * active questionnaire for a new response, the one the response was saved with for an update.
 * The response is then stamped with this patient and questionnaire before it is stored,
 * because it flows out through the FHIR API and CCDA, where consumers trust its content.
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
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\InvalidQuestionnaireResponseException;
use OpenEMR\Services\NativeQuestionnaireResponseProcessor;
use OpenEMR\Services\Questionnaire\AssessmentStore;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Throwable;

final readonly class AssessmentSaveController
{
    /**
     * @param int $pid the session patient, 0 when none is selected
     * @param bool $authorized the user may add to patients/med (write or addonly), enough for a new response
     * @param bool $canAmend the user may change patients/med (write), needed to update a saved response;
     *                       addonly means "may view and add but not modify"
     */
    public function __construct(
        private AssessmentStore $store,
        private NativeQuestionnaireResponseProcessor $processor,
        private SessionInterface $session,
        private LoggerInterface $logger,
        private int $pid,
        private bool $authorized,
        private bool $canAmend,
    ) {
    }

    /**
     * Save the posted response and answer with its ids, or with a message the workspace shows.
     *
     * @throws Throwable anything other than a refusal, after logging it
     */
    public function dispatchAction(Request $request): JsonResponse
    {
        $post = $request->request;
        if (!CsrfUtils::verifyCsrfToken($post->getString('csrf_token_form'), $this->session)) {
            return self::refused(xl('Unable to save QuestionnaireResponse.'));
        }
        if ($this->pid < 1 || !$this->authorized) {
            return self::refused(xl('Not authorized to save patient assessments.'));
        }

        try {
            $saved = $this->save(
                AssessmentValues::positiveInt($post->getString('questionnaire_id')),
                $post->getString('questionnaire_response'),
                trim($post->getString('response_id'))
            );
        } catch (AssessmentUnavailable | InvalidQuestionnaireResponseException $e) {
            return self::refused($e->getMessage());
        } catch (JsonException) {
            return self::refused(xl('Unable to save QuestionnaireResponse.'));
        } catch (Throwable $e) {
            $this->logger->error('Native QuestionnaireResponse save failed with a fatal error.', ['exception' => $e]);
            throw $e;
        }

        return new JsonResponse([
            'success' => true,
            'id' => $saved['id'] ?? null,
            'response_id' => $saved['response_id'] ?? '',
            'new' => $saved['new'],
        ]);
    }

    /**
     * @return array{id: mixed, response_id: mixed, new: bool}
     * @throws AssessmentUnavailable|InvalidQuestionnaireResponseException|JsonException when the save is refused
     */
    public function save(?int $questionnaireId, string $responseJson, string $responseId): array
    {
        if ($questionnaireId === null) {
            throw new AssessmentUnavailable(xl('A Questionnaire repository id is required.'));
        }
        if ($responseId !== '' && !$this->canAmend) {
            throw new AssessmentUnavailable(xl('Not authorized to amend an existing patient assessment.'));
        }
        $response = $this->processor->decodeAndValidateResponse($responseJson);
        $questionnaireJson = $responseId !== ''
            ? $this->savedQuestionnaire($responseId, $questionnaireId)
            : $this->activeQuestionnaire($questionnaireId);
        $this->processor->decodeAndValidateQuestionnaire($questionnaireJson);

        $stamped = $this->processor->stampResponse($response, $this->store->patientUuid($this->pid));

        return $this->store->savePatientResponse(
            $this->pid,
            json_encode($stamped, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            $responseId !== '' ? $responseId : null,
            $questionnaireJson
        );
    }

    /**
     * The questionnaire an existing response of this patient's was saved with. The response
     * must be patient-level and linked to the questionnaire the workspace says it answers.
     */
    private function savedQuestionnaire(string $responseId, int $questionnaireId): string
    {
        $existing = $this->store->response($responseId);
        if ($existing === []) {
            throw new AssessmentUnavailable(xl('The QuestionnaireResponse to update was not found.'));
        }
        if ($this->processor->normalizeNonNegativeInt($existing['patient_id'] ?? null) !== $this->pid) {
            throw new AssessmentUnavailable(xl('The QuestionnaireResponse does not belong to the selected patient.'));
        }
        if (!$this->processor->isPatientLevelEncounter($existing['encounter'] ?? null)) {
            throw new AssessmentUnavailable(xl('Encounter questionnaires cannot be updated from the patient assessment workspace.'));
        }
        if ($this->processor->normalizeNonNegativeInt($existing['questionnaire_foreign_id'] ?? null) !== $questionnaireId) {
            throw new AssessmentUnavailable(xl('QuestionnaireResponse repository linkage is invalid.'));
        }

        // A response saved through the FHIR API (by a SMART app, say) has no stored questionnaire;
        // the workspace opened it with the linked repository questionnaire, so save it with that too.
        $questionnaireJson = AssessmentValues::text($existing['questionnaire'] ?? null);
        if ($questionnaireJson === '') {
            $questionnaireJson = AssessmentValues::text($this->store->questionnaire($questionnaireId)['questionnaire'] ?? null);
        }

        return $questionnaireJson;
    }

    /**
     * The repository's questionnaire, which must be active, for a new response.
     */
    private function activeQuestionnaire(int $questionnaireId): string
    {
        $record = $this->store->questionnaire($questionnaireId);
        if ($record === [] || $this->processor->normalizeNonNegativeInt($record['active'] ?? null) !== 1) {
            throw new AssessmentUnavailable(xl('The selected Questionnaire is not active or was not found.'));
        }

        return AssessmentValues::text($record['questionnaire'] ?? null);
    }

    /**
     * A 400 the workspace shows as $message.
     */
    private static function refused(string $message): JsonResponse
    {
        return new JsonResponse(['success' => false, 'message' => $message], JsonResponse::HTTP_BAD_REQUEST);
    }
}
