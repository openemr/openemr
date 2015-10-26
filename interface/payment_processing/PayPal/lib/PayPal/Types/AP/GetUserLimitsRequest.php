<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * The request to get the remaining limits for a user 
 */
class GetUserLimitsRequest  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\RequestEnvelope	 
	 */ 
	public $requestEnvelope;

	/**
	 * The account identifier for the user 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\AccountIdentifier	 
	 */ 
	public $user;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $country;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $currencyCode;

	/**
	 * List of limit types 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $limitType;

	/**
	 * Constructor with arguments
	 */
	public function __construct($requestEnvelope = NULL, $user = NULL, $country = NULL, $currencyCode = NULL, $limitType = NULL) {
		$this->requestEnvelope = $requestEnvelope;
		$this->user = $user;
		$this->country = $country;
		$this->currencyCode = $currencyCode;
		$this->limitType = $limitType;
	}


}
