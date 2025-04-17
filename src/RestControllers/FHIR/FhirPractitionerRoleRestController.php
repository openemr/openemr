<?php

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirPractitionerRoleService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

require_once(__DIR__ . '/../../../_rest_config.php');
/**
 * FHIR PractitionerRole Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirPractitionerRoleService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
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
    public function getAll($searchParams)
    {
        $processingResult = $this->fhirPractitionerRoleService->getAll($searchParams);
        $bundleEntries = array();
        foreach ($processingResult->getData() as $index => $searchResult) {
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
    public function getOne($fhirId)
    {
        $processingResult = $this->fhirPractitionerRoleService->getOne($fhirId);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }
}
