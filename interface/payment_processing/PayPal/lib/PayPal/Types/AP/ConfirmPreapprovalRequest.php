<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * The request to confirm a Preapproval. 
 */
class ConfirmPreapprovalRequest  
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
	public $preapprovalKey;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $fundingSourceId;

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
	public $agreementType;

	/**
	 * Constructor with arguments
	 */
	public function __construct($requestEnvelope = NULL, $preapprovalKey = NULL) {
		$this->requestEnvelope = $requestEnvelope;
		$this->preapprovalKey = $preapprovalKey;
	}


}
