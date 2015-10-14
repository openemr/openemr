<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * The request to get the allowed funding sources available for
 * a preapproval. 
 */
class GetAllowedFundingSourcesRequest  
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
	public $key;

	/**
	 * Constructor with arguments
	 */
	public function __construct($requestEnvelope = NULL, $key = NULL) {
		$this->requestEnvelope = $requestEnvelope;
		$this->key = $key;
	}


}
