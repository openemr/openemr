<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * GetPrePaymentDisclosureRequest is used to get the PrePayment
 * Disclosure.; GetPrePaymentDisclosureRequest contains
 * following parameters payKey :The pay key that identifies the
 * payment for which you want to retrieve details. this is the
 * pay key returned in the PayResponse message.
 * receiverInfoList : This is an optional.This needs to be
 * provided in case of Unilateral scenario. receiverInfoList
 * has a list of ReceiverInfo type. List is provided here to
 * support in future for Parallel/Chained Payemnts. Each
 * ReceiverInfo has following variables firstName : firstName
 * of recipient.  lastName : lastName of recipient. 
 * countryCode : CountryCode of Recipient. 
 */
class GetPrePaymentDisclosureRequest  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\RequestEnvelope	 
	 */ 
	public $requestEnvelope;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $payKey;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\ReceiverInfoList	 
	 */ 
	public $receiverInfoList;

	/**
	 * Constructor with arguments
	 */
	public function __construct($requestEnvelope = NULL, $payKey = NULL) {
		$this->requestEnvelope = $requestEnvelope;
		$this->payKey = $payKey;
	}


}
