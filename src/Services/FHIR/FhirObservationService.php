<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\Billing\BillingProcessor\LoggerInterface;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRObservation\FHIRObservationComponent;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\ObservationLabService;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ReferenceSearchField;
use OpenEMR\Services\Search\ReferenceSearchValue;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Services\VitalsService;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Observation Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirObservationService
 * @package            OpenEMR
 * @link               http://www.open-emr.org
 * @author             Yash Bothra <yashrajbothra786gmail.com>
 * @copyright          Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirObservationService extends FhirServiceBase implements IResourceSearchableService, IResourceUSCIGProfileService, IPatientCompartmentResourceService
{

    /**
     * @var ObservationLabService
     */
    private $observationService;

    /**
     * @var VitalsService
     */
    private $vitalsService;

    /**
     * @var BaseService[]
     */
    private $innerServices;

    /**
     * @var LoggerInterface
     */
    private $logger;

    const USCGI_PROFILE_BMI_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/pediatric-bmi-for-age';

    public function __construct()
    {
        parent::__construct();
        $this->innerServices = [];
        $this->observationService = new ObservationLabService();
        $this->vitalsService = new FhirVitalsService();
        $this->innerServices[] = $this->vitalsService;
        $this->logger = new SystemLogger();
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters(): array
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, ['uuid']),
        ];
    }

    /**
     * Parses an OpenEMR observation record, returning the equivalent FHIR Observation Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRObservation
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $observationResource = new FHIRObservation();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(gmdate('c'));
        $observationResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $observationResource->setId($id);

        $categoryCoding = new FHIRCoding();
        $categoryCode = new FHIRCodeableConcept();
        if (!empty($dataRecord['code']))
        {
            $categoryCoding->setCode($dataRecord['code']);
            $categoryCoding->setDisplay($dataRecord['codetext']);
            $categoryCoding->setSystem(FhirCodeSystemUris::LOINC);
            $categoryCode->addCoding($categoryCoding);
            $observationResource->setCode($categoryCode);
        }
        else if (!empty($dataRecord['procedure_code'])) {
            $categoryCoding->setCode($dataRecord['procedure_code']);
            $categoryCoding->setSystem('http://loinc.org');
            $categoryCoding->setDisplay($dataRecord['procedure_name']);
            $categoryCode->addCoding($categoryCoding);
            $observationResource->setCode($categoryCode);
        }

        $subject = new FHIRReference();
        $subject->setReference('Patient/' . $dataRecord['puuid']);
        $observationResource->setSubject($subject);

        if (!empty($dataRecord['date_report'])) {
            $observationResource->setEffectiveDateTime(gmdate('c', strtotime($dataRecord['date_report'])));
        }

        if (!empty($dataRecord['result_status'])) {
            $observationResource->setStatus(($dataRecord['result_status']));
        } else {
            $observationResource->setStatus("unknown");
        }

        if (!empty($dataRecord['units'])) {
            $observationResource->setValueQuantity($dataRecord['units']);
        }

        if (!empty($dataRecord['range'])) {
            $observationResource->setValueRange($dataRecord['range']);
        }

        if (!empty($dataRecord['result'])) {
            $quantity = new FHIRQuantity();
            $quantity->setValue($dataRecord['result']);
            if (!empty($dataRecord['units'])) {
                $quantity->setUnit($dataRecord['units']);
            }
            $observationResource->setValueQuantity($quantity);
        }


        if (!empty($dataRecord['comments'])) {
            $observationResource->addNote(['text' => $dataRecord['comments']]);
        }

        if ($encode) {
            return json_encode($observationResource);
        } else {
            return $observationResource;
        }
    }

    /**
     * Retrieves all of the fhir observation resources mapped to the underlying openemr data elements.
     * @param $fhirSearchParameters The FHIR resource search parameters
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return processing result
     */
    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        $fhirSearchResult = new ProcessingResult();
        try {
            if (isset($fhirSearchParameters['_id'])) {
                $result = $this->populateSurrogateSearchFieldsForUUID($fhirSearchParameters['_id'], $fhirSearchParameters);;
                if ($result instanceof ProcessingResult) // failed to populate so return the results
                {
                    return $result;
                }
            }

            if (isset($puuidBind)) {
                $field = $this->getPatientContextSearchField();
                $fhirSearchParameters[$field->getName()] = $puuidBind;
            }

            if (isset($fhirSearchParameters['category'])) {
                /**
                 * @var TokenSearchField
                 */
                $category = $fhirSearchParameters['category'];

                $service = $this->getObservationServiceForCategory($category);
                $fhirSearchResult = $service->getAll($fhirSearchParameters, $puuidBind);
            } else if (isset($fhirSearchParameters['code'])) {
                $service = $this->getObservationServiceForCode($fhirSearchParameters['code']);
                // if we have a service let's search on that
                if (isset($service))
                {
                    $fhirSearchResult = $service->getAll($fhirSearchParameters, $puuidBind);
                }
                else {
                    $fhirSearchResult = $this->searchAllObservationServices($fhirSearchParameters, $puuidBind);
                }
            }
            else {
                $fhirSearchResult = $this->searchAllObservationServices($fhirSearchParameters, $puuidBind);
            }
        } catch (SearchFieldException $exception) {
            (new SystemLogger())->error("FhirServiceBase->getAll() exception thrown", ['message' => $exception->getMessage(),
                'field' => $exception->getField()]);
            // put our exception information here
            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $fhirSearchResult;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param  array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null): ProcessingResult
    {
        // we need to differentiate between these different data types...

        // Smoking Status -> History Data Service

        // Pediatric Weight -> form_vitals

        // Lab Results -> ObservationLabService

        // Pulse Oximetry -> form_vitals

        // Body Height -> form_vitals

        // Body Temperature -> form_vitals

        // weight -> form_vitals

        // need to grab the code system for each one
        if (isset($openEMRSearchParameters['uuid'])) {
            $result = $this->populateSurrogateSearchFieldsForUUID($openEMRSearchParameters['uuid'], $openEMRSearchParameters);
            if ($result instanceof ProcessingResult) // failed to populate so return the results
            {
                return $result;
            }
        }

        if (isset($puuidBind)) {
            $openEMRSearchParameters['subject'] = new ReferenceSearchField('puuid', [new ReferenceSearchValue($puuidBind, 'Patient', true)]);
        }

        if (isset($openEMRSearchParameters['category']) && !empty($openEMRSearchParameters['category']->getValues())) {
            /**
             * @var TokenSearchField
             */
            $category = $openEMRSearchParameters['category'];

            $service = $this->getObservationServiceForCategory($category);
            return $service->search($openEMRSearchParameters);
        } else {
            return $this->searchAllObservationServices($openEMRSearchParameters, $puuidBind);
        }
    }

    /**
     * Take our uuid surrogate key and populate the underlying data elements and grabs the mapped key for it.
     * @param $fhirResourceId The uuid search field with the 1..* values to search on
     * @param $search Hashmap of search operators
     */
    private function populateSurrogateSearchFieldsForUUID($fhirResourceId, &$search)
    {
        $processingResult = new ProcessingResult();
        // we are going to get our
        $mapping = UuidMapping::getMappingForUUID($fhirResourceId);

        if (empty($mapping))
        {
            $processingResult->setValidationMessages(['_id' => 'Resource not found for that id']);
            return $processingResult;
        }

        // grab our category
        if ($mapping['resource'] !== 'Observation')
        {
            // we have a problem here
            $processingResult->setValidationMessages(["_id" => "Resource not found for that id"]);
            $this->logger->error("Requested observation resource for uuid that exists for a different resource", ['_id' => $fhirResourceId, 'mappingResource' => $mapping['resource']]);
            return $processingResult;
        }

        // grab category and code
        $query_vars = [];
        parse_str($mapping['resource_path'], $query_vars);
        if (empty($query_vars['category']))
        {
            $processingResult->setValidationMessages(["_id" => "Resource not found for that id"]);
            $this->logger->error("Requested observation with no resource_path category to parse the mapping", ['uuid' => $fhirResourceId, 'resource_path' => $mapping['resource_path']]);
            return $processingResult;
        }

        $code = empty($search['code']) ? $query_vars['code'] : $search['code'] . "," . $query_vars['code'];
        $search['code'] = $code;
        $search['category'] = $query_vars['category'];

        // we only want a single search value for now... not supporting combined uuids
        $search['_id'] = UuidRegistry::uuidToString($mapping['target_uuid']);
    }

    private function getObservationServiceForCode($code)
    {
        $field = new TokenSearchField('code', $code);
        // shouldn't ever hit the default but we have i there just in case.
        $values = $field->getValues() ?? [new TokenSearchValue(FhirVitalsService::VITALS_PANEL_LOINC_CODE)];
        $searchCode = $values[0]->getCode();

        // we only grab the first one as we assume each service only supports a single LOINC observation code
        foreach ($this->innerServices as $service)
        {
            if ($service->supportsCode($searchCode))
            {
                return $service;
            }
        }
    }

    private function getObservationServiceForCategory($category): FhirServiceBase
    {
        // let the field parse our category
        $field = new TokenSearchField('category', $category);
        $values = $field->getValues() ?? [new TokenSearchValue('vital-signs')];
        $categoryField = $values[0]->getCode();
        if ($categoryField === "vital-signs") {
            return $this->vitalsService;
        } else if ($categoryField === 'social-history') {
            throw new \BadMethodCallException("Category not implemented");
        } else {
            return $this->observationService;
        }
    }

    private function searchAllObservationServices($fhirSearchParams, $puuidBind)
    {
        $processingResult = new ProcessingResult();

        /**
         * @var $service BaseService
         */
        foreach ($this->innerServices as $service) {
            $innerResult = $service->getAll($fhirSearchParams, $puuidBind);
            $processingResult->addProcessingResult($innerResult);
            if ($processingResult->hasErrors()) {
                // clear our data out and just return the errors
                $processingResult->clearData();
                return $processingResult;
            }
        }
        return $processingResult;
    }

    public function parseFhirResource($fhirResource = array())
    {
        // TODO: If Required in Future
    }

    public function insertOpenEMRRecord($openEmrRecord)
    {
        // TODO: If Required in Future
    }

    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        // TODO: If Required in Future
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
    }
    public function getProfileURIs(): array
    {
        return [self::USCGI_PROFILE_BMI_URI];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

}
