<?php
class NFQ_0421_InitialPatientPopulation implements CqmFilterIF
{
    public function getTitle() 
    {
        return "Initial Patient Population";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        if ( convertDobtoAgeYearDecimal( $patient->dob, $dateBegin ) > 65  ) {
            return true;
        }
        
        return false;
    }
}