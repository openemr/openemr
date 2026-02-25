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
    description: "Schema for the insurance request.  Note the following additional validation checks on the request.\nIf the subscriber_relationship value is of type 'self' then the subscriber_fname and subscriber_lname fields\nmust match the patient's first and last name or a patient's previous first and last name.\n\nIf the subscriber_relationship value is of type 'self' then the subscriber_ss field must match the patient's\nsocial security number.\n\nIf the subscriber_relationship value is not of type 'self' then the subscriber_ss field MUST not be the current patient's social security number.\n\nIf the system's global configuration permits only a single insurance type option then any insurance request where the type is NOT 'primary' will fail.\n\nAn insurance is considered the current policy for the policy type if the policy date_end field is null.  Only one of these records per policy type can exist for a patient.",
    required: ["provider", "policy_number", "subscriber_fname", "subscriber_lname", "subscriber_relationship", "subscriber_ss", "subscriber_DOB", "subscriber_street", "subscriber_postal_code", "subscriber_city", "subscriber_state", "subscriber_sex", "accept_assignment"],
    properties: [
        new OA\Property(property: "provider", description: "The insurance company id.", type: "string"),
        new OA\Property(property: "plan_name", description: "The plan name of insurance. (2-255 characters)", type: "string"),
        new OA\Property(property: "policy_number", description: "The policy number of insurance. (2-255 characters)", type: "string"),
        new OA\Property(property: "group_number", description: "The group number of insurance.(2-255 characters)", type: "string"),
        new OA\Property(property: "subscriber_lname", description: "The subscriber last name of insurance.(2-255 characters).", type: "string"),
        new OA\Property(property: "subscriber_mname", description: "The subscriber middle name of insurance.", type: "string"),
        new OA\Property(property: "subscriber_fname", description: "The subscriber first name of insurance.", type: "string"),
        new OA\Property(property: "subscriber_relationship", description: "The subscriber relationship of insurance. `subscriber_relationship` can be found by querying `resource=/api/list/subscriber_relationship`", type: "string"),
        new OA\Property(property: "subscriber_ss", description: "The subscriber ss number of insurance.", type: "string"),
        new OA\Property(property: "subscriber_DOB", description: "The subscriber DOB of insurance.", type: "string"),
        new OA\Property(property: "subscriber_street", description: "The subscriber street address of insurance.", type: "string"),
        new OA\Property(property: "subscriber_postal_code", description: "The subscriber postal code of insurance.", type: "string"),
        new OA\Property(property: "subscriber_city", description: "The subscriber city of insurance.", type: "string"),
        new OA\Property(property: "subscriber_state", description: "The subscriber state of insurance. `state` can be found by querying `resource=/api/list/state`", type: "string"),
        new OA\Property(property: "subscriber_country", description: "The subscriber country of insurance. `country` can be found by querying `resource=/api/list/country`", type: "string"),
        new OA\Property(property: "subscriber_phone", description: "The subscriber phone of insurance.", type: "string"),
        new OA\Property(property: "subscriber_employer", description: "The subscriber employer of insurance.", type: "string"),
        new OA\Property(property: "subscriber_employer_street", description: "The subscriber employer street of insurance.", type: "string"),
        new OA\Property(property: "subscriber_employer_postal_code", description: "The subscriber employer postal code of insurance.", type: "string"),
        new OA\Property(property: "subscriber_employer_state", description: "The subscriber employer state of insurance.", type: "string"),
        new OA\Property(property: "subscriber_employer_country", description: "The subscriber employer country of insurance.", type: "string"),
        new OA\Property(property: "subscriber_employer_city", description: "The subscriber employer city of insurance.", type: "string"),
        new OA\Property(property: "copay", description: "The copay of insurance.", type: "string"),
        new OA\Property(property: "date", description: "The effective date of insurance in YYYY-MM-DD format.  This value cannot be after the date_end property and cannot be the same date as any other insurance policy for the same insurance type ('primary, 'secondary', etc).", type: "string"),
        new OA\Property(property: "date_end", description: "The effective end date of insurance in YYYY-MM-DD format.  This value cannot be before the date property. If it is null then this policy is the current policy for this policy type for the patient.  There can only be one current policy per type and the request will fail if there is already a current policy for this type.", type: "string"),
        new OA\Property(property: "subscriber_sex", description: "The subscriber sex of insurance.", type: "string"),
        new OA\Property(property: "accept_assignment", description: "The accept_assignment of insurance.", type: "string"),
        new OA\Property(property: "policy_type", description: "The 837p list of policy types for an insurance.  See src/Billing/InsurancePolicyType.php for the list of valid values.", type: "string"),
        new OA\Property(property: "type", description: "The type or category of OpenEMR insurance policy, 'primary', 'secondary', or 'tertiary'. If this field is missing it will default to 'primary'.", type: "string"),
    ],
    example: [
        "provider" => "33",
        "plan_name" => "Some Plan",
        "policy_number" => "12345",
        "group_number" => "252412",
        "subscriber_lname" => "Tester",
        "subscriber_mname" => "Xi",
        "subscriber_fname" => "Foo",
        "subscriber_relationship" => "other",
        "subscriber_ss" => "234231234",
        "subscriber_DOB" => "2018-10-03",
        "subscriber_street" => "183 Cool St",
        "subscriber_postal_code" => "23418",
        "subscriber_city" => "Cooltown",
        "subscriber_state" => "AZ",
        "subscriber_country" => "USA",
        "subscriber_phone" => "234-598-2123",
        "subscriber_employer" => "Some Employer",
        "subscriber_employer_street" => "123 Heather Lane",
        "subscriber_employer_postal_code" => "23415",
        "subscriber_employer_state" => "AZ",
        "subscriber_employer_country" => "USA",
        "subscriber_employer_city" => "Cooltown",
        "copay" => "35",
        "date" => "2018-10-15",
        "subscriber_sex" => "Female",
        "accept_assignment" => "TRUE",
        "policy_type" => "a",
        "type" => "primary",
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
