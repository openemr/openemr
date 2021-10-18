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
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
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
    use FhirBulkExportDomainResourceTrait;

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
        $fhirProvenance->setId($resource->get_fhirElementName() . ":" . $resource->getId());
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
        // TODO: adunsulag check with @sjpadgett or @brady.miller to see if we will always have a primary business entity.
        $primaryBusinessEntity = $fhirOrganizationService->getPrimaryBusinessEntityReference();
        if (empty($primaryBusinessEntity)) {
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

        $searchParams = ['_revinclude' => 'Provenance:target'];
        foreach ($servicesByResource as $resource => $service) {
            // if it doesn't support the readable service we've got issues
            if ($resource == 'Provenance' || !($service instanceof IResourceReadableService)) {
                continue;
            }
            try {
                $serviceResult = $service->getAll($searchParams, $puuidBind);
                // now loop through and grab all of our provenance resources
                if ($serviceResult->hasData()) {
                    foreach ($serviceResult->getData() as $record) {
                        if ($record instanceof FHIRProvenance) {
                            $processingResult->addData($record);
                        }
                    }
                }
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

    /**
     * Given a provenance record id retrieve the provenance record for the given resource and its uuid
     * @param $id string in the format of <resource>:<uuid>
     * @param $puuidBind string The patient uuid we will bind requests to in order to avoid patient data leaking
     * @return ProcessingResult
     */
    private function getProvenanceRecordsForId($id, $puuidBind)
    {
        $processingResult = new ProcessingResult();
        $idParts = explode(":", $id);
        $resourceName = array_shift($idParts);

        $innerId = implode(":", $idParts);
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
}
