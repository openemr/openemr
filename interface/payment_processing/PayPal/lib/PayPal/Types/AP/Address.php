<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * 
 */
class Address  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $addresseeName;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\BaseAddress	 
	 */ 
	public $baseAddress;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $addressId;


}
