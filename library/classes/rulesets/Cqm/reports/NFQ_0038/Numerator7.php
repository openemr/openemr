<?php
class NFQ_0038_Numerator7 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 7";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        return false;
    }
}
