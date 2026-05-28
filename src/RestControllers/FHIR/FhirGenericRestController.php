<?php

/*
 * FhirGenericRestController.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\SMART\ResourceConstraintFilterer;
use OpenEMR\RestControllers\Config\RestConfig;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\IGlobalsAware;
use OpenEMR\Services\Trait\GlobalInterfaceTrait;
use OpenEMR\Validators\ProcessingResult;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FhirGenericRestController implements IGlobalsAware {

    use GlobalInterfaceTrait;
    private FhirResourcesService $fhirResourcesService;

    private array $aclChecks = [];

    /**
     * Expected FHIR resourceType for write operations on this route. Set by the
     * route handler so deserializeFhirResource() can reject any payload whose
     * resourceType does not match the route's bound resource. Required for
     * post() and put(); not used for read paths.
     */
    private ?string $expectedResourceType = null;

    private ResourceConstraintFilterer $resourcePolicyEnforcementDecisionChecker;

    public function __construct(protected HttpRestRequest $request, protected FhirServiceBase $fhirService, OEGlobalsBag $globalsBag)
    {
        $this->setGlobalsBag($globalsBag);
        if ($request->getSession()) {
            $this->fhirService->setSession($request->getSession());
        }
    }

    public function getResourcePolicyEnforcementDecisionChecker(): ResourceConstraintFilterer {
        // TODO: eventually we could inject the ACLs here and do more advanced checking on a per-resource basis
        if (!isset($this->resourcePolicyEnforcementDecisionChecker)) {
            $this->resourcePolicyEnforcementDecisionChecker = new ResourceConstraintFilterer();
        }
        return $this->resourcePolicyEnforcementDecisionChecker;
    }

    public function addAclRestrictions(string $section, string $subSection = '', string $aclPermission = '') : void {
        $this->aclChecks[] = ['section' => $section, 'subSection' => $subSection, 'aclPermission' => $aclPermission];
    }

    /**
     * Sets the FHIR resourceType this route accepts for writes. Required for
     * post() and put() — the request body's resourceType must match this value
     * exactly, otherwise the request is rejected with 400. This prevents a POST
     * to one resource endpoint from instantiating a different FHIR class.
     */
    public function setExpectedResourceType(string $resourceType): void
    {
        $this->expectedResourceType = $resourceType;
    }

    protected function getFhirResourcesService(): FhirResourcesService
    {
        if (!isset($this->fhirResourcesService)) {
            $this->fhirResourcesService = new FhirResourcesService();
        }
        return $this->fhirResourcesService;
    }

    public function getHttpRestRequest(): HttpRestRequest
    {
        return $this->request;
    }

    public function getFhirService(): FhirServiceBase
    {
        return $this->fhirService;
    }

    /**
     * Queries for a single FHIR condition resource by FHIR id
     * @param string $fhirId The FHIR condition resource id (uuid)
     * @returns Response 200 if the operation completes successfully
     */
    public function getOne(string $fhirId): Response
    {
        // security constraints are added as additional query parameters here
        // so that the same processing logic can be used for both single resource
        // and multiple resource retrieval
        // that is why we override the _id parameter and pass along any other query parameters
        // while this means that a 404 will be returned instead of a 401, that's ok.
        // TODO: consider changing status code to 401 in the future if needed
        $queryParams = $this->getHttpRestRequest()->query->all();
        $queryParams['_id'] = $fhirId;
        $processingResult = $this->getAllProcessingResult($queryParams);

        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    protected function getAllProcessingResult(array $searchParams): ProcessingResult {
        if ($this->getHttpRestRequest()->isPatientRequest()) {
            $puuidBind = $this->getHttpRestRequest()->getPatientUUIDString();
        } else {
            // perform ACL checks
            foreach ($this->aclChecks as $aclCheck) {
                RestConfig::request_authorization_check($this->getHttpRestRequest(), $aclCheck['section'], $aclCheck['subSection'], $aclCheck['aclPermission']);
            }
            $puuidBind = null;
        }
        $filteredProcessingResult = new ProcessingResult();
        $searchResult = $this->getFhirService()->getAll($searchParams, $puuidBind);
        if ($searchResult->isValid() && $searchResult->hasData()) {
            foreach ($searchResult->getData() as $resource) {
                if ($this->canAccessResource($resource)) {
                    $filteredProcessingResult->addData($resource);
                }
            }
        } else {
            $filteredProcessingResult = $searchResult;
        }
        return $filteredProcessingResult;
    }

    /**
     * Queries for FHIR condition resources using various search parameters.
     * @param array $searchParams
     * @return JsonResponse|Response FHIR bundle with query results, if found
     */
    public function getAll(?array $searchParams = null): JsonResponse|Response
    {
        if (empty( $searchParams)) {
            $searchParams = $this->request->query->all();
        }
        $redirectUrl = $this->getHttpRestRequest()->getServerParams()['REDIRECT_URL'] ?? '';
        $bundleEntries = [];
        $resourceName = 'FhirDomainResource';
        $processingResult = $this->getAllProcessingResult($searchParams);
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $this->getGlobalsBag()->get('site_addr_oath') . $redirectUrl . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            if ($searchResult instanceof FHIRDomainResource) {
                $resourceName = $searchResult->get_fhirElementName();
            }
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->getFhirResourcesService()->createBundle($resourceName, $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }

    public function canAccessResource(FHIRDomainResource $resource): bool {
        return $this->getResourcePolicyEnforcementDecisionChecker()->canAccessResource($resource, $this->getHttpRestRequest());
    }

    /**
     * Creates a new FHIR resource
     * @param array $fhirJson The FHIR resource as a JSON-decoded array
     * @return Response 201 if the resource is created, 400 if invalid
     */
    public function post(array $fhirJson): Response
    {
        if ($this->getHttpRestRequest()->isPatientRequest()) {
            return RestControllerHelper::responseHandler(null, null, 403);
        }

        foreach ($this->aclChecks as $aclCheck) {
            RestConfig::request_authorization_check(
                $this->getHttpRestRequest(),
                $aclCheck['section'],
                $aclCheck['subSection'],
                $aclCheck['aclPermission']
            );
        }

        $fhirValidationService = new FhirValidationService();
        $validationResult = $fhirValidationService->validate($fhirJson);
        if (!empty($validationResult)) {
            return RestControllerHelper::responseHandler($validationResult, null, 400);
        }

        $compartmentCheck = $this->enforcePatientCompartment($fhirJson);
        if ($compartmentCheck instanceof Response) {
            return $compartmentCheck;
        }

        $fhirResource = $this->deserializeFhirResource($fhirJson);
        if ($fhirResource instanceof Response) {
            return $fhirResource;
        }

        try {
            $processingResult = $this->getFhirService()->insert($fhirResource);
        } catch (\InvalidArgumentException $e) {
            return RestControllerHelper::responseHandler(
                UtilsService::createOperationOutcomeResource('error', 'invalid', $e->getMessage()),
                null,
                400
            );
        }
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 201);
    }

    /**
     * Updates an existing FHIR resource
     * @param string $fhirId The FHIR resource id (uuid)
     * @param array $fhirJson The updated FHIR resource (complete resource)
     * @return Response 200 if the resource is updated, 400 if invalid
     */
    public function put(string $fhirId, array $fhirJson): Response
    {
        if ($this->getHttpRestRequest()->isPatientRequest()) {
            return RestControllerHelper::responseHandler(null, null, 403);
        }

        foreach ($this->aclChecks as $aclCheck) {
            RestConfig::request_authorization_check(
                $this->getHttpRestRequest(),
                $aclCheck['section'],
                $aclCheck['subSection'],
                $aclCheck['aclPermission']
            );
        }

        $fhirValidationService = new FhirValidationService();
        $validationResult = $fhirValidationService->validate($fhirJson);
        if (!empty($validationResult)) {
            return RestControllerHelper::responseHandler($validationResult, null, 400);
        }

        $compartmentCheck = $this->enforcePatientCompartment($fhirJson);
        if ($compartmentCheck instanceof Response) {
            return $compartmentCheck;
        }

        $fhirResource = $this->deserializeFhirResource($fhirJson);
        if ($fhirResource instanceof Response) {
            return $fhirResource;
        }

        try {
            $processingResult = $this->getFhirService()->update($fhirId, $fhirResource);
        } catch (\InvalidArgumentException $e) {
            return RestControllerHelper::responseHandler(
                UtilsService::createOperationOutcomeResource('error', 'invalid', $e->getMessage()),
                null,
                400
            );
        }
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Enforce that, when the request carries a token-bound patient UUID (SMART
     * patient context — e.g. patient/*.write or launch/patient), the resource
     * body's Patient reference matches that bound patient. Prevents an
     * IDOR-style write that targets another patient than the token authorized.
     *
     * For practitioner/admin tokens (no bound patient) this is a no-op — the
     * upstream ACL check already gates the resource type, and OpenEMR's
     * broader provider/facility access model governs which patients a
     * practitioner can act on.
     *
     * For resources outside the patient compartment (Medication, Practitioner,
     * Organization, Location, etc.) this is a no-op — they have no patient
     * binding.
     *
     * @param array<string, mixed> $fhirJson
     */
    private function enforcePatientCompartment(array $fhirJson): ?Response
    {
        $boundPatientUuid = $this->getHttpRestRequest()->getPatientUUIDString();
        if (empty($boundPatientUuid)) {
            return null;
        }
        if (!($this->getFhirService() instanceof \OpenEMR\Services\FHIR\IPatientCompartmentResourceService)) {
            return null;
        }
        $bodyPatientUuid = self::extractPatientUuidFromFhirJson($fhirJson);
        if ($bodyPatientUuid === null) {
            // Compartment resource that didn't supply a patient reference — let
            // the service-level validator surface the missing-reference error.
            return null;
        }
        if (strcasecmp($bodyPatientUuid, $boundPatientUuid) !== 0) {
            return RestControllerHelper::responseHandler(
                UtilsService::createOperationOutcomeResource(
                    'error',
                    'forbidden',
                    'Patient reference in request body does not match the token-bound patient'
                ),
                null,
                403
            );
        }
        return null;
    }

    /**
     * Extracts the patient UUID from common FHIR compartment fields
     * (`subject.reference`, `patient.reference`, `beneficiary.reference`).
     * Returns null if no patient reference is present or the value isn't a
     * `Patient/{uuid}` reference.
     *
     * @param array<string, mixed> $fhirJson
     */
    private static function extractPatientUuidFromFhirJson(array $fhirJson): ?string
    {
        foreach (['subject', 'patient', 'beneficiary'] as $field) {
            $reference = $fhirJson[$field]['reference'] ?? null;
            if (!is_string($reference) || $reference === '') {
                continue;
            }
            if (preg_match('#(?:^|/)Patient/([A-Za-z0-9\-]+)$#', $reference, $matches) === 1) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * Deserializes a FHIR JSON array into a FHIRDomainResource. The payload's
     * resourceType must exactly match the route's expected type (set via
     * setExpectedResourceType). The class to instantiate is taken from a
     * static map (not from user input), so untrusted JSON cannot trigger
     * autoload of an arbitrary class.
     *
     * @param array $fhirJson The FHIR resource as a JSON-decoded array
     * @return FHIRDomainResource|Response The deserialized resource, or a 400 Response on error
     */
    private function deserializeFhirResource(array $fhirJson): FHIRDomainResource|Response
    {
        if ($this->expectedResourceType === null) {
            // Misconfigured route: a write controller without an expected type
            // would fall back to dynamic class resolution. Refuse to proceed.
            return RestControllerHelper::responseHandler(
                UtilsService::createOperationOutcomeResource('error', 'exception', 'Server misconfiguration'),
                null,
                500
            );
        }

        $resourceType = $fhirJson['resourceType'] ?? '';
        if ($resourceType === '' || !is_string($resourceType)) {
            return RestControllerHelper::responseHandler(
                UtilsService::createOperationOutcomeResource('error', 'invalid', 'resourceType is required'),
                null,
                400
            );
        }

        // Strict equality against the route-bound type. Rejects payloads that
        // try to switch the FHIR class (e.g. POST /Appointment with
        // resourceType: "Patient") and avoids any string-derived class lookup.
        if ($resourceType !== $this->expectedResourceType) {
            return RestControllerHelper::responseHandler(
                UtilsService::createOperationOutcomeResource(
                    'error',
                    'invalid',
                    'resourceType must be ' . $this->expectedResourceType
                ),
                null,
                400
            );
        }

        $className = 'OpenEMR\\FHIR\\R4\\FHIRDomainResource\\FHIR' . $this->expectedResourceType;
        if (!class_exists($className)) {
            // The expected type is set by trusted route code, so a missing
            // class is a server misconfiguration rather than client error.
            return RestControllerHelper::responseHandler(
                UtilsService::createOperationOutcomeResource('error', 'exception', 'Unsupported resource type'),
                null,
                500
            );
        }

        unset($fhirJson['resourceType']);
        return new $className($fhirJson);
    }
}
