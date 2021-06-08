<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\Search\FHIRSearchFieldFactory;
use OpenEMR\Services\Search\SearchFieldException;
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

    /**
     * @var FHIRSearchFieldFactory
     */
    private $searchFieldFactory;

    public function __construct($fhirApiURL = null)
    {
        $params = $this->loadSearchParameters();
        $this->resourceSearchParameters = is_array($params) ? $params : [];
        $searchFieldFactory = new FHIRSearchFieldFactory($this->resourceSearchParameters);

        // anything using a 'reference' search field MUST have a URL resolver to handle the reference translation
        // so if we have the api url we are going to create our resolver.
        if (!empty($fhirApiURL)) {
            $urlResolver = new FhirUrlResolver($fhirApiURL);
            $searchFieldFactory->setFhirUrlResolver($urlResolver);
        }
        $this->setSearchFieldFactory($searchFieldFactory);
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
    public function getOne($fhirResourceId, $puuidBind = null)
    {
        // every FHIR resource must support the _id search parameter so we will just piggy bag on
        $searchParam = ['_id' => $fhirResourceId];
        if (isset($puuidBind) && $this instanceof IPatientCompartmentResourceService) {
            $searchField = $this->getPatientContextSearchField();
            $searchParam[$searchField->getName()] = $puuidBind;
        }
        return $this->getAll($searchParam, $puuidBind);
    }

    /**
     * Executes a FHIR Resource search given a set of parameters.
     * @param $fhirSearchParameters The FHIR resource search parameters
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return processing result
     */
    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        $provenanceRequest = false;
        //Checking for provenance reqest
        if (isset($fhirSearchParameters['_revinclude'])) {
            if ($fhirSearchParameters['_revinclude'] == 'Provenance:target') {
                $provenanceRequest = true;
            }
            // once we've got our flag we clear it as it doesn't map to anything in our search parameters.
            unset($fhirSearchParameters['_revinclude']);
        }
        $fhirSearchResult = new ProcessingResult();

        try {
            $oeSearchParameters = $this->createOpenEMRSearchParameters($fhirSearchParameters, $puuidBind);

            (new SystemLogger())->debug("FhirServiceBase->getAll() Created search parameters ", ['searchParameters' => array_keys($oeSearchParameters)]);
            // gives a ton of information but this can be helpful in debugging this stuff.
//            array_walk($oeSearchParameters, function ($v) {
//                echo $v;
//            });
            $oeSearchResult = $this->searchForOpenEMRRecords($oeSearchParameters);


            $fhirSearchResult->setInternalErrors($oeSearchResult->getInternalErrors());
            $fhirSearchResult->setValidationMessages($oeSearchResult->getValidationMessages());

            if ($oeSearchResult->isValid()) {
                foreach ($oeSearchResult->getData() as $index => $oeRecord) {
                    $fhirResource = $this->parseOpenEMRRecord($oeRecord);
                    $fhirSearchResult->addData($fhirResource);
                    if ($provenanceRequest) {
                        $provenanceResource = $this->createProvenanceResource($fhirResource);
                        if ($provenanceResource) {
                            $fhirSearchResult->addData($provenanceResource);
                        } else {
                            (new SystemLogger())->debug(get_class($this) . ":getAll() did not return a provenance record when requested");
                        }
                    }
                }
            }
        } catch (SearchFieldException $exception) {
            (new SystemLogger())->error("FhirServiceBase->getAll() exception thrown", ['message' => $exception->getMessage(),
                'field' => $exception->getField()]);
            // put our exception information here
            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $fhirSearchResult;
    }

    public function setSearchFieldFactory(FHIRSearchFieldFactory $factory)
    {
        $this->searchFieldFactory = $factory;
    }

    public function getSearchFieldFactory(): FHIRSearchFieldFactory
    {
        return $this->searchFieldFactory;
    }

    /**
     * Given the hashmap of search parameters to values it generates a map of search keys to ISearchField objects that
     * are used to search in the OpenEMR system.  Service classes that extend the base class can override this method
     *
     * to either add search fields or change the functionality of the created ISearchFields.
     *
     * @param $fhirSearchParameters
     * @param $puuidBind The patient unique id if searching in a patient context
     * @return ISearchField[] where the keys are the search fields.
     */
    protected function createOpenEMRSearchParameters($fhirSearchParameters, $puuidBind)
    {
        $oeSearchParameters = array();
        $searchFactory = $this->getSearchFieldFactory();

        foreach ($fhirSearchParameters as $fhirSearchField => $searchValue) {
            try {
                // format: <field>{:modifier1|:modifier2}={comparator1|comparator2}[value1{,value2}]
                // field is the FHIR search field
                // modifier is the search modifier ie :exact, :contains, etc
                // comparator is used with dates / numbers, ie :gt, :lt
                // values can be comma separated and are treated as an OR condition
                // if the $searchValue is an array then this is treated as an AND condition
                // if $searchValue is an array and individual fields contains comma separated values the and clause takes
                // precedence and ALL values will be UNIONED (AND clause).
                if ($searchFactory->hasSearchField($fhirSearchField)) {
                    $searchField = $searchFactory->buildSearchField($fhirSearchField, $searchValue);
                    $oeSearchParameters[$searchField->getName()] = $searchField;
                } else {
                    throw new SearchFieldException($fhirSearchField, xlt("This search field does not exist or is not supported"));
                }
            } catch (\InvalidArgumentException $exception) {
                throw new SearchFieldException($fhirSearchField, "The search field argument was invalid, improperly formatted, or could not be parsed", $exception->getCode(), $exception);
            }
        }

        // we make sure if we are a resource that deals with patient data and we are in a patient bound context that
        // we restrict the data to JUST that patient.
        if (!empty($puuidBind) && $this instanceof IPatientCompartmentResourceService) {
            $patientField = $this->getPatientContextSearchField();
            // TODO: @adunsulag not sure if every service will already have a defined binding for the patient... I'm assuming for Patient compartments we would...
            // yet we may need to extend the factory in the future to handle this.
            $oeSearchParameters[$patientField->getName()] = $searchFactory->buildSearchField($patientField->getName(), [$puuidBind]);
        }

        return $oeSearchParameters;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
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
    abstract public function createProvenanceResource($dataRecord, $encode = false);

    /*
    * public function to return search params
    */
    public function getSearchParams()
    {
        return $this->loadSearchParameters();
    }
}
