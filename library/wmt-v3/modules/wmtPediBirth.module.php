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
class PediBirthModule extends BaseModule {
	
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of form class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtPediBirthModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		$limit = ($form_data->id)? $form_data->encounter : '';
		
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
		
		$show_ob_totals = false;
		
		if (!isset($dt['pp_date_of_pregnancy'])) $dt['pp_date_of_pregnancy'] = '';
		if (!isset($dt['pp_ga_weeks'])) $dt['pp_ga_weeks'] = '';
		if (!isset($dt['pp_labor_length'])) $dt['pp_labor_length'] = '';
		if (!isset($dt['pp_weight_lb'])) $dt['pp_weight_lb'] = '';
		if (!isset($dt['pp_weight_oz'])) $dt['pp_weight_oz'] = '';
		if (!isset($dt['pp_sex'])) $dt['pp_sex'] = '';
		if (!isset($dt['pp_delivery'])) $dt['pp_delivery'] = '';
		if (!isset($dt['pp_anes'])) $dt['pp_anes'] = '';
		if (!isset($dt['pp_comment'])) $dt['pp_comment'] = '';

		if (!isset($dt['pp_preg_issue'])) $dt['pp_preg_issue'] = '';
		if (!isset($dt['pp_delv_issue'])) $dt['pp_delv_issue'] = '';
		if (!isset($dt['pp_hearing'])) $dt['pp_hearing'] = '';
		if (!isset($dt['pp_hepb'])) $dt['pp_hepb'] = ''; ?>

			<table style="border-collapse:collapse;width:100%">
				<tr class='wmtColorHeader' style='line-height:12px;font-size:.9em'>
					<td class="wmtLabel wmtC" style="width: 3%">Current</td>
					<td class="wmtLabel wmtC wmtBorder1L" style="width: 5%" >Date</td>
					<td class="wmtLabel wmtC wmtBorder1L" style="width: 5%">GA</td>
					<td class="wmtLabel wmtC wmtBorder1L" style="width: 5%">Pregnancy</td>
					<td class="wmtLabel wmtC wmtBorder1L" style="width: 5%">Length</td>
					<td class="wmtLabel wmtC wmtBorder1L" colspan="2">Weight</td>
					<td class="wmtLabel wmtC wmtBorder1L" style="width: 5%">Sex</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B" style="width: 7%" rowspan="2">Delivery</td>
					<td class="wmtLabel wmtC wmtBorder1L" style="width: 5%">Critical CHD</td>
					<td class="wmtLabel wmtC wmtBorder1L" style="width: 5%">Delivery</td>
					<td class="wmtLabel wmtC wmtBorder1L" style="width: 5%">Hearing</td>
					<td class="wmtLabel wmtC wmtBorder1L" style="width: 5%">Received</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B" rowspan="2">Comments / Complications</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B" rowspan="2" style="width:105px">&nbsp;</td>
				</tr>
				
				<tr class='wmtColorHeader' style='line-height:12px;font-size:.9em'>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B">Patient</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B">YYYY-MM</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B">Weeks</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B">Problems</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B">of Labor</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B" style="width: 3%">lb.</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B" style="width: 3%">oz.</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B">M/F</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B">Normal</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B">Problems</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B">Test</td>
					<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B">Hep B#1</td>
				</tr>
<?php 
		$cnt = 1;
		if (count($obhist) > 0) {
			foreach($obhist as $preg) { ?>

	 			<tr>
	 				<td class="wmtBorder1B">
						<input name="pp_baby_pid_<?php echo $cnt; ?>" id="pp_baby_pid_<?php echo $cnt; ?>" class="current wmtFullInput" type="checkbox" value="<?php echo $this->form_data->pid; ?>" <?php if ($preg['pp_baby_pid'] == $this->form_data->pid) echo " checked"; ?> />
					</td>

	 				<td class="wmtBorder1L wmtBorder1B">
						<input name="pp_date_of_pregnancy_<?php echo $cnt; ?>" id="pp_date_of_pregnancy_<?php echo $cnt; ?>" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($preg['pp_date_of_pregnancy'], ENT_QUOTES, '', FALSE); ?>" />
					</td>
		
	 				<td class="wmtBorder1L wmtBorder1B">
						<input name="pp_ga_weeks_<?php echo $cnt; ?>" id="pp_ga_weeks_<?php echo $cnt; ?>" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($preg['pp_ga_weeks'], ENT_QUOTES, '', FALSE); ?>" />
					</td>
		
	 				<td class="wmtBorder1L wmtBorder1B">
						<select name="pp_preg_issue_<?php echo $cnt; ?>" id="pp_preg_issue_<?php echo $cnt; ?>" class="wmtFullInput">
	 						<?php ListSel($preg['pp_preg_issue'],'yesno'); ?>
	 					</select>
					</td>
		
	 				<td class="wmtBorder1L wmtBorder1B">
						<input name="pp_labor_length_<?php echo $cnt; ?>" id="pp_labor_length_<?php echo $cnt; ?>" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($preg['pp_labor_length'], ENT_QUOTES, '', FALSE); ?>" />
					</td>
		
	 				<td class="wmtBorder1L wmtBorder1B">
						<input name="pp_weight_lb_<?php echo $cnt; ?>" id="pp_weight_lb_<?php echo $cnt; ?>" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($preg['pp_weight_lb'], ENT_QUOTES, '', FALSE); ?>" />
					</td>
		
	 				<td class="wmtBorder1L wmtBorder1B">
						<input name="pp_weight_oz_<?php echo $cnt; ?>" id="pp_weight_oz_<?php echo $cnt; ?>" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($preg['pp_weight_oz'], ENT_QUOTES, '', FALSE); ?>" />
					</td>
		
	 				<td class="wmtBorder1L wmtBorder1B">
						<select name="pp_sex_<?php echo $cnt; ?>" id="pp_sex_<?php echo $cnt; ?>" class="wmtFullInput">
	 						<?php ListSel($preg['pp_sex'],'PP_Sex'); ?>
	 					</select>
					</td>
		
	 				<td class="wmtBorder1L wmtBorder1B">
						<select name="pp_delivery_<?php echo $cnt; ?>" id="pp_delivery_<?php echo $cnt; ?>" class="wmtFullInput">
	 						<?php ListSel($preg['pp_delivery'],'PP_Delivery'); ?>
	 					</select>
					</td>
		
		 			<td class="wmtBorder1L wmtBorder1B">
						<select name="pp_chd_<?php echo $cnt; ?>" id="pp_chd_<?php echo $cnt; ?>" class="wmtFullInput">
		 					<?php ListSel($preg['pp_chd'],'yesno'); ?>
		 				</select>
					</td>
		
	 				<td class="wmtBorder1L wmtBorder1B">
						<select name="pp_delv_issue_<?php echo $cnt; ?>" id="pp_delv_issue_<?php echo $cnt; ?>" class="wmtFullInput">
	 						<?php ListSel($preg['pp_delv_issue'],'yesno'); ?>
	 					</select>
					</td>
		
	 				<td class="wmtBorder1L wmtBorder1B">
						<select name="pp_hearing_<?php echo $cnt; ?>" id="pp_hearing_<?php echo $cnt; ?>" class="wmtFullInput">
	 						<?php ListSel($preg['pp_hearing'],'yesno'); ?>
	 					</select>
					</td>
		
	 				<td class="wmtBorder1L wmtBorder1B">
						<select name="pp_hepb_<?php echo $cnt; ?>" id="pp_hepb_<?php echo $cnt; ?>" class="wmtFullInput">
	 						<?php ListSel($preg['pp_hepb'],'yesno'); ?>
	 					</select>
					</td>
		
		 			<td class="wmtBorder1L wmtBorder1B">
						<input name="pp_comment_<?php echo $cnt; ?>" id="pp_comment_<?php echo $cnt; ?>" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($preg['pp_comment'], ENT_QUOTES, '', FALSE); ?>" /><input name="pp_id_<?php echo $cnt; ?>" id="pp_id_<?php echo $cnt; ?>" type="hidden" value="<?php echo $preg['id']; ?>" /><input name="pp_num_links_<?php echo $cnt; ?>" id="pp_num_links_<?php echo $cnt; ?>" type="hidden" value="<?php echo $preg['num_links']; ?>" />
					</td>
		
					<td class="wmtBorder1L wmtBorder1B" >
						<div style="display: inline-block; padding-left: 0px;"><a class="css_button_small" tabindex="-1" onClick="return UpdatePastPregnancy('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');" href="javascript:;" title="Update this entry"><span>Update</span></a></div>
						<div style="display: inline-block; padding-left: 0px;"><a class="css_button_small" tabindex="-1" onClick="return DeletePastPregnancy('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');" href="javascript:;" title="Delete this entry"><span>Delete</span></a></div>
					</td>
		 		</tr>
<?php
				$cnt++;
			}
		} ?>
		
				<tr>
		 			<td class="wmtBorder1B">
		 				<input name="pp_baby_pid" id="pp_baby_pid" class="wmtFullInput" type="checkbox" value="<?php echo $this->form_data->pid ?>" />
		 			</td>

		 			<td class="wmtBorder1L wmtBorder1B">
		 				<input name="pp_date_of_pregnancy" id="pp_date_of_pregnancy" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt['pp_date_of_pregnancy'], ENT_QUOTES, '', FALSE); ?>" />
		 			</td>

		 			<td class="wmtBorder1L wmtBorder1B">	
		 				<input name="pp_ga_weeks" id="pp_ga_weeks" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt['pp_ga_weeks'], ENT_QUOTES, '', FALSE); ?>" />
		 			</td>

	 				<td class="wmtBorder1L wmtBorder1B">
						<select name="pp_preg_issue" id="pp_preg_issue" class="wmtFullInput">
	 						<?php ListSel($dt['pp_preg_issue'],'yesno'); ?>
	 					</select>
					</td>
		
		 			<td class="wmtBorder1L wmtBorder1B">
		 				<input name="pp_labor_length" id="pp_labor_length" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt['pp_labor_length'], ENT_QUOTES, '', FALSE); ?>" />
		 			</td> 
		 			
		 			<td class="wmtBorder1L wmtBorder1B">
		 				<input name="pp_weight_lb" id="pp_weight_lb" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt['pp_weight_lb'], ENT_QUOTES, '', FALSE); ?>" />
		 			</td> 
		 			
		 			<td class="wmtBorder1L wmtBorder1B">
		 				<input name="pp_weight_oz" id="pp_weight_oz" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt['pp_weight_oz'], ENT_QUOTES, '', FALSE); ?>" />
		 			</td>
					
					<td class="wmtBorder1L wmtBorder1B">
						<select name="pp_sex" id="pp_sex" class="wmtFullInput">
							<?php ListSel($dt['pp_sex'],'PP_Sex'); ?>
						</select>
					</td>
		 			
		 			<td class="wmtBorder1L wmtBorder1B">
		 				<select name="pp_delivery" id="pp_delivery" class="wmtFullInput">
							<?php ListSel($dt['pp_delivery'],'PP_Delivery'); ?>
						</select>
					</td>
		 			
		 			<td class="wmtBorder1L wmtBorder1B">
		 				<select name="pp_chd" id="pp_chd" class="wmtFullInput">
							<?php ListSel($dt['pp_chd'],'yesno'); ?>
						</select>
					</td>
					
	 				<td class="wmtBorder1L wmtBorder1B">
						<select name="pp_delv_issue" id="pp_delv_issue" class="wmtFullInput">
	 						<?php ListSel($dt['pp_delv_issue'],'yesno'); ?>
	 					</select>
					</td>
		
	 				<td class="wmtBorder1L wmtBorder1B">
						<select name="pp_hearing" id="pp_hearing" class="wmtFullInput">
	 						<?php ListSel($dt['pp_hearing'],'yesno'); ?>
	 					</select>
					</td>
		
	 				<td class="wmtBorder1L wmtBorder1B">
						<select name="pp_hepb" id="pp_hepb" class="wmtFullInput">
	 						<?php ListSel($dt['pp_hepb'],'yesno'); ?>
	 					</select>
					</td>
		
		 			<td class="wmtBorder1L wmtBorder1B">
		 				<input name="pp_comment" id="pp_comment" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt['pp_comment'], ENT_QUOTES, '', FALSE); ?>" />
		 			</td>
					<td class="wmtBorder1L wmtBorder1B">&nbsp;</td>
				</tr>
				
				<tr>
					<td class="wmtColorHeader wmtBorder1B" colspan="5">
						<a class="css_button" onClick="return SubmitPastPregnancy('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>');" href="javascript:;"><span>Add Another</span></a>
					</td>
					<td class="wmtColorHeader wmtBorder1B" colspan="10">
						<input name="tmp_pp_cnt" id="tmp_pp_cnt" type="hidden" value="<?php echo ($cnt-1); ?>" />&nbsp;
					</td>
				</tr>
		
				<tr>
					<td class="wmtLabel wmtPadSides">Notes:</td>
				</tr>
				<tr>
					<td class='wmtPadSides' colspan="15">
						<textarea name="fyi_pp_nt" id="fyi_pp_nt" rows="4" class="wmtFullInput"><?php echo htmlspecialchars($dt['fyi_pp_nt'], ENT_QUOTES, '', FALSE); ?></textarea>
					</td>
				</tr>
			</table>

			<script>	
				$('input.current').on('click', function() { 
					$('input.current').not(this).prop('checked', false);
				});
			</script>
		
<?php
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
		
		include($GLOBALS['srcdir'] . '/wmt-v2/past_pregnancies.print.inc.php');
			
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
			$pp_id = AddPP($pid, $dt);
			
			if ($pp_id) { LinkListEntry($pid, $pp_id, $encounter, 'past_pregnancy'); }
			
			$dt['pp_id'] = $dt['pp_date_of_pregnancy'] = $dt['pp_ga_weeks'] = '';
			$dt['pp_labor_length'] = $dt['pp_weight_lb']= $dt['pp_weight_oz'] = '';
			$dt['pp_sex'] = $dt['pp_delivery'] = $dt['pp_anes'] = $dt['pp_place']= '';
			$dt['pp_preterm'] = $dt['pp_comment'] = $dt['pp_doc'] = $dt['pp_conception'] = '';
			$dt['pp_preg_issue'] = $dt['pp_devl_issue'] = $dt['pp_hearing'] = $dt['pp_hepb'] = '';
				
			$form_focus='pp_date_of_pregnancy';

		} else if($form_mode == 'updatepp') {
			$cnt = trim($_GET['itemID']);
			UpdatePP($pid, $cnt, $dt); 
			
		} else if($form_mode == 'delpp') {
			$cnt = trim($_GET['itemID']);
			DeletePP($pid, $dt['pp_id_'.$cnt], $dt['pp_num_links_'.$cnt]);
	
		} else if ($form_mode == 'unlinkpp') {
			$cnt = trim($_GET['itemID']);
			UnlinkListEntry($pid, $dt['pp_id_'.$cnt], $encounter, 'past_pregnancy');
		}
			
		// refresh pregnancy data
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