<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * 
 */
class ReceiverInfoList  
  extends PPMessage   {

	/**
	 * 
     * @array
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\ReceiverInfo	 
	 */ 
	public $receiverInfo;

	/**
	 * Constructor with arguments
	 */
	public function __construct($receiverInfo = NULL) {
		$this->receiverInfo = $receiverInfo;
	}


}
