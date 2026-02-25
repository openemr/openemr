<?php

/**
 * EncounterRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\EncounterService;
use OpenEMR\RestControllers\RestControllerHelper;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[OA\Schema(
    schema: "api_encounter_request",
    properties: [
        new OA\Property(property: "date", description: "The date of encounter.", type: "string"),
        new OA\Property(property: "onset_date", description: "The onset date of encounter.", type: "string"),
        new OA\Property(property: "reason", description: "The reason of encounter.", type: "string"),
        new OA\Property(property: "facility", description: "The facility of encounter.", type: "string"),
        new OA\Property(property: "pc_catid", description: "The pc_catid of encounter.", type: "string"),
        new OA\Property(property: "facility_id", description: "The facility id of encounter.", type: "string"),
        new OA\Property(property: "billing_facility", description: "The billing facility of encounter.", type: "string"),
        new OA\Property(property: "sensitivity", description: "The sensitivity of encounter.", type: "string"),
        new OA\Property(property: "referral_source", description: "The referral source of encounter.", type: "string"),
        new OA\Property(property: "pos_code", description: "The pos_code of encounter.", type: "string"),
        new OA\Property(property: "external_id", description: "The external id of encounter.", type: "string"),
        new OA\Property(property: "provider_id", description: "The provider id of encounter.", type: "string"),
        new OA\Property(property: "class_code", description: "The class code of encounter.", type: "string"),
    ]
)]
#[OA\Schema(
    schema: "api_encounter_response",
    properties: [
        new OA\Property(property: "validationErrors", description: "Validation errors.", type: "array", items: new OA\Items(type: "object")),
        new OA\Property(property: "internalErrors", description: "Internal errors.", type: "array", items: new OA\Items(type: "object")),
        new OA\Property(property: "data", description: "Returned data.", type: "array", items: new OA\Items(type: "object")),
    ]
)]
#[OA\Schema(
    schema: "api_vital_request",
    properties: [
        new OA\Property(property: "bps", description: "Blood pressure systolic.", type: "string"),
        new OA\Property(property: "bpd", description: "Blood pressure diastolic.", type: "string"),
        new OA\Property(property: "weight", description: "Patient weight.", type: "string"),
        new OA\Property(property: "height", description: "Patient height.", type: "string"),
        new OA\Property(property: "temperature", description: "Patient temperature.", type: "string"),
        new OA\Property(property: "pulse", description: "Patient pulse.", type: "string"),
        new OA\Property(property: "respiration", description: "Patient respiration.", type: "string"),
        new OA\Property(property: "BMI", description: "Patient BMI.", type: "string"),
        new OA\Property(property: "waist_circ", description: "Patient waist circumference.", type: "string"),
        new OA\Property(property: "head_circ", description: "Patient head circumference.", type: "string"),
        new OA\Property(property: "oxygen_saturation", description: "Patient oxygen saturation.", type: "string"),
    ]
)]
#[OA\Schema(
    schema: "api_soap_note_request",
    properties: [
        new OA\Property(property: "subjective", description: "The subjective of soap note.", type: "string"),
        new OA\Property(property: "objective", description: "The objective of soap note.", type: "string"),
        new OA\Property(property: "assessment", description: "The assessment of soap note.", type: "string"),
        new OA\Property(property: "plan", description: "The plan of soap note.", type: "string"),
    ]
)]
class EncounterRestController
{
    private $encounterService;

    /**
     * White list of patient search fields
     */
    private const SUPPORTED_SEARCH_FIELDS = [
        "pid",
        "provider_id"
    ];

    public function __construct(private readonly SessionInterface $session)
    {
        $this->encounterService = new EncounterService();
    }

    /**
     * Process a HTTP POST request used to create a encounter record.
     * @param $puuid - The patient identifier used to lookup the existing record.
     * @param $data - array of encounter fields.
     * @return a 201/Created status code and the encounter identifier if successful.
     */
    #[OA\Post(
        path: "/api/patient/{puuid}/encounter",
        description: "Creates a new encounter",
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
                schema: new OA\Schema(ref: "#/components/schemas/api_encounter_request")
            )
        ),
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: "validationErrors",
                                description: "Validation errors.",
                                type: "array",
                                items: new OA\Items(type: "object")
                            ),
                            new OA\Property(
                                property: "internalErrors",
                                description: "Internal errors.",
                                type: "array",
                                items: new OA\Items(type: "object")
                            ),
                            new OA\Property(
                                property: "data",
                                description: "Returned data.",
                                type: "array",
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(
                                            property: "encounter",
                                            description: "encounter id",
                                            type: "integer"
                                        ),
                                        new OA\Property(
                                            property: "uuid",
                                            description: "encounter uuid",
                                            type: "string"
                                        ),
                                    ]
                                )
                            ),
                        ],
                        example: [
                            "validationErrors" => [],
                            "error_description" => [],
                            "data" => [
                                "encounter" => 1,
                                "uuid" => "90c196f2-51cc-4655-8858-3a80aebff3ef",
                            ],
                        ]
                    )
                )
            ),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function post($puuid, $data, HttpRestRequest $request)
    {
        $session = $request->getSession();
        $data['user'] = $session->get('authUser');
        $data['group'] = $session->get('authProvider');
        $processingResult = $this->encounterService->insertEncounter($puuid, $data);
        return RestControllerHelper::handleProcessingResult($processingResult, 201);
    }

    /**
     * Processes a HTTP PUT request used to update an existing encounter record.
     * @param $puuid - The patient identifier used to lookup the existing record.
     * @param $euuid - The encounter identifier used to lookup the existing record.
     * @param $data - array of encounter fields (full resource).
     * @return a 200/Ok status code and the encounter resource.
     */
    #[OA\Put(
        path: "/api/patient/{puuid}/encounter/{euuid}",
        description: "Modify a encounter",
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
                name: "euuid",
                in: "path",
                description: "The uuid for the encounter.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(ref: "#/components/schemas/api_encounter_request")
            )
        ),
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(ref: "#/components/schemas/api_encounter_response")
                )
            ),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function put($puuid, $euuid, $data)
    {
        $processingResult = $this->encounterService->updateEncounter($puuid, $euuid, $data);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Fetches a single encounter resource by pid and eid.
     * @param $puuid The patient identifier used to lookup the existing record.
     * @param $euuid The encounter identifier to fetch.
     * @return a 200/Ok status code and the encounter resource.
     */
    #[OA\Get(
        path: "/api/patient/{puuid}/encounter/{euuid}",
        description: "Retrieves a single encounter for a patient",
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
                name: "euuid",
                in: "path",
                description: "The uuid for the encounter.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(ref: "#/components/schemas/api_encounter_response")
                )
            ),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    #[OA\Get(
        path: "/portal/patient/encounter/{euuid}",
        description: "Returns a selected encounter by its uuid.",
        tags: ["standard-patient"],
        parameters: [
            new OA\Parameter(
                name: "euuid",
                in: "path",
                description: "The uuid for the encounter.",
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
    public function getOne($puuid, $euuid)
    {
        $processingResult = $this->encounterService->getEncounter($euuid, $puuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns all encounter resources which match (pid) patient identifier.
     * @param $puuid The patient identifier used to lookup the existing record.
     * @return a 200/Ok status code and the encounter resource.
     */
    #[OA\Get(
        path: "/api/patient/{puuid}/encounter",
        description: "Retrieves a list of encounters for a single patient",
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
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    #[OA\Get(
        path: "/portal/patient/encounter",
        description: "Returns encounters for the patient.",
        tags: ["standard-patient"],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getAll($puuid)
    {
        $processingResult = $this->encounterService->search([], true, $puuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    #[OA\Post(
        path: "/api/patient/{pid}/encounter/{eid}/vital",
        description: "Submits a new vitals form",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "pid",
                in: "path",
                description: "The id for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "eid",
                in: "path",
                description: "The id for the encounter.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(ref: "#/components/schemas/api_vital_request")
            )
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function postVital($pid, $eid, $data)
    {
        $validationResult = $this->encounterService->validateVital($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->encounterService->insertVital($pid, $eid, $data);
        return RestControllerHelper::responseHandler(
            $serviceResult,
            [
                'vid' => $serviceResult[0],
                'fid' => $serviceResult[1]
            ],
            201
        );
    }

    #[OA\Put(
        path: "/api/patient/{pid}/encounter/{eid}/vital/{vid}",
        description: "Edit a vitals form",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "pid",
                in: "path",
                description: "The id for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "eid",
                in: "path",
                description: "The id for the encounter.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "vid",
                in: "path",
                description: "The id for the vitalss form.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(ref: "#/components/schemas/api_vital_request")
            )
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function putVital($pid, $eid, $vid, $data)
    {
        $validationResult = $this->encounterService->validateVital($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->encounterService->updateVital($pid, $eid, $vid, $data);
        return RestControllerHelper::responseHandler($serviceResult, ['vid' => $vid], 200);
    }

    #[OA\Get(
        path: "/api/patient/{pid}/encounter/{eid}/vital",
        description: "Retrieves all vitals from an encounter for a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "pid",
                in: "path",
                description: "The pid for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "eid",
                in: "path",
                description: "The id for the encounter.",
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
    public function getVitals($pid, $eid)
    {
        $serviceResult = $this->encounterService->getVitals($pid, $eid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Get(
        path: "/api/patient/{pid}/encounter/{eid}/vital/{vid}",
        description: "Retrieves a vitals form from an encounter for a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "pid",
                in: "path",
                description: "The pid for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "eid",
                in: "path",
                description: "The id for the encounter.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "vid",
                in: "path",
                description: "The id for the vitals form.",
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
    public function getVital($pid, $eid, $vid)
    {
        $serviceResult = $this->encounterService->getVital($pid, $eid, $vid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Get(
        path: "/api/patient/{pid}/encounter/{eid}/soap_note",
        description: "Retrieves soap notes from an encounter for a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "pid",
                in: "path",
                description: "The pid for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "eid",
                in: "path",
                description: "The id for the encounter.",
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
    public function getSoapNotes($pid, $eid)
    {
        $serviceResult = $this->encounterService->getSoapNotes($pid, $eid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Get(
        path: "/api/patient/{pid}/encounter/{eid}/soap_note/{sid}",
        description: "Retrieves a soap note from an encounter for a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "pid",
                in: "path",
                description: "The pid for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "eid",
                in: "path",
                description: "The id for the encounter.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "sid",
                in: "path",
                description: "The id for the soap note.",
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
    public function getSoapNote($pid, $eid, $sid)
    {
        $serviceResult = $this->encounterService->getSoapNote($pid, $eid, $sid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Post(
        path: "/api/patient/{pid}/encounter/{eid}/soap_note",
        description: "Submits a new soap note",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "pid",
                in: "path",
                description: "The id for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "eid",
                in: "path",
                description: "The id for the encounter.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(ref: "#/components/schemas/api_soap_note_request")
            )
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function postSoapNote($pid, $eid, $data)
    {
        $validationResult = $this->encounterService->validateSoapNote($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->encounterService->insertSoapNote($pid, $eid, $data);
        return RestControllerHelper::responseHandler(
            $serviceResult,
            [
                'sid' => $serviceResult[0],
                'fid' => $serviceResult[1]
            ],
            201
        );
    }

    #[OA\Put(
        path: "/api/patient/{pid}/encounter/{eid}/soap_note/{sid}",
        description: "Edit a soap note",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "pid",
                in: "path",
                description: "The id for the patient.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "eid",
                in: "path",
                description: "The id for the encounter.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "sid",
                in: "path",
                description: "The id for the soap noted.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(ref: "#/components/schemas/api_soap_note_request")
            )
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function putSoapNote($pid, $eid, $sid, $data)
    {
        $validationResult = $this->encounterService->validateSoapNote($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->encounterService->updateSoapNote($pid, $eid, $sid, $data);
        return RestControllerHelper::responseHandler($serviceResult, ['sid' => $sid], 200);
    }
}
