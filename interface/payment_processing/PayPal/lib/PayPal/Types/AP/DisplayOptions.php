<?php 
namespace PayPal\Types\AP;
use PayPal\Core\PPMessage;  
/**
 * Customizable options that a client application can specify
 * for display purposes. 
 */
class DisplayOptions  
  extends PPMessage   {

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $emailHeaderImageUrl;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $emailMarketingImageUrl;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $headerImageUrl;

	/**
	 * 
	 * @access public
	 
	 	 	 	 
	 * @var string	 
	 */ 
	public $businessName;


}
