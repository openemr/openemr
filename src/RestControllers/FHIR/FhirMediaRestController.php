<?php

/**
 * FhirLocationRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\FHIR\FhirLocationService;
use OpenEMR\Services\FHIR\FhirMediaService;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use Symfony\Component\HttpFoundation\Response;

class FhirMediaRestController
{
    /**
     * @var FhirMediaService
     */
    private readonly FhirMediaService $fhirMediaService;


    public function __construct(HttpRestRequest $request)
    {
        $this->fhirMediaService = new FhirMediaService();
        $this->fhirMediaService->setSession($request->getSession());
    }

    /**
     * Queries for a single FHIR location resource by FHIR id
     * @param string[] $fhirSearchParameters The FHIR search parameters
     * @param string|null $puuidBind The patient uuid to bind to the search, if applicable
     * @returns Response 200 if the operation completes successfully
     */
    public function getAll(array $fhirSearchParameters, ?string $puuidBind = null): Response
    {
        $processingResult = $this->fhirMediaService->getAll($fhirSearchParameters, $puuidBind);
        $bundleEntries = [];
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '') . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = (new FhirResourcesService())->createBundle('Media', $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }

    /**
     * Queries for a single FHIR location resource by FHIR id
     * @param $fhirId The FHIR location resource id (uuid)
     * @returns Response 200 if the operation completes successfully
     */
    public function getOne($fhirId, $patientUuid): Response
    {
        $processingResult = $this->fhirMediaService->getOne($fhirId, $patientUuid);
        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }
}
