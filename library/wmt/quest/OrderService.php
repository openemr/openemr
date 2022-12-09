<?php
/** **************************************************************************
 *	OrderService.PHP
 *
 *	Copyright (c)2014 - Williams Medical Technology, Inc.
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package laboratory
 *  @subpackage quest
 *  @version 2.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 *************************************************************************** */
require_once 'SoapAuthClient.php';

if (!class_exists("BaseHubServiceResponse")) {
/**
 * BaseHubServiceResponse
 */
class BaseHubServiceResponse {
	/**
	 * @access public
	 * @var string
	 */
	public $responseCode;
	/**
	 * @access public
	 * @var string
	 */
	public $responseMsg;
	/**
	 * @access public
	 * @var ResponseProperty[]
	 */
	public $responseProperties;
	/**
	 * @access public
	 * @var string
	 */
	public $status;
}}

if (!class_exists("ResponseProperty")) {
/**
 * ResponseProperty
 */
class ResponseProperty {
	/**
	 * @access public
	 * @var string
	 */
	public $propertyName;
	/**
	 * @access public
	 * @var string
	 */
	public $propertyValue;
}}

if (!class_exists("OrderSupportDocument")) {
/**
 * OrderSupportDocument
 */
class OrderSupportDocument {
	/**
	 * @access public
	 * @var base64Binary
	 */
	public $documentData;
	/**
	 * @access public
	 * @var string
	 */
	public $documentType;
	/**
	 * @access public
	 * @var string
	 */
	public $requestStatus;
	/**
	 * @access public
	 * @var string
	 */
	public $responseMessage;
	/**
	 * @access public
	 * @var boolean
	 */
	public $success;
}}

if (!class_exists("Order")) {
/**
 * Order
 */
class Order {
	/**
	 * @access public
	 * @var base64Binary
	 */
	public $hl7Order;
}}

if (!class_exists("OrderResponse")) {
/**
 * OrderResponse
 */
class OrderResponse extends BaseHubServiceResponse {
	/**
	 * @access public
	 * @var string
	 */
	public $messageControlId;
	/**
	 * @access public
	 * @var string
	 */
	public $orderTransactionUid;
	/**
	 * @access public
	 * @var string[]
	 */
	public $validationErrors;
}}

if (!class_exists("OrderSupportServiceRequest")) {
/**
 * OrderSupportServiceRequest
 */
class OrderSupportServiceRequest extends Order {
	/**
	 * @access public
	 * @var string[]
	 */
	public $orderSupportRequests;
}}

if (!class_exists("OrderSupportServiceResponse")) {
/**
 * OrderSupportServiceResponse
 */
class OrderSupportServiceResponse extends OrderResponse {
	/**
	 * @access public
	 * @var OrderSupportDocument[]
	 */
	public $orderSupportDocuments;
}}

if (!class_exists("ServiceException")) {
/**
 * ServiceException
 */
class ServiceException {
}}

if (!class_exists("SOAPException")) {
/**
 * SOAPException
 */
class SOAPException {
	/**
	 * @access public
	 * @var string
	 */
	public $message;
}}

if (!class_exists("OrderService")) {
/**
 * OrderService
 * @author WSDLInterpreter
 */
class OrderService extends SoapAuthClient {
	/**
	 * Default class map for wsdl=>php
	 * @access private
	 * @var array
	 */
	private static $classmap = array(
		"BaseHubServiceResponse" => "BaseHubServiceResponse",
		"ResponseProperty" => "ResponseProperty",
		"OrderSupportDocument" => "OrderSupportDocument",
		"Order" => "Order",
		"OrderResponse" => "OrderResponse",
		"OrderSupportServiceRequest" => "OrderSupportServiceRequest",
		"OrderSupportServiceResponse" => "OrderSupportServiceResponse",
		"ServiceException" => "ServiceException",
		"SOAPException" => "SOAPException",
	);

	/**
	 * Constructor using wsdl location and options array
	 * @param string $wsdl WSDL location for this service
	 * @param array $options Options for the SoapClient
	 */
	public function __construct($wsdl, $options=array()) {
		foreach(self::$classmap as $wsdlClassName => $phpClassName) {
		    if(!isset($options['classmap'][$wsdlClassName])) {
		        $options['classmap'][$wsdlClassName] = $phpClassName;
		    }
		}
		parent::__construct($wsdl, $options);
	}

	/**
	 * Checks if an argument list matches against a valid argument type list
	 * @param array $arguments The argument list to check
	 * @param array $validParameters A list of valid argument types
	 * @return boolean true if arguments match against validParameters
	 * @throws Exception invalid function signature message
	 */
	public function _checkArguments($arguments, $validParameters) {
		$variables = "";
		foreach ($arguments as $arg) {
		    $type = gettype($arg);
		    if ($type == "object") {
		        $type = get_class($arg);
		    }
		    $variables .= "(".$type.")";
		}
		if (!in_array($variables, $validParameters)) {
		    throw new Exception("Invalid parameter types: ".str_replace(")(", ", ", $variables));
		}
		return true;
	}

	/**
	 * Service Call: submitOrder
	 * Parameter options:
	 * (Order) order
	 * @param mixed,... See function description for parameter options
	 * @return OrderResponse
	 * @throws Exception invalid function signature message
	 */
	public function submitOrder($mixed = null) {
		$validParameters = array(
			"(OrderSupportServiceRequest)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("submitOrder", $args);
	}


	/**
	 * Service Call: validateOrder
	 * Parameter options:
	 * (Order) order
	 * @param mixed,... See function description for parameter options
	 * @return OrderResponse
	 * @throws Exception invalid function signature message
	 */
	public function validateOrder($mixed = null) {
		$validParameters = array(
			"(OrderSupportServiceRequest)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("validateOrder", $args);
	}


	/**
	 * Service Call: getOrderDocuments
	 * Parameter options:
	 * (OrderSupportServiceRequest) request
	 * @param mixed,... See function description for parameter options
	 * @return OrderSupportServiceResponse
	 * @throws Exception invalid function signature message
	 */
	public function getOrderDocuments($mixed = null) {
		$validParameters = array(
			"(OrderSupportServiceRequest)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("getOrderDocuments", $args);
	}


}}

?>