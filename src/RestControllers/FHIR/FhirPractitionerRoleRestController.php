<?php

/**
 * FHIR PractitionerRole Service
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Services\FHIR\FhirPractitionerRoleService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirPractitionerRoleRestController
{
    private $fhirPractitionerRoleService;
    private $fhirService;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirPractitionerRoleService = new FhirPractitionerRoleService();
    }

    /**
     * Queries for FHIR practitionerRole resources using various search parameters.
     * Search parameters include:
     * - specialty
     * - practitioner
     * @return FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: "/fhir/PractitionerRole",
        description: "Returns a list of PractitionerRole resources.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "_id",
                in: "query",
                description: "The uuid for the PractitionerRole resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "specialty",
                in: "query",
                description: "The specialty of the PractitionerRole resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "practitioner",
                in: "query",
                description: "The practitioner of the PractitionerRole resource.",
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
    public function getAll($searchParams)
    {
        $processingResult = $this->fhirPractitionerRoleService->getAll($searchParams);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('PractitionerRole', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }


    /**
     * Queries for a single FHIR practitionerRole resource by FHIR id
     * @param $fhirId The FHIR practitionerRole resource id (uuid)
     * @returns 200 if the operation completes successfully
     */
    #[OA\Get(
        path: "/fhir/PractitionerRole/{uuid}",
        description: "Returns a single PractitionerRole resource.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                description: "The uuid for the PractitionerRole resource.",
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
    public function getOne($fhirId)
    {
        $processingResult = $this->fhirPractitionerRoleService->getOne($fhirId);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }
}
