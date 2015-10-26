<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * Details about the party that initiated this payment. The API
 * user is making this payment on behalf of the initiator. The
 * initiator can simply be an institution or a customer of the
 * institution. 
 */
class InitiatingEntity  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\InstitutionCustomer	 
	 */ 
	public $institutionCustomer;


}
