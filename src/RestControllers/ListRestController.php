<?php

/**
 * ListRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Services\ListService;
use OpenEMR\RestControllers\RestControllerHelper;

#[OA\Schema(
    schema: "api_dental_issue_request",
    properties: [
        new OA\Property(property: "title", description: "The title of dental issue.", type: "string"),
        new OA\Property(property: "begdate", description: "The start date of dental issue.", type: "string"),
        new OA\Property(property: "enddate", description: "The end date of dental issue.", type: "string"),
        new OA\Property(property: "diagnosis", description: "The diagnosis of dental issue.", type: "string"),
    ]
)]
#[OA\Schema(
    schema: "api_medication_request",
    properties: [
        new OA\Property(property: "title", description: "The title of medication.", type: "string"),
        new OA\Property(property: "begdate", description: "The start date of medication.", type: "string"),
        new OA\Property(property: "enddate", description: "The end date of medication.", type: "string"),
        new OA\Property(property: "diagnosis", description: "The diagnosis of medication.", type: "string"),
    ]
)]
#[OA\Schema(
    schema: "api_surgery_request",
    properties: [
        new OA\Property(property: "title", description: "The title of surgery.", type: "string"),
        new OA\Property(property: "begdate", description: "The start date of surgery.", type: "string"),
        new OA\Property(property: "enddate", description: "The end date of surgery.", type: "string"),
        new OA\Property(property: "diagnosis", description: "The diagnosis of surgery.", type: "string"),
        new OA\Property(property: "comments", description: "Comments about the surgery.", type: "string"),
    ]
)]
class ListRestController
{
    private $listService;

    public function __construct()
    {
        $this->listService = new ListService();
    }

    // Dental Issue endpoints
    #[OA\Get(
        path: "/api/patient/{pid}/dental_issue",
        description: "Retrieves all dental issues for a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The pid for the patient.", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    // Medication endpoints
    #[OA\Get(
        path: "/api/patient/{pid}/medication",
        description: "Retrieves all medications for a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The pid for the patient.", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    // Surgery endpoints
    #[OA\Get(
        path: "/api/patient/{pid}/surgery",
        description: "Retrieves all surgeries for a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The pid for the patient.", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getAll($pid, $list_type)
    {
        $serviceResult = $this->listService->getAll($pid, $list_type);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Get(
        path: "/api/patient/{pid}/dental_issue/{did}",
        description: "Retrieves a dental issue for a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The id for the patient.", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "did", in: "path", description: "The id for the dental issue.", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    #[OA\Get(
        path: "/api/patient/{pid}/medication/{mid}",
        description: "Retrieves a medication for a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The id for the patient.", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "mid", in: "path", description: "The id for the medication.", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    #[OA\Get(
        path: "/api/patient/{pid}/surgery/{sid}",
        description: "Retrieves a surgery for a patient",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The id for the patient.", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "sid", in: "path", description: "The id for the surgery.", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getOne($pid, $list_type, $list_id)
    {
        $serviceResult = $this->listService->getOne($pid, $list_type, $list_id);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Get(
        path: "/api/list/{list_name}",
        description: "Retrieves a list",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "list_name",
                in: "path",
                description: "The list_id of the list.",
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
    public function getOptions($list_name)
    {
        $serviceResult = $this->listService->getOptionsByListName($list_name);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    #[OA\Post(
        path: "/api/patient/{pid}/dental_issue",
        description: "Submits a new dental issue",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The pid for the patient.", required: true, schema: new OA\Schema(type: "string")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: "application/json", schema: new OA\Schema(ref: "#/components/schemas/api_dental_issue_request"))
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    #[OA\Post(
        path: "/api/patient/{pid}/medication",
        description: "Submits a new medication",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The pid for the patient.", required: true, schema: new OA\Schema(type: "string")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: "application/json", schema: new OA\Schema(ref: "#/components/schemas/api_medication_request"))
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    #[OA\Post(
        path: "/api/patient/{pid}/surgery",
        description: "Submits a new surgery",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The pid for the patient.", required: true, schema: new OA\Schema(type: "string")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: "application/json", schema: new OA\Schema(ref: "#/components/schemas/api_surgery_request"))
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function post($pid, $list_type, $data)
    {
        $data['type'] = $list_type;
        $data['pid'] = $pid;

        $validationResult = $this->listService->validate($data);
        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

        $serviceResult = $this->listService->insert($data);
        return RestControllerHelper::responseHandler($serviceResult, ['id' => $serviceResult], 201);
    }

    #[OA\Put(
        path: "/api/patient/{pid}/dental_issue/{did}",
        description: "Edit a dental issue",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The pid for the patient.", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "did", in: "path", description: "The id for the dental issue.", required: true, schema: new OA\Schema(type: "string")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: "application/json", schema: new OA\Schema(ref: "#/components/schemas/api_dental_issue_request"))
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    #[OA\Put(
        path: "/api/patient/{pid}/medication/{mid}",
        description: "Edit a medication",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The pid for the patient.", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "mid", in: "path", description: "The id for the medication.", required: true, schema: new OA\Schema(type: "string")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: "application/json", schema: new OA\Schema(ref: "#/components/schemas/api_medication_request"))
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    #[OA\Put(
        path: "/api/patient/{pid}/surgery/{sid}",
        description: "Edit a surgery",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The pid for the patient.", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "sid", in: "path", description: "The id for the surgery.", required: true, schema: new OA\Schema(type: "string")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: "application/json", schema: new OA\Schema(ref: "#/components/schemas/api_surgery_request"))
        ),
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function put($pid, $list_id, $list_type, $data)
    {
        $data['type'] = $list_type;
        $data['pid'] = $pid;
        $data['id'] = $list_id;

        $validationResult = $this->listService->validate($data);
        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }


        $serviceResult = $this->listService->update($data);
        return RestControllerHelper::responseHandler($serviceResult, ['id' => $list_id], 200);
    }

    #[OA\Delete(
        path: "/api/patient/{pid}/dental_issue/{did}",
        description: "Delete a dental issue",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The id for the patient.", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "did", in: "path", description: "The id for the dental issue.", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    #[OA\Delete(
        path: "/api/patient/{pid}/medication/{mid}",
        description: "Delete a medication",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The id for the patient.", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "mid", in: "path", description: "The id for the medication.", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    #[OA\Delete(
        path: "/api/patient/{pid}/surgery/{sid}",
        description: "Delete a surgery",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(name: "pid", in: "path", description: "The id for the patient.", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "sid", in: "path", description: "The id for the surgery.", required: true, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function delete($pid, $list_id, $list_type)
    {
        $serviceResult = $this->listService->delete($pid, $list_id, $list_type);
        return RestControllerHelper::responseHandler($serviceResult, true, 200);
    }
}
