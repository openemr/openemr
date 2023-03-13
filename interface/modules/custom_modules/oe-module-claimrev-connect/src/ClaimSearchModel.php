<?php
    namespace OpenEMR\Modules\ClaimRevConnector;
    class ClaimSearchModel
    {

        public $patientFirstName = "";
        public $patientLastName = "";
        public $receivedDateStart;
        public $receivedDateEnd;         
        public $serviceDateStart;
        public $serviceDateEnd;

        public function __construct() {
        
    }
}