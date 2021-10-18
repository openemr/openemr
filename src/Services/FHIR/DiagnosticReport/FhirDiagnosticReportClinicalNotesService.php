<?php

/**
 * FhirDiagnosticReportClinicalNotesService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\DiagnosticReport;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDiagnosticReport;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\Services\ClinicalNotesService;
use OpenEMR\Services\CodeTypesService;
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
use OpenEMR\Services\ListService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirDiagnosticReportClinicalNotesService extends FhirServiceBase
{
    use FhirServiceBaseEmptyTrait;
    use PatientSearchTrait;

    /**
     * @var ClinicalNotesService
     */
    private $service;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new ClinicalNotesService();
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('type', SearchFieldType::TOKEN, ['code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category_code']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    public function supportsCategory($category)
    {
        $loincCategory = "LOINC:" . $category;
        $listService = new ListService();
        $options = $listService->getOptionsByListName('Clinical_Note_Category', ['notes' => $loincCategory]);
        return !empty($options);
    }


    public function supportsCode($code)
    {
        return $this->service->isValidClinicalNoteCode($code);
    }

    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $report = new FHIRDiagnosticReport();
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(gmdate('c'));
        $report->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $report->setId($id);

        if (!empty($dataRecord['date'])) {
            $date = gmdate('c', strtotime($dataRecord['date']));
            $report->setEffectiveDateTime(new FHIRDateTime($date));
            $report->setIssued(new FHIRInstant($date));
        } else {
            $report->setDate(UtilsService::createDataMissingExtension());
        }

        if (!empty($dataRecord['euuid'])) {
            $report->setEncounter(UtilsService::createRelativeReference('Encounter', $dataRecord['euuid']));
        }

        $fhirOrganizationService = new FhirOrganizationService();

        if (!empty($dataRecord['user_uuid'])) {
            if (!empty($dataRecord['user_npi'])) {
                $report->addPerformer(UtilsService::createRelativeReference('Practitioner', $dataRecord['user_uuid']));
            } else {
                $orgReference = $fhirOrganizationService->getOrganizationReferenceForUser($dataRecord['user_uuid']);
                $report->addPerformer($orgReference);
            }
        } else {
            $report->addPerformer($fhirOrganizationService->getPrimaryBusinessEntityReference());
        }

        // populate our clinical narrative notes
        if (!empty($dataRecord['description'])) {
            $attachment = new FHIRAttachment();
            $attachment->setContentType("text/plain");
            $attachment->setData(base64_encode($dataRecord['description']));
            $report->addPresentedForm($attachment);
        } else {
            // need to support data missing if its not there.
            $report->addPresentedForm(UtilsService::createDataMissingExtension());
        }

        if (!empty($dataRecord['puuid'])) {
            $report->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        }

        $codeTypesService = new CodeTypesService();
        $codeParts = $codeTypesService->parseCode($dataRecord['category_code']);
        $code = $codeParts['code'];

        $category = UtilsService::createCodeableConcept([
            $code => [
                'code' => $code
                ,'description' => $dataRecord['category_title']
                ,'system' => $codeTypesService->getSystemForCodeType($codeParts['code_type'])
            ]
        ], FhirCodeSystemConstants::LOINC); // we default to LOINC if we don't have a valid type

        $report->addCategory($category);

        if (!empty($dataRecord['status'])) {
            $report->setStatus($dataRecord['status']);
        } else {
            $report->setStatus('final');
        }

        if (!empty($dataRecord['code'])) {
            $code = UtilsService::createCodeableConcept($dataRecord['code'], FhirCodeSystemConstants::LOINC, $dataRecord['codetext']);
            $report->setCode($code);
        } else {
            $report->setCode(UtilsService::createNullFlavorUnknownCodeableConcept());
        }

        return $report;
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
            $openEMRSearchParameters['code']->transformValues([$this, 'addLOINCPrefix']);
        }

        if (isset($openEMRSearchParameters['category_code']) && $openEMRSearchParameters['category_code'] instanceof TokenSearchField) {
            $openEMRSearchParameters['category_code']->transformValues([$this, 'addLOINCPrefix']);
        } else {
            // we need to make sure we only include things with a category code in our clinical notes
            $openEMRSearchParameters['category_code'] = new TokenSearchField('category_code', [new TokenSearchValue(false)]);
            $openEMRSearchParameters['category_code']->setModifier(SearchModifier::MISSING);
        }
        return $this->service->search($openEMRSearchParameters);
    }

    public function addLOINCPrefix(TokenSearchValue $val)
    {
        // TODO: @adunsulag I don't like this, is there a way we can mark the code system we are using that will prefix the value
        // already?
        $val->setCode("LOINC:" . $val->getCode());
        return $val;
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
        if (!($dataRecord instanceof FHIRDiagnosticReport)) {
            throw new \BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $performer = null;
        if (!empty($dataRecord->getPerformer())) {
            $performer = current($dataRecord->getPerformer());
        }
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord, $performer);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }
}
