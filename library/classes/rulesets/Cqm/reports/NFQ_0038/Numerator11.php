<?php
class NFQ_0038_Numerator11 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 11";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        return false;
    }
}
