<?php 
namespace PayPal\Types\Common;
use PayPal\Core\PPMessage;  
/**
 * @hasAttribute
 * 
 */
if(!class_exists('ErrorParameter', false)) {
class ErrorParameter  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 
	 * @attribute 
	 	 	 	 
	 * @var string	 
	 */ 
	public $name;

	/**
	 * 
	 * @access public
	 
	 
	 * @value
	 	 	 	 
	 * @var string	 
	 */ 
	public $value;


}
}
