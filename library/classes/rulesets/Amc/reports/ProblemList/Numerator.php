<?php
class ProblemList_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "Problem List Numerator";
    }
    
    public function test( AmcPatient $patient, $dateBegin, $dateEnd ) 
    {
        // TODO have at least one entry or an indication that no problems are known for the 
        // patient recorded as structured data 
        return false;
    }
}