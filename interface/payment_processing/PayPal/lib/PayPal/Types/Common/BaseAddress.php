<?php 
namespace PayPal\Types\Common;
use PayPal\Core\PPMessage;  
/**
 * 
 */
if(!class_exists('BaseAddress', false)) {
class BaseAddress  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $line1;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $line2;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $city;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $state;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $postalCode;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $countryCode;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $type;

	/**
	 * Constructor with arguments
	 */
	public function __construct($line1 = NULL, $city = NULL, $countryCode = NULL) {
		$this->line1 = $line1;
		$this->city = $city;
		$this->countryCode = $countryCode;
	}


}
}
