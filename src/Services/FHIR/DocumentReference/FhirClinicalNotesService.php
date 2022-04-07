<?php

/**
 * FhirClinicalNotesService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\DocumentReference;

use Monolog\Utils;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentReference;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceContent;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDocumentReference\FHIRDocumentReferenceContext;
use OpenEMR\RestControllers\FHIR\FhirDocumentReferenceRestController;
use OpenEMR\Services\ClinicalNotesService;
use OpenEMR\Services\FHIR\a;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Indicates;
use OpenEMR\Services\FHIR\OpenEMR;
use OpenEMR\Services\FHIR\openEMRSearchParameters;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;
use Twig\Token;

class FhirClinicalNotesService extends FhirServiceBase
{
    use FhirServiceBaseEmptyTrait;
    use PatientSearchTrait;

    /**
     * @var ClinicalNotesService
     */
    private $service;

    const CATEGORY = 'clinical-note';

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new ClinicalNotesService();
    }

    public function supportsCategory($category)
    {
        return $category == self::CATEGORY;
    }


    public function supportsCode($code)
    {
        return $this->service->isValidClinicalNoteCode($code);
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'type' => new FhirSearchParameterDefinition('type', SearchFieldType::TOKEN, ['code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $docReference = new FHIRDocumentReference();
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(gmdate('c'));
        $docReference->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $docReference->setId($id);

        $identifier = new FHIRIdentifier();
        $identifier->setValue(new FHIRString($dataRecord['uuid']));
        $docReference->addIdentifier($identifier);

        if (!empty($dataRecord['date'])) {
            $docReference->setDate(gmdate('c', strtotime($dataRecord['date'])));
        } else {
            $docReference->setDate(UtilsService::createDataMissingExtension());
        }

        if (!empty($dataRecord['euuid'])) {
            $context = new FHIRDocumentReferenceContext();

            // we currently don't track anything dealing with start and end date for the context
            if (!empty($dataRecord['encounter_date'])) {
                $period = new FHIRPeriod();
                $period->setStart(gmdate('c', strtotime($dataRecord['encounter_date'])));
                $context->setPeriod($period);
            }
            $context->addEncounter(UtilsService::createRelativeReference('Encounter', $dataRecord['euuid']));
            $docReference->setContext($context);
        }

        // populate our clinical narrative notes
        if (!empty($dataRecord['description'])) {
            $content = new FHIRDocumentReferenceContent();
            $attachment = new FHIRAttachment();
            $attachment->setContentType("text/plain");
            $attachment->setData(base64_encode($dataRecord['description']));
            $content->setAttachment($attachment);
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

        $docReference->addCategory(UtilsService::createCodeableConcept([
            'clinical-note' => ['code' => 'clinical-note', 'description' => 'Clinical Note', 'system' => FhirCodeSystemConstants::DOCUMENT_REFERENCE_CATEGORY]
        ]));

        $fhirOrganizationService = new FhirOrganizationService();
        $orgReference = $fhirOrganizationService->getPrimaryBusinessEntityReference();
        $docReference->setCustodian($orgReference);

        if (!empty($dataRecord['user_uuid'])) {
            if (!empty($dataRecord['npi'])) {
                $docReference->addAuthor(UtilsService::createRelativeReference('Practitioner', $dataRecord['user_uuid']));
            } else {
                // if we don't have a practitioner reference then it is the business owner that will be the author on
                // the clinical notes
                $docReference->addAuthor($orgReference);
            }
        }

        if (!empty($dataRecord['activity'])) {
            if ($dataRecord['activity'] == ClinicalNotesService::ACTIVITY_ACTIVE) {
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

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     * @param openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return OpenEMR records
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        if (isset($openEMRSearchParameters['code']) && $openEMRSearchParameters['code'] instanceof TokenSearchField) {
            $openEMRSearchParameters['code']->transformValues(function (TokenSearchValue $val) {
                // TODO: @adunsulag I don't like this, is there a way we can mark the code system we are using that will prefix the value
                // automatically?
                $val->setCode("LOINC:" . $val->getCode());
                return $val;
            });
        }
        // we know category is clinical-notes here and we remove it before dropping to the lower level as it doesn't
        // understand category.
        if (isset($openEMRSearchParameters['category'])) {
            unset($openEMRSearchParameters['category']);
        }
        // only return notes that have no category specified, otherwise we are going to use diagnostic report
        $openEMRSearchParameters['clinical_notes_type'] = new TokenSearchField(
            'clinical_notes_category',
            new TokenSearchValue(true)
        );
        $openEMRSearchParameters['clinical_notes_type']->setModifier(SearchModifier::MISSING);
        return $this->service->search($openEMRSearchParameters);
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
