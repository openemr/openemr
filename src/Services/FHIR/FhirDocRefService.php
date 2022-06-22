<?php

/**
 * FhirDocRefService handles the creation / retrieve of Clinical Summary of Care (CCD) documents for a patient.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\System\System;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Events\PatientDocuments\PatientDocumentCreateCCDAEvent;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDocumentReference;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationOutcome;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueSeverity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueType;
use OpenEMR\FHIR\R4\FHIRResource\FHIROperationOutcome\FHIROperationOutcomeIssue;
use OpenEMR\Services\CDADocumentService;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FHIR\DocumentReference\FhirPatientDocumentReferenceService;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\Traits\ResourceServiceSearchTrait;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\FHIRSearchFieldFactory;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ReferenceSearchField;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;
use Ramsey\Uuid\Uuid;

// TODO: @adunsulag look at putting this into its own operations folder
class FhirDocRefService
{
    use ResourceServiceSearchTrait;
    use PatientSearchTrait;

    private $resourceSearchParameters;

    const LOINC_CCD_CLINICAL_SUMMARY_OF_CARE = "34133-9";

    public function __construct()
    {
        $this->resourceSearchParameters = $this->loadSearchParameters();
        $searchFieldFactory = new FHIRSearchFieldFactory($this->resourceSearchParameters);
        $this->setSearchFieldFactory($searchFieldFactory);
    }

    /**
     * Returns an array mapping FHIR Coverage Resource search parameters to OpenEMR Condition search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'start' => new FhirSearchParameterDefinition('start', SearchFieldType::DATETIME, ['start']),
            'end' => new FhirSearchParameterDefinition('end', SearchFieldType::DATETIME, ['end']),
            'type' => new FhirSearchParameterDefinition('type', SearchFieldType::TOKEN, ['type']),
        ];
    }

    /**
     * @param $searchParams
     * @param $puuidBind
     * @return ProcessingResult
     * @throws SearchFieldException if there is an invalid search field parameter
     * @throws \Exception If another system exception occurs
     */
    public function getAll($searchParams, $puuidBind): ProcessingResult
    {
        $fhirSearchResult = new ProcessingResult();
        $oeSearchParameters = $this->createOpenEMRSearchParameters($searchParams, $puuidBind);
        $type = $oeSearchParameters['type'] ?? $this->createDefaultType();
        // if type != 'CCD LOINC' then return no data
        if ($this->isValidType($type)) {
            $oeSearchParameters['type'] = $type;
        } else {
            throw new SearchFieldException("type", "Unsupported code for parameter");
        }

        // if no start & end, return current CCD
        if ($this->shouldReturnMostCurrentCCD($oeSearchParameters)) {
            $documentReference = $this->getMostCurrentCCDReference($oeSearchParameters, $fhirSearchResult);
        } else {
            // else
            // generate CCD using start & end
            $documentReference = $this->generateCCD($oeSearchParameters);
        }
        $fhirSearchResult->addData($documentReference);
        return $fhirSearchResult;
    }

    private function createDefaultType()
    {
        return new TokenSearchField('type', [self::LOINC_CCD_CLINICAL_SUMMARY_OF_CARE]);
    }

    private function isValidType(TokenSearchField $type)
    {

        if ($type->hasCodeValue(self::LOINC_CCD_CLINICAL_SUMMARY_OF_CARE)) {
            return true;
        }
        return false;
    }

    private function shouldReturnMostCurrentCCD($oeSearchparameters): bool
    {
        // if start and end date is empty then we return the most current ccd
        return empty($oeSearchparameters['start']) && empty($oeSearchparameters['end']);
    }

    private function getPatientRecordForSearchParameters($oeSearchParameters): array
    {
        $mappedField = reset($this->getPatientContextSearchField()->getMappedFields()); // grab the first one
        $searchPatient = $oeSearchParameters[$mappedField->getField()];

        // we only allow one patient CCD to be generated at a time.
        if (empty($searchPatient->getValues()) || count($searchPatient->getValues()) > 1) {
            throw new SearchFieldException($mappedField->getField(), "Field is required and cardinality is 1..1");
        }

        $fhirPatientService = new FhirPatientService();

        $field = current($fhirPatientService->getPatientContextSearchField()->getMappedFields())->getField();
        $newSearchField = new ReferenceSearchField($field, $searchPatient->getValues(), true);

        $patientService = new PatientService();
        $patient = $patientService->search([$field => $newSearchField])->getData() ?? null;
        if (empty($patient)) {
            (new SystemLogger())->errorLogCaller("Failed to find patient with uuid", ['uuids' => $searchPatient->getValues()]);
            throw new SearchFieldException($field, "Invalid argument");
        } else {
            $patient = $patient[0];
        }
        return $patient;
    }

    /**
     * At some point we could return an already generated CCD, but we generate a CCD that covers the patient's entire
     * medical history as their most current
     * @param $oeSearchParameters
     * @return FHIRDocumentReference
     * @throws \Exception
     */
    private function getMostCurrentCCDReference($oeSearchParameters): FHIRDocumentReference
    {
        // let's grab our patient
        // while these are right now 100% the same field names, the underlying data models can change so we have to handle that.
        $patient = $this->getPatientRecordForSearchParameters($oeSearchParameters);

        // document create event
        $event = new PatientDocumentCreateCCDAEvent($patient['pid']);

        // apparently the PHP download CCDA just sends over every single section/component regardless of whether its used or not
        // the node server on the backend is what distinguishes between what sections to include / exclude based on the
        // document type that is sent.
        // TODO: if we ever decide to actually put the filtering on the PHP side we will need to adjust this.
//        $event->addSection("continuity_care_document"); // make it a CCD
//        $event->addComponent("vitals");
//        $event->addComponent("social_history");
        return $this->getDocumentReferenceForCCDAEvent($event);
    }

    private function generateCCD($oeSearchParameters): FHIRDocumentReference
    {
        $patient = $this->getPatientRecordForSearchParameters($oeSearchParameters);
        $event = new PatientDocumentCreateCCDAEvent($patient['pid']);

        if (!empty($oeSearchParameters['start'])) {
            $dateFrom = $oeSearchParameters['start'];
            if (!($dateFrom instanceof DateSearchField)) {
                throw new SearchFieldException("start", "start parameter could not be parsed as a valid date");
            }
            if (empty($dateFrom->getValues())) {
                throw new SearchFieldException("start", "start parameter could not be parsed as a valid date");
            }
            // getValue() returns a DatePeriod object
            // TODO: if we implement FHIR support for dates we will pass this along
            $startDate = current($dateFrom->getValues())->getValue()->getStartDate();
            $event->setDateFrom($startDate);
        }

        if (!empty($oeSearchParameters['end'])) {
            $dateTo = $oeSearchParameters['end'];
            if (!($dateTo instanceof DateSearchField)) {
                throw new SearchFieldException("start", "start parameter could not be parsed as a valid date");
            }
            if (empty($dateTo->getValues())) {
                throw new SearchFieldException("start", "start parameter could not be parsed as a valid date");
            }
            // TODO: if we implement FHIR support for dates we will pass this along
            $endDate = current($dateTo->getValues())->getValue()->getEndDate();
            $event->setDateTo($endDate);
        }
        return $this->getDocumentReferenceForCCDAEvent($event);
    }

    private function getDocumentReferenceForCCDAEvent(PatientDocumentCreateCCDAEvent $event)
    {
        // this creates our CCDA
        $createdEvent = $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, PatientDocumentCreateCCDAEvent::EVENT_NAME_CCDA_CREATE);
        if (empty($createdEvent->getCcdaId())) {
            // TODO: handle the case where nothing was generated
            throw new \Exception("Failed to generate ccda");
        }
        // should only be one
        $docs = \Document::getDocumentsForForeignReferenceId('ccda', $createdEvent->getCcdaId());
        if (!empty($docs)) {
            $doc = $docs[0];
        } else {
            throw new \Exception("Document did not exist for ccda table with id " . $createdEvent->getCcdaId());
        }

        // a bit annoying but it makes sure we have just a central place for how our doc reference is generated
        // and that it is always the same data
        $patientDocService = new FhirPatientDocumentReferenceService();
        $docStringUuid = UuidRegistry::uuidToString($doc->get_uuid());
        $result = $patientDocService->getOne($docStringUuid, $patient['uuid']); // make sure we don't deviate from the patient

        if (!$result->hasData()) {
            throw new \Exception("Fhir DocumentReference resource could not be found for uuid");
        }
        return $result->getData()[0];
    }
}
