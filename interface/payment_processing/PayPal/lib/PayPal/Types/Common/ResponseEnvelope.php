<?php 
namespace PayPal\Types\Common;
use PayPal\Core\PPMessage;  
/**
 * This specifies a list of parameters with every response from
 * a service. 
 */
if(!class_exists('ResponseEnvelope', false)) {
class ResponseEnvelope  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var dateTime	 
	 */ 
	public $timestamp;

	/**
	 * Application level acknowledgment code. 
	 * @access public
	 
	 	 	 	 
	 * @var string 	 
	 */ 
	public $ack;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $correlationId;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $build;


}
}
