<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * The response to get the shipping addresses available for a
 * payment. 
 */
class GetAvailableShippingAddressesResponse  
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
	 
	 	 	 	 
	 * @var PayPal\Types\AP\Address	 
	 */ 
	public $availableAddress;

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ErrorData	 
	 */ 
	public $error;


}
