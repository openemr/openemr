<?php
/** **************************************************************************
 *	wmtDiagPlan.module.php
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
include($GLOBALS['srcdir'] . '/wmt-v2/favorites.inc');

/**
 * Provides standardized processing for many forms.
 *
 * @package wmt
 * @subpackage base
 */
class DiagPlanModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of form class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtAllergyModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		$this->diag_window_mode = 'enc';
		$this->use_checkbox = checkSettingMode('wmt::diag_link_checkbox', '', 'pedi_wcc');
		
		// retrieve allergy data
		$this->diagnoses = GetProblemsWithDiags($form_data->pid, $this->diag_window_mode, $form_data->encounter);

		return;
	}
	
	
	/**
	 * Display a collapsable section in the form.
	 *
	 * @param boolean $toggle - true section open, false section collapsed
	 */
	public function display($open=false, $bottom=false) {
		$this->toggle = ($open)? 'block' : 'none';

		$pid = &$this->form_data->pid;
		$encounter = &$this->form_data->encounter;
		$frmdir = $this->form_data->form_name;
		$dt = &$_POST;
		
		echo "<script>";
		include($GLOBALS['srcdir'] . '/wmt-v3/js/wmt.diagnosis.js');
		include($GLOBALS['srcdir'] . '/wmt-v3/js/wmt.popup.js'); ?>
		
		// This the 'Plan Favorites' popup window.
		function get_favorite(plan_field, code_field) {
			var code = document.forms[0].elements[code_field].value;
			var type = '<?php echo $diagnosis_type; ?>';
			if(arguments.length > 2) {
				type = document.forms[0].elements[arguments[2]].value;
			}
			if(!code || code == '') {
				alert("Please choose a diagnosis code before searching for a plan");
				return false;
			}
			wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/favorites/new.php?choose=yes&ctype='+type+'&code='+code+'&target='+plan_field, '_blank', 800, 600);
		}
		
<?php 		
		echo "</script>";
		
		echo "<div class='wmtMainContainer wmtMainColor'>\n";
		Display::chapter($this->title, $this->key, $open);
		echo "<div id='".$this->key."Box' class='wmtCollapseBox wmtColorBox' style='padding:0;display: ".$this->toggle.";'>\n";
		
		// CONTENT GOES HERE !!!
		$frmdir = $this->form_data->form_name;
		$diag = $this->diagnoses;
		
		$base_action = $GLOBALS['base_action'];
		$form_mode = $GLOBALS['form_mode'];
		$wrap_mode = $GLOBALS['wrap_mode'];
		
		include($GLOBALS['srcdir'] . '/wmt-v2/diagnosis.inc.php');
		
		echo "	</div>\n";
		Display::bottom($this->title, $this->key, $open, $bottom);
		echo "</div>\n";
	}


	/**
	 * Print a collapsable section in the report.
	 *
	 */
	public function report() {
		$encounter = $this->form_data->encounter;
		$frmdir = $this->form_data->form_name;
		$diag = $this->diagnoses;
		
		$chp_printed = false;
	
		include($GLOBALS['srcdir'] . '/wmt-v2/form_views/diag_view.php');
		
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

		$max_diags = 0;
		if( isset($dt['tmp_diag_cnt'])) { $max_diags = $dt['tmp_diag_cnt']; }
		if( isset($dt['tmp_diag_window_mode'])) { $this->diag_window_mode = trim($dt['tmp_diag_window_mode']); }
		
		$cnt = 1;
		while ($cnt <= $max_diags) {
			if ($this->use_checkbox) {
				if (!isset($dt['dg_link_'.$cnt])) $dt['dg_link_'.$cnt] = '';
				if ($dt['dg_link_'.$cnt]) { 
					LinkDiagnosis($pid, $dt['dg_id_'.$cnt], $encounter, $dt['dg_seq_'.$cnt]);
				} else {
					UnLinkDiagnosis($pid, $dt['dg_id_'.$cnt], $encounter);
				}
			}
			
			UpdateDiagnosis($pid, $dt['dg_id_'.$cnt], 
				$dt['dg_code_'.$cnt], $dt['dg_title_'.$cnt],
				$dt['dg_plan_'.$cnt], $dt['dg_begdt_'.$cnt],
				$dt['dg_enddt_'.$cnt], $dt['dg_type_'.$cnt], 
				$dt['dg_remain_'.$cnt], $dt['dg_seq_'.$cnt], 
					$encounter);
			
			$cnt++;
		}
		
		
		if($form_mode == 'diag') {
			AddDiagnosis($pid, $encounter, $dt['dg_type'], $dt['dg_code'], $dt['dg_title'],
					$dt['dg_code_plan'],$dt['dg_begdt'],$dt['dg_enddt'],$dt['dg_seq']);
			
			// Clear the variables to input another
			$dt['dg_seq']='';
			$dt['dg_type']='';
			$dt['dg_code']='';
			$dt['dg_title']='';
			$dt['dg_begdt']='';
			$dt['dg_enddt']='';
			$dt['dg_code_plan']='';
			$dt['tmp_dg_desc']='';

			$form_focus = 'dg_code';
			$scroll_point = 'tmp_dg_desc';
	
		} else if ($form_mode == 'deldiag') {
			$cnt = trim($_GET['itemID']);
			DeleteDiagnosis($pid, $dt['dg_id_'.$cnt]);
	
		} else if ($form_mode == 'linkdiag') {
			$cnt = trim($_GET['itemID']);
			LinkDiagnosis($pid, $dt['dg_id_'.$cnt], $encounter, $dt['dg_seq_'.$cnt]);
	
		} else if ($form_mode == 'unlinkdiag') {
			$cnt = trim($_GET['itemID']);
			UnLinkDiagnosis($pid, $dt['dg_id_'.$cnt], $encounter);
	
		} else if ($form_mode == 'unlinkalldiags') {
			$max = $dt['tmp_diag_cnt'];
			$cnt = 1;
			while($cnt <= $max) {
				UnLinkDiagnosis($pid, $dt['dg_id_'.$cnt], $encounter);
				$cnt++;
			}
	
		} else if ($form_mode == 'updatediag') {
			$cnt = trim($_GET['itemID']);
			UpdateDiagnosis($pid, $dt['dg_id_'.$cnt], $dt['dg_code_'.$cnt], $dt['dg_title_'.$cnt],
					$dt['dg_plan_'.$cnt], $dt['dg_begdt_'.$cnt], $dt['dg_enddt_'.$cnt],
					$dt['dg_type_'.$cnt], $dt['dg_remain_'.$cnt], $dt['dg_seq_'.$cnt],
					$encounter);
	
		} else if ($form_mode == 'window') {
			if (isset($_GET['disp'])) $this->diag_window_mode = substr($_GET['disp'], 0, 3);
			$max = $dt['tmp_diag_cnt'];
			$cnt = 1;
			while ($cnt <= $max) {
				unset ($dt['dg_link_'.$cnt]);
				$cnt++;
			}
	
		} else if ($form_mode == 'fav') {
			$cnt = 0;
			if (isset($_GET['itemID'])) $cnt=trim($_GET['itemID']);
			if ($cnt) {	
				$test = AddFavorite($dt['dg_type_'.$cnt], $dt['dg_code_'.$cnt],
						$dt['dg_plan_'.$cnt]);
			} else {
				$test = AddFavorite($dt['dg_type'], $dt['dg_code'], $dt['dg_code_plan']);
			}
		}
		
		// refresh diag/plan data
		$this->diagnoses = GetProblemsWithDiags($pid, $this->diag_window_mode, $encounter);
		
		return $form_focus;
	}
	
	/**
	 * Save data from a form object into the database.
	 *
	 * @return int $id identifier for object
	 */
	public function store() {
		$pid = &$this->form_data->pid;
		$encounter = &$this->form_data->encounter;
		$dt = &$_POST;

		$max_diags = 0;
		if( isset($dt['tmp_diag_cnt'])) { $max_diags = $dt['tmp_diag_cnt']; }
		
		if( isset($dt['dg_code']) ) {
	 		AddDiagnosis($pid,$encounter,$dt['dg_type'], $dt['dg_code'],
				$dt['dg_title'], $dt['dg_code_plan'], $dt['dg_begdt'],
				$dt['dg_enddt'], $dt['dg_seq']);
		}

		$cnt = 1;
		while ($cnt <= $max_diags) {
			if ($this->use_checkbox) {
				if (!isset($dt['dg_link_'.$cnt])) $dt['dg_link_'.$cnt] = '';
				if ($dt['dg_link_'.$cnt]) { 
					LinkDiagnosis($pid, $dt['dg_id_'.$cnt], $encounter, $dt['dg_seq_'.$cnt]);
				} else {
					UnLinkDiagnosis($pid, $dt['dg_id_'.$cnt], $encounter);
				}
			}
			
			UpdateDiagnosis($pid, $dt['dg_id_'.$cnt], 
				$dt['dg_code_'.$cnt], $dt['dg_title_'.$cnt],
				$dt['dg_plan_'.$cnt], $dt['dg_begdt_'.$cnt],
				$dt['dg_enddt_'.$cnt], $dt['dg_type_'.$cnt], 
				$dt['dg_remain_'.$cnt], $dt['dg_seq_'.$cnt], 
					$encounter);
			
			$cnt++;
		}
		
	}
	
}
?>