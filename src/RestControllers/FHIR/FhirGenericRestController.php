<?php
/*
 * FhirGenericRestController.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\RestControllers\Config\RestConfig;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\Trait\GlobalInterfaceTrait;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\IGlobalsAware;
use OpenEMR\Validators\ProcessingResult;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FhirGenericRestController implements IGlobalsAware {

    use GlobalInterfaceTrait;
    private FhirResourcesService $fhirResourcesService;

    private array $aclChecks = [];

    public function __construct(protected HttpRestRequest $request, protected FhirServiceBase $fhirService, OEGlobalsBag $globalsBag)
    {
        $this->setGlobalsBag($globalsBag);
    }

    public function addAclRestrictions(string $section, string $subSection = '', string $aclPermission = '') : void {
        $this->aclChecks[] = ['section' => $section, 'subSection' => $subSection, 'aclPermission' => $aclPermission];
    }

    protected function getFhirResourcesService(): FhirResourcesService
    {
        if (!isset($this->fhirResourcesService)) {
            $this->fhirResourcesService = new FhirResourcesService();
        }
        return $this->fhirResourcesService;
    }

    public function getHttpRestRequest(): HttpRestRequest
    {
        return $this->request;
    }

    public function getFhirService(): FhirServiceBase
    {
        return $this->fhirService;
    }

    /**
     * Queries for a single FHIR condition resource by FHIR id
     * @param string $fhirId The FHIR condition resource id (uuid)
     * @returns Response 200 if the operation completes successfully
     */
    public function getOne(string $fhirId): Response
    {
        // security constraints are added as additional query parameters here
        // so that the same processing logic can be used for both single resource
        // and multiple resource retrieval
        // that is why we override the _id parameter and pass along any other query parameters
        // while this means that a 404 will be returned instead of a 401, that's ok.
        // TODO: consider changing status code to 401 in the future if needed
        $queryParams = $this->getHttpRestRequest()->query->all();
        $queryParams['_id'] = $fhirId;
        $processingResult = $this->getAllProcessingResult($queryParams);

        return RestControllerHelper::handleFhirProcessingResult($processingResult, 200);
    }

    protected function getAllProcessingResult(array $searchParams): ProcessingResult {
        if ($this->getHttpRestRequest()->isPatientRequest()) {
            $puuidBind = $this->getHttpRestRequest()->getPatientUUIDString();
        } else {
            // perform ACL checks
            foreach ($this->aclChecks as $aclCheck) {
                RestConfig::request_authorization_check($this->getHttpRestRequest(), $aclCheck['section'], $aclCheck['subSection'], $aclCheck['aclPermission']);
            }
            $puuidBind = null;
        }
        return $this->getFhirService()->getAll($searchParams, $puuidBind);
    }

    /**
     * Queries for FHIR condition resources using various search parameters.
     * @param array $searchParams
     * @return JsonResponse|Response FHIR bundle with query results, if found
     */
    public function getAll(?array $searchParams = null): JsonResponse|Response
    {
        if (empty( $searchParams)) {
            $searchParams = $this->request->query->all();
        }
        $redirectUrl = $this->getHttpRestRequest()->getServerParams()['REDIRECT_URL'] ?? '';
        $bundleEntries = [];
        $resourceName = 'FhirDomainResource';
        $processingResult = $this->getAllProcessingResult($searchParams);
        foreach ($processingResult->getData() as $searchResult) {
            $bundleEntry = [
                'fullUrl' =>  $this->getGlobalsBag()->get('site_addr_oath') . $redirectUrl . '/' . $searchResult->getId(),
                'resource' => $searchResult
            ];
            if ($searchResult instanceof FHIRDomainResource) {
                $resourceName = $searchResult->get_fhirElementName();
            }
            $fhirBundleEntry = new FHIRBundleEntry($bundleEntry);
            array_push($bundleEntries, $fhirBundleEntry);
        }
        $bundleSearchResult = $this->getFhirResourcesService()->createBundle($resourceName, $bundleEntries, false);
        $searchResponseBody = RestControllerHelper::responseHandler($bundleSearchResult, null, 200);
        return $searchResponseBody;
    }
}
