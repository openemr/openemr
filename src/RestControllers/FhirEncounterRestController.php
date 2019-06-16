<?php
/**
 * FhirEncounterRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\RestControllers;

use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirEncounterRestController
{
    private $encounterService;
    private $fhirService;

    public function __construct()
    {
        $this->encounterService = new EncounterService();
        $this->fhirService = new FhirResourcesService();
    }

    // implement put post in future

    public function getOne($eid)
    {
        $oept = $this->encounterService->getEncounter($eid);
        $encounterResource = $this->fhirService->createEncounterResource($eid, $oept, false);

        return RestControllerHelper::responseHandler($encounterResource, null, 200);
    }

    public function getAll($search)
    {
        $resourceURL = \RestConfig::$REST_FULL_URL;
        if (strpos($resourceURL, '?') > 0) {
            $resourceURL = strstr($resourceURL, '?', true);
        }

        $searchParam = array(
            'pid' => $search['patient'],
            'provider_id' => $search['practitioner']);

        $searchResult = $this->encounterService->getEncountersBySearch($searchParam);
        if ($searchResult === false) {
            http_response_code(404);
            exit;
        }
        $entries = array();
        foreach ($searchResult as $oept) {
            $entryResource = $this->fhirService->createEncounterResource($oept['encounter'], $oept, false);
            $entry = array(
                'fullUrl' => $resourceURL . "/" . $oept['encounter'],
                'resource' => $entryResource
            );
            $entries[] = new FHIRBundleEntry($entry);
        }
        $searchResult = $this->fhirService->createBundle('Encounter', $entries, false);
        return RestControllerHelper::responseHandler($searchResult, null, 200);
    }
}
