<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * A request to create a Preapproval. A Preapproval is an
 * agreement between a Paypal account holder (the sender) and
 * the API caller (the service invoker) to make payment(s) on
 * the the sender's behalf with various limitations defined. 
 */
class PreapprovalRequest  
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
	 
	 	 	 	 
	 * @var integer	 
	 */ 
	public $dateOfMonth;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $dayOfWeek;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var dateTime	 
	 */ 
	public $endingDate;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var double	 
	 */ 
	public $maxAmountPerPayment;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var integer	 
	 */ 
	public $maxNumberOfPayments;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var integer	 
	 */ 
	public $maxNumberOfPaymentsPerPeriod;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var double	 
	 */ 
	public $maxTotalAmountOfAllPayments;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $paymentPeriod;

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
	public $memo;

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
	public $senderEmail;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var dateTime	 
	 */ 
	public $startingDate;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $pinType;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $feesPayer;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var boolean	 
	 */ 
	public $displayMaxTotalAmount;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var boolean	 
	 */ 
	public $requireInstantFundingSource;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\SenderIdentifier	 
	 */ 
	public $sender;

	/**
	 * Constructor with arguments
	 */
	public function __construct($requestEnvelope = NULL, $cancelUrl = NULL, $currencyCode = NULL, $returnUrl = NULL, $startingDate = NULL) {
		$this->requestEnvelope = $requestEnvelope;
		$this->cancelUrl = $cancelUrl;
		$this->currencyCode = $currencyCode;
		$this->returnUrl = $returnUrl;
		$this->startingDate = $startingDate;
	}


}
