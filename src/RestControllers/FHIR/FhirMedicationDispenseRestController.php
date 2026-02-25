<?php

/**
 * FhirMedicationDispenseRestController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirMedicationDispenseService;
use OpenEMR\RestControllers\RestControllerHelper;
use Symfony\Component\HttpFoundation\Response;

/**
 * FHIR MedicationDispense REST Controller
 */
class FhirMedicationDispenseRestController
{
    /**
     * @var FhirMedicationDispenseService
     */
    private readonly FhirMedicationDispenseService $fhirMedicationDispenseService;

    /**
     * @var FhirResourcesService
     */
    private readonly FhirResourcesService $fhirService;

    public function __construct()
    {
        $this->fhirMedicationDispenseService = new FhirMedicationDispenseService();
        $this->fhirService = new FhirResourcesService();
    }

    /**
     * Queries for FHIR fhir MedicationDispenseResource resources using various search parameters.
     * Search parameters include:
     * - patient (puuid)
     * - status (token)
     * - type (token)
     * - _id (token)
     * - _lastUpdated (date)
     *
     * @param array <string, int|string> $searchParams - The search parameters
     * @param string $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return Response FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: "/fhir/MedicationDispense",
        description: "Returns a list of MedicationDispense resources.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "_id",
                in: "query",
                description: "The uuid for the MedicationDispense resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "_lastUpdated",
                in: "query",
                description: "Allows filtering resources by the _lastUpdated field. A FHIR Instant value in the format YYYY-MM-DDThh:mm:ss.sss+zz:zz.  See FHIR date/time modifiers for filtering options (ge,gt,le, etc).",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "patient",
                in: "query",
                description: "The patient the MedicationDispense is for.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "status",
                in: "query",
                description: "The status of the MedicationDispense resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "type",
                in: "query",
                description: "The type of the MedicationDispense resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard Response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: "resourceType", type: "string"),
                            new OA\Property(property: "type", type: "string"),
                            new OA\Property(property: "total", type: "integer"),
                            new OA\Property(property: "link", type: "array", items: new OA\Items(type: "object")),
                            new OA\Property(property: "entry", type: "array", items: new OA\Items(type: "object")),
                        ]
                    )
                )
            ),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getAll(array $searchParams, ?string $puuidBind = null): Response
    {
        $fhirSearchResult = $this->fhirMedicationDispenseService->getAll($searchParams, $puuidBind);
        $bundleEntries = [];

        foreach ($fhirSearchResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' => $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            $bundleEntries[] = $fhirBundleEntry;
        }

        $bundleSearchResult = $this->fhirService->createBundle('MedicationDispense', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }

    /**
     * Queries for a single FHIR MedicationDispense resource by FHIR id
     * @param $fhirId - The FHIR MedicationDispense resource id (uuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return Response FHIR MedicationDispense resource with query results, if found
     */
    #[OA\Get(
        path: "/fhir/MedicationDispense/{uuid}",
        description: "Returns a single MedicationDispense resource.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                description: "The uuid for the MedicationDispense resource.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard Response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: "meta", type: "object"),
                            new OA\Property(property: "resourceType", type: "string"),
                            new OA\Property(property: "status", type: "string"),
                            new OA\Property(property: "medicationCodeableConcept", type: "object"),
                            new OA\Property(property: "subject", type: "object"),
                            new OA\Property(property: "context", type: "object"),
                            new OA\Property(property: "performer", type: "array", items: new OA\Items(type: "object")),
                            new OA\Property(property: "whenHandedOver", type: "string"),
                            new OA\Property(property: "dosageInstruction", type: "array", items: new OA\Items(type: "object")),
                        ]
                    )
                )
            ),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
            new OA\Response(response: "404", ref: "#/components/responses/uuidnotfound"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function getOne(string $fhirId, ?string $puuidBind = null): Response
    {
        $fhirSearchResult = $this->fhirMedicationDispenseService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($fhirSearchResult, 200);
    }
     // the below methods were generated by AI, we will comment them out for now as they are not needed
    // they can be added in later
//
//    /**
//     * Creates a FHIR MedicationDispense resource
//     * @param $data - FHIR MedicationDispense resource data
//     * @return FHIR MedicationDispense resource creation result
//     */
//    public function post($data)
//    {
//        // MedicationDispense creation is not supported as these represent historical dispense events
//        // that are created through other workflows (prescription dispensing, immunization administration)
//        $operationOutcome = $this->fhirService->createOpOutcomeResource(
//            'error',
//            'processing',
//            'MedicationDispense creation not supported. Dispense records are created through dispensing workflows.',
//            [],
//            'OperationOutcome.issue'
//        );
//
//        return RestControllerHelper::responseHandler($operationOutcome, null, 405); // Method Not Allowed
//    }
//
//    /**
//     * Updates a FHIR MedicationDispense resource
//     * @param $fhirId - The FHIR MedicationDispense resource id (uuid)
//     * @param $data - Updated FHIR MedicationDispense resource data
//     * @return FHIR MedicationDispense resource update result
//     */
//    public function put($fhirId, $data)
//    {
//        // MedicationDispense updates are not supported as these represent historical dispense events
//        // that should not be modified after creation
//        $operationOutcome = $this->fhirService->createOpOutcomeResource(
//            'error',
//            'processing',
//            'MedicationDispense updates not supported. Dispense records represent historical events that cannot be modified.',
//            [],
//            'OperationOutcome.issue'
//        );
//
//        return RestControllerHelper::responseHandler($operationOutcome, null, 405); // Method Not Allowed
//    }
//
//    /**
//     * Deletes a FHIR MedicationDispense resource
//     * @param $fhirId - The FHIR MedicationDispense resource id (uuid)
//     * @return FHIR MedicationDispense resource deletion result
//     */
//    public function delete($fhirId)
//    {
//        // MedicationDispense deletion is not supported as these represent historical dispense events
//        // If a dispense was entered in error, it should be marked with status 'entered-in-error'
//        $operationOutcome = $this->fhirService->createOpOutcomeResource(
//            'error',
//            'processing',
//            'MedicationDispense deletion not supported. Use status "entered-in-error" to indicate erroneous dispenses.',
//            [],
//            'OperationOutcome.issue'
//        );
//
//        return RestControllerHelper::responseHandler($operationOutcome, null, 405); // Method Not Allowed
//    }
}
