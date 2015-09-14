<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * A response that contains a table of estimated converted
 * currencies based on the Convert Currency Request. 
 */
class ConvertCurrencyResponse  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ResponseEnvelope	 
	 */ 
	public $responseEnvelope;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\CurrencyConversionTable	 
	 */ 
	public $estimatedAmountTable;

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ErrorData	 
	 */ 
	public $error;


}
