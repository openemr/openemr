<?php
require_once( "CqmPatient.php" );
/*	Defines a population of patients
 * 
 */
class CqmPopulation extends RsPopulation
{
    /*
     * initialize the patient population
     */
    public function __construct( array $patientIdArray ) {
        foreach ( $patientIdArray as $patientId ) {
            $this->_patients[]= new CqmPatient( $patientId );
        }
    }

    /*
     * ArrayAccess Interface
     */
    public function offsetSet($offset,$value) {
        if ($value instanceof CqmPatient ) {
            if ( $offset == "" ) {
                $this->_patients[] = $value;
            }else {
                $this->_patients[$offset] = $value;
            }
        } else {
            throw new Exception( "Value must be an instance of CqmPatient" );
        }
    }

}