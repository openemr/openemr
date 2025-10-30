<?php

/*
 * FhirConditionTrait.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Condition\Trait;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\FHIR\Condition\Enum\FhirConditionCategory;
use Exception;
use DateTime;
use DateTimeZone;

trait FhirConditionTrait
{
    use VersionedProfileTrait;

    protected function populateId(array $dataRecord, FHIRCondition $conditionResource)
    {
        if (isset($dataRecord['uuid'])) {
            $fhirId = new FHIRId();
            $fhirId->setValue($dataRecord['uuid']);
            $conditionResource->setId($fhirId);
        }
    }

    protected function populateMeta(array $dataRecord, FHIRCondition $conditionResource)
    {
        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        if (!empty($dataRecord['last_updated_time'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['last_updated_time']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }

        // Add profile reference for US Core Problems and Health Concerns
        if ($this instanceof IResourceUSCIGProfileService) {
            $profiles = $this->getProfileURIs();
            foreach ($profiles as $profile) {
                $meta->addProfile($profile);
            }
        }
        $conditionResource->setMeta($meta);
    }
    /**
     * Populate the category field of the Condition resource.
     * @param array $dataRecord
     * @param FHIRCondition $conditionResource
     * @param FhirConditionCategory $conditionCategory
     * @return void
     */
    protected function populateCategory(
        array $dataRecord,
        FHIRCondition $conditionResource,
        FhirConditionCategory $category,
        string $defaultSystem = FhirCodeSystemConstants::HL7_CONDITION_CATEGORY
    ) {
        // note the codesystem w/ problem-list-item was deprecated after 3.1.1 so we use the newer terminology codesystem by default
        // but health-concern still uses the old terminology codesystem

        $concept = UtilsService::createCodeableConcept([
            $category->value => [
                'system' => $defaultSystem,
                'code' => $category->value,
                'description' => $category->display()->value
            ]
        ]);
        $concept->setText($category->display()->value);
        $conditionResource->addCategory($concept);
    }


    /**
     * Compute clinical status based on condition data
     */
    protected function computeClinicalStatus($dataRecord): string
    {
        // Check if condition has ended based on enddate
        if ($this->isClinicalStatusInactive($dataRecord)) {
            return 'inactive';
        }

        // Check occurrence and outcome for additional status
        if ($dataRecord['occurrence'] == 1 || $dataRecord['outcome'] == 1) {
            return 'resolved';
        } elseif ($dataRecord['occurrence'] > 1) {
            return 'recurrence';
        }

        // Default to active for ongoing problems
        return 'active';
    }

    protected function isClinicalStatusInactive(array $dataRecord): bool
    {

        try {
            if (!empty($dataRecord['enddate'])) {
                $date = new DateTime($dataRecord['enddate'], new DateTimeZone(date('P')));
                $now = new DateTime('now', new DateTimeZone(date('P')));
                if ($date < $now) {
                    return true;
                }
            }
        } catch (Exception) {
        }
        return false;
    }

    protected function populateCode($dataRecord, FHIRCondition $conditionResource, string $defaultText)
    {
        if (!empty($dataRecord['diagnosis']) && is_array($dataRecord['diagnosis'])) {
            $diagnosisCoding = new FHIRCoding();
            $diagnosisCode = new FHIRCodeableConcept();

            foreach ($dataRecord['diagnosis'] as $code => $codeValues) {
                if (!is_string($code)) {
                    $code = "$code"; // FHIR expects a string
                }
                $diagnosisCoding->setCode($code);
                $diagnosisCoding->setDisplay($codeValues['description']);
                $diagnosisCoding->setSystem($codeValues['system']);
                $diagnosisCode->addCoding($diagnosisCoding);
            }
            $conditionResource->setCode($diagnosisCode);
        } else {
            // Fallback to title if no structured diagnosis
            $diagnosisCode = new FHIRCodeableConcept();
            $diagnosisCode->setText($dataRecord['title'] ?? 'Problem');
            $conditionResource->setCode($diagnosisCode);
        }
    }

    protected function populateSubject($dataRecord, FHIRCondition $conditionResource)
    {
        if (isset($dataRecord['puuid'])) {
            $patient = new FHIRReference();
            $patient->setReference('Patient/' . $dataRecord['puuid']);
            $conditionResource->setSubject($patient);
        }
    }

    protected function populateClinicalStatus($dataRecord, FHIRCondition $conditionResource)
    {
        $clinicalStatus = $this->computeClinicalStatus($dataRecord);
        $conditionResource->setClinicalStatus(UtilsService::createCodeableConcept([
            $clinicalStatus => [
                'system' => "http://terminology.hl7.org/CodeSystem/condition-clinical",
                'code' => $clinicalStatus,
                'description' => ucwords((string) $clinicalStatus),
            ]
        ]));
    }

    protected function populateVerificationStatus($dataRecord, FHIRCondition $conditionResource)
    {
        $verificationStatus = $this->computeVerificationStatus($dataRecord);
        $conditionResource->setVerificationStatus(UtilsService::createCodeableConcept([
            $verificationStatus => [
                'system' => "http://terminology.hl7.org/CodeSystem/condition-ver-status",
                'code' => $verificationStatus,
                'description' => ucwords(str_replace('-', ' ', $verificationStatus)),
            ]
        ]));
    }


    /**
     *  AI Generated
     * @param $dataRecord
     * @param FHIRCondition $conditionResource
     * @return void
     */
    protected function populateRecordedDate($dataRecord, FHIRCondition $conditionResource): void
    {
        // Use encounter date as recorded date for encounter diagnoses
        $recordedDate = $dataRecord['date'] ?? $dataRecord['begdate'];
        if ($recordedDate) {
            $conditionResource->setRecordedDate(UtilsService::getLocalDateAsUTC($recordedDate));
        }
    }
    // end AI Generated



    protected function populateOnsetDateTime($dataRecord, FHIRCondition $conditionResource)
    {
        if (!empty($dataRecord['begdate'])) {
            $conditionResource->setOnsetDateTime(UtilsService::getLocalDateAsUTC($dataRecord['begdate']));
        }
    }

    protected function populateAssertedDate($dataRecord, FHIRCondition $conditionResource)
    {
        if (!empty($dataRecord['date'])) { // created date
            $fhirExtension = new FHIRExtension();
            $fhirExtension->setUrl('http://hl7.org/fhir/StructureDefinition/condition-assertedDate');
            $fhirExtension->setValueDateTime(UtilsService::getLocalDateAsUTC($dataRecord['date']));
            $conditionResource->addExtension($fhirExtension);
        }
    }

    protected function populateAbatementDateTime($dataRecord, FHIRCondition $conditionResource)
    {
        if (!empty($dataRecord['enddate'])) {
            $conditionResource->setAbatementDateTime(UtilsService::getLocalDateAsUTC($dataRecord['enddate']));
        }
    }

    protected function populateNote($dataRecord, FHIRCondition $conditionResource)
    {
        if (!empty($dataRecord['comments'])) {
            $note = new \OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation();
            $note->setText($dataRecord['comments']);
            $conditionResource->addNote($note);
        }
    }

    /**
     * Compute verification status
     */
    protected function computeVerificationStatus($dataRecord): string
    {
        // For encounter diagnoses, default to confirmed unless specified otherwise
        if (!empty($dataRecord['verification'])) {
            return $dataRecord['verification'];
        }
        return 'confirmed';
    }
}
