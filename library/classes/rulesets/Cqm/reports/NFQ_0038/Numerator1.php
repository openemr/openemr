<?php
class NFQ_0038_Numerator1 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 1";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        return false;
    }
}
