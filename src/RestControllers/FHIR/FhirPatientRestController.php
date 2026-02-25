<?php

/**
 * FhirPatientRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Services\FHIR\Serialization\FhirPatientSerializer;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Validators\ProcessingResult;
use Symfony\Component\HttpFoundation\Response;

/**
 * Supports REST interactions with the FHIR patient resource
 */
class FhirPatientRestController
{
    use SystemLoggerAwareTrait;

    private ?FhirPatientService $fhirPatientService = null;
    private $fhirService;
    private $fhirValidate;

    private ?OEGlobalsBag $oeGlobalsBag = null;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirValidate = new FhirValidationService();
    }
    public function getOEGlobals(): OEGlobalsBag
    {
        if (!isset($this->oeGlobalsBag)) {
            $this->oeGlobalsBag = new OEGlobalsBag();
        }
        return $this->oeGlobalsBag;
    }

    public function setOEGlobals(OEGlobalsBag $oeGlobals): void
    {
        $this->oeGlobalsBag = $oeGlobals;
    }

    public function getFhirPatientService(): FhirPatientService
    {
        if (!isset($this->fhirPatientService)) {
            $this->fhirPatientService = new FhirPatientService();
            $this->fhirPatientService->setGlobalsBag($this->getOEGlobals());
            if (isset($this->systemLogger)) {
                $this->fhirPatientService->setSystemLogger($this->systemLogger);
            }
        }
        return $this->fhirPatientService;
    }

    public function setFhirPatientService(FhirPatientService $fhirPatientService): void
    {
        $this->fhirPatientService = $fhirPatientService;
    }

    public function setSystemLogger(SystemLogger $systemLogger): void
    {
        $this->getFhirPatientService()->setSystemLogger($systemLogger);
        $this->systemLogger = $systemLogger;
    }

    /**
     * Creates a new FHIR patient resource
     * @param $fhirJson array The FHIR patient resource
     * @returns 201 if the resource is created, 400 if the resource is invalid
     */
    #[OA\Post(
        path: "/fhir/Patient",
        description: "Adds a Patient resource.",
        tags: ["fhir"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    description: "The json object for the Patient resource.",
                    type: "object"
                )
            )
        ),
        responses: [
            new OA\Response(
                response: "200",
                description: "Standard Response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: "json object",
                                description: "FHIR Json object.",
                                type: "object"
                            ),
                        ]
                    )
                )
            ),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function post($fhirJson)
    {
        $fhirValidate = $this->fhirValidate->validate($fhirJson);
        if (!empty($fhirValidate)) {
            return RestControllerHelper::responseHandler($fhirValidate, null, 400);
        }

        $object = FhirPatientSerializer::deserialize($fhirJson);

        $processingResult = $this->getFhirPatientService()->insert($object);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 201);
    }

    /**
     * Updates an existing FHIR patient resource
     * @param string $fhirId The FHIR patient resource id (uuid)
     * @param array $fhirJson The updated FHIR patient resource (complete resource)
     * @returns 200 if the resource is created, 400 if the resource is invalid
     */
    #[OA\Put(
        path: "/fhir/Patient/{uuid}",
        description: "Modifies a Patient resource.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                description: "The uuid for the Patient resource.",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    description: "The json object for the Patient resource.",
                    type: "object"
                )
            )
        ),
        responses: [
            new OA\Response(
                response: "201",
                description: "Standard Response",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        example: [
                            "id" => 2,
                            "uuid" => "95f2ad04-5834-4243-8838-e396a7faadbf",
                        ]
                    )
                )
            ),
            new OA\Response(response: "400", ref: "#/components/responses/badrequest"),
            new OA\Response(response: "401", ref: "#/components/responses/unauthorized"),
        ],
        security: [["openemr_auth" => []]]
    )]
    public function put(string $fhirId, array $fhirJson)
    {
        $fhirValidate = $this->fhirValidate->validate($fhirJson);
        if (!empty($fhirValidate)) {
            return RestControllerHelper::responseHandler($fhirValidate, null, 400);
        }
        $object = FhirPatientSerializer::deserialize($fhirJson);

        $processingResult = $this->getFhirPatientService()->update($fhirId, $object);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for a single FHIR patient resource by FHIR id
     * @param string $fhirId The FHIR patient resource id (uuid)
     * @returns Response 200 if the operation completes successfully
     */
    #[OA\Get(
        path: "/fhir/Patient/{uuid}",
        description: "Returns a single Patient resource.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                description: "The uuid for the Patient resource.",
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
                            new OA\Property(
                                property: "json object",
                                description: "FHIR Json object.",
                                type: "object"
                            ),
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
    public function getOne(string $fhirId): Response
    {
        $processingResult = $this->getFhirPatientService()->getOne($fhirId);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR patient resources using various search parameters.
     * Search parameters include:
     * - address (street, postal code, city, or state)
     * - address-city
     * - address-postalcode
     * - address-state
     * - birthdate
     * - email
     * - family
     * - gender
     * - given (first name or middle name)
     * - name (title, first name, middle name, last name)
     * - phone (home, business, cell)
     * - telecom (email, phone)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return Response FHIR bundle with query results, if found
     */
    #[OA\Get(
        path: "/fhir/Patient",
        description: "Returns a list of Patient resources.",
        tags: ["fhir"],
        parameters: [
            new OA\Parameter(
                name: "_id",
                in: "query",
                description: "The uuid for the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "_lastUpdated",
                in: "query",
                description: "Allows filtering resources by the _lastUpdated field. A FHIR Instant value in the format YYYY-MM-DDThh:mm:ss.sss+zz:zz.  See FHIR date/time modifiers for filtering options (ge,gt,le, etc)",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "identifier",
                in: "query",
                description: "The identifier of the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "name",
                in: "query",
                description: "The name of the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "birthdate",
                in: "query",
                description: "The birthdate of the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "gender",
                in: "query",
                description: "The gender of the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "address",
                in: "query",
                description: "The address of the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "address-city",
                in: "query",
                description: "The address-city of the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "address-postalcode",
                in: "query",
                description: "The address-postalcode of the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "address-state",
                in: "query",
                description: "The address-state of the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "email",
                in: "query",
                description: "The email of the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "family",
                in: "query",
                description: "The family name of the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "given",
                in: "query",
                description: "The given name of the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "phone",
                in: "query",
                description: "The phone number of the Patient resource.",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "telecom",
                in: "query",
                description: "The fax number of the Patient resource.",
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
                            new OA\Property(
                                property: "json object",
                                description: "FHIR Json object.",
                                type: "object"
                            ),
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
        $processingResult = $this->getFhirPatientService()->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Patient', $bundleEntries, false);
        return RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
    }
}
