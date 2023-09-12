<?php

namespace OpenEMR\Services\FHIR;

use Laminas\Form\Element\Search;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\Search\FHIRSearchFieldFactory;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\FHIR\Traits\ResourceServiceSearchTrait;
use OpenEMR\Services\Search\SearchQueryConfig;
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
abstract class FhirServiceBase implements IResourceSearchableService, IResourceReadableService, IResourceCreatableService, IResourceUpdateableService
{
    use ResourceServiceSearchTrait;

    /**
     * Maps FHIR Resource search parameters to OpenEMR parameters
     */
    protected $resourceSearchParameters = array();

    /**
     * @var FHIRSearchFieldFactory
     */
    private $searchFieldFactory;

    /**
     * Url to the base fhir api location
     * @var string
     */
    private $fhirApiURL;

    public function __construct($fhirApiURL = null)
    {
        $params = $this->loadSearchParameters();
        $this->resourceSearchParameters = is_array($params) ? $params : [];
        $searchFieldFactory = new FHIRSearchFieldFactory($this->resourceSearchParameters);
        $this->setSearchFieldFactory($searchFieldFactory);
        $this->setFhirApiUrl($fhirApiURL);
    }

    public function setFhirApiUrl($fhirApiURL)
    {
        // anything using a 'reference' search field MUST have a URL resolver to handle the reference translation
        // so if we have the api url we are going to create our resolver.
        if (!empty($fhirApiURL)) {
            $searchFieldFactory = $this->getSearchFieldFactory();
            $urlResolver = new FhirUrlResolver($fhirApiURL);
            $searchFieldFactory->setFhirUrlResolver($urlResolver);
        }
        $this->fhirApiURL = $fhirApiURL;
    }

    /**
     * @return string
     */
    public function getFhirApiURL()
    {
        return $this->fhirApiURL;
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    abstract protected function loadSearchParameters();

    /**
     * Returns only the supported search parameters that the service supports from the passed in parameters array.  If
     * the search parameters include any search modifiers they are retained in the return array if the value is supported
     * @param $paramsToFilter hashmap of search terms to search values
     * @return array Hashmap of supported search terms to the search values
     */
    public function getSupportedSearchParams($paramsToFilter)
    {
        $searchParams = $this->getSearchParams();
        if (empty($searchParams)) {
            return [];
        }
        $filteredParams = [];
        foreach ($paramsToFilter as $param => $value) {
            if ($this->getSearchFieldFactory()->hasSearchField($param)) {
                $filteredParams[$param] = $paramsToFilter[$param];
            }
        }
        return $filteredParams;
    }


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
    abstract public function parseFhirResource(FHIRDomainResource $fhirResource);

    /**
     * Inserts a FHIR resource into the system.
     * @param $fhirResource The FHIR resource
     * @return ProcessingResult The OpenEMR Service Result
     */
    public function insert(FHIRDomainResource $fhirResource): ProcessingResult
    {
        $openEmrRecord = $this->parseFhirResource($fhirResource);
        return $this->insertOpenEmrRecord($openEmrRecord);
    }

    /**
     * Inserts an OpenEMR record into the sytem.
     * @return ProcessingResult The OpenEMR processing result.
     */
    abstract protected function insertOpenEMRRecord($openEmrRecord);

    /**
     * Inserts a FHIR resource into the system.
     * @param $fhirResourceId The FHIR Resource ID used to lookup the existing FHIR resource/OpenEMR record
     * @param $fhirResource The FHIR resource.
     * @return The OpenEMR Service Result
     */
    public function update($fhirResourceId, FHIRDomainResource $fhirResource): ProcessingResult
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
    public function getOne($fhirResourceId, $puuidBind = null): ProcessingResult
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
            // need to handle our search (pagination, sort order, etc) configuration here.
            if (isset($oeSearchParameters['_config'])) {
                $searchConfig = SearchQueryConfig::createFhirConfigFromSearchParams($oeSearchParameters['_config']);
                $fhirSearchResult->setPagination($searchConfig->getPagination());
                $oeSearchResult = $this->searchForOpenEMRRecordsWithConfig($oeSearchParameters, $searchConfig);
            } else {
                $oeSearchResult = $this->searchForOpenEMRRecords($oeSearchParameters);
            }

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
            $systemLogger = new SystemLogger();
            $systemLogger->error(get_class($this) . "->getAll() exception thrown", ['message' => $exception->getMessage(),
                'field' => $exception->getField(), 'trace' => $exception->getTraceAsString()]);
            // put our exception information here
            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $fhirSearchResult;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @return OpenEMR records
     */
    abstract protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult;

    /**
     * Searches for OpenEMR records using OpenEMR search parameters and the search configuration.  We would make this
     * abstract but to preserve backwards compatability with existing installations we leave it as is.  Services that
     * wish to leverage the search query config can implement this method.
     * @param openEMRSearchParameters OpenEMR search fields
     * @param SearchQueryConfig $searchConfig The search configuration (sort order, pagination, etc)
     * @return OpenEMR records
     */
    protected function searchForOpenEMRRecordsWithConfig($openEMRSearchParameters, SearchQueryConfig $searchConfig): ProcessingResult
    {
        return $this->searchForOpenEMRRecords($openEMRSearchParameters);
    }

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
