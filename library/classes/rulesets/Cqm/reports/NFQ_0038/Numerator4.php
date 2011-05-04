<?php
class NFQ_0038_Numerator4 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 4";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        return false;
    }
}
