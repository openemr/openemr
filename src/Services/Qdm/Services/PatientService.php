<?php

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\AbstractType;
use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\DataElement;
use OpenEMR\Cqm\Qdm\BaseTypes\DateTime;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\Diagnosis;
use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Cqm\Qdm\PatientCharacteristicBirthdate;
use OpenEMR\Cqm\Qdm\PatientCharacteristicRace;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class PatientService extends AbstractQdmService implements QdmServiceInterface
{
    public function getSqlStatement()
    {
        $sql = "SELECT
                    pid,
                    fname,
                    lname,
                    DOB,
                    RACE.notes as race_code
            FROM patient_data P
            JOIN list_options RACE ON list_id = 'race' AND P.race = RACE.option_id
            ";
        return $sql;
    }

    public function makeQdmModel(array $record)
    {
        $qdmPatient = new Patient([
            'birthDatetime' => $record['DOB'],
            '_fullName' => $record['fname'] . ' ' . $record['lname'],
            '_pid' => $record['pid'],
            '_id' => $record['pid']
        ]);

        $qdmPatient->extendedData = [
            'pid' => $record['pid']
        ];

        $patientCharacteristic = new PatientCharacteristicBirthdate([
            'birthDatetime' => new DateTime([
                'date' => $record['DOB']
            ]),
            'dataElementCodes' => [
                new Code([
                    'code' => '21112-8',
                    'system' => '2.16.840.1.113883.6.1'
                ])
            ]
        ]);

        $qdmPatient->add_data_element($patientCharacteristic);

        // Reference: https://phinvads.cdc.gov/vads/ViewCodeSystem.action?id=2.16.840.1.113883.6.238
        $patientCharacteristic = new PatientCharacteristicRace([
            'dataElementCodes' => [
                new Code([
                    'code' => $record['race_code'],
                    'system' => '2.16.840.1.113883.6.238'
                ])
            ]
        ]);

        $qdmPatient->add_data_element($patientCharacteristic);

        return $qdmPatient;
    }

}
