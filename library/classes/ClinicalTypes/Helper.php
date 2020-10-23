<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once('ClinicalType.php');

class Helper
{
    public static function checkAllergy($subType, RsPatient $patient, $beginDate = null, $endDate = null, $options = null)
    {
        return self::check(ClinicalType::ALLERGY, $subType, $patient, $beginDate, $endDate, $options);
    }

    public static function checkDiagActive($subType, RsPatient $patient, $beginDate = null, $endDate = null, $options = null)
    {
        // TODO append options array
        return self::check(ClinicalType::DIAGNOSIS, $subType, $patient, $beginDate, $endDate, array( Diagnosis::OPTION_STATE => Diagnosis::STATE_ACTIVE ));
    }

    public static function checkDiagInactive($subType, RsPatient $patient, $beginDate = null, $endDate = null, $options = null)
    {
        return self::check(ClinicalType::DIAGNOSIS, $subType, $patient, $beginDate, $endDate, array( Diagnosis::OPTION_STATE => Diagnosis::STATE_INACTIVE ));
    }

    public static function checkDiagResolved($subType, RsPatient $patient, $beginDate = null, $endDate = null, $options = null)
    {
        return self::check(ClinicalType::DIAGNOSIS, $subType, $patient, $beginDate, $endDate, array( Diagnosis::OPTION_STATE => Diagnosis::STATE_RESOLVED ));
    }

    public static function checkEncounter($subType, RsPatient $patient, $beginDate = null, $endDate = null, $options = null)
    {
        return self::check(ClinicalType::ENCOUNTER, $subType, $patient, $beginDate, $endDate, $options);
    }

    public static function checkLab($subType, RsPatient $patient, $beginDate = null, $endDate = null, $options = null)
    {
        return self::check(ClinicalType::LAB_RESULT, $subType, $patient, $beginDate, $endDate, $options);
    }

    public static function checkMed($subType, RsPatient $patient, $beginDate = null, $endDate = null, $options = null)
    {
        return self::check(ClinicalType::MEDICATION, $subType, $patient, $beginDate, $endDate, $options);
    }

    public static function check($type, $subType, RsPatient $patient, $beginDate = null, $endDate = null, $options = null)
    {
        $typeObj = new $type($subType);
        if ($typeObj instanceof ClinicalType) {
            if ($beginDate == null) {
                $beginDate = $patient->dob;
            }

            if ($endDate == null) {
                $endDate = date("Y-m-d");
            }

            return $typeObj->doPatientCheck($patient, $beginDate, $endDate, $options);
        } else {
            throw new Exception("Type must be a subclass of AbstractClinicalType");
        }
    }

    public static function fetchEncounterDates($encounterType, RsPatient $patient, $beginDate = null, $endDate = null)
    {
        $encounter = new Encounter($encounterType);
        return $encounter->fetchDates($patient, $beginDate, $endDate);
    }

    public static function checkAnyEncounter(RsPatient $patient, $beginDate = null, $endDate = null, $options = null)
    {
        $encounters = Encounter::getEncounterTypes();
        foreach ($encounters as $encounter) {
            if (self::checkEncounter($encounter, $patient, $beginDate, $endDate, $options)) {
                return true;
            }
        }

        return false;
    }
}
