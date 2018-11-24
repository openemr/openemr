<?php
/**
 * FhirEncounterRestController
 *
 * Copyright (C) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleEntry;

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
        if (strpos($resourceURL, '?') > 0)
            $resourceURL = strstr($resourceURL, '?', true);

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
