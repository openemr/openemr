<?php

/**
 * EmployerRestController handles the API rest requests to the employer data for a patient
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenApi\Attributes as OA;
use OpenEMR\Services\EmployerService;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;

class EmployerRestController
{
    private $employerService;

    public function __construct()
    {
        $this->employerService = new EmployerService();
    }

    /**
     * Retrieves all employer data for a patient.
     * @param array $searchParams - Search parameters including puuid.
     */
    #[OA\Get(
        path: "/api/patient/{puuid}/employer",
        description: "Retrieves all the employer data for a patient. Returns an array of the employer data for the patient.",
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
        security: [["openemr_auth" => ["user/employer.read", "patient/employer.read"]]]
    )]
    public function getAll($searchParams)
    {
        if (isset($searchParams['id'])) {
            $searchParams['id'] = new TokenSearchField('id', new TokenSearchValue($searchParams['id']), false);
        }
        if (isset($searchParams['puuid'])) {
            $searchParams['puuid'] = new TokenSearchField('puuid', $searchParams['puuid'], true);
        }
        if (isset($searchParams['pid'])) {
            $searchParams['pid'] = new TokenSearchField('pid', $searchParams['pid'], true);
        }
        $serviceResult = $this->employerService->search($searchParams);
        return RestControllerHelper::handleProcessingResult($serviceResult, null, 200);
    }
}
