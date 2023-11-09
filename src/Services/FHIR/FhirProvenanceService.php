<?php

/**
 * FhirProvenanceService handles all data model operations against a FHIR provenance resource.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\System\System;
use OpenEMR\FHIR\Export\ExportCannotEncodeException;
use OpenEMR\FHIR\Export\ExportException;
use OpenEMR\FHIR\Export\ExportJob;
use OpenEMR\FHIR\Export\ExportStreamWriter;
use OpenEMR\FHIR\Export\ExportWillShutdownException;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRProvenance\FHIRProvenanceAgent;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Utils\FhirServiceLocator;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ReferenceSearchValue;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;
use Exception;

class FhirProvenanceService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;

    // Note: FHIR 4.0.1 id columns put a constraint on ids such that:
    // Ids can be up to 64 characters long, and contain any combination of upper and lowercase ASCII letters,
    // numerals, "-" and ".".  Logical ids are opaque to the resource server and should NOT be changed once they've
    // been issued by the resource server
    // Up to OpenEMR 6.1.0 patch 0 we used : as our separator for Provenance, and _ as our separator for resources such
    // as CarePlan and Goals.
    const SURROGATE_KEY_SEPARATOR_V1 = ":";
    // use the abbreviation PSK for Provenance Surrogate key and hyphens.  Since Logical ids are opaque we can do this as long as
    // our UUID NEVER generates a three digit hyphenated id which none of the standards currently do.
    // our other resources use -SK- as a surrogate key
    // the best approach would be to have a complete accessible provenance data table with its own uuids that's
    // searchable but right now provenance is tracked so dispararately across the system we go to each resource
    // in order to grab these ids.
    const SURROGATE_KEY_SEPARATOR_V2 = "-PSK-";
    const V2_TIMESTAMP = 1649476800; // strtotime("2022-04-09");


    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-provenance';


    /**
     * @var FhirServiceLocator
     */
    private $serviceLocator;

    /**
     * @var
     */
    private $accessTokenScopes;

    public function __construct($fhirApiURL = null, $serviceLocator = null)
    {
        parent::__construct($fhirApiURL);
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param FhirServiceLocator $serviceLocator
     */
    public function setServiceLocator(FhirServiceLocator $serviceLocator): void
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return FhirServiceLocator
     */
    public function getServiceLocator(): FhirServiceLocator
    {
        return $this->serviceLocator;
    }

    /**
     * Given a FHIR domain object (such as Patient/AllergyIntolerance,etc), grab the provenance record for that resource.
     * If there is a connected user object to the resource attempt to create the provenance record using that individual.
     * Otherwise the provenance is tied to the main business organization designated in the system.
     * @param FHIRDomainResource $resource The resource we are retrieving the provenance for
     * @param FHIRReference|null $userWHO The user that will be the provenance agent
     * @return FHIRProvenance|null
     */
    public function createProvenanceForDomainResource(FHIRDomainResource $resource, FHIRReference $userWHO = null)
    {

        $fhirProvenance = new FHIRProvenance();

        $fhirProvenance->setId($this->getSurrogateKeyForResource($resource));
        /**
         * Attributes required/must support for US.Core
         */

        // target.reference (1.*) required
        $fhirProvenance->addTarget(UtilsService::createRelativeReference($resource->get_fhirElementName(), $resource->getId()));

        // recorded - required
        if (!empty($resource->getMeta())) {
            $fhirProvenance->setRecorded($resource->getMeta()->getLastUpdated());
        } else {
            // we should ALWAYS have a last updated date... but we will log this if we don't
            $fhirProvenance->setRecorded(UtilsService::createDataMissingExtension());
            (new SystemLogger())->error("Meta element was missing to populate recorded date in " . self::class . "->createProvenanceForDomainResource()", [
                "resource" => $resource->get_fhirElementName() . "/" . $resource->getId()
            ]);
        }

        $fhirOrganizationService = new FhirOrganizationService();
        $primaryBusinessEntity = $fhirOrganizationService->getPrimaryBusinessEntityReference();
        if (empty($primaryBusinessEntity)) {
            if (!empty($userWHO)) {
                (new SystemLogger())->debug(self::class . "->createProvenanceForDomainResource() primary business entity not found, attempting to find user organization");
                $primaryBusinessEntity = $fhirOrganizationService->getOrganizationReferenceFromUserReference($userWHO);
            }
        }

        if (empty($primaryBusinessEntity)) {
            // see if we can get this from the who if we
            (new SystemLogger())->debug(self::class . "->createProvenanceForDomainResource() could not find organization reference");
            return null;
        }

        // agent - required
        // agent.type - must support
        // agent.who - required
        // agent.onBehalfOf - must support

        // agent:provenanceAuthor - must support
        // agent:provenanceAuthor.type.coding.system - required
        // agent:provenanceAuthor.type.coding.code - required
        $author = $this->createAgentAuthorForResource($resource, $primaryBusinessEntity, $userWHO);
        $fhirProvenance->addAgent($author);


        $transmitter = $this->createAgentTransmitterForResource($resource, $primaryBusinessEntity);
        $fhirProvenance->addAgent($transmitter);

        return $fhirProvenance;
    }

    protected function createAgentAuthorForResource(FHIRDomainResource $resource, FHIRReference $primaryBusinessEntity, FHIRReference $who = null)
    {
        $agent = new FHIRProvenanceAgent();
        $agentConcept = new FHIRCodeableConcept();
        $agentConceptCoding = new FHIRCoding();
        $agentConceptCoding->setSystem("http://terminology.hl7.org/CodeSystem/provenance-participant-type");
        $agentConceptCoding->setCode("author");
        $agentConceptCoding->setDisplay(xlt("Author"));
        $agentConcept->addCoding($agentConceptCoding);
        $agent->setType($agentConcept);

        if (empty($who)) {
            $who = $primaryBusinessEntity;
        }
        $agent->setWho($who);
        $agent->setOnBehalfOf($primaryBusinessEntity);

        return $agent;
    }

    protected function createAgentTransmitterForResource(FHIRDomainResource $resource, FHIRReference $primaryBusinessEntity)
    {
        // agent:ProvenanceTransmitter - must support
        // agent:provenanceAuthor.type.coding.system=http://hl7.org/fhir/us/core/CodeSystem/us-core-provenance-participant-type - required
        // agent:provenanceAuthor.type.coding.code=transmitter - required

        $agent = new FHIRProvenanceAgent();
        $agentConcept = new FHIRCodeableConcept();
        $agentConceptCoding = new FHIRCoding();
        $agentConceptCoding->setSystem("http://hl7.org/fhir/us/core/CodeSystem/us-core-provenance-participant-type");
        $agentConceptCoding->setCode("transmitter");
        $agentConceptCoding->setDisplay(xlt("Transmitter"));
        $agentConcept->addCoding($agentConceptCoding);
        $agent->setType($agentConcept);
        $agent->setWho($primaryBusinessEntity);
        $agent->setOnBehalfOf($primaryBusinessEntity);
        return $agent;
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, ['_id']),
        ];
    }

    /**
     * Parses an OpenEMR data record, returning the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        return $dataRecord; //
    }

    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        $fhirSearchResult = new ProcessingResult();
        try {
            if (!empty($fhirSearchParameters['_id'])) {
                $fhirSearchResult = $this->getProvenanceRecordsForId($fhirSearchParameters['_id'], $puuidBind);
            } else {
                $fhirSearchResult = $this->getAllProvenanceRecordsFromServices($puuidBind);
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

    private function getAllProvenanceRecordsFromServices($puuidBind = null)
    {
        $processingResult = new ProcessingResult();
        if (empty($this->serviceLocator)) {
            (new SystemLogger())->errorLogCaller("class was not properly configured with the service locator");
        }

        // we only return provenances for
        $servicesByResource = $this->serviceLocator->findServices(IResourceUSCIGProfileService::class);

        foreach ($servicesByResource as $resource => $service) {
            // if it doesn't support the readable service we've got issues
            if ($resource == 'Provenance' || !($service instanceof IResourceReadableService)) {
                continue;
            }
            try {
                $this->addAllProvenanceRecordsForService($processingResult, $service, [], $puuidBind);
            } catch (SearchFieldException $ex) {
                $systemLogger = new SystemLogger();
                $systemLogger->error(get_class($this) . "->getAll() exception thrown", ['message' => $exception->getMessage(),
                    'field' => $exception->getField(), 'trace' => $exception->getTraceAsString()]);
                // put our exception information here
                $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
                return $processingResult;
            } catch (Exception $ex) {
                $systemLogger = new SystemLogger();
                $processingResult->addInternalError("Failed to process provenance search");
                $systemLogger->error(get_class($this) . "->getAll() exception thrown", ['message' => $ex->getMessage(),
                    'trace' => $ex->getTraceAsString()]);
                return $processingResult;
            }
        }
        return $processingResult;
    }

    private function addAllProvenanceRecordsForService(ProcessingResult $processingResult, $service, array $searchParams, $puuidBind = null)
    {
        $searchParams['_revinclude'] = 'Provenance:target';
        $serviceResult = $service->getAll($searchParams, $puuidBind);
        // now loop through and grab all of our provenance resources
        if ($serviceResult->hasData()) {
            foreach ($serviceResult->getData() as $record) {
                if ($record instanceof FHIRProvenance) {
                    $processingResult->addData($record);
                }
            }
        }
    }

    /**
     * Given a provenance record id retrieve the provenance record for the given resource and its uuid
     * @param $id string in the format of <resource>:<uuid>
     * @param $puuidBind string The patient uuid we will bind requests to in order to avoid patient data leaking
     * @return ProcessingResult
     */
    private function getProvenanceRecordsForId($id, $puuidBind)
    {
        $processingResult = new ProcessingResult();
        $idParts = $this->splitSurrogateKeyIntoParts($id);
        $resourceName = $idParts['resource'];
        $innerId = $idParts['id'];

        $className = RestControllerHelper::FHIR_SERVICES_NAMESPACE . $resourceName . "Service";
        if (!class_exists($className)) {
            throw new SearchFieldException("_id", "Provenance _id was invalid");
        }

        try {
            $newServiceClass = new $className();
            if ($newServiceClass instanceof IResourceReadableService) {
                $searchParams = [
                    '_id' => $innerId
                    ,'_revinclude' => 'Provenance:target'
                ];
                $results = $newServiceClass->getAll($searchParams, $puuidBind);
                if ($results->hasData()) {
                    foreach ($results->getData() as $datum) {
                        if ($datum instanceof  FHIRProvenance) {
                            $processingResult->addData($datum);
                        }
                    }
                } else {
                    $processingResult->addProcessingResult($results);
                }
            }
        } catch (Exception $exception) {
            $processingResult->addInternalError("Server error occurred in returning provenance for _id " . $id);
        }
        return $processingResult;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $patientToken = $openEMRSearchParameters['patient'] ?? new TokenSearchField('patient', []);
        $patientBinding = !empty($patientToken->getValues()) ? $patientToken->getValues()[0]->getCode() : null;
        /**
         * @var TokenSearchField
         */
        $id = $openEMRSearchParameters['_id'] ?? new TokenSearchField('_id', []);
        $processingResult = new ProcessingResult();
        foreach ($id->getValues() as $value) {
            // should be in format of ResourceType/uuid
            $code = $value->getCode() ?? "";
            try {
                $idParts = explode(":", $code);
                $resourceName = array_shift($idParts);

                $innerId = implode(":", $idParts);
                $className = RestControllerHelper::FHIR_SERVICES_NAMESPACE . $resourceName . "Service";
                if (class_exists($className)) {
                    $newServiceClass = new $className();
                    if ($newServiceClass instanceof IResourceReadableService) {
                        $searchParams = [
                            '_id' => $innerId
                            ,'_revinclude' => 'Provenance:target'
                        ];
                        $results = $newServiceClass->getAll($searchParams, $patientBinding);
                        if ($results->hasData()) {
                            foreach ($results->getData() as $datum) {
                                if ($datum instanceof  FHIRProvenance) {
                                    $processingResult->addData($datum);
                                }
                            }
                        } else {
                            $processingResult->addProcessingResult($results);
                        }
                    }
                }
            } catch (\Exception $exception) {
                // TODO: @adunsulag log the exception
                $processingResult->addInternalError("Server error occurred in returning provenance for _id " . $code);
            }
        }
        return $processingResult;
    }

    /**
     * Returns the Canonical URIs for the FHIR resource for each of the US Core Implementation Guide Profiles that the
     * resource implements.  Most resources have only one profile, but several like DiagnosticReport and Observation
     * has multiple profiles that must be conformed to.
     * @see https://www.hl7.org/fhir/us/core/CapabilityStatement-us-core-server.html for the list of profiles
     * @return string[]
     */
    function getProfileURIs(): array
    {
        return [self::USCGI_PROFILE_URI];
    }

    /**
     * Given a FHIRDomainResource resource generate the surrogate key.  If either column is empty it uses an empty string as the value.
     * @param array $resource The domain resource
     * @return string The surrogate key.
     */
    public function getSurrogateKeyForResource(FHIRDomainResource $resource)
    {
        $separator = self::SURROGATE_KEY_SEPARATOR_V2;

        // lastUpdated is a timesecond instant so we are going to get int value for comparison
        if (empty($resource->getMeta())) {
            (new SystemLogger())->errorLogCaller(
                "Resource missing required Meta field",
                ['resource' => $resource->getId(), 'type' => $resource->get_fhirElementName()]
            );
        } else if (empty($resource->getMeta()->getLastUpdated())) {
            (new SystemLogger())->errorLogCaller(
                "Resource missing required Meta->lastUpdated field",
                ['resource' => $resource->getId(), 'type' => $resource->get_fhirElementName()]
            );
        } else {
            // we use DATE_ATOM to get an ISO8601 compatible date as DATE_ISO8601 does not actually conform to an ISO8601 date for php legacy purposes
            $lastUpdated = \DateTime::createFromFormat(DATE_ATOM, $resource->getMeta()->getLastUpdated());

            if ($lastUpdated !== false && $lastUpdated->getTimestamp() < self::V2_TIMESTAMP) {
                $separator = self::SURROGATE_KEY_SEPARATOR_V1;
            }
        }

        return $resource->get_fhirElementName() . $separator . $resource->getId();
    }

    /**
     * Given the surrogate key representing a Provenance, split the key into its component parts.
     * @param $key string the key to parse
     * @return array The broken up key parts.
     */
    public function splitSurrogateKeyIntoParts($key)
    {
        $delimiter = self::SURROGATE_KEY_SEPARATOR_V2;
        if (strpos($key, self::SURROGATE_KEY_SEPARATOR_V1) !== false) {
            $delimiter = self::SURROGATE_KEY_SEPARATOR_V1;
        }
        $parts = explode($delimiter, $key);
        $key = [
            "resource" => $parts[0] ?? ""
            ,"id" => $parts[1] ?? ""
        ];
        return $key;
    }
    /**
     * Grabs all the objects in my service that match the criteria specified in the ExportJob.  If a
     * $lastResourceIdExported is provided, The service executes the same data collection query it used previously and
     * startes processing at the resource that is immediately after (ordered by date) the resource that matches the id of
     * $lastResourceIdExported.  This allows processing of the service to be resumed or paused.
     * @param ExportStreamWriter $writer Object that writes out to a stream any object that extend the FhirResource object
     * @param ExportJob $job The export job we are processing the request for.  Holds all of the context information needed for the export service.
     * @return void
     * @throws ExportWillShutdownException  Thrown if the export is about to be shutdown and all processing must be halted.
     * @throws ExportException  If there is an error in processing the export
     * @throws ExportCannotEncodeException Thrown if the resource cannot be properly converted into the right format (ie JSON).
     */
    public function export(ExportStreamWriter $writer, ExportJob $job, $lastResourceIdExported = null): void
    {
        if (!($this instanceof IResourceReadableService)) {
            // we need to ensure we only get called in a method that implements the getAll method.
            throw new \BadMethodCallException("Trait can only be used in classes that implement the " . IResourceReadableService::class . " interface");
        }
        $type = $job->getExportType();

        // algorithm
        // go through each resource and grab the related service
        // check if the service is a PatientCompartment resource, if so, set the patient uuids to export
        // if we are a Medication request since we are using RXCUI for our drug formulariesthere is no Provenance resource and we can just skip it

        $servicesByResource = $this->serviceLocator->findServices(IResourceUSCIGProfileService::class);

        $patientUuids = [];
        if ($type == ExportJob::EXPORT_OPERATION_GROUP) {
            $patientUuids = $job->getPatientUuidsToExport();
        }

        foreach ($job->getResources() as $resource) {
            $searchParams = [];
            $searchParams['_revinclude'] = 'Provenance:target';
            if ($resource != "Provenance" && isset($servicesByResource[$resource]) && $servicesByResource[$resource] instanceof IResourceReadableService) {
                $service = $servicesByResource[$resource];
                if ($type == ExportJob::EXPORT_OPERATION_GROUP) {
                    // service supports filtering by patients so let's do that
                    if ($service instanceof IPatientCompartmentResourceService) {
                        $searchField = $service->getPatientContextSearchField();
                        $searchParams[$searchField->getName()] = implode(",", $patientUuids);
                    }
                }

                $serviceResult = $service->getAll($searchParams);
                // now loop through and grab all of our provenance resources
                if ($serviceResult->hasData()) {
                    foreach ($serviceResult->getData() as $record) {
                        if (!($record instanceof FHIRDomainResource)) {
                            throw new ExportException(self::class . " returned records that are not a valid fhir resource type for this class", 0, $lastResourceIdExported);
                        }
                        // we only want to write out provenance records
                        if (!($record instanceof FHIRProvenance)) {
                            continue;
                        }
                        $writer->append($record);
                        $lastResourceIdExported = $record->getId();
                    }
                }
            }
        }
    }
}
