<?php 
namespace PayPal\Types\Common;
use PayPal\Core\PPMessage;  
/**
 * This specifies a fault, encapsulating error data, with
 * specific error codes. 
 */
if(!class_exists('FaultMessage', false)) {
class FaultMessage  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ResponseEnvelope	 
	 */ 
	public $responseEnvelope;

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ErrorData	 
	 */ 
	public $error;


}
}
