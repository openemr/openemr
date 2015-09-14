<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * The PayRequest contains the payment instructions to make
 * from sender to receivers. 
 */
class PayRequest  
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
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ClientDetailsType	 
	 */ 
	public $clientDetails;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $actionType;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $cancelUrl;

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
	public $feesPayer;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $ipnNotificationUrl;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $memo;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $pin;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $preapprovalKey;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\ReceiverList	 
	 */ 
	public $receiverList;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var boolean	 
	 */ 
	public $reverseAllParallelPaymentsOnError;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $senderEmail;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $returnUrl;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $trackingId;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\FundingConstraint	 
	 */ 
	public $fundingConstraint;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\SenderIdentifier	 
	 */ 
	public $sender;

	/**
	 * The pay key expires after the duration specified in this
	 * column. If not provided, it defaults to normal expiration
	 * behavior. Valid values are 5 minutes to 30 days. 
	 * @access public
	 
	 	 	 	 
	 * @var duration	 
	 */ 
	public $payKeyDuration;

	/**
	 * Constructor with arguments
	 */
	public function __construct($requestEnvelope = NULL, $actionType = NULL, $cancelUrl = NULL, $currencyCode = NULL, $receiverList = NULL, $returnUrl = NULL) {
		$this->requestEnvelope = $requestEnvelope;
		$this->actionType = $actionType;
		$this->cancelUrl = $cancelUrl;
		$this->currencyCode = $currencyCode;
		$this->receiverList = $receiverList;
		$this->returnUrl = $returnUrl;
	}


}
