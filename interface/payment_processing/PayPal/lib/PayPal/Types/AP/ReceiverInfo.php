<?php 
namespace PayPal\Types\AP;
use PayPal\Types\Common\AccountIdentifier; 
/**
 * ReceiverInfo needs to be populate for the receiver who
 * doesn't have paypal account. 
 */
class ReceiverInfo  extends AccountIdentifier  
  {

	/**
	 * The two-character ISO country code of the home country of
	 * the Receiver 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $countryCode;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $firstName;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $lastName;


}
