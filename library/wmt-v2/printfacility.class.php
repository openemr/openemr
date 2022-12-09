<?php
/** **************************************************************************
 *	PRINTFACILITY.CLASS.PHP
 *	This file contains a print class for use with any print form
 *
 *  NOTES:
 *  1) __CONSTRUCT - always uses the ID to retrieve data 
 *  2) GET - uses alternate selectors to find and return associated object
 *   
 * 
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */

/** 
 * Provides a partial representation of the facility data record This object
 * does NOT include all of the fields associated with the core facility data
 * record and should NOT be used for database updates.  It is intended only
 * for retrieval of partial facility information primarily for display 
 * purposes (reports for example).
 *
 * 
 */

if(!class_exists('wmtPrintFacility')) {

class wmtPrintFacility{
	public $id;
	public $facility;
	public $addr;
	public $city;
	public $state;
	public $zip;
	public $phone;
	public $fax;
	public $csz;
	public $website;
	public $email;
	public $phone_fax;
	
	// generated values - none in use currently
	
	/**
	 * Constructor for the 'facility' class which retrieves the requested 
	 * facility information from the database.
	 * 
	 * @param int $id facility record identifier
	 * @return object instance of facility print class
	 */
	public function __construct($id = false) {
		if(!$id) return false;

		$query = "SELECT * FROM facility WHERE id =?";
		$results = sqlStatementNoLog($query, array($id));
	
		if ($data = sqlFetchArray($results)) {
			$this->id = $data['id'];
			$this->facility = $data['name'];
			$this->addr = $data['street'];
			$this->city = $data['city'];
			$this->state = $data['state'];
			$this->zip = $data['postal_code'];
			$this->phone = $data['phone'];
			$this->fax = $data['fax'];
			$this->website = $data['website'];
			$this->email = $data['email'];
		}
		else {
			throw new Exception('wmtPrintFacility::_construct - no facility record with id ('.$this->id.').');
		}
		
		// preformat commonly used data elements	
		if($data['city'] || $data['state'] || $data['postal_code']) {
			$this->csz= $data['city'].', '.$data['state'].' '.$data['postal_code'];
		}
		if($data['phone']) $this->phone_fax .= 'Phone: '.$data['phone'];
		if($data['fax']) {
			$this->phone_fax .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			$this->phone_fax .= 'Fax: '.$data['fax'];
		}
	}	

	/**
	 * Retrieve a facility object by ID value. Uses the base constructor 
   * for the 'facility print' class to create and return the object.
	 * 
	 * @static
	 * @param int $psr facility record id
	 * @return object instance of facility print class
	 */
	public static function getFacility($psr=0) {
		// For now always default to '3' if not set
		if(!$psr) $psr = 3;
		if(!$psr) {
			throw new Exception('wmtPrintFacility::getFacility - no facility identifier provided.');
		}
		
		return new wmtPrintFacility($psr);
	}
	
}

}
                                            
?>
