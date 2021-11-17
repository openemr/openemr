<?php

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\Diagnosis;
use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Cqm\Qdm\PatientCharacteristicBirthdate;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;

class PatientService extends AbstractQdmService implements QdmServiceInterface
{
    public function getSqlStatement()
    {
        $sql = "SELECT pid, fname, lname, DOB
            FROM patient_data
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

        return $qdmPatient;
    }

}
