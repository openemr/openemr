<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * The request to execute the payment request. 
 */
class ExecutePaymentRequest  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\RequestEnvelope	 
	 */ 
	public $requestEnvelope;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $payKey;

	/**
	 * Describes the action that is performed by this API 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $actionType;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $fundingPlanId;

	/**
	 * Constructor with arguments
	 */
	public function __construct($requestEnvelope = NULL, $payKey = NULL) {
		$this->requestEnvelope = $requestEnvelope;
		$this->payKey = $payKey;
	}


}
