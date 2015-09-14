<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * This holds the conversion rate from "Sender currency for one
 * bucks to equivalent value in the receivers currency" 
 */
class ConversionRate  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $senderCurrency;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $receiverCurrency;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var double	 
	 */ 
	public $exchangeRate;


}
