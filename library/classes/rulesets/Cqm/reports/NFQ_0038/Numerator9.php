<?php
class NFQ_0038_Numerator9 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 9";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        return false;
    }
}
