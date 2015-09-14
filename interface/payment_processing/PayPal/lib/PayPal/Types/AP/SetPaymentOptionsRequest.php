<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * The request to set the options of a payment request. 
 */
class SetPaymentOptionsRequest  
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
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\InitiatingEntity	 
	 */ 
	public $initiatingEntity;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\DisplayOptions	 
	 */ 
	public $displayOptions;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $shippingAddressId;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\SenderOptions	 
	 */ 
	public $senderOptions;

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\ReceiverOptions	 
	 */ 
	public $receiverOptions;

	/**
	 * Constructor with arguments
	 */
	public function __construct($requestEnvelope = NULL, $payKey = NULL) {
		$this->requestEnvelope = $requestEnvelope;
		$this->payKey = $payKey;
	}


}
