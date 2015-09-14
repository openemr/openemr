<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * FundingPlan describes the funding sources to be used for a
 * specific payment. 
 */
class FundingPlan  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $fundingPlanId;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\CurrencyType	 
	 */ 
	public $fundingAmount;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\FundingSource	 
	 */ 
	public $backupFundingSource;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\CurrencyType	 
	 */ 
	public $senderFees;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\CurrencyConversion	 
	 */ 
	public $currencyConversion;

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\FundingPlanCharge	 
	 */ 
	public $charge;


}
