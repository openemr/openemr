<?php
    namespace OpenEMR\Modules\ClaimRevConnector;
    class EligibilityInquiryRequest
    {

        public $originatingSystemId;
        public $relationship;
        public $payerNumber;
        public $payerName;
        public $payerResponsibility;
        public $provider;
        public $subscriber;
        public $patient;
        public $industryCode;
        public $serviceTypeCodes;

        public function __construct($subscriber,$patient,$relationship,$payerResponsibility) 
        { 
            if(strtolower($payerResponsibility) == "primary")
            {
                $this->payerResponsibility = "p";
            }
            else if(strtolower($payerResponsibility) == "secondary")
            {
                $this->payerResponsibility = "s";
            }
            else if(strtolower($payerResponsibility) == "tertiary")
            {
                $this->payerResponsibility = "t";
            }


            if(strtolower($relationship) == "spouse")
            {
                $this->relationship = "01";
            }
            else if(strtolower($relationship) == "child")
            {
                $this->relationship = "19";
            }
            else 
            {
                $this->relationship = "34";
            }
           
            $this->subscriber = $subscriber;
            $this->patient = $patient;
   
        }
}