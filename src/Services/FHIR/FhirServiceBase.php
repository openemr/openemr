<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Validators\ProcessingResult;

/**
 * Base class for FHIR Service implementations.
 *
 * Implementations are required to override the following methods:
 * - loadSearchParameters
 * - parseOpenEMRRecord
 * - parseFhirResource
 * - insertOpenEMRRecord
 * - updateOpenEMRRecord
 * - getOne
 * - searchForOpenEMRRecords
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
abstract class FhirServiceBase
{

    /**
     * Maps FHIR Resource search parameters to OpenEMR parameters
     */
    protected $resourceSearchParameters = array();

    public function __construct()
    {
        $this->resourceSearchParameters = $this->loadSearchParameters();
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    abstract protected function loadSearchParameters();


    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    abstract public function parseOpenEMRRecord($dataRecord = array(), $encode = false);

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
     * @return The OpenEMR processing result.
     */
    abstract protected function insertOpenEMRRecord($openEmrRecord);

    /**
     * Inserts a FHIR resource into the system.
     * @param $fhirResourceId The FHIR Resource ID used to lookup the existing FHIR resource/OpenEMR record
     * @param $fhirResource The FHIR resource.
     * @return The OpenEMR Service Result
     */
    public function update($fhirResourceId, $fhirResource)
    {
        $openEmrRecord = $this->parseFhirResource($fhirResource);
        $openEmrRecord['uuid'] = $fhirResourceId;
        $processingResult =  $this->updateOpenEMRRecord($fhirResourceId, $openEmrRecord);

        if ($processingResult->hasErrors()) {
            return $processingResult;
        }

        if (isset($processingResult->getData()[0])) {
            $openEmrRecord = $processingResult->getData()[0];
            $fhirRecord = $this->parseOpenEMRRecord($openEmrRecord);

            $processingResult->setData([]);
            $processingResult->addData($fhirRecord);
        }
        return $processingResult;
    }

    /**
     * Updates an existing OpenEMR record.
     * @param $fhirResourceId The OpenEMR record's FHIR Resource ID.
     * @param $updatedOpenEMRRecord The "updated" OpenEMR record.
     * @return The OpenEMR Service Result
     */
    abstract protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord);

    /**
     * Performs a FHIR Resource lookup by FHIR Resource ID
     * @param $fhirResourceId The OpenEMR record's FHIR Resource ID.
     */
    abstract protected function getOne($fhirResourceId);

    /**
     * Executes a FHIR Resource search given a set of parameters.
     * @param $fhirSearchParameters The FHIR resource search parameters
     * @return processing result
     */
    public function getAll($fhirSearchParameters)
    {
        $oeSearchParameters = array();
        $provenanceRequest = false;
        //Checking for provenance reqest
        if (isset($fhirSearchParameters['_revinclude'])) {
            if ($fhirSearchParameters['_revinclude'] == 'Provenance:target') {
                $provenanceRequest = true;
            }
        }
        foreach ($fhirSearchParameters as $fhirSearchField => $searchValue) {
            if (isset($this->resourceSearchParameters[$fhirSearchField])) {
                $oeSearchFields = $this->resourceSearchParameters[$fhirSearchField];
                foreach ($oeSearchFields as $index => $oeSearchField) {
                    $oeSearchParameters[$oeSearchField] = $searchValue;
                }
            }
        }

        $oeSearchResult = $this->searchForOpenEMRRecords($oeSearchParameters);

        $fhirSearchResult = new ProcessingResult();
        $fhirSearchResult->setInternalErrors($oeSearchResult->getInternalErrors());
        $fhirSearchResult->setValidationMessages($oeSearchResult->getValidationMessages());

        if ($oeSearchResult->isValid()) {
            foreach ($oeSearchResult->getData() as $index => $oeRecord) {
                $fhirResource = $this->parseOpenEMRRecord($oeRecord);
                $fhirSearchResult->addData($fhirResource);
                if ($provenanceRequest) {
                    $provenanceResource = $this->createProvenanceResource($oeRecord);
                    if ($provenanceResource) {
                        $fhirSearchResult->addData($provenanceResource);
                    }
                }
            }
        }
        return $fhirSearchResult;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @return OpenEMR records
     */
    abstract protected function searchForOpenEMRRecords($openEMRSearchParameters);

    /**
     * Creates the Provenance resource  for the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    abstract public function createProvenanceResource($dataRecord = array(), $encode = false);

    /*
    * public function to return search params
    */
    public function getSearchParams()
    {
        return $this->loadSearchParameters();
    }
}
