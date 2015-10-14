<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * The customer of the initiating institution 
 */
class InstitutionCustomer  
  extends PPMessage   {

	/**
	 * The unique identifier as assigned to the institution. 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $institutionId;

	/**
	 * The first (given) name of the end consumer as known by the
	 * institution. 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $firstName;

	/**
	 * The last (family) name of the end consumer as known by the
	 * institution. 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $lastName;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $middleName;

	/**
	 * The full name of the end consumer as known by the
	 * institution. 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $displayName;

	/**
	 * The unique identifier as assigned to the end consumer by the
	 * institution. 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $institutionCustomerId;

	/**
	 * The two-character ISO country code of the home country of
	 * the end consumer 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $countryCode;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $email;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var date	 
	 */ 
	public $dateOfBirth;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\Common\BaseAddress	 
	 */ 
	public $address;

	/**
	 * Constructor with arguments
	 */
	public function __construct($institutionId = NULL, $firstName = NULL, $lastName = NULL, $displayName = NULL, $institutionCustomerId = NULL, $countryCode = NULL) {
		$this->institutionId = $institutionId;
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		$this->displayName = $displayName;
		$this->institutionCustomerId = $institutionCustomerId;
		$this->countryCode = $countryCode;
	}


}
