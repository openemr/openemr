<?php
/**
 * FhirPatientRestController
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

use OpenEMR\Services\PatientService;
use OpenEMR\Services\FhirResourcesService;
use OpenEMR\RestControllers\RestControllerHelper;
use HL7\FHIR\STU3\FHIRResource\FHIRBundle\FHIRBundleEntry;

class FhirPatientRestController
{
    private $patientService;
    private $fhirService;

    public function __construct($pid)
    {
        $this->patientService = new PatientService();
        $this->patientService->setPid($pid);
        $this->fhirService = new FhirResourcesService();
    }

    // implement put post in future

    public function getOne()
    {
        $oept = $this->patientService->getOne();
        $pid = $this->patientService->getPid();
        $patientResource = $this->fhirService->createPatientResource($pid, $oept, false);

        return RestControllerHelper::responseHandler($patientResource, null, 200);
    }

    public function getAll($search)
    {
        $resourceURL = \RestConfig::$REST_FULL_URL;
        if (strpos($resourceURL, '?') > 0)
            $resourceURL = strstr($resourceURL, '?', true);

        $searchParam = array(
            'name' => $search['name'],
            'dob' => $search['birthdate']);

        $searchResult = $this->patientService->getAll($searchParam);
        if ($searchResult === false) {
            http_response_code(404);
            exit;
        }
        $entries = array();
        foreach ($searchResult as $oept) {
            $entryResource = $this->fhirService->createPatientResource($oept['pid'], $oept, false);
            $entry = array(
                'fullUrl' => $resourceURL . "/" . $oept['pid'],
                'resource' => $entryResource
            );
            $entries[] = new FHIRBundleEntry($entry);
        }
        $searchResult = $this->fhirService->createBundle('Patient', $entries, false);
        return RestControllerHelper::responseHandler($searchResult, null, 200);
    }
}
