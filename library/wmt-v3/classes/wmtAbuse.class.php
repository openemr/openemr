<?php
/** **************************************************************************
 *	ABUSE CLASS
 *
 *	Copyright (c)2017 - Medical Technology Services <MDTechSvcs.com>
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package class
 *  @subpackage order
 *  @version 1.0.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

require_once 'wmtForm.class.php';
class Abuse extends Form {
	/* Inherited from wmtForm
	public $id;
	public $created;
	public $date;
	public $pid;
	public $user;
	public $provider;
	public $encounter;
	public $groupname;
	public $authorized;
	public $activity;
	public $status;
	public $priority;
	public $approved_by;
	public $approved_dt;
	public $title;
	
	protected $form_name;
	protected $form_table;
	*/
	
	public $phy_flag;
	public $phy_notes;
	public $sex_flag;
	public $sex_notes;
	public $emo_flag;
	public $emo_notes;
	public $kid_flag;
	public $kid_notes;
	
	/**
	 * Constructor for the 'form' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param int $id record identifier
	 * @return object instance of form class
	 */
	public function __construct($id = false) {
		// store table name in object
		$this->form_name = 'abuse_screen';
		$this->form_table = 'form_abuse_screen';
		$this->form_title = 'Abuse Screen';
		
		// create empty record with no id
		if (!$id) return false;

		parent::__construct('abuse_screen', $id);
		if (!$this->id)
			throw new \Exception('wmtAbuse::_construct - no base abuse record with id ('.$id.').');
				
		return;
	}

	/**
	 * Constructor for the 'form' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param int $id record identifier
	 * @return object instance of form class
	 */
	public static function fetchPidList($pid, $active=true, $order=false) {
		return parent::fetchPidList('abuse_screen', $active, $order);
	}
	
}

?>