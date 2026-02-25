<?php

/**
 * ProcedureRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Services\ProcedureService;
use OpenEMR\RestControllers\RestControllerHelper;

class ProcedureRestController
{
    private $procedureService;

    public function __construct()
    {
        $this->procedureService = new ProcedureService();
    }

    /**
     * Fetches a single procedure resource by id.
     * @param $uuid- The procedure uuid identifier in string format.
     */
    #[OA\Get(
        path: "/api/procedure/{uuid}",
        description: "Retrieves a procedure",
        tags: ["standard"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                description: "The uuid for the procedure.",
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
        $processingResult = $this->procedureService->getOne($uuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns procedure resources which match an optional search criteria.
     */
    #[OA\Get(
        path: "/api/procedure",
        description: "Retrieves a list of all procedures",
        tags: ["standard"],
        responses: [
            new OA\Response(response: "200", ref: "#/components/responses/standard"),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getAll($search = [])
    {
        $processingResult = $this->procedureService->getAll($search);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
