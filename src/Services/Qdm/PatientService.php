<?php

namespace OpenEMR\Services\Qdm;

use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\Diagnosis;
use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Cqm\Qdm\PatientCharacteristicBirthdate;
use OpenEMR\Services\PatientService as BaseService;

class PatientService extends BaseService
{
    public function makePatient($pid)
    {
        $patient = $this->findByPid($pid);
        $qdmPatient = new Patient([
            'birthDatetime' => $patient['DOB'],
            'bundleId' => 1,
            '_fullName' => $patient['fname'] . ' ' . $patient['lname'],
            '_openEmrPid' => $patient['pid']
        ]);

        $patientCharacteristic = new PatientCharacteristicBirthdate([
            'birthDatetime' => $patient['DOB'], // TODO should format include timezone offset and actual "time"
            'dataElementCodes' => [
                new Code([
                    'code' => '21112-8',
                    'system' => '2.16.840.1.113883.6.1'
                ])
            ]
        ]);

        $conditionService = new ConditionService();
        $conditions = $conditionService->fetchAllByPid($pid);
        foreach ($conditions as $condition) {
            $qdmCondition = $conditionService->makeQdmModel($condition);
            $qdmPatient->add_data_element($qdmCondition);
        }

        $allergyIntoleranceService = new AllergyIntoleranceService();
        $allergies = $allergyIntoleranceService->fetchAllByPid($pid);
        foreach ($allergies as $allergy) {
            $qdmAllergy = $allergyIntoleranceService->makeQdmModel($allergy);
            $qdmPatient->add_data_element($qdmAllergy);
        }

        $encounterService = new EncounterService();
        $encounters = $encounterService->fetchAllByPid($pid);
        foreach ($encounters as $encounter) {
            $qdmEncounterPerformed = $encounterService->makeQdmModel($encounter);
            $qdmPatient->add_data_element($qdmEncounterPerformed);
        }

        return $qdmPatient;
    }
}
