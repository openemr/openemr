<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * A request to make a refund based on various criteria. A
 * refund can be made against the entire payKey, an individual
 * transaction belonging to a payKey, a tracking id, or a
 * specific receiver of a payKey. 
 */
class RefundRequest  
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
	public $currencyCode;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $payKey;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $transactionId;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $trackingId;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\ReceiverList	 
	 */ 
	public $receiverList;

	/**
	 * Constructor with arguments
	 */
	public function __construct($requestEnvelope = NULL) {
		$this->requestEnvelope = $requestEnvelope;
	}


}
