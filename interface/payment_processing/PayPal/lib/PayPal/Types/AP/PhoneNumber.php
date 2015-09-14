<?php 
namespace PayPal\Types\AP;
use PayPal\Types\Common\PhoneNumberType; 
/**
 * Phone number with Type of phone number 
 */
class PhoneNumber  extends PhoneNumberType  
  {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $type;

	/**
	 * Constructor with arguments
	 */
	public function __construct($type = NULL) {
		$this->type = $type;
	}


}
