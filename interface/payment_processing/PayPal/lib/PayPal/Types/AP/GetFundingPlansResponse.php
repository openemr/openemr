<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * The response to get the funding plans available for a
 * payment. 
 */
class GetFundingPlansResponse  
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
	 
	 	 	 	 
	 * @var PayPal\Types\AP\FundingPlan	 
	 */ 
	public $fundingPlan;

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\ErrorData	 
	 */ 
	public $error;


}
