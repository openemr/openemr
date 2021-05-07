<?php

/**
 * FhirImmunizationRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirImmunizationService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

require_once(__DIR__ . '/../../../_rest_config.php');

/**
 * Supports REST interactions with the FHIR immunization resource
 */
class FhirImmunizationRestController
{
    private $fhirImmunizationService;
    private $fhirService;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirImmunizationService = new FhirImmunizationService();
        $this->fhirValidate = new FhirValidationService();
    }

    /**
     * Queries for a single FHIR immunization resource by FHIR id
     * @param $fhirId The FHIR immunization resource id (uuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns 200 if the operation completes successfully
     */
    public function getOne($fhirId, $puuidBind = null)
    {
        $processingResult = $this->fhirImmunizationService->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR immunization resources using various search parameters.
     * Search parameters include:
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return FHIR bundle with query results, if found
     */
    public function getAll($searchParams, $puuidBind = null)
    {
        $processingResult = $this->fhirImmunizationService->getAll($searchParams, $puuidBind);
        $bundleEntries = array();
        foreach ($processingResult->getData() as $index => $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('Immunization', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
