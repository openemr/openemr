<?php
class DenominatorAllPatients implements CqmFilterIF
{
    public function getTitle() 
    {
        return "Denominator: All patients in the initial patient population";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        return true;
    }
}