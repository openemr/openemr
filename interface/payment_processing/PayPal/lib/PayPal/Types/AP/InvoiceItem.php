<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * Describes an individual item for an invoice. 
 */
class InvoiceItem  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $name;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $identifier;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var double	 
	 */ 
	public $price;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var double	 
	 */ 
	public $itemPrice;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var integer	 
	 */ 
	public $itemCount;


}
