<?php

/**
 * PrescriptionRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @author    Ivan Googla <ivan.jo.dev@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2024 Ivan Googla
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\PrescriptionService;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;

class PrescriptionRestController
{
    private readonly PrescriptionService $prescriptionService;

    public function __construct(?PrescriptionService $prescriptionService = null)
    {
        $this->prescriptionService = $prescriptionService ?? new PrescriptionService();
    }

    /**
     * Per-patient chart-access gate for the staff REST path. Mirrors the
     * UI's `interface/patient_file/summary/demographics.php` guard: caller
     * must hold `patients / demo`, plus (when the patient carries a squad
     * tag) `squads / <squad>`. Returns null when the caller is authorized;
     * returns a 400 validation-error ProcessingResult response otherwise.
     *
     * The tenant-wide route-level `patients/rx *` ACL is a coarse gate; a
     * caller with that grant can otherwise select any patient's UUID.
     * Applied at the controller (not the service) so the FHIR/SMART path
     * — which delegates through PrescriptionService::getAll via
     * FhirMedicationRequestService — is unaffected. That path's per-patient
     * authorization runs earlier in
     * BearerTokenAuthorizationStrategy::checkUserHasAccessToPatient.
     *
     * @param array<string,mixed>|null $patient The resolved patient row (pid, squad, uuid) or null.
     */
    private function denyIfNoChartAccess(HttpRestRequest $request, ?array $patient): ?ResponseInterface
    {
        if ($patient === null) {
            // Caller supplied a patient uuid that does not resolve. Return a
            // validation error so the shape matches other "not found" paths.
            return $this->validationErrorResponse(
                $request,
                ['patient.uuid' => 'Patient does not exist.']
            );
        }
        $squadRaw = $patient['squad'] ?? '';
        $squad = is_string($squadRaw) ? $squadRaw : '';
        if (!AclMain::aclCheckCore('patients', 'demo')) {
            return $this->validationErrorResponse(
                $request,
                ['patient.uuid' => 'User does not have access to this patient.']
            );
        }
        if ($squad !== '' && !AclMain::aclCheckCore('squads', $squad)) {
            return $this->validationErrorResponse(
                $request,
                ['patient.uuid' => 'User does not have access to this patient.']
            );
        }
        return null;
    }

    /**
     * @param array<string, string> $messages
     */
    private function validationErrorResponse(HttpRestRequest $request, array $messages): ResponseInterface
    {
        $processingResult = new ProcessingResult();
        $processingResult->setValidationMessages($messages);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 400);
    }

    /**
     * Process a HTTP POST request used to create a prescription record.
     *
     * @param array<string, mixed> $data array of prescription fields.
     * @param HttpRestRequest $request The HTTP request.
     * @return ResponseInterface 201/Created status code and the prescription identifier if successful.
     */
    #[OA\Post(
        path: '/api/prescription',
        description: 'Creates a new prescription',
        tags: ['standard'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'patient_id', description: 'Patient ID', type: 'integer'),
                    new OA\Property(property: 'drug', description: 'Drug name', type: 'string'),
                    new OA\Property(property: 'dosage', description: 'Dosage', type: 'string'),
                    new OA\Property(property: 'quantity', description: 'Quantity', type: 'string'),
                    new OA\Property(property: 'provider_id', description: 'Provider ID', type: 'integer'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: '201', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function post(array $data, HttpRestRequest $request): ResponseInterface
    {
        $processingResult = $this->prescriptionService->insert($data);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 201);
    }

    /**
     * Soft-deletes a prescription record by setting active = 0.
     *
     * @param string $uuid The prescription uuid.
     * @param HttpRestRequest $request The HTTP request.
     * @return ResponseInterface 200 status on success, 400 if uuid is invalid.
     */
    #[OA\Delete(
        path: '/api/prescription/{uuid}',
        description: 'Soft-deletes a prescription (sets active = 0)',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the prescription.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'patient_uuid',
                in: 'query',
                description: 'Optional. When supplied, the prescription must belong to this patient — otherwise the request is rejected with 400. Callers driving from a patient chart should always supply this to enforce per-patient ownership.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function delete(string $uuid, HttpRestRequest $request): ResponseInterface
    {
        // Per-patient ACL: resolve the prescription's owner and gate on
        // the caller's chart access to that patient. Runs regardless of
        // whether the caller supplied `patient_uuid` (the query hint is
        // an additional caller-side constraint, not the authorization).
        // findPatientForPrescription returns null for missing prescriptions
        // AND for prescriptions whose owning patient row cannot be
        // resolved (orphaned) — in either case the service delete() below
        // returns the "uuid" validation-error shape that the controller
        // maps to 400.
        $owner = $this->prescriptionService->findPatientForPrescription($uuid);
        if ($owner !== null) {
            $denied = $this->denyIfNoChartAccess($request, $owner);
            if ($denied !== null) {
                return $denied;
            }
        }
        // If the caller supplied a `patient_uuid` query parameter, forward it
        // so the service can assert the prescription belongs to that patient
        // before deactivating it. When omitted (e.g. administrative cleanup),
        // the service falls back to the pre-existing behaviour.
        $expectedPatientUuid = $request->query->getString('patient_uuid') ?: null;
        $processingResult = $this->prescriptionService->delete($uuid, $expectedPatientUuid);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }

    /**
     * Fetches a single prescription resource by id.
     * @param $uuid- The prescription uuid identifier in string format.
     */
    #[OA\Get(
        path: '/api/prescription/{uuid}',
        description: 'Retrieves a prescription',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                description: 'The uuid for the prescription.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getOne(string $uuid, HttpRestRequest $request): ResponseInterface
    {
        // Per-patient ACL: resolve the prescription's owner and gate on
        // the caller's chart access. findPatientForPrescription returns
        // null for missing prescriptions AND for prescriptions whose
        // owning patient row cannot be resolved (orphaned) — in either
        // case the service getOne() below returns the "uuid" validation-
        // error shape that the controller maps to 400.
        $owner = $this->prescriptionService->findPatientForPrescription($uuid);
        if ($owner !== null) {
            $denied = $this->denyIfNoChartAccess($request, $owner);
            if ($denied !== null) {
                return $denied;
            }
        }
        $processingResult = $this->prescriptionService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) === 0) {
            return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 404);
        }

        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200);
    }

    /**
     * Returns prescription resources which match an optional search criteria.
     */
    #[OA\Get(
        path: '/api/prescription',
        description: 'Retrieves a list of prescriptions for a patient. A `patient_uuid` query parameter is REQUIRED — tenant-wide enumeration is no longer supported.',
        tags: ['standard'],
        parameters: [
            new OA\Parameter(
                name: 'patient_uuid',
                in: 'query',
                description: 'REQUIRED. UUID of the patient whose prescriptions should be listed.',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: '200', ref: '#/components/responses/standard'),
            new OA\Response(response: '400', ref: '#/components/responses/badrequest'),
            new OA\Response(response: '401', ref: '#/components/responses/unauthorized'),
        ],
        security: [['openemr_auth' => []]]
    )]
    public function getAll(HttpRestRequest $request): ResponseInterface
    {
        $search = $request->getQueryParams();
        unset($search['_REWRITE_COMMAND']);
        // Standard REST callers pass `patient_uuid`; the service consumes the
        // FHIR-style `patient.uuid` search key. Translate here so the required
        // patient binding surfaces cleanly to the data layer. The service
        // rejects the request with a validation error if no binding is
        // present.
        if (isset($search['patient_uuid'])) {
            $search['patient.uuid'] = $search['patient_uuid'];
            unset($search['patient_uuid']);
        }
        // Per-patient ACL: gate on the caller's chart access to the
        // supplied patient before running the query. Without this, the
        // route-level `patients/rx view` grant (tenant-wide) would allow
        // any authorized caller to enumerate any patient's prescriptions
        // by selecting the UUID.
        $patientUuid = $search['patient.uuid'] ?? null;
        if (is_string($patientUuid) && $patientUuid !== '') {
            $patient = $this->prescriptionService->findPatientByPatientUuid($patientUuid);
            $denied = $this->denyIfNoChartAccess($request, $patient);
            if ($denied !== null) {
                return $denied;
            }
        }
        $processingResult = $this->prescriptionService->getAll($search);
        return RestControllerHelper::createProcessingResultResponse($request, $processingResult, 200, true);
    }
}
