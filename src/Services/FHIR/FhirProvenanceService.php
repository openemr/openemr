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
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRProvenance\FHIRProvenanceAgent;
use OpenEMR\Services\Search\ReferenceSearchValue;

class FhirProvenanceService extends FhirServiceBase implements IResourceUSCIGProfileService
{
    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-provenance';

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

        $targetReference = new FHIRReference();
        $targetReference->setType($resource->get_fhirElementName());
        $targetReference->setReference($resource->get_fhirElementName() . "/" . $resource->getId());

        $fhirProvenance = new FHIRProvenance();
        $fhirProvenance->addTarget($targetReference);

        // we are only going to provide the meta if we have it
        if (!empty($resource->getMeta())) {
            $fhirProvenance->setRecorded($resource->getMeta()->getLastUpdated());
        }

        $agent = new FHIRProvenanceAgent();
        $agentConcept = new FHIRCodeableConcept();
        $agentConceptCoding = new FHIRCoding();
        $agentConceptCoding->setSystem("http://terminology.hl7.org/CodeSystem/provenance-participant-type");
        $agentConceptCoding->setCode("author");
        $agentConceptCoding->setDisplay(xlt("Author"));
        $agentConcept->addCoding($agentConceptCoding);
        $agent->setType($agentConcept);

        $orgReference = null;
        $whoReference = null;

        if (!empty($userWHO)) {
            $whoReference = $userWHO;

            // attempt to get the org for our agent
            $searchValue = ReferenceSearchValue::createFromRelativeUri($userWHO->getReference());
            $fhirOrganizationService = new FhirOrganizationService();
            $orgReference = $fhirOrganizationService->getOrganizationReferenceForUser($searchValue->getId());
        }

        if (empty($orgReference)) {
            // easiest provenance is to make the primary business entity organization be the author of the provenance
            // resource.
            $fhirOrganizationService = new FhirOrganizationService();
            // TODO: adunsulag check with @sjpadgett or @brady.miller to see if we will always have a primary business entity.
            $orgReference = $fhirOrganizationService->getPrimaryBusinessEntityReference();
            $whoReference = $orgReference; // if we didn't get an org reference from our WHO we will overwrite our WHO
        }
        if (!empty($orgReference)) {
            $fhirProvenance->setId($orgReference->getId());
            $agent->setWho($whoReference);
            $agent->setOnBehalfOf($orgReference);
        } else {
            (new SystemLogger())->debug(self::class . "->createProvenanceForDomainResource() could not find organization reference");
            return null;
        }
        $fhirProvenance->addAgent($agent);
        return $fhirProvenance;
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        // TODO: Implement loadSearchParameters() method.
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
        // TODO: Implement parseOpenEMRRecord() method.
    }

    /**
     * Parses a FHIR Resource, returning the equivalent OpenEMR record.
     *
     * @param $fhirResource The source FHIR resource
     * @return a mapped OpenEMR data record (array)
     */
    public function parseFhirResource($fhirResource = array())
    {
        // TODO: Implement parseFhirResource() method.
    }

    /**
     * Inserts an OpenEMR record into the sytem.
     * @return The OpenEMR processing result.
     */
    protected function insertOpenEMRRecord($openEmrRecord)
    {
        // TODO: Implement insertOpenEMRRecord() method.
    }

    /**
     * Updates an existing OpenEMR record.
     * @param $fhirResourceId The OpenEMR record's FHIR Resource ID.
     * @param $updatedOpenEMRRecord The "updated" OpenEMR record.
     * @return The OpenEMR Service Result
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        // TODO: Implement updateOpenEMRRecord() method.
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters)
    {
        // TODO: Implement searchForOpenEMRRecords() method.
    }

    /**
     * Creates the Provenance resource  for the equivalent FHIR Resource
     *
     * @param $dataRecord The source OpenEMR data record
     * @param $encode Indicates if the returned resource is encoded into a string. Defaults to True.
     * @return the FHIR Resource. Returned format is defined using $encode parameter.
     */
    public function createProvenanceResource($dataRecord, $encode = false)
    {
        // TODO: Implement createProvenanceResource() method.
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
