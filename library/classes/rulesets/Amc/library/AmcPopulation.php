<?php
require_once( "AmcPatient.php" );
/*	Defines a population of patients
 * 
 */
class AmcPopulation extends RsPopulation
{
    /*
     * initialize the patient population
     */
    public function __construct( array $patientIdArray ) {
        foreach ( $patientIdArray as $patientId ) {
            $this->_patients[]= new AmcPatient( $patientId );
        }
    }

    /*
     * ArrayAccess Interface
     */
    public function offsetSet($offset,$value) {
        if ($value instanceof AmcPatient ) {
            if ( $offset == "" ) {
                $this->_patients[] = $value;
            }else {
                $this->_patients[$offset] = $value;
            }
        } else {
            throw new Exception( "Value must be an instance of AmcPatient" );
        }
    }

}