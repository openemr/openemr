<?php

/**
 * FhirPatientRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

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
            $globals = $this->getOEGlobals();
            $defaultVersion = $globals->getString(GlobalConnectorsEnum::FHIR_US_CORE_MAX_SUPPORTED_PROFILE_VERSION->value, FhirPatientService::PROFILE_VERSION_8_0_0);
            $this->fhirPatientService->setHighestCompatibleUSCoreProfileVersion($defaultVersion);
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
