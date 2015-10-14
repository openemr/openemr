<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * A request to convert one or more currencies into their
 * estimated values in other currencies. 
 */
class ConvertCurrencyRequest  
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
	 
	 	 	 	 
	 * @var PayPal\Types\AP\CurrencyList	 
	 */ 
	public $baseAmountList;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var PayPal\Types\AP\CurrencyCodeList	 
	 */ 
	public $convertToCurrencyList;

	/**
	 * The two-character ISO country code where fx suppposed to
	 * happen 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $countryCode;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $conversionType;

	/**
	 * Constructor with arguments
	 */
	public function __construct($requestEnvelope = NULL, $baseAmountList = NULL, $convertToCurrencyList = NULL) {
		$this->requestEnvelope = $requestEnvelope;
		$this->baseAmountList = $baseAmountList;
		$this->convertToCurrencyList = $convertToCurrencyList;
	}


}
