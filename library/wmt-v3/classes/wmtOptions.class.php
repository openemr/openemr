<?php
/** **************************************************************************
 *	OPTIONS CLASS
 *
 *	Copyright (c)2016 - Medical Technology Services <MDTechSvcs.com>
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
 *  @subpackage options
 *  @version 2.0.0
 *  @category General List Utility Functions
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/** 
 * Provides general utility functions related to the list_option table and its contents.
 *
 * @package wmt
 * @subpackage Options
 */
class Options {
	/** 
	 * Class variables
	 */	
	public $id;  // list identifier
	public $list; // content of list by key
	
	/**
	 * Creates the list class variables and initializes the object. 
	 *
	 * @param id - list table id
	 */
	public function __construct($id=false, $entry=false) {
		// list id is required
		if (!$id || $id == '')
			throw new \Exception("wmtOptions::__construct - no list identifier provided");

		// set default class variables
		$this->id = $id;
		$this->list = array();
		
		// retrieve list contents
		$binds = array($id);
		$query = "SELECT * FROM `list_options` WHERE `list_id` = ? AND `activity` = 1 ";
		if ($entry) {
			$query .= "AND `option_id` LIKE ? ";
			$binds[] = $entry;
		}
		$query .= "ORDER BY `seq`, `title`";
		$result = sqlStatementNoLog($query,$binds);
		
		// store results
		if ($result) {
			while ($row = sqlFetchArray($result)) {
				if ($entry) {
					$this->entry = $row;
					break;
				} else {
					$this->list[$row['option_id']] = $row;
				}
			}
		}
	}		
	
	/**
	 * Returns the translation of a list key value.
	 *
	 * @param itemId - entry id in table
	 * @param result - default value if none found
	 * 
	 */
	public function getItem($id, $result='') {
		if ($this->list[$id])
			$result = $this->list[$id]['title']; // title from list

		if ($result == '' && $id == 'occurrence')
				$result = 'Unknown or N/A';

		return $result;
	}
	
	/**
	 * Returns the translation of a list key value.
	 *
	 * @param itemId - entry id in table
	 * @param result - default value if none found
	 * 
	 */
	public function showItem($id, $result='') {
		echo $this->getItem($id, $result);
	}
	
	/**
	 * Build selection list from table data.
	 *
	 * @param itemId - current entry id
	 * @param result - string html option list
	 */
	public function getOptions($id, $default='') {
		$result = '';
		
		// create default if needed
		if (!$id && $default) {
			$result .= "<option value='' ";
			$result .= (!$id || $id == '')? "disabled selected hidden " : "";
			$result .= ">".$default."</option>\n";
		}

		// build options
		$in_group = false;
		foreach ($this->list AS $item) {
			if (strtolower($item['notes']) == 'group') {
				if ($in_group) $result .= "</optgroup>\n";
				$result .= "<optgroup label='" . $item['title'] ."'>\n";	
				$in_group = true;
			} else {
				$result .= "<option value='" . $item['option_id'] . "' ";
				if ((!$id && !$default && $item['is_default']) || $id == $item['option_id']) 
					$result .= "selected ";
				$result .= ">" . $item['title'] ."</option>\n";
			}
		}
		if ($in_group) $result .= "</optgroup>\n";
		
		return $result;
	}

	/**
	 * Build selection list from table data.
	 *
	 * @param itemId - current entry id
	 * @param result - string html option list
	 */
	public function getOptionsWithTitle($id, $default='') {
		$result = '';
		
		// create default if needed
		if (!$id && $default) {
			$result .= "<option value='' ";
			$result .= (!$id || $id == '')? "disabled selected hidden " : "";
			$result .= ">".$default."</option>\n";
		}

		// build options
		$in_group = false;
		foreach ($this->list AS $item) {
			$result_data = "";
			if(!empty($item['option_id'])) {
				$result_data = sqlQueryNoLog("SELECT `id`, `name`, `title` FROM `templates` WHERE `name` LIKE ?", array($item['option_id']));
			}

			$item_title = $item['title'];
			if(isset($result_data) && isset($result_data['title']) && !empty($result_data['title'])) {
				$item_title = $result_data['title']."_".$item['title'];
			}

			if (strtolower($item['notes']) == 'group') {
				if ($in_group) $result .= "</optgroup>\n";
				$result .= "<optgroup label='" . $item_title ."'>\n";	
				$in_group = true;
			} else {
				$result .= "<option value='" . $item['option_id'] . "' ";
				if ((!$id && !$default && $item['is_default']) || $id == $item['option_id']) 
					$result .= "selected ";
				$result .= ">" . $item_title ."</option>\n";
			}
		}
		if ($in_group) $result .= "</optgroup>\n";
		
		return $result;
	}
	
	/**
	 * Echo selection option list from table data.
	 *
	 * @param itemId - current entry id
	 * @param result - string html option list
	 */
	public function showOptions($id, $default='') {
		echo $this->getOptions($id, $default);
	}
	
	
	/**
	 * Build selection list from table data.
	 *
	 * @param itemId - current entry id
	 * @param result - string html option list
	 */
	public function getChecks($id) {
		$result = '';
	
		// build options
		foreach ($this->list AS $item) {
			$result .= "<div style='float:left'>";
			$result .= "<input type='checkbox' value='1' id='" . $item['option_id'] . "' name='" . $item['option_id'] . "' ";
			if ($id == $item['option_id']) $result .= "checked ";
			$result .= "/><label for='" . $item['option_id'] . "' style='margin-right:15px'>" . $item['title'] ."</label></div>\n";
		}
	
		return $result;
	}
	
	/**
	 * Echo selection option list from table data.
	 *
	 * @param itemId - current entry id
	 * @param result - string html option list
	 */
	public function showChecks($id) {
		echo $this->getChecks($id);
	}
	
	
}
?>