<?php
/*
 * FhirDocumentReferenceTrait.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\DocumentReference\Trait;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUrl;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceContent;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceContext;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\FHIR\UtilsService;

trait FhirDocumentReferenceTrait {
    use FhirServiceBaseEmptyTrait;
    use VersionedProfileTrait;

    const US_CORE_PROFILE = "http://hl7.org/fhir/us/core/StructureDefinition/us-core-documentreference";

    public function getProfileUrl(): string
    {
        return self::US_CORE_PROFILE;
    }
    public function populateProfile(FHIRMeta $docReference, array $dataRecord): void
    {
        $profileUrlSet = $this->getProfileForVersions($this->getProfileUrl(), $this->getSupportedVersions());
        foreach ($profileUrlSet as $profile) {
            $docReference->addProfile(new FHIRCanonical($profile));
        }
    }
    public function populateMetaData(FHIRDocumentReference $docReference, array $dataRecord)
    {
        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId('1');
        if (!empty($dataRecord['date'])) {
            $fhirMeta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['date']));
        } else {
            $fhirMeta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $this->populateProfile($fhirMeta, $dataRecord);
        $docReference->setMeta($fhirMeta);
    }

    public function populateId(FHIRDocumentReference $docReference, array $dataRecord)
    {
        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $docReference->setId($id);
    }

    public function populateIdentifiers(FHIRDocumentReference $docReference, array $dataRecord)
    {
        $identifier = new FHIRIdentifier();
        $identifier->setValue(new FHIRString($dataRecord['uuid']));
        // using RFC 3986 URN as the system for the document reference identifier since we have no
        // other business identifier and these are globally unique
        $identifier->setSystem(FhirCodeSystemConstants::RFC_3986);
        $docReference->addIdentifier($identifier);
    }

    public function populateDate(FHIRDocumentReference $docReference, array $dataRecord)
    {
        if (!empty($dataRecord['date'])) {
            $docReference->setDate(UtilsService::getLocalDateAsUTC($dataRecord['date']));
        } else {
            $docReference->setDate(UtilsService::createDataMissingExtension());
        }
    }

    public function populateContext(FHIRDocumentReference $docReference, array $dataRecord)
    {
        if (!empty($dataRecord['euuid'])) {
            $context = new FHIRDocumentReferenceContext();

            // we currently don't track anything dealing with start and end date for the context
            if (!empty($dataRecord['encounter_date'])) {
                $period = new FHIRPeriod();
                $period->setStart(UtilsService::getLocalDateAsUTC($dataRecord['encounter_date']));
                $context->setPeriod($period);
            }
            $context->addEncounter(UtilsService::createRelativeReference('Encounter', $dataRecord['euuid']));
            $docReference->setContext($context);
        }
    }

    public function populateContent(FHIRDocumentReference $docReference, array $dataRecord): void {
        // populate the link to download the patient document
        if (!empty($dataRecord['uuid'])) {
            $url = $this->getFhirApiURL() . '/fhir/Binary/' . $dataRecord['uuid'];
            $content = new FHIRDocumentReferenceContent();
            $attachment = new FHIRAttachment();
            $attachment->setContentType($dataRecord['mimetype']);
            $attachment->setUrl(new FHIRUrl($url));
            $attachment->setTitle($dataRecord['name'] ?? '');
            $content->setAttachment($attachment);
            // TODO: if we support tagging a specific document with a reference code we can put that here.
            // since it's plain text we have no other interpretation so we just use the mime type sufficient IHE Format code
            $contentCoding = UtilsService::createCoding(
                "urn:ihe:iti:xds:2017:mimeTypeSufficient",
                "mimeType Sufficient",
                FhirCodeSystemConstants::IHE_FORMATCODE_CODESYSTEM
            );
            $content->setFormat($contentCoding);
            $docReference->addContent($content);
        } else {
            // need to support data missing if its not there.
            $docReference->addContent(UtilsService::createDataMissingExtension());
        }
    }

    public function populateSubject(FHIRDocumentReference $docReference, array $dataRecord): void
    {
        if (!empty($dataRecord['puuid'])) {
            $docReference->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        } else {
            $docReference->setSubject(UtilsService::createDataMissingExtension());
        }
    }

    protected function populateCategories(FHIRDocumentReference $docReference, array $dataRecord): void
    {

        if (!empty($dataRecord['codes'])) {
            foreach ($dataRecord['codes'] as $codeableConcept) {
                $docReference->addCategory(UtilsService::createCodeableConcept($codeableConcept));
            }
        } else {
            if (!empty($dataRecord['category_name'])) {
                $concept = new FHIRCodeableConcept();
                $concept->setText($dataRecord['category_name']);
                $docReference->addCategory($concept);
            } else {
                // although the category is extensible, ONC inferno fails to validate with an extended code set so we are
                // going to create data absent reasons.  The codes come from the document categories codes column.  If we are
                // missing the codes we will just go with a Data Absent Reason (DAR)
                $docReference->addCategory(UtilsService::createDataAbsentUnknownCodeableConcept());
            }
        }
    }

    public function populateAuthor(FHIRDocumentReference $docReference, array $dataRecord): void
    {
        $fhirOrganizationService = new FhirOrganizationService();
        $orgReference = $fhirOrganizationService->getPrimaryBusinessEntityReference();
        $docReference->setCustodian($orgReference);

        // if we don't have a practitioner reference then it is the business owner that will be the author on
        // the clinical notes
        $authorReference = $orgReference;
        if (!empty($dataRecord['user_uuid'])) {
            if (!empty($dataRecord['user_npi'])) {
                $authorReference = UtilsService::createRelativeReference('Practitioner', $dataRecord['user_uuid']);
            }
        }

        if (!empty($authorReference)) {
            $docReference->addAuthor($authorReference);
        }
    }

    public function populateStatus(FhirDocumentReference $docReference, array $dataRecord): void
    {
        if (!empty($dataRecord['deleted'])) {
            if ($dataRecord['deleted'] != 1) {
                $docReference->setStatus("current");
            } else {
                $docReference->setStatus("entered-in-error");
            }
        } else {
            $docReference->setStatus('current');
        }
    }

    public function populateType(FHIRDocumentReference $docReference, array $dataRecord): void
    {
        if (!empty($dataRecord['code'])) {
            $type = UtilsService::createCodeableConcept($dataRecord['code'], FhirCodeSystemConstants::LOINC, $dataRecord['codetext']);
            $docReference->setType($type);
        } else {
            $docReference->setType(UtilsService::createNullFlavorUnknownCodeableConcept());
        }
    }

    protected function parseOpenEMRRecordIntoFHIRDocumentReference($dataRecord = []): FHIRDocumentReference
    {
        $docReference = new FHIRDocumentReference();
        $this->populateMetaData($docReference, $dataRecord);
        $this->populateId($docReference, $dataRecord);
        $this->populateIdentifiers($docReference, $dataRecord);
        $this->populateDate($docReference, $dataRecord);
        $this->populateContext($docReference, $dataRecord);
        $this->populateContent($docReference, $dataRecord);
        $this->populateSubject($docReference, $dataRecord);
        $this->populateCategories($docReference, $dataRecord);
        $this->populateAuthor($docReference, $dataRecord);
        $this->populateStatus($docReference, $dataRecord);
        $this->populateType($docReference, $dataRecord);

        return $docReference;
    }

    /**
     * @param $dataRecord
     * @param $encode
     * @return FHIRDocumentReference|string
     * @throws \JsonException
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $resource = $this->parseOpenEMRRecordIntoFHIRDocumentReference($dataRecord);
        if ($encode) {
            return $this->encodeResource($resource);
        } else {
            return $resource;
        }
    }

    /**
     * @param FHIRDocumentReference $resource
     * @return string
     * @throws \JsonException
     */
    protected function encodeResource(FHIRDocumentReference $resource): string
    {
        return json_encode($resource, JSON_THROW_ON_ERROR);
    }

    public function createProvenanceResource($dataRecord, $encode = false)
    {
        if (!($dataRecord instanceof FHIRDocumentReference)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }

        $fhirProvenanceService = new FhirProvenanceService();
        $authors = $dataRecord->getAuthor();
        $author = null;
        if (!empty($authors)) {
            $author = reset($authors); // grab the first one, as we only populate one anyways.
        }
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord, $author);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }
}
