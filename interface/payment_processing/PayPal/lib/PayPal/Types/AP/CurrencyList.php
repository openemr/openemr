<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * A list of ISO currencies. 
 */
class CurrencyList  
  extends PPMessage   {

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\CurrencyType	 
	 */ 
	public $currency;

	/**
	 * Constructor with arguments
	 */
	public function __construct($currency = NULL) {
		$this->currency = $currency;
	}


}
