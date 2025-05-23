<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once('ClinicalType.php');

class Diagnosis extends ClinicalType
{
    const OPTION_STATE = 'state';
    const STATE_ACTIVE = 'active';
    const STATE_INACTIVE = 'inactive';
    const STATE_RESOLVED = 'resolved';

    const HYPERTENSION = 'diag_hypertension';
    const PREGNANCY = 'diag_pregnancy';
    const ENCEPHALOPATHY = 'diag_encephalopathy';
    const PROG_NEURO_DISORDER = 'diag_prog_neuro_disorder';
    const CANCER_LYMPH_HIST = 'diag_cancer_lypmh_hist';
    const ASYMPTOMATIC_HIV = 'diag_asymptomatic_hiv';
    const MULT_MYELOMA = 'diag_mult_myeloma';
    const LUKEMIA = 'diag_lukemia';
    const IMMUNODEF = 'diag_immunodef';
    const MEASLES = 'diag_measles';
    const MUMPS = 'diag_mumps';
    const RUBELLA = 'diag_rubella';
    const HEP_B = 'diag_hep_b';
    const HEP_A = 'diag_hep_a';
    const VZV = 'diag_vzv';
    const INFLUENZA_IMMUN_CONTRADICT = 'diag_influenza_immun_contradict';
    const DIABETES = 'diag_diabetes';
    const POLYCYSTIC_OVARIES = 'diag_polycystic_ovaries';
    const GESTATIONAL_DIABETES = 'diag_gestational_diabetes';
    const STEROID_INDUCED_DIABETES = 'diag_steroid_induced_diabetes';
    const END_STAGE_RENAL_DISEASE = 'diag_end_stage_renal_disease';
    const CHRONIC_KIDNEY_DISEASE = 'diag_chrnoic_kidney_disease';
    const ACUTE_PHARYNGITIS = 'diag_acute_pharyngitis';
    const ACUTE_TONSILLITIS = 'diag_acute_tonsillitis';
    const LIMITED_LIFE = 'diag_limited_life_expectancy';

    public function getListType()
    {
        return 'medical_problem';
    }

    public function getListColumn()
    {
        return 'diagnosis';
    }

    public function getListId()
    {
        return 'Clinical_Rules_Diagnosis_Types';
    }

    /*
     * Check if the patient has this diagnosis
     *
     * @param (CqmPatient) $patient
     * @param (date) $beginMeasurement
     * @param (date) $endMeasurement
     *
     * @return true if patient meets criteria, false ow
     */
    public function doPatientCheck(RsPatient $patient, $beginDate = null, $endDate = null, $options = null)
    {
        $data = Codes::lookup($this->getOptionId());
        $type = $this->getListType();
        foreach ($data as $codeType => $codes) {
            foreach ($codes as $code) {
                if (exist_lists_item($patient->id, $type, $codeType . '::' . $code, $endDate)) {
                    return true;
                }
            }
        }

        return false;
    }
}
