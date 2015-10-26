<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * Describes the conversion between 2 currencies. 
 */
class CurrencyConversion  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\CurrencyType	 
	 */ 
	public $from;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\CurrencyType	 
	 */ 
	public $to;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var double	 
	 */ 
	public $exchangeRate;


}
