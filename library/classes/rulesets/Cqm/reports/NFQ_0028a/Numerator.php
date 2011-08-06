<?php
class NFQ_0028a_Numerator implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator";
    }

    public function test( CqmPatient $patient, $dateBegin, $dateEnd )
    {
        // See if user has been a tobacco user before or simultaneosly to the encounter within two years (24 months)
        foreach ( $this->getApplicableEncounters() as $encType ) 
        {
            $dates = Helper::fetchEncounterDates( $encType, $patient, $dateBegin, $dateEnd );
            foreach ( $dates as $date ) 
            {
                $beginMinus24Months = strtotime( '-24 month' , strtotime ( $date ) );
                $beginMinus24Months = date( 'Y-m-d 00:00:00' , $beginMinus24Months );
                // this is basically a check to see if the patient's tobacco status has been evaluated in the past two years.
                if ( Helper::check( ClinicalType::CHARACTERISTIC, Characteristic::TOBACCO_USER, $patient, $beginMinus24Months, $dateEnd ) ||
                    Helper::check( ClinicalType::CHARACTERISTIC, Characteristic::TOBACCO_NON_USER, $patient, $beginMinus24Months, $dateEnd ) ) {
                    return true;
                }
            }
        }

        return false;
    }
    
    private function getApplicableEncounters() 
    {
        return array( 
            Encounter::ENC_OFF_VIS,
            Encounter::ENC_HEA_AND_BEH,
            Encounter::ENC_OCC_THER,
            Encounter::ENC_PSYCH_AND_PSYCH,
            Encounter::ENC_PRE_MED_SER_18_OLDER,
            Encounter::ENC_PRE_IND_COUNSEL,
            Encounter::ENC_PRE_MED_GROUP_COUNSEL,
            Encounter::ENC_PRE_MED_OTHER_SERV );
    }
}