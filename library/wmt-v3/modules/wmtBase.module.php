<?php
/** **************************************************************************
 *	BASE.MODULE.PHP
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
 *  @package wmt
 *  @subpackage modules
 *  @version 2.0.0
 *  @category Module Base Class
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/**
 * Standard helper classes
 */
require_once($GLOBALS['srcdir']."/wmt-v3/includes/wmt.standard.php");
require_once($GLOBALS['srcdir']."/wmt-v3/includes/wmt.settings.php");

/**
 * Provides standardized processing for many modules.
 *
 * @package wmt
 * @subpackage base
 */
class BaseModule {
	protected $key;
	protected $title;
	protected $toggle;
	protected $bottom;
	protected $form_data;
	
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of form class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtBaseModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
			
		return;
	}

	/**
	 * Update data from a form object into the database.
	 * Used for 'on-the-fly' changes.
	 *
	 * @return int $id identifier for object
	 */
	public function update() {
	}

	/**
	 * Stores data from a form object into the database.
	 * Used for 'form save' operation.
	 *
	 * @return int $id identifier for object
	 */
	public function store() {
	}

	/**
	 * Display a collapsable section in the form.
	 *
	 */
	public function display($open=false, $bottom=false) {
		$this->toggle = ($open)? 'block' : 'none';
		
		echo "<div class='wmtMainContainer wmtMainColor'>\n";
		Display::chapter($this->title, $this->key, $open);
		echo "<div id='".$this->key."Box' class='wmtCollapseBox wmtColorBox' style='padding:0 0 10px;display: ".$this->toggle.";'>\n";
		
		// CONTENT GOES HERE !!!
		
		echo "	</div>\n";
		Display::bottom($this->title, $this->key, $open, $bottom);
		echo "</div>\n";
	}
	

	/**
	 * Display a collapsable section in the form.
	 *
	 */
	public function report() {
		echo "<div class='wmtPrnMainContainer'>\n";
		echo "  <div class='wmtPrnCollapseBar'>\n";
		echo "    <span class='wmtPrnChapter'>" . $this->title . "</span>\n";
		echo "  </div>\n";
		echo "  <div class='wmtPrnCollapseBox'>\n";
		echo "	  <table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
		echo "      <tr>\n";
		echo "		  <td class='wmtPrnLabelCenterBorderB' style='width: 95px'>\n";
		echo "          <br/>CONTENT BELONGS HERE<br/><br/>\n";
		echo "        </td>\n";
		echo "      </tr>\n";
		echo "    </table>\n";
		echo "	</div>\n";
		echo "</div>\n";
	}
	

	/**
	 * Returns an array of valid database fields for the object. Note that this
	 * function only returns fields that are defined in the object and are
	 * columns of the specified database.
	 *
	 * @return array list of database field names
	 */
	public function listFields() {
		if (!$this->form_table)
			throw new \Exception('wmtBaseModule::listFields - no form table name available.');
		
		$fields = array();
		$columns = sqlListFields($this->form_table);
		foreach ($columns AS $property) {
			$fields[] = $property;
		}
		
		return $fields;
	}
	
}

?>