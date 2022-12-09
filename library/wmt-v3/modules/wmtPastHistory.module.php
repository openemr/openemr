<?php
/** **************************************************************************
 *	PASTHISTORY.MODULE.PHP
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
 * Provides standardized processing for many forms.
 *
 * @package wmt
 * @subpackage base
 */
class PastHistoryModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of form class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtPastHistoryModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		$limit = ($form_data->id)? $form_data->encounter : '';
				
		// retrieve past history data
		$this->pmh = GetMedicalHistory($form_data->pid, $limit);
	
		return;
	}
	
	
	/**
	 * Display a collapsable section in the form.
	 *
	 * @param boolean $toggle - true section open, false section collapsed
	 */
	public function display($open=false, $bottom=false) {
		$this->toggle = ($open)? 'block' : 'none';
		
		echo "<div class='wmtMainContainer wmtMainColor'>\n";
		Display::chapter($this->title, $this->key, $open);
		echo "<div id='".$this->key."Box' class='wmtCollapseBox wmtColorBox' style='padding:0 0 10px;display: ".$this->toggle.";'>\n";
	
		// CONTENT GOES HERE !!!
		$pmh = &$this->pmh;
		$base_action = $GLOBALS['base_action'];
		$dt['fyi_pmh_nt'] = $this->form_data->pmh_notes;
		$frmdir = $this->form_data->form_name;
		
		include($GLOBALS['srcdir'] . '/wmt-v2/past_med_history.inc.php');
	
		echo "	</div>\n";
		Display::bottom($this->title, $this->key, $open, $bottom);
		echo "</div>\n";
	}


	/**
	 * Print a collapsable section in the report.
	 *
	 */
	public function report() {
		$pmh = &$this->pmh;
		$base_action = $GLOBALS['base_action'];
		$dt['fyi_pmh_nt'] = $this->form_data->medhist_notes;
		include($GLOBALS['srcdir'] . '/wmt-v2/past_med_history.print.inc.php');
	}
	
	
	/**
	 * Update data from a form object into the database.
	 *
	 * @return int $id identifier for object
	 */
	public function update($form_mode=false) {
		$pid = &$this->form_data->pid;
		$encounter = &$this->form_data->encounter;
		$dt = &$_POST;

		if ($form_mode === false) {
			return;
			
		} else if ($form_mode == 'pmh') {
			$mh_id = AddMedicalHistory($pid, $dt['pmh_type'], '', $dt['pmh_nt']);
			if($mh_id) { LinkListEntry($pid, $mh_id, $encounter, 'wmt_med_history'); }

			$dt['pmh_type']='';
			$dt['pmh_nt']='';
	
			$form_focus='pmh_type';
	
		} else if ($form_mode == 'updatepmh') {
			$cnt = trim($_GET['itemID']);
			UpdateMedicalHistory($pid, $dt['pmh_id_'.$cnt], $dt['pmh_type_'.$cnt], '', $dt['pmh_nt_'.$cnt]);
	
		} else if ($form_mode == 'delpmh') {
			$cnt = trim($_GET['itemID']);
			DeleteMedicalHistory($pid, $dt['pmh_id_'.$cnt], $dt['pmh_num_links_'.$cnt]);
	
		} else if ($form_mode == 'unlinkpmh') {
			$cnt = trim($_GET['itemID']);
			UnlinkListEntry($pid, $dt['pmh_id_'.$cnt], $encounter, 'wmt_med_history');
	
		}
		
		// refresh allergy data
		$limit = ($this->form_data->id)? $encounter : '';
		$this->pmh = GetMedicalHistory($pid, $limit);
		
		return $form_focus;
	}
	
				
	
	/**
	 * Stores data from a form object into the database.
	 *
	 * @return int $id identifier for object
	 */
	public function store() {
		$id = &$this->form_data->id;
		$pid = &$this->form_data->pid;
		$encounter = &$this->form_data->encounter;
		$dt = &$_POST;

		if (!$id) {
			// link all to new form
			foreach($this->pmh as $prev) {
				LinkListEntry($pid, $prev['id'], $encounter, 'wmt_med_history');
			}
		}
		
		// store notes with form data
		$this->form_data->pmh_notes = $dt['fyi_pmh_nt'];
	}
		
}
?>