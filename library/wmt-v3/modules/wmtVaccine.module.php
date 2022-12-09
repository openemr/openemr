<?php
/** **************************************************************************
 *	VACCINE.MODULE.PHP
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
 * @subpackage modules
 */
class VaccineModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of form class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtVaccineModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
			
		// retrieve allergy data
		$limit = ($form_data->id)? $form_data->encounter : '';
		$this->immunizations = GetAllImmunizationsbyPatient($form_data->pid, $limit);
		
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
		echo "<div id='".$this->key."Box' class='wmtCollapseBox wmtColorBox' style='padding:0;display: ".$this->toggle.";'>\n";
	
		// CONTENT GOES HERE !!!
		$imm = &$this->immunizations;
		$dt['fyi_imm_nt'] = $this->form_data->immunization_notes;
		$base_action = $GLOBALS['base_action'];
		$frmdir = $this->form_data->form_name;
		
		include($GLOBALS['srcdir'] . '/wmt-v2/immunizations.inc.php');
?>		
				<div class="wmtColorHeader" style="width:100%;text-align:left">
					<a class="css_button" href="javascript:;" onclick="wmtOpen('https://www.cdc.gov/vaccines/schedules/hcp/imz/child-adolescent-shell.html', '_blank', 800, 600);"><span>CDC Schedule</span></a>
				</div>
			</div>
		</div>
<?php 
	}


	/**
	 * Print a collapsable section in the report.
	 *
	 */
	public function report() {
		$imm = &$this->immunizations;
		$dt['fyi_imm_nt'] = $this->form_data->immunization_notes;
		$base_action = $GLOBALS['base_action'];
		include($GLOBALS['srcdir'] . '/wmt-v2/immunizations.print.inc.php');
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
			
		} else if ($form_mode == 'updateimm') {
			$cnt = trim($_GET['itemID']);
			UpdateImmunization($pid, $dt['imm_id_'.$cnt], $dt['imm_comments_'.$cnt]);
			$form_focus = 'imm_comments_'.$cnt;
	
		} else if ($form_mode == 'unlinkimm') {
			$cnt = trim($_GET['itemID']);
			UnlinkListEntry($pid, $dt['imm_id_'.$cnt], $encounter, 'immunizations');
	
		}
		
		// refresh allergy data
		$limit = ($this->form_data->id)? $encounter : '';
		$this->immunizations = GetAllImmunizationsbyPatient($pid, $limit);
		
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
			foreach($this->immunizations as $prev) {
				LinkListEntry($pid, $prev['id'], $encounter, 'immunizations');
			}
		}
			
		// store notes with form data
		$this->form_data->imm_notes = $dt['fyi_surg_nt'];
	}
	
}

?>