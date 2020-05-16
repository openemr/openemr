<?php

namespace OpenEMR\Services\FHIR;

/**
 * Base class for FHIR Service implementations.
 *
 * Implementations are required to override the following methods:
 * - parseOpenEMRRecord
 * - parseFhirResource
 * - insertOpenEMRRecord
 * - updateOpenEMRRecord
 * - getOne
 * - getAll
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
abstract class FhirServiceBase
{
    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    abstract public function parseOpenEMRRecord($dataRecord = array(), $encode = true);

    /**
     * Parses a FHIR Resource, returning the equivalent OpenEMR record.
     *
     * @param $fhirResource The source FHIR resource
     * @return a mapped OpenEMR data record (array)
     */
    abstract public function parseFhirResource($fhirResource = array());

    /**
     * Inserts a FHIR resource into the system.
     * @param $fhirResource The FHIR resource
     * @return The OpenEMR Service Result
     */
    public function insert($fhirResource)
    {
        $openEmrRecord = $this->parseFhirResource($fhirResource);
        return $this->insertOpenEmrRecord($openEmrRecord);
    }

    /**
     * Inserts an OpenEMR record into the sytem.
     * @return The OpenEMR service result.
     */
    abstract function insertOpenEMRRecord($openEmrRecord);

    /**
     * Inserts a FHIR resource into the system.
     * @param $openEMRLookupId The ID field used to lookup the OpenEMR record for update.
     * @param $fhirResource The FHIR resource.
     * @return The OpenEMR Service Result
     */
    public function update($openEMRLookupId, $fhirResource)
    {
        $openEmrRecord = $this->parseFhirResource($fhirResource);
        return $this->updateOpenEMRRecord($openEMRLookupId, $openEmrRecord);
    }

    /**
     * Updates an existing OpenEMR record.
     * @param $openEMRLookupId The OpenEMR ID used to lookup the OpenEMR record.
     * @param $updatedOpenEMRRecord The "updated" OpenEMR record.
     * @return The OpenEMR Service Result
     */
    abstract function updateOpenEMRRecord($openEMRLookupId, $updatedOpenEMRRecord);

    /**
     * Performs a FHIR R4 Resource lookup by FHIR Resource ID
     * NOT YET IMPLEMENTED - requires updates to support associating a fhir resource id to an openemr record
     */
    abstract function getOne($fhirResourceId);

    /**
     * Executes a FHIR Resource search
     */
    public function getAll($fhirSearchParameters)
    {
        $fhirSearchResults = array();

        $openEMRSearchParameters = $this->mapSearchParameters($fhirSearchParameters);
        $openEMRSearchResults = $this->searchForOpenEMRRecords($openEMRSearchParameters);

        foreach ($openEMRSearchResults as $index => $openEMRSearchResult) {
            $fhirResource = $this->parseOpenEMRRecord($openEMRSearchResult);
            array_push($fhirSearchResults, $fhirResource);
        }

        return $fhirSearchResults;
    }

    /**
     * Maps FHIR R4 Search Parameters/Terms to OpenEMR Fields
     * @param $fhirSearchParameters The FHIR R4 Search parameters
     * @return OpenEMR Search parameters
     */
    abstract function mapSearchParameters($fhirSearchParameters);

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @return OpenEMR records
     */
    abstract function searchForOpenEMRRecords($openEMRSearchParameters);
}
