<?php

namespace OpenEMR\OemrAd;

@include_once(__DIR__ . "/../interface/globals.php");
@include_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");
@include_once($GLOBALS['srcdir']."/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Twiliolib;

class Smslib {

	/**
	 * Constructor for the 'SMS' class
	 */
	public function __construct() {
	}

	/*Get SMS Obj*/
	public static function getSmsObj($from='') {
	    $serviceType = $GLOBALS['SMS_SERVICE_TYPE'];

	    $sms = null;
		if($serviceType == "nexmo") {
			//$sms = new \wmt\Nexmo($from);
		} else if($serviceType == "twilio") {
			$sms = new Twiliolib($from);
		}

		return $sms;
	}

	/*Get Defualt from no*/
	public static function getDefaultFromNo($from='') {
	    $serviceType = $GLOBALS['SMS_SERVICE_TYPE'];

	    $fromNo = '';
		if($serviceType == "nexmo") {
			$fromNo = $GLOBALS['SMS_DEFAULT_FROM'];
		} else if($serviceType == "twilio") {
			$fromNo = $GLOBALS['SMS_TWILIO_DEFAULT_FROM'];
		}

		return $fromNo;
	}
}