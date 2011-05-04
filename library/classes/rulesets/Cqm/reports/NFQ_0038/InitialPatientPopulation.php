<?php
class NFQ_0038_InitialRs_PatientPopulation implements FilterIF
{
    public function getTitle()
    {
        return "Initial Rs_Patient Population";
    }

    public function test( Rs_Patient $Rs_Patient, $dateBegin, $dateEnd )
    {
        // Rs_Patient characteristic: birth dateÓ (age) >=1 year and <2 years to capture all Rs_Patients who will reach 2 years during the Òmeasurement periodÓ;
        $age = convertDobtoAgeYearDecimal( $Rs_Patient->dob, $dateBegin );
        if ( $age >= 1 &&
            $age < 2 ) { 
            return true;        
        }
        
        return false;
    }
}
