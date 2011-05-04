<?php
class NFQ_0038_Numerator2 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 2";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        return false;
    }
}
