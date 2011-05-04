<?php
class NFQ_0038_Numerator12 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 12";
    }
    
    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        return false;
    }
}
