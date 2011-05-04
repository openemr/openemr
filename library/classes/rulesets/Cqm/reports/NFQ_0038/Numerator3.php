<?php
class NFQ_0038_Numerator3 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 3";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        return false;
    }
}
