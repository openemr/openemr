<?php
require_once( dirname(__FILE__)."/../../../patient.inc" );

class RsPatient
{
    public $id;
    public $dob;

    public function __construct( $id ) {
        $this->id = $id;
        $this->dob = $this->get_DOB( $id );
    }

    /* Function to get patient dob
     * @param $patient_id
     * @return (string) containing date of birth in the format "YYYY mm dd"
     */
    private function get_DOB( $patient_id ) {
        $dob = getPatientData( $patient_id, "DATE_FORMAT(DOB,'%Y %m %d') as TS_DOB" );
        return $dob['TS_DOB'];
    }
}
