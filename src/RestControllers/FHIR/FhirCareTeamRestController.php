<?php

/**
 * FhirCareTeamRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FHIR\FhirCareTeamService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;

class FhirCareTeamRestController
{
    /**
     * @var FhirCareTeamService
     */
    private $fhirCareTeamService;
    private $fhirService;

    private ?OEGlobalsBag $oeGlobalsBag = null;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
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

    public function getFhirCareTeamService(): FhirCareTeamService
    {
        if (!isset($this->fhirCareTeamService)) {
            $this->fhirCareTeamService = new FhirCareTeamService();
            $globals = $this->getOEGlobals();
            $defaultVersion = $globals->getString(GlobalConnectorsEnum::FHIR_US_CORE_MAX_SUPPORTED_PROFILE_VERSION->value, FhirCareTeamService::PROFILE_VERSION_8_0_0);
            $this->fhirCareTeamService->setHighestCompatibleUSCoreProfileVersion($defaultVersion);
            if (isset($this->systemLogger)) {
                $this->fhirCareTeamService->setSystemLogger($this->systemLogger);
            }
        }
        return $this->fhirCareTeamService;
    }

    /**
     * Queries for a single FHIR location resource by FHIR id
     * @param $fhirId The FHIR location resource id (uuid)
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @returns 200 if the operation completes successfully
     */
    public function getOne($fhirId, $puuidBind = null)
    {
        $processingResult = $this->getFhirCareTeamService()->getOne($fhirId, $puuidBind);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    /**
     * Queries for FHIR location resources using various search parameters.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return FHIR bundle with query results, if found
     */
    public function getAll($searchParams, $puuidBind = null)
    {
        $processingResult = $this->getFhirCareTeamService()->getAll($searchParams, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->fhirService->createBundle('CareTeam', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
