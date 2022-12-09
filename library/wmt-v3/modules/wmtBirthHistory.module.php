<?php
/** **************************************************************************
 *	BIRTH HISTORY MODULE
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
class BirthHistoryModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of form class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtBirthHistoryModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		$limit = ($form_data->id)? $form_data->encounter : '';
		$frmdir = $this->form_data->form_name;
				
		// retrieve pregnancy data
		$this->pregnancies = getPastPregnancies($form_data->pid, $limit);

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
		$obhist = $this->pregnancies;
		$dt['fyi_pp_nt'] = $this->form_data->bhist_notes;
		$base_action = $GLOBALS['base_action'];
		$unlink_allow = false;
		$delete_allow = false;
		$portal_mode = false;
		$show_ob_totals = false;
		include($GLOBALS['srcdir'] . '/wmt-v2/past_pregnancies.inc.php');
	
		echo "	</div>\n";
		Display::bottom($this->title, $this->key, $open, $bottom);
		echo "</div>\n";
	}


	/**
	 * Print a collapsable section in the report.
	 *
	 */
	public function report() {
		$hosp = $this->hospitals;
		$dt['fyi_admissions_nt'] = $this->form_data->bhist_notes;
		$pedi_cat_title = 'Birth History / Hospitalizations';
		$base_action = $GLOBALS['base_action'];
		
		$chp_printed = false;
		
		include($GLOBALS['srcdir'] . '/wmt-v2/hospitalizations.print.inc.php');
			
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
			
		} else if($form_mode == 'pp') {
			$pp_id = AddPP($pid, $dt['pp_date_of_pregnancy'], $dt['pp_ga_weeks'],
				$dt['pp_labor_length'], $dt['pp_weight_lb'], $dt['pp_weight_oz'],
				$dt['pp_sex'], $dt['pp_delivery'], $dt['pp_anes'], $dt['pp_place'],
				$dt['pp_preterm'], $dt['pp_comment'], $dt['pp_doc'], $dt['pp_conception']);
			
			if ($pp_id) { LinkListEntry($pid, $pp_id, $encounter, 'past_pregnancy'); }
			
			$dt['pp_id'] = $dt['pp_date_of_pregnancy'] = $dt['pp_ga_weeks'] = '';
			$dt['pp_labor_length'] = $dt['pp_weight_lb']= $dt['pp_weight_oz'] = '';
			$dt['pp_sex'] = $dt['pp_delivery'] = $dt['pp_anes'] = $dt['pp_place']= '';
			$dt['pp_preterm'] = $dt['pp_comment'] = $dt['pp_doc'] = $dt['pp_conception'] = '';

			$form_focus='pp_date_of_pregnancy';

		} else if($form_mode == 'updatepp') {
			$cnt = trim($_GET['itemID']);
			UpdatePP($pid, $dt['pp_id_'.$cnt], $dt['pp_date_of_pregnancy_'.$cnt],
				$dt['pp_ga_weeks_'.$cnt], $dt['pp_labor_length_'.$cnt],
				$dt['pp_weight_lb_'.$cnt], $dt['pp_weight_oz_'.$cnt], $dt['pp_sex_'.$cnt],
				$dt['pp_delivery_'.$cnt], $dt['pp_anes_'.$cnt], $dt['pp_place_'.$cnt],
				$dt['pp_preterm_'.$cnt], $dt['pp_comment_'.$cnt], $dt['pp_doc_'.$cnt],
				$dt['pp_conception_'.$cnt]);

		} else if($form_mode == 'delpp') {
			$cnt = trim($_GET['itemID']);
			DeletePP($pid, $dt['pp_id_'.$cnt], $dt['pp_num_links_'.$cnt]);
	
		} else if ($form_mode == 'unlinkpp') {
			$cnt = trim($_GET['itemID']);
			UnlinkListEntry($pid, $dt['pp_id_'.$cnt], $encounter, 'past_pregnancy');
		}
			
		// refresh surgery data
		$limit = ($this->form_data->id)? $encounter : '';
		$this->pregnancies = getPastPregnancies($pid, $limit);
		
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
			foreach($this->pregnancies as $prev) {
				LinkListEntry($pid, $prev['id'], $encounter, 'past_pregnancy');
			}
		}
		
		// store notes with form data
		$this->form_data->bhist_notes = $dt['fyi_pp_nt'];
	}
		
		
}
?>