<?php

namespace OpenEMR\Services\Qdm\Services;

use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\Interval;
use OpenEMR\Cqm\Qdm\Diagnosis;
use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Cqm\Qdm\PatientCharacteristicBirthdate;

class PatientService extends AbstractQdmService
{
    public function getSqlStatement()
    {
        $sql = "SELECT pid, fname, lname, DOB
            FROM patient_data
            WHERE pid IN ({$this->getRequest()->getPidString()})";
        return $sql;
    }

    public function makeQdmModel(array $record)
    {
        $qdmPatient = new Patient([
            'birthDatetime' => $record['DOB'],
            '_fullName' => $record['fname'] . ' ' . $record['lname'],
            '_openEmrPid' => $record['pid']
        ]);

        return $qdmPatient;
    }

}
