<?php
class NFQ_0038_Numerator8 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 8";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        return false;
    }
}
