<?php
/** **************************************************************************
 *	MEDICATION.MODULE.PHP
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
class MedHistoryModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of form class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtMedHistoryModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		$this->unlink_allow = checkSettingMode('wmt::unlink_allow');
		$limit = ($form_data->id)? $form_data->encounter : '';
				
		// determine data required
		if ($this->med_add_allowed) {
			$this->med_hist = getInactiveRxByPatient($form_data->pid, $limit);
		} else {
			$this->med_hist = getInactiveRxByPatient($form_data->pid, $limit);
		}
	
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
		$unlink_allow = $this->unlink_allow;
		$unlink_all_rx_history = $this->unlink_allow;
		$med_hist = $this->med_hist;
		$base_action = $GLOBALS['base_action'];
		$frmdir = $this->form_data->form_name;
		$dt['fyi_medhist_nt'] = $this->form_data->medhist_notes;
		
		include($GLOBALS['srcdir'] . '/wmt-v2/medications_history_erx.inc.php');
	
		echo "	</div>\n";
		Display::bottom($this->title, $this->key, $open, $bottom);
		echo "</div>\n";
	}


	/**
	 * Print a collapsable section in the report.
	 *
	 */
	public function report() {
		$unlink_allow = $this->unlink_allow;
		$unlink_all_rx_history = $this->unlink_allow;
		$med_hist = $this->med_hist;
		$base_action = $GLOBALS['base_action'];
		$dt['fyi_medhist_nt'] = $this->form_data->medhist_notes; 
		
		$chp_printed = false;
		
		include($GLOBALS['srcdir'] . '/wmt-v2/medications_history_erx.print.inc.php');
			
		if($chp_printed) { CloseChapter(); }
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
			
		} else if ($form_mode == 'updatemedhist') {
			$cnt = trim($_GET['itemID']);
			UpdatePrescription($pid, $dt['med_hist_id_'.$cnt], $dt['med_hist_comments_'.$cnt]);
			$form_focus='med_hist_comments_'.$cnt;
	
		} else if ($form_mode == 'linkmedhist') {
			$cnt = trim($_GET['itemID']);
			LinkListEntry($pid, $dt['med_hist_id_'.$cnt], $encounter, 'prescriptions');
	
		} else if($form_mode == 'unlinkmedhist') {
			$cnt = trim($_GET['itemID']);
			UnlinkListEntry($pid, $dt['med_hist_id_'.$cnt], $encounter, 'prescriptions');
	
		} else if ($form_mode == 'unlinkallmedhist') {
			$unlink = getLinkedPrescriptionsByPatient($pid, $encounter, '<=0');
			foreach ($unlink as $rx) {
				UnlinkListEntry($pid, $rx['id'], $encounter, 'prescriptions');
			}
	
		} else if($form_mode == 'medhistwindow') {
			if (isset($_GET['disp'])) { $dt['tmp_mhist_window_mode'] = trim($_GET['disp']); }

		} else if($form_mode == 'medhistlink') {
			if (isset($_GET['link'])) { $dt['tmp_mhist_link_mode'] = trim($_GET['link']); }
		
		}
		
		// if record exists, limit to this encounter
		$limit = ($this->form_data->id)? $encounter : '';
		
		// determine data required
		if ($this->med_add_allowed) {
			$this->med_hist = getInactiveRxByPatient($pid, $limit);
		} else {
			$this->med_hist = getInactiveRxByPatient($pid, $limit);
		}
	
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
			foreach($this->med_hist as $prev) {
				LinkListEntry($pid, $prev['id'], $encounter, 'prescriptions');
			}
		}
		
		// store notes with form data
		$this->form_data->medhist_notes = $dt['fyi_medhist_nt'];
	}
		
		
}
?>