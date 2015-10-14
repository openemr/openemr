<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * Describes a payment for a particular receiver (merchant),
 * contains list of additional per item details. 
 */
class InvoiceData  
  extends PPMessage   {

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\InvoiceItem	 
	 */ 
	public $item;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var double	 
	 */ 
	public $totalTax;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var double	 
	 */ 
	public $totalShipping;


}
