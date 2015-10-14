<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * The response to get the backup funding sources available for
 * a preapproval. 
 */
class GetAllowedFundingSourcesResponse  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ResponseEnvelope	 
	 */ 
	public $responseEnvelope;

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\FundingSource	 
	 */ 
	public $fundingSource;

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ErrorData	 
	 */ 
	public $error;


}
