<?php

// TODO: missing header
namespace Comlink\OpenEMR\Modules\TeleHealthModule\Services;

use InvalidArgumentException;
use OpenEMR\Services\AddressService;
use OpenEMR\Services\PatientService;

class FormattedPatientService
{
    public function getPatientForPid($pid)
    {
        // TODO: @adunsulag since patient service hits the db to grab the schema... do we want to put this in our
        // Boostrap DI system to make sure we only have a single instance of it?
        $patientService = new PatientService();
        $patientResult = $patientService->getAll(['pid' => $pid])->getData();
        if (empty($patientResult)) {
            throw new InvalidArgumentException("patient could not be found for pid " . $pid);
        }

        $patientResult = $patientResult[0];
        $date = \DateTime::createFromFormat("Y-m-d", $patientResult['DOB']);
        $dobYmd = $date->format("Ymd");
        $patientResult['dobFormatted'] = $dobYmd;
        $patientResult['age'] = $patientService->getPatientAgeDisplay($dobYmd);
        $addressService = new AddressService();
        $patientResult['addressFull'] = $addressService->getAddressFromRecordAsString($patientResult);
        return $patientResult;
    }
}
