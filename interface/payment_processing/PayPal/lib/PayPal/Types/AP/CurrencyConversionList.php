<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * A list of estimated currency conversions for a base
 * currency. 
 */
class CurrencyConversionList  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\CurrencyType	 
	 */ 
	public $baseAmount;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\CurrencyList	 
	 */ 
	public $currencyList;


}
