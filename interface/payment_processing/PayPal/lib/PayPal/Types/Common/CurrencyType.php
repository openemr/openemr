<?php 
namespace PayPal\Types\Common;
use PayPal\Core\PPMessage;  
/**
 * 
 */
if(!class_exists('CurrencyType', false)) {
class CurrencyType  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $code;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var double	 
	 */ 
	public $amount;

	/**
	 * Constructor with arguments
	 */
	public function __construct($code = NULL, $amount = NULL) {
		$this->code = $code;
		$this->amount = $amount;
	}


}
}
