<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * The details of the Preapproval as specified in the
 * Preapproval operation. 
 */
class PreapprovalDetailsResponse  
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
	 
	 	 	 	 
	 * @var boolean	 
	 */ 
	public $approved;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $cancelUrl;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var integer	 
	 */ 
	public $curPayments;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var double	 
	 */ 
	public $curPaymentsAmount;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var integer	 
	 */ 
	public $curPeriodAttempts;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var dateTime	 
	 */ 
	public $curPeriodEndingDate;

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
	public $pinType;

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
	public $senderEmail;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $memo;

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
	public $status;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $ipnNotificationUrl;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\AddressList	 
	 */ 
	public $addressList;

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
	 
	 	 	 	 
	 * @var PayPal\Types\AP\SenderIdentifier	 
	 */ 
	public $sender;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $agreementType;

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ErrorData	 
	 */ 
	public $error;


}
