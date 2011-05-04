<?php
class NFQ_0038_Numerator5 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 5";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        return false;
    }
}
