<?php

/**
 * InsuranceRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\InsuranceService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

#[OA\Schema(
    schema: "api_insurance_request",
    properties: [
        new OA\Property(property: "provider", description: "The provider/insurance company id.", type: "string"),
        new OA\Property(property: "plan_name", description: "The plan name.", type: "string"),
        new OA\Property(property: "policy_number", description: "The policy number.", type: "string"),
        new OA\Property(property: "group_number", description: "The group number.", type: "string"),
        new OA\Property(property: "subscriber_lname", description: "Subscriber last name.", type: "string"),
        new OA\Property(property: "subscriber_fname", description: "Subscriber first name.", type: "string"),
        new OA\Property(property: "subscriber_DOB", description: "Subscriber date of birth.", type: "string"),
        new OA\Property(property: "subscriber_relationship", description: "Subscriber relationship.", type: "string"),
        new OA\Property(property: "subscriber_ss", description: "Subscriber social security.", type: "string"),
        new OA\Property(property: "date", description: "The effective date.", type: "string"),
        new OA\Property(property: "accept_assignment", description: "Accept assignment.", type: "string"),
    ]
)]
class InsuranceRestController
{
    private $insuranceService;

    public function __construct()
    {
        $this->insuranceService = new InsuranceService();
    }

    /**
     * Retrieves all insurances for a patient.
     */
    #[OA\Get(
        path: "/api/patient/{puuid}/insurance",
        description: "Retrieves all insurances for a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "pid",
                in: "path",
                description: "The uuid for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getAll($searchParams)
    {
        if (isset($searchParams['uuid'])) {
            $searchParams['uuid'] = new TokenSearchField('uuid', $searchParams['uuid'], true);
        }
        if (isset($searchParams['puuid'])) {
            $searchParams['puuid'] = new TokenSearchField('puuid', $searchParams['puuid'], true);
        }
        $serviceResult = $this->insuranceService->search($searchParams);
        return RestControllerHelper::handleProcessingResult($serviceResult, null, 200);
    }

    /**
     * Retrieves a single insurance for a patient.
     */
    #[OA\Get(
        path: "/api/patient/{puuid}/insurance/{uuid}",
        description: "Retrieves all insurances for a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "pid",
                in: "path",
                description: "The uuid for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getOne($insuranceUuid, $puuid)
    {
        $searchParams = [];
        // we do this again cause we have to handle the 404 result here.
        $searchParams['uuid'] = new TokenSearchField('uuid', $insuranceUuid, true);
        $searchParams['puuid'] = new TokenSearchField('puuid', $puuid, true);
        $processingResult = $this->insuranceService->search($searchParams);
        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200, false);
    }

    /**
     * Updates an existing patient insurance policy.
     */
    #[OA\Put(
        path: "/api/patient/{puuid}/insurance/{insuranceUuid}",
        description: "Edit a specific patient insurance policy. Requires the patients/demo/write ACL to call. This method is the preferred method for updating a patient insurance policy. The {insuranceId} can be found by querying /api/patient/{pid}/insurance",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "puuid",
                in: "path",
                description: "The uuid for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "insuranceUuid",
                in: "path",
                description: "The insurance policy uuid for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(ref: "#/components/schemas/api_insurance_request")
            )
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function put($puuid, $insuranceUuid, $data)
    {
        $data['uuid'] = $insuranceUuid;
        $data['type'] ??= 'primary';

        $processingResult = new ProcessingResult();
        $validationMessages = ['puuid::INVALID_PUUID' => 'Patient uuid invalid'];
        $processingResult->setValidationMessages($validationMessages);
        if (!UuidRegistry::isValidStringUUID($puuid)) {
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        }
        $puuid = UuidRegistry::uuidToBytes($puuid);
        $patientService = new PatientService();
        $pid = $patientService->getPidByUuid($puuid);
        if (empty($pid)) {
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        }
        $data['pid'] = $pid;

        $updatedResults = $this->insuranceService->update($data);
        return RestControllerHelper::handleProcessingResult($updatedResults, 200, false);
    }

    /**
     * Submits a new patient insurance.
     */
    #[OA\Post(
        path: "/api/patient/{puuid}/insurance",
        description: "Submits a new patient insurance.",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "puuid",
                in: "path",
                description: "The uuid for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(ref: "#/components/schemas/api_insurance_request")
            )
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function post($puuid, $data)
    {
        $data['type'] ??= 'primary';

        $processingResult = new ProcessingResult();
        $validationMessages = ['puuid::INVALID_PUUID' => 'Patient uuid invalid'];
        $processingResult->setValidationMessages($validationMessages);
        if (!UuidRegistry::isValidStringUUID($puuid)) {
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        }
        $puuid = UuidRegistry::uuidToBytes($puuid);
        $patientService = new PatientService();
        $pid = $patientService->getPidByUuid($puuid);
        if (empty($pid)) {
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        }
        $data['pid'] = $pid;
        $insertedResult = $this->insuranceService->insert($data);
        if (!$insertedResult->isValid()) {
            return RestControllerHelper::handleProcessingResult($insertedResult, 200, false);
        } else if (empty($insertedResult->hasData())) {
            $insertedResult = new ProcessingResult();
            $insertedResult->addInternalError('Insurance Policy record not found after insert');
            return RestControllerHelper::handleProcessingResult($insertedResult, 200);
        }
        $insertedUuid = $insertedResult->getData()[0]['uuid'];

        $processingResult = $this->insuranceService->getOne($insertedUuid);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Swap insurance operation.
     */
    #[OA\Get(
        path: '/api/patient/{puuid}/insurance/$swap-insurance',
        description: "Updates the insurance for the passed in uuid to be a policy of type `type` and updates (if one exists) the current or most recent insurance for the passed in `type` for a patient to be the `type` of the insurance for the given `uuid`. Validations on the swap operation are performed to make sure the effective `date` of the src and target policies being swapped can be received in each given policy `type` as a policy `type` and `date` must together be unique per patient.",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "pid",
                in: "path",
                description: "The uuid for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "type",
                in: "query",
                description: "The type or category of OpenEMR insurance policy, 'primary', 'secondary', or 'tertiary'.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "uuid",
                in: "query",
                description: "The insurance uuid that will be swapped into the list of insurances for the type query parameter",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function operationSwapInsurance(string $puuid, string $type, string $insuranceUuid)
    {
        $processingResult = new ProcessingResult();
        $validationMessages = ['puuid::INVALID_PUUID' => 'Patient uuid invalid'];
        $processingResult->setValidationMessages($validationMessages);
        if (!UuidRegistry::isValidStringUUID($puuid)) {
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        }
        $puuid = UuidRegistry::uuidToBytes($puuid);
        $patientService = new PatientService();
        $pid = $patientService->getPidByUuid($puuid);
        if (empty($pid)) {
            return RestControllerHelper::handleProcessingResult($processingResult, 200);
        }
        $processingResult = $this->insuranceService->swapInsurance($pid, $type, $insuranceUuid);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }
}
