<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * Funding source information. 
 */
class FundingSource  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $lastFourOfAccountNumber;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $type;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $displayName;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $fundingSourceId;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var boolean	 
	 */ 
	public $allowed;


}
