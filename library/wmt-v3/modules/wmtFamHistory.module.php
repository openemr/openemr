<?php
/** **************************************************************************
 *	wmtFamHistory.module.php
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
class FamHistoryModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of form class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtFamHistoryModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		$limit = ($form_data->id)? $form_data->encounter : '';
				
		// retrieve allergy data
		$this->fh = GetFamilyHistory($form_data->pid, $limit);
	
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
		$fh = $this->fh;
		$frmdir = $this->form_data->form_name;
		$base_action = $GLOBALS['base_action'];
		$dt['fyi_fh_nt'] = $this->form_data->fh_notes;
		$dt['db_fh_non_contrib'] = $this->form_data->fh_non_contrib;
		$dt['db_fh_adopted'] = $this->form_data->fh_non_contrib;
		
		include($GLOBALS['srcdir'] . '/wmt-v2/family_history.inc.php');
	
		echo "	</div>\n";
		Display::bottom($this->title, $this->key, $open, $bottom);
		echo "</div>\n";
	}


	/**
	 * Print a collapsable section in the report.
	 *
	 */
	public function report() {
		$fh = $this->fh;
		
		include($GLOBALS['srcdir'] . '/wmt-v2/family_history.print.inc.php');

		$nt = '';
		if ($this->form_data->fh_non_contrib) { $nt = 'Non-Contributory'; }
		if ($this->form_data->fh_adopted) { $nt = AppendItem($nt,'Patient is Adopted'); }

		if (!empty($nt)) {
			if ($chp_printed) {
				echo "	</table>\n";
				echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
			}
			$chp_printed = PrintChapter('Family History', $chp_printed);
			PrintSingleLine($nt);
		}
		
		$nt = trim($this->form_data->fh_notes);
		if (!empty($nt)) {
			if ($chp_printed) {
				echo "	</table>\n";
				echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
			}
			$chp_printed = PrintChapter('Family History', $chp_printed);
			PrintOverhead('Other Notes:', $nt);
		}
		
		if ($chp_printed) { CloseChapter(); }
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
			
		} else if ($form_mode == 'fh') {
			$fh_id = AddFamilyHistory($pid, $dt['fh_who'], $dt['fh_type'], $dt['fh_nt'], $dt['fh_dead'], $dt['fh_age'], $dt['fh_age_dead']);
			if ($fh_id) { LinkListEntry($pid, $fh_id, $encounter, 'wmt_family_history'); }

			$dt['fh_type']='';
			$dt['fh_nt']='';

			$form_focus='fh_who';
	
		} else if ($form_mode == 'delfh') {
			$cnt = trim($_GET['itemID']);
			DeleteFamilyHistory($pid, $dt['fh_id_'.$cnt], $dt['fh_num_links_'.$cnt]);
	
		} else if ($form_mode == 'updatefh') {
			$cnt = trim($_GET['itemID']);
			UpdateFamilyHistory($pid, $dt['fh_id_'.$cnt], $dt['fh_who_'.$cnt], $dt['fh_type_'.$cnt], $dt['fh_nt_'.$cnt], $dt['fh_dead_'.$cnt], $dt['fh_age_'.$cnt], $dt['fh_age_dead_'.$cnt]);
	
		} else if ($form_mode == 'unlinkfh') {
			$cnt = trim($_GET['itemID']);
			UnlinkListEntry($pid, $dt['fh_id_'.$cnt], $encounter, 'wmt_family_history');
	
		}
		
		// refresh allergy data
		$limit = ($this->form_data->id)? $encounter : '';
		$this->fh = GetFamilyHistory($pid, $limit);
		
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
			foreach($this->fh as $prev) {
				LinkListEntry($pid, $prev['id'], $encounter, 'wmt_family_history');
			}
		}
		
		// store notes with form data
		$this->form_data->fh_notes = $dt['fyi_fh_nt'];
		$this->form_data->fh_non_contrib = $dt['db_fh_non_contrib'];
		$this->form_data->fh_adopted = $dt['db_fh_adopted'];
	}
		
		
}
?>