<?php

/**
 * ObservationController handles observation form operations with QuestionnaireResponse linking
 * AI Generated: Enhanced to support QuestionnaireResponse linking functionality
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers\Interface\Forms\Observation;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Forms\ReasonStatusCodes;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\Services\FormService;
use OpenEMR\Services\ObservationService;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Services\Utils\DateFormatterUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use InvalidArgumentException;

class ObservationController
{
    use SystemLoggerAwareTrait;

    const DATE_FORMAT_SAVE = 'Y-m-d H:i:s';
    const DEFAULT_STATUS = 'preliminary';
    private ObservationService $observationService;
    private FormService $formService;
    private Environment $twig;
    private CodeTypesService $codeTypeService;
    private PatientService $patientService;

    public function __construct(
        ?ObservationService $observationService = null,
        ?FormService $formService = null,
        ?Environment $twig = null,
        ?PatientService $patientService = null
    ) {
        $this->observationService = $observationService ?? new ObservationService();
        $this->formService = $formService ?? new FormService();
        $this->twig = $twig ?? (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();
        $this->codeTypeService = new CodeTypesService();
        $this->patientService = $patientService ?? new PatientService();
    }

    public function setCodeTypesService(CodeTypesService $service): void
    {
        $this->codeTypeService = $service;
    }

    public function getCodeTypesService(): CodeTypesService
    {
        return $this->codeTypeService;
    }

    /**
     * Handle observation new/edit form using Twig template
     * AI Generated: Enhanced to support QuestionnaireResponse linking
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request): Response
    {
        if (!$this->getFormService()->hasFormPermission("observation")) {
            return $this->createResponse(xlt("Unauthorized access"), Response::HTTP_UNAUTHORIZED);
        }
        $formId = $request->query->getInt('form_id');
        $id = $request->query->getInt('id', 0);
        if ($formId <= 0) {
            $formId = $id; // openemr sends us the form_id as the id param for new forms
        }

        $pid = $_SESSION['pid'] ?? 0;
        $encounter = $_SESSION['encounter'] ?? 0;

        try {
            // Get observations for this form
            $observation = null;
            if ($id > 0) {
                $observation = $this->observationService->getObservationById($id, $pid, true);
            }

            // If no observations found, create a default empty observation
            if (empty($observation)) {
                $observation = $this->observationService->getNewObservationTemplate();
            }

            // Get observation types and reason code statii
            $reasonCodeStatii = ReasonStatusCodes::getCodesWithDescriptions();

            // AI Generated: Get patient UUID for FHIR API calls
            $patientUuid = null;
            if ($pid > 0) {
                $patientUuid = $this->patientService->getUuid($pid);
                $patientUuid = $patientUuid !== false ? UuidRegistry::uuidToString($patientUuid) : null;
            }

            $serverConfig = new ServerConfig();
            // Prepare template data
            $templateData = [
                'isEdit' => !empty($observation['id']),
                'formId' => $formId ?: $observation['id'],
                'observation' => $observation,
                'reasonCodeStatii' => $reasonCodeStatii,
                'csrf_token' => CsrfUtils::collectCsrfToken(),
                'apiCsrfToken' => CsrfUtils::collectCsrfToken('api'),
                'title' => xl('Observation Form'),
                'reasonCodeTypes' => $this->codeTypeService->collectCodeTypes("medical_problem", "csv"),
                'linkedQuestionnaireResponse' => $observation['questionnaire_response'] ?? null,
                // AI Generated: FHIR configuration for QuestionnaireResponse dialog
                'fhir_config' => [
                    'base_url' => $serverConfig->getFhirUrl(),
                    'patient_uuid' => $patientUuid,
                ],
                'translations' => [
                    'CONFIRM_SUB_OBSERVATION_DELETE' => xl("Are you sure you want to delete this sub-observation?"),
                    'CONFIRM_QUESTIONNAIRE_REMOVE' => xl("Are you sure you want to remove the questionnaire link?"),
                    'QUESTIONNAIRE_LOAD_ERROR' => xl("Error loading questionnaire responses. Please try again."),
                    'QUESTIONNAIRE_SELECT_ERROR' => xl("Please select a questionnaire response."),
                    'VALIDATION_CODE_REQUIRED' => xl('Code is required'),
                    'VALIDATION_DESCRIPTION_REQUIRED' => xl('Description is required'),
                    'VALIDATION_DATE_REQUIRED' => xl('Date is required'),
                    'VALIDATION_VALUE_REQUIRED' => xl('Value is required for the selected observation'),
                    'VALIDATION_SUB_VALUE_REQUIRED' => xl('Value is required for the selected sub-observation'),
                    'VALIDATION_SUB_DESCRIPTION_REQUIRED' => xl('Description is required for the selected sub-observation'),
                    'RESPONSE_ANSWER_YES' => xl('Yes'),
                    'RESPONSE_ANSWER_NO' => xl('No'),
                    'RESPONSE_ANSWER_MISSING' => xl('No Answer'),
                    'RESPONSE_QUESTION_MISSING' => xl('Question'),
                    'QUESTIONNAIRE_DIALOG_TITLE' => xl('Select Questionnaire Response'),
                    'QUESTIONNAIRE_DIALOG_CANCEL' => xl('Cancel'),
                    'QUESTIONNAIRE_LOADING_RESPONSES' => xl('Loading questionnaire responses...'),
                    'QUESTIONNAIRE_ITEM_SUMMARY_DETAILS_SHOW' => xl('Show Details'),
                    'QUESTIONNAIRE_ITEM_SUMMARY_DETAILS_HIDE' => xl('Hide Details'),
                    'CLOSE_TAB_ERROR' => xl('Error closing tab, please close manually.'),
                ],
                'defaultStatusType' => self::DEFAULT_STATUS
            ];

            // Render the Twig template
            $content = $this->twig->render($this->getTemplatePath('observation_edit.html.twig'), $templateData);

            return $this->createResponse($content);
        } catch (\Exception $e) {
            $this->getSystemLogger()->errorLogCaller("Error rendering observation form", [
                'error' => $e->getMessage(),
                'formId' => $formId,
                'pid' => $pid,
                'encounter' => $encounter
            ]);

            return $this->createResponse(
                "<div class='alert alert-danger'>" . xlt("An error occurred loading the observation form") . "</div>",
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Handle observation list view using Twig template
     * AI Generated: New method to render list view from observation_list_screen.html design
     *
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request): Response
    {
        if (!$this->getFormService()->hasFormPermission("observation")) {
            return $this->createResponse(xlt("Unauthorized access"), Response::HTTP_UNAUTHORIZED);
        }

        $pid = $_SESSION['pid'] ?? 0;
        $encounter = $_SESSION['encounter'] ?? 0;

        try {
            // Get observations with filtering
            // make sure we only get top-level observations (no parent_observation_id)
            $parentObservation = new TokenSearchField("parent_observation_id", [new TokenSearchValue(null)]);
            $parentObservation->setModifier(SearchModifier::MISSING);
            $result = $this->observationService->searchAndPopulateChildObservations(['pid' => $pid, 'encounter' => $encounter
                ,'parent_observation_id' => $parentObservation, 'form_id' => $request->query->get('id', null)]); //, 'parent_observation' => $parentObservation]);
            $observations = $result->getData();

            // Prepare template data
            $templateData = [
                'observations' => $observations,
                'formId' => $request->query->get('id', null)
            ];
            if ($request->query->get('status') === 'saved') {
                $templateData['statusMessage'] = xl('Observation saved successfully.');
                $templateData['statusType'] = 'success';
                $templateData['refreshParent'] = true;
            } else if ($request->query->get('status') === 'delete_success') {
                $templateData['statusMessage'] = xl('Observation deleted successfully.');
                $templateData['statusType'] = 'success';
                $templateData['refreshParent'] = true;
            } else if ($request->query->get('status') === 'delete_failed') {
                $templateData['statusMessage'] = xl('Failed to delete observation. Please try again.');
                $templateData['statusType'] = 'danger';
            }

            // Render the Twig template
            $content = $this->twig->render($this->getTemplatePath('observation_list.html.twig'), $templateData);

            return $this->createResponse($content);
        } catch (\Exception $e) {
            $this->getSystemLogger()->errorLogCaller("Error rendering observation list", [
                'error' => $e->getMessage(),
                'pid' => $pid,
                'encounter' => $encounter
            ]);

            return $this->createResponse(
                "<div class='alert alert-danger'>" . xlt("An error occurred loading the observation list") . "</div>",
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Handle observation save with enhanced data structure
     * AI Generated: Enhanced to support Design 1 sub-observations and new schema, to handle QuestionnaireResponse linking
     *
     * @param Request $request
     * @return Response
     */
    public function saveAction(Request $request): Response
    {
        if (!$this->getFormService()->hasFormPermission("observation")) {
            return $this->createResponse(xlt("Unauthorized access"), Response::HTTP_UNAUTHORIZED);
        }
        if (!CsrfUtils::verifyCsrfToken($request->request->get('csrf_token_form'))) {
            return $this->createResponse(
                xlt("Authentication Error"),
                Response::HTTP_UNAUTHORIZED
            );
        }

        $formId = $request->query->getInt('id');
        $postData = $request->request->all();

        try {
            // JS should catch validation errors before reaching here..
            $observation = $this->processEnhancedFormSave($formId, $postData);

            // we have to keep id as the formId due to backwards compatibility
            return $this->createRedirectResponse(
                $GLOBALS['webroot'] . "/interface/forms/observation/new.php?id="
                . urlencode((string) $observation['form_id'])
                . "&status=saved"
            );
        } catch (\Exception $e) {
            $this->getSystemLogger()->errorLogCaller("Error saving observation", [
                'error' => $e->getMessage(),
                'formId' => $formId,
                'postData' => $postData
            ]);
            return $this->createResponse(
                xlt("An error occurred saving the observation"),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function processEnhancedFormSave(int $formId, array $postData): array
    {
        // bundle everything in a transaction
        try {
            QueryUtils::startTransaction();
            // Extract main observation data
            $observationId = intval($postData['observation_id'] ?? 0);
            if ($observationId > 0) {
                $observation = $this->observationService->getObservationById($observationId, $_SESSION['pid']);
                if (empty($observation)) {
                    throw new \Exception("Observation with ID {$observationId} not found for patient.");
                }
            } else {
                $observation = $this->observationService->getNewObservationTemplate();
            }
            $observation['form_id'] = $formId;
            $observation['pid'] = $_SESSION['pid'];
            $observation['encounter'] = $_SESSION['encounter'];
            $observation['user'] = $_SESSION['authUser'];
            $observation['groupname'] = $_SESSION['authProvider'];
            $observation['authorized'] = $_SESSION['userauthorized'] ?? 0;

            // grab any ids before postData can overwrite them
            $originalIds = array_filter(array_map(fn($sub) => $sub['id'] ?? 0, $observation['sub_observations'] ?? []));

            foreach ($postData as $fieldName => $value) {
                // update only fields that exist in the observation
                if (!in_array($fieldName, ['form_id', 'pid', 'encounter', 'userauthorized', 'groupname', 'user'])) {
                    $observation[$fieldName] = $value;
                }
            }
            $observation['date'] = $postData["date"];
            // AI Generated: Handle QuestionnaireResponse ID conversion
            if (!empty($postData['questionnaire_response_fhir_id'])) {
                $fhirId = $postData['questionnaire_response_fhir_id'];
                $localId = $this->convertFhirIdToLocalId($fhirId);
                $observation['questionnaire_response_id'] = $localId;
            } else {
                $observation['questionnaire_response_id'] = null; // empty this out as there's no link
            }

            // Validate observation data
            $validationErrors = $this->observationService->validateObservationData($observation);
            if (!empty($validationErrors)) {
                throw new \Exception("Validation failed: " . implode(", ", $validationErrors));
            }
            // Format date for saving, no seconds on this one
            // TODO: @adunsulag do we want to preserve seconds on the observation?
            $observation['date'] = (DateFormatterUtils::dateStringToDateTime($observation['date']))->format(self::DATE_FORMAT_SAVE);

            // Extract sub-observations data
            $submittedObservations = $this->extractSubObservationsData($observation, $postData);
            // we cleanup the sub observations first so the final save will return the correct data structure
            $submittedIds = array_filter(array_map(fn($sub) => $sub['id'] ?? 0, $submittedObservations));
            $idsToDelete = array_diff($originalIds, $submittedIds);
            foreach ($idsToDelete as $idToDelete) {
                $this->observationService->deleteObservationById($idToDelete, $formId, $_SESSION['pid'], $_SESSION['encounter']);
            }
            $observation['sub_observations'] = $submittedObservations;
            // Save main observation with sub-observations
            $observation = $this->observationService->saveObservation(
                $observation
            );
            QueryUtils::commitTransaction();
        } catch (\Exception $e) {
            QueryUtils::rollbackTransaction();
            throw $e; // rethrow to be handled in save()
        }
        return $observation;
    }

    /**
     * AI Generated: Convert FHIR QuestionnaireResponse ID to local database ID
     *
     * @param string $fhirId FHIR ID like "QuestionnaireResponse/123" or just "123"
     * @return int|null Local database ID
     */
    private function convertFhirIdToLocalId(string $fhirId): ?int
    {
        try {
            // Extract the numeric ID from FHIR format
            $numericId = str_replace('QuestionnaireResponse/', '', $fhirId);

            // Query the questionnaire_response table to get the local ID
            $sql = "SELECT id FROM questionnaire_response WHERE response_id = ? OR id = ?";
            $result = QueryUtils::fetchSingleValue($sql, 'id', [$numericId, $numericId]);

            return $result ? (int)$result : null;
        } catch (\Exception $e) {
            $this->getSystemLogger()->errorLogCaller("Error converting FHIR ID to local ID", [
                'fhir_id' => $fhirId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * AI Generated: Get QuestionnaireResponse details for display
     *
     * @param int $questionnaireResponseId Local database ID
     * @return array|null QuestionnaireResponse details
     */
    private function getQuestionnaireResponseDetails(int $questionnaireResponseId): ?array
    {
        try {
            $sql = "SELECT id, response_id, questionnaire_name, status, create_time, questionnaire_response
                   FROM questionnaire_response WHERE id = ?";
            $result = QueryUtils::fetchRecords($sql, [$questionnaireResponseId]);

            if (empty($result)) {
                return null;
            }

            $response = $result[0];

            // Parse questionnaire_response JSON for additional details
            $questionnaireData = null;
            if (!empty($response['questionnaire_response'])) {
                $questionnaireData = json_decode((string) $response['questionnaire_response'], true);
            }

            return [
                'id' => $response['id'],
                'response_id' => $response['response_id'],
                'name' => $response['questionnaire_name'],
                'status' => $response['status'],
                'date' => $response['create_time'],
                'questionnaire_data' => $questionnaireData
            ];
        } catch (\Exception $e) {
            $this->getSystemLogger()->errorLogCaller("Error getting QuestionnaireResponse details", [
                'questionnaire_response_id' => $questionnaireResponseId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Extract sub-observations data from form POST
     * AI Generated: New method to handle Design 1 sub-observations
     *
     * @param array $postData
     * @return array
     */
    private function extractSubObservationsData(array $mainObservation, array $postData): array
    {
        $subObservations = [];

        // Check if sub-observation data exists
        if (!empty($postData['sub_ob_value']) && is_array($postData['sub_ob_value'])) {
            foreach ($postData['sub_ob_value'] as $index => $value) {
                if (!empty($value) || !empty($postData['sub_description'][$index] ?? '')) {
                    $subObservations[] = [
                        'id' => $postData['sub_observation_id'][$index] ?? 0,
                        'form_id' => $mainObservation['form_id'],
                        'pid' => $mainObservation['pid'],
                        'encounter' => $mainObservation['encounter'],
                        'user' => $mainObservation['user'],
                        'groupname' => $mainObservation['groupname'],
                        'authorized' => $mainObservation['authorized'],
                        'parent_observation_id' => $mainObservation['id'],
                        'ob_value' => $value ?? '',
                        'ob_status' => $postData['sub_ob_status'][$index] ?? $mainObservation['ob_status'] ?? self::DEFAULT_STATUS,
                        'ob_unit' => $postData['sub_ob_unit'][$index] ?? '',
                        'description' => $postData['sub_description'][$index] ?? '',
                        'code' => $postData['sub_ob_code'][$index] ?? '',
                        'code_type' => $postData['sub_code_type'][$index] ?? '',
                        'ob_type' => $mainObservation['ob_type'],
                        'date' => $mainObservation['date'] ?? date(self::DATE_FORMAT_SAVE),
                        'date_end' => $mainObservation['date_end'] ?? date(self::DATE_FORMAT_SAVE),
                        'observation' => '', // Sub-observations do not have their own comments
                        'questionnaire_response_id' => $mainObservation['questionnaire_response_id']
                    ];
                }
            }
        }

        return $subObservations;
    }

    /**
     * Handle observation delete
     * AI Generated: Enhanced to handle sub-observations
     *
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request): Response
    {
        if (!$this->getFormService()->hasFormPermission("observation")) {
            return $this->createResponse(xlt("Unauthorized access"), Response::HTTP_UNAUTHORIZED);
        }
        $observationId = $request->query->getInt('id');
        $pid = $_SESSION['pid'];
        $encounter = $_SESSION['encounter'];
        $formId = $request->query->getInt('form_id', 0);
        $committed = false;
        try {
            if ($observationId <= 0) {
                throw new InvalidArgumentException('Missing observation form id');
            }
            // first grab the observation
            QueryUtils::startTransaction();
            $observation = $this->observationService->getObservationById($observationId, $pid);
            if (!$observation) {
                // observation may have already been deleted, just redirect to list with success to avoid error loops
                return $this->createRedirectResponse(
                    $GLOBALS['webroot'] . "/interface/forms/observation/new.php?id=" . urlencode($formId)
                    . "&status=delete_success" // still redirect to list with success to avoid error loops
                );
            }
            if ($observation['form_id'] != $formId) {
                throw new InvalidArgumentException('Mismatched observation form id');
            }
            if ($observation['pid'] != $pid) {
                throw new InvalidArgumentException('Mismatched patient id');
            }
            if ($observation['encounter'] != $encounter) {
                throw new InvalidArgumentException('Mismatched encounter id');
            }
            $this->observationService->deleteObservationById($observationId, $formId, $pid, $encounter);
            QueryUtils::commitTransaction();
            $committed = true;
            return $this->createRedirectResponse(
                $GLOBALS['webroot'] . "/interface/forms/observation/new.php?id=" . urlencode($formId)
                . "&status=delete_success" // still redirect to list with success to avoid error loops
            );
        } catch (\Exception $e) {
            $this->getSystemLogger()->errorLogCaller("Error deleting observation", [
                'error' => $e->getMessage(),
                'formId' => $formId
            ]);
            return $this->createRedirectResponse(
                $GLOBALS['webroot'] . "/interface/forms/observation/new.php?id=" . urlencode($formId)
                . "&status=delete_failed" // let them know that the delete failed
            );
        } finally {
            if (!$committed) {
                QueryUtils::rollbackTransaction();
            }
        }
    }

    /**
     * Handle observation report (unchanged from original)
     *
     * @param int $pid
     * @param int $encounter
     * @param int $cols
     * @param int|null $id
     * @return Response
     */
    public function reportAction(int $pid, int $encounter, int $cols, ?int $id): Response
    {
        if (!$this->getFormService()->hasFormPermission("observation")) {
            return $this->createResponse(xlt("Unauthorized access"), Response::HTTP_UNAUTHORIZED);
        }

        $content = $this->renderReport($pid, $encounter, $cols, $id);

        return $this->createResponse($content);
    }

    /**
     * Handle observation view (delegates to new)
     *
     * @param Request $request
     * @return Response
     */
    public function viewAction(Request $request): Response
    {
        return $this->newAction($request);
    }

    /**
     * Create HTTP Response
     *
     * @param string $content
     * @param int $status
     * @return Response
     */
    private function createResponse(string $content, int $status = Response::HTTP_OK): Response
    {
        return new Response($content, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function createRedirectResponse(string $url, int $status = Response::HTTP_SEE_OTHER): Response
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Generate form redirect HTML
     *
     * @param string $title
     * @return string
     */
    private function getFormJumpHtml(string $title): string
    {
        return $this->twig->render($this->getTemplatePath('observation_formjump.html.twig'), ['title' => $title]);
    }

    /**
     * Render observation report (unchanged from original)
     *
     * @param int $pid
     * @param int $encounter
     * @param mixed $cols
     * @param int|null $id
     * @return string
     */
    private function renderReport(int $pid, int $encounter, $cols, $id): string
    {
        if (!$id) {
            return "";
        }

        $parentObservation = new TokenSearchField("parent_observation_id", [new TokenSearchValue(null)]);
        $parentObservation->setModifier(SearchModifier::MISSING);
        $result = $this->observationService->searchAndPopulateChildObservations(['form_id' => $id, 'pid' => $pid, 'encounter' => $encounter,
            'parent_observation_id' => $parentObservation]);
        $observations = $result->getData();
        $formattedObs = array_map($this->formatObservationForDisplay(...), $observations);
        return $this->twig->render($this->getTemplatePath("observation_report.html.twig"), ['observations' => $formattedObs]);
    }


    /**
     * Format observation for display in list or report
     * AI Generated: New method to support consistent display formatting
     *
     * @param array $observation
     * @return array
     */
    public function formatObservationForDisplay(array $observation): array
    {
        return [
            'id' => $observation['id'],
            'form_id' => $observation['form_id'],
            'code' => $observation['code'] ?? '',
            'description' => $observation['description'] ?? xl('Untitled Observation'),
            'ob_type' => $observation['ob_type'] ?? '',
            'type' => $observation['ob_type_display'] ?? '',
            'status' => $observation['ob_status_display'] ?? '',
            'ob_value' => $observation['ob_value'] ?? '',
            'ob_unit' => $observation['ob_unit'] ?? '',
            'date' => $observation['date'] ?? '',
            'date_end' => $observation['date_end'] ?? '',
            'observation' => $observation['observation'] ?? '',
            'questionnaire_name' => $observation['questionnaire_name'] ?? '',
            'questionnaire_response_id' => $observation['questionnaire_response_id'] ?? null,
            'parent_observation_id' => $observation['parent_observation_id'] ?? null,
            'ob_reason_code' => $observation['ob_reason_code'] ?? '',
            'ob_reason_status' => $observation['ob_reason_status'] ?? '',
            'ob_reason_text' => $observation['ob_reason_text'] ?? '',
            'subObservations' => array_map($this->formatObservationForDisplay(...), $observation['sub_observations'] ?? [])
        ];
    }

    private function getTemplatePath(string $templateName): string
    {
        return '/forms/observation/templates/' . $templateName;
    }

    /**
     * Get ObservationService instance (for testing)
     *
     * @return ObservationService
     */
    public function getObservationService(): ObservationService
    {
        return $this->observationService;
    }

    /**
     * Get FormService instance (for testing)
     *
     * @return FormService
     */
    public function getFormService(): FormService
    {
        return $this->formService;
    }

    public function shouldShowListView(Request $request): bool
    {

        // if no id and no form_id provided we show the list view
        if (
            $request->query->getInt('id') <= 0
            && $request->query->getInt('form_id') <= 0
        ) {
            // no id provided and no form_id provided
            // show new / edit view
            return false;
        } else if ($request->query->getInt('form_id') <= 0) {
            // if we only have an id(form_id) provided we show the list view as data exists for this form
            return true;
        } else {
            // we have either a form_id (new observation) or both id and form_id provided (edit observation)
            // show the edit view
            return false;
        }
    }
}
