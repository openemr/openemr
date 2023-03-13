<?php
    namespace OpenEMR\Modules\ClaimRevConnector;
    class SubscriberPatientEligibilityRequest
    {

        public $firstName;//
        public $lastName;//
        public $middleName;//
        public $suffix;
        public $address1;
        public $address2;
        public $city;
        public $state;
        public $zip;    
        public $dateOfBirth;//
        public $gender;//
        public $memberId;//
       

        public function __construct() {
            
    }
}