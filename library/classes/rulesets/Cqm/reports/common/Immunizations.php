<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class Immunizations
{
    public static function checkDtap( CqmPatient $patient, $beginDate, $endDate )
    {
        $dobPlus42Days = date( 'Y-m-d 00:00:00', strtotime( '+42 day', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );
        $fourCount = array( Medication::OPTION_COUNT => 4, Medication::OPTION_UNIQUE_DATES => true );
        if ( Helper::checkMed( Medication::DTAP_VAC, $patient, $dobPlus42Days, $dobPlus2Years, $fourCount ) &&
            !( Helper::checkAllergy( Allergy::DTAP_VAC, $patient, $patient->dob, $endDate ) ||
               Helper::checkDiagActive( Diagnosis::ENCEPHALOPATHY, $patient, $beginDate, $endDate ) ||
               Helper::checkDiagActive( Diagnosis::PROG_NEURO_DISORDER, $patient, $beginDate, $endDate ) ) ) {
            return true;
        }
        
        return false;
    }
    
    public static function checkIpv( CqmPatient $patient, $beginDate, $endDate )
    {
        $dobPlus42Days = date( 'Y-m-d 00:00:00', strtotime( '+42 day', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );
        $threeCount = array( Medication::OPTION_COUNT => 3 );
        if ( Helper::checkMed( Medication::IPV, $patient, $dobPlus42Days, $dobPlus2Years, $threeCount ) &&
            !( Helper::checkAllergy( Allergy::IPV, $patient, $patient->dob, $endDate ) ||
               Helper::checkAllergy( Allergy::NEOMYCIN, $patient, $patient->dob, $endDate ) ||
               Helper::checkAllergy( Allergy::STREPTOMYCIN, $patient, $patient->dob, $endDate ) ) ) {
            return true;
        }
        return false;
    }
    
    public static function checkMmr( CqmPatient $patient, $beginDate, $endDate )
    {
        $dobPlus1Year = date( 'Y-m-d 00:00:00', strtotime( '+1 year', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        $dateMinus2Years = date( 'Y-m-d 00:00:00', strtotime( '-2 year', strtotime( $endDate ) ) ); 
        if ( Helper::checkMed( Medication::MMR, $patient, $dobPlus1Year, $dobPlus2Years ) ||
             ( Helper::checkMed( Medication::MUMPS_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::MUMPS_VAC, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::MEASLES_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::MEASLES_VAC, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::RUBELLA_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::RUBELLA_VAC, $patient, $patient->dob, $endDate ) ) ||
             ( Helper::checkDiagResolved( Diagnosis::MEASLES, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::MUMPS_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::MUMPS_VAC, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::RUBELLA_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::RUBELLA_VAC, $patient, $patient->dob, $endDate ) ) ||
             ( Helper::checkDiagResolved( Diagnosis::MUMPS, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::MEASLES_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::MEASLES_VAC, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::RUBELLA_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::RUBELLA_VAC, $patient, $patient->dob, $endDate ) ) ||
             ( Helper::checkDiagResolved( Diagnosis::RUBELLA, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::MUMPS_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::MUMPS_VAC, $patient, $patient->dob, $endDate ) &&
               Helper::checkMed( Medication::MEASLES_VAC, $patient, $patient->dob, $dobPlus2Years ) && 
               !Helper::checkAllergy( Allergy::MEASLES_VAC, $patient, $patient->dob, $endDate ) ) &&
              !( Helper::checkDiagActive( Diagnosis::CANCER_LYMPH_HIST, $patient, $beginDate, $endDate ) ||
                 Helper::checkDiagInactive( Diagnosis::CANCER_LYMPH_HIST, $patient, $beginDate, $endDate ) ||
                 Helper::checkDiagActive( Diagnosis::ASYMPTOMATIC_HIV, $patient, $beginDate, $endDate ) ||
                 Helper::checkDiagActive( Diagnosis::MULT_MYELOMA, $patient, $beginDate, $endDate ) ||
                 Helper::checkDiagActive( Diagnosis::LUKEMIA, $patient, $beginDate, $endDate ) ||
                 Helper::checkAllergy( Allergy::MMR, $patient, $patient->dob, $dateMinus2Years ) ||
                 Helper::checkDiagActive( Diagnosis::IMMUNODEF, $patient, $beginDate, $endDate ) ) ) {
            return true;
        }
        
        return false;
    }
    
    public static function checkHib( CqmPatient $patient, $beginDate, $endDate )
    {
        $options = array( Medication::OPTION_COUNT => 2 );
        $dobPlus42Days = date( 'Y-m-d 00:00:00', strtotime( '+42 day', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        if ( Helper::checkMed( Medication::HIB, $patient, $dobPlus42Days, $dobPlus2Years, $options ) &&
            !Helper::checkAllergy( Allergy::HIB, $patient, $patient->dob, $endDate ) ) {
            return true;
        }
        return false;
    }
    
    public static function checkHepB( CqmPatient $patient, $beginDate, $endDate )
    {
        $options = array( Medication::OPTION_COUNT => 3 );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        if ( Helper::checkMed( Medication::HEP_B_VAC, $patient, $patient->dob, $dobPlus2Years, $options ) ||
            Helper::checkDiagResolved( Diagnosis::HEP_B, $patient, $patient->dob, $endDate ) &&
            !( Helper::checkAllergy( Allergy::HEP_B_VAC, $patient, $patient->dob, $endDate ) ||
               Helper::checkAllergy( Allergy::BAKERS_YEAST, $patient, $patient->dob, $endDate ) ) ) {
            return true;
        }
        
        return false;
    }
    
    public static function checkVzv( CqmPatient $patient, $beginDate, $endDate )
    {
        $options = array( Medication::OPTION_COUNT => 1 );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        if ( Helper::checkMed( Medication::VZV, $patient, $patient->dob, $dobPlus2Years, $options ) ||
             ( Helper::checkDiagResolved( Diagnosis::VZV, $patient, $patient->dob, $endDate ) &&
               !( Helper::checkDiagActive( Diagnosis::CANCER_LYMPH_HIST, $patient, $beginDate, $endDate ) ||
                  Helper::checkDiagInactive( Diagnosis::CANCER_LYMPH_HIST, $patient, $beginDate, $endDate ) ||
                  Helper::checkDiagActive( Diagnosis::ASYMPTOMATIC_HIV, $patient, $beginDate, $endDate ) ||
                  Helper::checkDiagActive( Diagnosis::MULT_MYELOMA, $patient, $beginDate, $endDate ) ||
                  Helper::checkDiagActive( Diagnosis::LUKEMIA, $patient, $beginDate, $endDate ) ||
                  Helper::checkAllergy( Allergy::VZV, $patient, $patient->dob, $endDate ) ||
                  Helper::checkDiagActive( Diagnosis::IMMUNODEF, $patient, $beginDate, $endDate ) ) ) ) {
            return true;
        }
        
        return false;
    }
    
    public static function checkPheumococcal( CqmPatient $patient, $beginDate, $endDate )
    {
        $options = array( Medication::OPTION_COUNT => 4 );
        $dobPlus42Days = date( 'Y-m-d 00:00:00', strtotime( '+42 day', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        if ( Helper::checkMed( Medication::PNEUMOCOCCAL_VAC, $patient, $dobPlus42Days, $dobPlus2Years, $options ) &&
            !Helper::checkAllergy( Allergy::PNEUM_VAC, $patient ) ) {
            return true;
        }
        
        return false;
    }
    
    public static function checkHepA( CqmPatient $patient, $beginDate, $endDate )
    {
        $options = array( Medication::OPTION_COUNT => 2 );
        $dobPlus42Days = date( 'Y-m-d 00:00:00', strtotime( '+42 day', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        if ( Helper::checkMed( Medication::HEP_A_VAC, $patient, $dobPlus42Days, $dobPlus2Years, $options ) ||
            ( Helper::checkDiagResolved( Diagnosis::HEP_A, $patient, $patient->dob, $endDate ) &&
              !Helper::checkAllergy( Allergy::HEP_A_VAC, $patient, $patient->dob, $endDate ) ) ) {
            return true;
        }
        
        return false;
    }
    
    public static function checkRotavirus( CqmPatient $patient, $beginDate, $endDate )
    {
        $options = array( Medication::OPTION_COUNT => 4 );
        $dobPlus42Days = date( 'Y-m-d 00:00:00', strtotime( '+42 day', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        if ( Helper::checkMed( Medication::ROTAVIRUS_VAC, $patient, $dobPlus42Days, $dobPlus2Years, $options ) &&
            !Helper::checkAllergy( Allergy::ROTAVIRUS_VAC, $patient, $patient->dob, $endDate ) ) {
            return true;
        }
        return false;
    }
    
    public static function checkInfluenza( CqmPatient $patient, $beginDate, $endDate )
    {
        $options = array( Medication::OPTION_COUNT => 2 );
        $dobPlus180Days = date( 'Y-m-d 00:00:00', strtotime( '+180 day', strtotime( $patient->dob ) ) );
        $dobPlus2Years = date( 'Y-m-d 00:00:00', strtotime( '+2 year', strtotime( $patient->dob ) ) );  
        if ( Helper::checkMed( Medication::INFLUENZA_VAC, $patient, $dobPlus180Days, $dobPlus2Years, $options ) &&
            !( Helper::checkAllergy( Allergy::INFLUENZA_VAC, $patient, $patient->dob, $endDate ) || 
               Helper::checkDiagActive( Diagnosis::CANCER_LYMPH_HIST, $patient, $patient->dob, $endDate ) ||
               Helper::checkDiagInactive( Diagnosis::CANCER_LYMPH_HIST, $patient, $patient->dob, $endDate ) ||
               Helper::checkDiagActive( Diagnosis::ASYMPTOMATIC_HIV, $patient, $patient->dob, $endDate ) ||
               Helper::checkDiagActive( Diagnosis::MULT_MYELOMA, $patient, $patient->dob, $endDate ) ||
               Helper::checkDiagActive( Diagnosis::LUKEMIA, $patient, $patient->dob, $endDate ) ||
               Helper::checkDiagActive( Diagnosis::IMMUNODEF, $patient, $patient->dob, $endDate ) ) ) {
            return true;
        }
        
        return false;
    }
}
