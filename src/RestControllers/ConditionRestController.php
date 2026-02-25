<?php

/**
 * ConditionRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Services\ConditionService;
use OpenEMR\RestControllers\RestControllerHelper;

#[OA\Schema(
    schema: "api_medical_problem_request",
    properties: [
        new OA\Property(property: "title", description: "The title of the medical problem.", type: "string"),
        new OA\Property(property: "begdate", description: "The begin date of medical problem.", type: "string"),
        new OA\Property(property: "enddate", description: "The end date of medical problem.", type: "string"),
        new OA\Property(property: "diagnosis", description: "The diagnosis of medical problem.", type: "string"),
    ]
)]
class ConditionRestController
{
    private $conditionService;

    /**
     * White list of search/insert fields
     */
    private const WHITELISTED_FIELDS = [
        'title',
        'begdate',
        'enddate',
        'diagnosis'
    ];

    public function __construct()
    {
        $this->conditionService = new ConditionService();
    }

    /**
     * Fetches a single condition resource by id.
     * @param $uuid - The condition uuid identifier in string format.
     */
    #[OA\Get(
        path: "/api/medical_problem/{muuid}",
        description: "Retrieves a single medical problem by their uuid",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "muuid",
                in: "path",
                description: "The uuid for the medical problem.",
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
    public function getOne($uuid)
    {
        $processingResult = $this->conditionService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns condition resources which match an optional search criteria.
     */
    #[OA\Get(
        path: "/api/medical_problem",
        description: "Retrieves a list of medical problems",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "puuid",
                in: "query",
                description: "The uuid for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "condition_uuid",
                in: "query",
                description: "The uuid for the medical problem.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "title",
                in: "query",
                description: "The title for the medical problem.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "begdate",
                in: "query",
                description: "The start date for the medical problem.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "enddate",
                in: "query",
                description: "The end date for the medical problem.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "diagnosis",
                in: "query",
                description: "The diagnosis for the medical problem.",
                required: false,
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
        path: "/api/patient/{puuid}/medical_problem",
        description: "Retrieves all medical problems for a patient",
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
        path: "/api/patient/{puuid}/medical_problem/{muuid}",
        description: "Retrieves a medical problem for a patient",
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
                name: "muuid",
                in: "path",
                description: "The uuid for the medical problem.",
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
    public function getAll($search = [])
    {
        $processingResult = $this->conditionService->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    #[OA\Post(
        path: "/api/patient/{puuid}/medical_problem",
        description: "Submits a new medical problem",
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
                schema: new OA\Schema(
                    ref: "#/components/schemas/api_medical_problem_request"
                )
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
        $filteredData = $this->conditionService->filterData($data, self::WHITELISTED_FIELDS);
        $filteredData['puuid'] = $puuid;
        $processingResult = $this->conditionService->insert($filteredData);
        return RestControllerHelper::handleProcessingResult($processingResult, 201);
    }

    #[OA\Put(
        path: "/api/patient/{puuid}/medical_problem/{muuid}",
        description: "Edit a medical problem",
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
                name: "muuid",
                in: "path",
                description: "The uuid for the medical problem.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    ref: "#/components/schemas/api_medical_problem_request"
                )
            )
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function put($puuid, $uuid, $data)
    {
        $filteredData = $this->conditionService->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->conditionService->update($uuid, $filteredData);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    #[OA\Delete(
        path: "/api/patient/{puuid}/medical_problem/{muuid}",
        description: "Delete a medical problem",
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
                name: "muuid",
                in: "path",
                description: "The uuid for the medical problem.",
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
    public function delete($puuid, $uuid)
    {
        $processingResult = $this->conditionService->delete($puuid, $uuid);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }
}
