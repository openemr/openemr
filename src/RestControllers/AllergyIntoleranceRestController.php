<?php

/**
 * AllergyIntoleranceRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Services\AllergyIntoleranceService;
use OpenEMR\RestControllers\RestControllerHelper;

#[OA\Schema(
    schema: "api_allergy_request",
    description: "Schema for the allergy request",
    required: ["title", "begdate"],
    properties: [
        new OA\Property(property: "title", description: "The title of allergy.", type: "string"),
        new OA\Property(property: "begdate", description: "The beginning date of allergy.", type: "string"),
        new OA\Property(property: "enddate", description: "The end date of allergy.", type: "string"),
        new OA\Property(property: "diagnosis", description: "The diagnosis of allergy. In format `<codetype>:<code>`", type: "string"),
    ],
    example: ["title" => "Iodine", "begdate" => "2010-10-13", "enddate" => null]
)]
class AllergyIntoleranceRestController
{
    private $allergyIntoleranceService;

    /**
     * White list of search/insert fields
     */
    private const WHITELISTED_FIELDS = [
        'title',
        'begdate',
        'enddate',
        'diagnosis',
        'comments'
    ];

    public function __construct()
    {
        $this->allergyIntoleranceService = new AllergyIntoleranceService();
    }

    /**
     * Fetches a single allergyIntolerance resource by id.
     * @param $uuid - The allergyIntolerance uuid identifier in string format.
     */
    #[OA\Get(
        path: "/api/allergy/{auuid}",
        description: "Retrieves a single allergy by their uuid",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "auuid",
                in: "path",
                description: "The uuid for the allergy.",
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
        $processingResult = $this->allergyIntoleranceService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    #[OA\Get(
        path: "/api/allergy",
        description: "Retrieves a list of allergies",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "lists.pid",
                in: "query",
                description: "The uuid for the patient.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "lists.id",
                in: "query",
                description: "The uuid for the allergy.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "title",
                in: "query",
                description: "The title for the allergy.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "begdate",
                in: "query",
                description: "The start date for the allergy.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "enddate",
                in: "query",
                description: "The end date for the allergy.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "diagnosis",
                in: "query",
                description: "The diagnosis for the allergy.",
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
        path: "/api/patient/{puuid}/allergy",
        description: "Retrieves all allergies for a patient",
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
        path: "/api/patient/{puuid}/allergy/{auuid}",
        description: "Retrieves a allergy for a patient",
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
                name: "auuid",
                in: "path",
                description: "The uuid for the allergy.",
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
        $processingResult = $this->allergyIntoleranceService->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    #[OA\Post(
        path: "/api/patient/{puuid}/allergy",
        description: "Submits a new allergy",
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
                schema: new OA\Schema(ref: "#/components/schemas/api_allergy_request")
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
        $filteredData = $this->allergyIntoleranceService->filterData($data, self::WHITELISTED_FIELDS);
        $filteredData['puuid'] = $puuid;
        $processingResult = $this->allergyIntoleranceService->insert($filteredData);
        return RestControllerHelper::handleProcessingResult($processingResult, 201);
    }

    #[OA\Put(
        path: "/api/patient/{puuid}/allergy/{auuid}",
        description: "Edit a allergy",
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
                name: "auuid",
                in: "path",
                description: "The uuid for the allergy.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(ref: "#/components/schemas/api_allergy_request")
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
        $filteredData = $this->allergyIntoleranceService->filterData($data, self::WHITELISTED_FIELDS);
        $processingResult = $this->allergyIntoleranceService->update($uuid, $filteredData);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    #[OA\Delete(
        path: "/api/patient/{puuid}/allergy/{auuid}",
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
                name: "auuid",
                in: "path",
                description: "The uuid for the allergy.",
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
        $processingResult = $this->allergyIntoleranceService->delete($puuid, $uuid);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }
}
