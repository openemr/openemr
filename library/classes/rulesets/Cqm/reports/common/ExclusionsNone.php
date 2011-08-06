<?php
class ExclusionsNone implements CqmFilterIF
{
    public function getTitle() 
    {
        return "Exclusions: None";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        return false;
    }
}