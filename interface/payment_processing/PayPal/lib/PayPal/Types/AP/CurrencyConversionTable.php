<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * A table that contains a list of estimated currency
 * conversions for a base currency in each row. 
 */
class CurrencyConversionTable  
  extends PPMessage   {

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\CurrencyConversionList	 
	 */ 
	public $currencyConversionList;


}
