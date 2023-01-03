<?php

/**
 * FhirPatientDocumentReferenceService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\DocumentReference;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUrl;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceContent;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceContext;
use OpenEMR\Services\DocumentService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirPatientDocumentReferenceService extends FhirServiceBase
{
    use FhirServiceBaseEmptyTrait;
    use PatientSearchTrait;

    /**
     * @var DocumentService
     */
    private $service;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new DocumentService();
    }


    public function supportsCategory($category)
    {
        // we have no category definitions right now for patient
        return false;
    }


    public function supportsCode($code)
    {
        // we don't support searching by code
        return false;
    }

    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        if (isset($openEMRSearchParameters['category'])) {
            // we have nothing with this category so we are going to return nothing as we never should have gotten here

            unset($openEMRSearchParameters['category']);
        }
        if (isset($openEMRSearchParameters['patient'])) {
            // make sure that no other modifier such as NOT_EQUALS, OR missing=true is sent which would let system file names be
            // leaked out in the API
            $openEMRSearchParameters['patient']->setModifier(SearchModifier::EXACT);
        } else {
            // make sure we only return documents that are tied to patients
            $openEMRSearchParameters['patient'] = new TokenSearchField('puuid', [new TokenSearchValue(false, null)]);
            $openEMRSearchParameters['patient']->setModifier(SearchModifier::MISSING);
        }
        return $this->service->search($openEMRSearchParameters);
    }

    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $docReference = new FHIRDocumentReference();
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        $docReference->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $docReference->setId($id);

        $identifier = new FHIRIdentifier();
        $identifier->setValue(new FHIRString($dataRecord['uuid']));
        $docReference->addIdentifier($identifier);

        // TODO: @adunsulag need to support content.attachment.url

        if (!empty($dataRecord['date'])) {
            $docReference->setDate(UtilsService::getLocalDateAsUTC($dataRecord['date']));
        } else {
            $docReference->setDate(UtilsService::createDataMissingExtension());
        }

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

        if (!empty($dataRecord['puuid'])) {
            $docReference->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        } else {
            $docReference->setSubject(UtilsService::createDataMissingExtension());
        }

        if (!empty($dataRecord['codes'])) {
            foreach ($dataRecord['codes'] as $code => $codeableConcept) {
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

        if (!empty($dataRecord['deleted'])) {
            if ($dataRecord['deleted'] != 1) {
                $docReference->setStatus("current");
            } else {
                $docReference->setStatus("entered-in-error");
            }
        } else {
            $docReference->setStatus('current');
        }

        if (!empty($dataRecord['code'])) {
            $type = UtilsService::createCodeableConcept($dataRecord['code'], FhirCodeSystemConstants::LOINC, $dataRecord['codetext']);
            $docReference->setType($type);
        } else {
            $docReference->setType(UtilsService::createNullFlavorUnknownCodeableConcept());
        }

        return $docReference;
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
