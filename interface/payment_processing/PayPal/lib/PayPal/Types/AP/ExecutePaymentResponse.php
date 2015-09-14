<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * The result of a payment execution. 
 */
class ExecutePaymentResponse  
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
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $paymentExecStatus;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\PayErrorList	 
	 */ 
	public $payErrorList;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\PostPaymentDisclosureList	 
	 */ 
	public $postPaymentDisclosureList;

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ErrorData	 
	 */ 
	public $error;


}
