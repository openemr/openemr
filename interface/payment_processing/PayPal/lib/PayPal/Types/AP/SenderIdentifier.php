<?php 
namespace PayPal\Types\AP;
use PayPal\Types\Common\AccountIdentifier; 
/**
 * The sender identifier type contains information to identify
 * a PayPal account. 
 */
class SenderIdentifier  extends AccountIdentifier  
  {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var boolean	 
	 */ 
	public $useCredentials;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\TaxIdDetails	 
	 */ 
	public $taxIdDetails;


}
