<?php
require_once( 'ClinicalType.php' );

class Characteristic extends ClinicalType
{   
    const TERMINAL_ILLNESS = 'terminal_illness';
    const TOBACCO_USER = 'char_tobacco_user';
    const TOBACCO_NON_USER = 'char_tobacco_non_user';
    
    public function getListId() {
        return 'Clinical_Rules_Char_Types';
    }
    
    public function doPatientCheck( RsPatient $patient, $beginDate = null, $endDate = null, $options = null ) 
    {
        $return = false;
        
        if ( $this->getOptionId() == self::TERMINAL_ILLNESS ) 
        {
            // TODO check for terminal illness
        } 
        else if ( $this->getOptionId() == self::TOBACCO_USER ) 
        {
            $tobaccoHistory = getHistoryData( $patient->id, "tobacco", $beginDate, $endDate );
            if ( isset( $tobaccoHistory['tobacco'] ) ) {
                $tmp = explode( '|', $tobaccoHistory['tobacco'] );
                $tobaccoStatus = $tmp[1];
                if ( $tobaccoStatus == 'currenttobacco' ) {
                    $return = true;
                } else if ( $tobaccoStatus == 'quittobacco' ) {
                    $quitDate = $tmp[2];
                    if ( strtotime( $quitDate ) > strtotime( $beginDate ) ) {
                        $return = true;
                    }     
                }        
            }
        } 
        else if ( $this->getOptionId() == self::TOBACCO_NON_USER ) 
        {
            $tobaccoHistory = getHistoryData( $patient->id, "tobacco", $beginDate, $endDate );
            if ( isset( $tobaccoHistory['tobacco'] ) ) {
                $tmp = explode( '|', $tobaccoHistory['tobacco'] );
                $tobaccoStatus = $tmp[1];
                if ( $tobaccoStatus == 'quittobacco' ) {
                    $quitDate = $tmp[2];
                    if ( strtotime( $quitDate ) < strtotime( $beginDate ) ) {
                        $return = true;
                    }     
                } else if ( $tobaccoStatus == 'nevertobacco' ) {
                    $return = true;
                }        
            }
        }
        
        return $return;
    }
    
}