<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * 
 */
class FundingTypeList  
  extends PPMessage   {

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\FundingTypeInfo	 
	 */ 
	public $fundingTypeInfo;

	/**
	 * Constructor with arguments
	 */
	public function __construct($fundingTypeInfo = NULL) {
		$this->fundingTypeInfo = $fundingTypeInfo;
	}


}
