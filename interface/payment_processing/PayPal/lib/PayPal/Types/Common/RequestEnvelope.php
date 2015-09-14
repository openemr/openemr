<?php 
namespace PayPal\Types\Common;
use PayPal\Core\PPMessage;  
/**
 * This specifies the list of parameters with every request to
 * the service. 
 */
if(!class_exists('RequestEnvelope', false)) {
class RequestEnvelope  
  extends PPMessage   {

	/**
	 * This specifies the required detail level that is needed by a
	 * client application pertaining to a particular data component
	 * (e.g., Item, Transaction, etc.). The detail level is
	 * specified in the DetailLevelCodeType which has all the
	 * enumerated values of the detail level for each component. 
	 * @access public
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $detailLevel;

	/**
	 * This should be the standard RFC 3066 language identification
	 * tag, e.g., en_US. 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $errorLanguage;

	/**
	 * Constructor with arguments
	 */
	public function __construct($errorLanguage = NULL) {
		$this->errorLanguage = $errorLanguage;
	}


}
}
