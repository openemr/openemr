<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * Options that apply to the sender of a payment. 
 */
class SenderOptions  
  extends PPMessage   {

	/**
	 * Require the user to select a shipping address during the web
	 * flow. 
	 * @access public
	 
	 	 	 	 
	 * @var boolean	 
	 */ 
	public $requireShippingAddressSelection;

	/**
	 * Determines whether or not the UI pages should display the
	 * shipping address set by user in this SetPaymentOptions
	 * request. 
	 * @access public
	 
	 	 	 	 
	 * @var boolean	 
	 */ 
	public $addressOverride;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $referrerCode;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\ShippingAddressInfo	 
	 */ 
	public $shippingAddress;


}
