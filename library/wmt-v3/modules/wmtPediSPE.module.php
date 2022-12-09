<?php
/** **************************************************************************
 *	wmtPediSPE.module.php
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
require_once($GLOBALS['srcdir'].'/options.inc.php');
class PediSPEModule extends BaseModule {
	// these must be class variables to 
	// pass between class functions
	private $max_cells = 3;
	private $last_group;
	private $item_count;
	private $cell_count;
	
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtPediSPEModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		
		// base or age-specific layout
		$this->layout_key = $form_data->form_type.'_'.strtolower($sec_data['prefix']);
		$query = "SELECT `form_id` FROM `layout_options` WHERE `form_id` LIKE ? AND `uor` > 0 ";
		$test = sqlQuery($query,array($this->layout_key));
		if ($test && $test['form_id']) { // age-specific form
			if ($form_data->form_version) $this->title .= " - " . $form_data->form_version;
		} else {
			$this->layout_key = $sec_data['prefix']; // use default layout
		}
		
		// load layout information
		$this->pedi_data = PediLayout::fetchEncounter($this->layout_key, $this->form_data->pid, $this->form_data->encounter);
			
		// no layout, module is inactive
		if (empty($this->pedi_data->layout_list)) $this->active = false;
		
		return;
	}
	
	
	/**
	 * Display a collapsable section in the form.
	 *
	 */
	public function display($open=false, $bottom=false) {
		$this->toggle = ($open)? 'block' : 'none'; 
		$wrap_mode = ($this->pedi_data->id)? 'update' : 'new';
		
		$vitals = 'g'; // grade school default
		if ($this->form_data->form_type == 'WCC0' || 
				$this->form_data->form_type == 'WCC2' || 
				$this->form_data->form_type == 'WCC4' || 
				$this->form_data->form_type == 'WCC6' || 
				$this->form_data->form_type == 'WCC9')
			$vitals = 'i'; // infant
		
		if ($this->form_data->form_type == 'WCC12' || 
				$this->form_data->form_type == 'WCC15' || 
				$this->form_data->form_type == 'WCC18')
			$vitals = 't'; // toddler
		
		if ($this->form_data->form_type == 'WCC24' || 
				$this->form_data->form_type == 'WCC30')
			$vitals = 'p'; // preschool
		
		// get list data
		$grade_list = new Options('one_to_six');
		
		// initialize 
		$this->last_group = '';
		$this->cell_count = 0;
		$this->item_count = 0;
		$big_cell = false; ?>

		<script>
			// This invokes the find-vitals popup.
			function get_vitals() {
				 wmtOpen('../../../custom/vital_choice_popup.php', '_blank', 500, 400);
			}

			// This is for callback by the vitals popup.
			function set_vitals(Vals) {
				var test = Vals.length;
				var vid = Vals[0];
				if (vid) {
					$('#form_vis').val(vid);
					$('#form_vital_ht').val(Vals[1]);
					$('#form_vital_wt').val(Vals[2]);
					$('#form_vital_hr').val(Vals[5]);
					$('#form_vital_timestamp').val(Vals[8]);
					$('#form_vital_pox').val(Vals[15]);
					$('#form_vital_temp').val(Vals[17]);
					$('#form_vital_hc').val(Vals[21]);
					$('#form_vital_resp').val(Vals[16]);

					var bps = Vals[3]; // seated
					if (!bps) bps = Vals[10]; // prone
					if (!bps) bps = Vals[12]; // standing
					var bpd = Vals[4];
					if (!bpd) bpd = Vals[11]; // prone
					if (!bpd) bpd = Vals[13]; // standing

					$('#form_vital_bps').val(bps);
					$('#form_vital_bpd').val(bpd);
					
				}
			}

			// Reset to specific values
			function setSPE(task) {
				$('#<?php echo $this->key ?>Box').find(':input').each(function() {
					if ($(this).attr('id') && $(this).attr('id').substr(0,10) != 'form_vital') {
						switch(this.type) {
							case 'text':
							case 'textarea':
							case 'select-one':
							case 'select-multiple':
								$(this).val('');
								if (task == 'default') $(this).val($(this).attr('default'));
								break;
							case 'checkbox':
							case 'radio':
								$(this).attr('checked',false);
								if (task == 'default' && $(this).attr('default') == 1) $(this).attr('checked','checked');
								break;
						}
					}
				});
			}

			// Wait until page loaded
			$(document).ready(function() {
				// Pop grade for murmur 'yes'
				$('#form_card_mur_dd').change(function() {
					var murmur = $(this).find(":selected").val();
					if (murmur == 'YES') { 
						$('#card_mur_pop').show(); }
					else {
						$('#card_mur_pop').hide(); }
				});
			});
			
		</script>

		<div class='wmtMainContainer wmtColorMain'>
			<?php Display::chapter($this->title, $this->key, $open); ?>
			<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
			
				<table style="width:100%">
					<tr>
						<td class="wmtLabel">
							Vital Signs:
							<input type='hidden' id='form_vid' value='' />
						</td>
						<td class='wmtLabelRed' colspan='2'>Vitals Taken: <input name='form_vital_timestamp' id='form_vital_timestamp' type='text' class='wmtLabelRed' readonly="readonly" style='font-size:12px' tabstop='-1' value="<?php echo $dt['form_vital_timestamp']; ?>" /></td>
						<td colspan="5" class="wmtBodyR">
				        	<a href="javascript:;" class="css_button" tabindex="-1" onclick="get_vitals()"><span>Vitals</span></a>&nbsp;&nbsp;
							<a class="css_button" tabindex="-1" onClick="setSPE('default');" href="javascript:;"><span>Set Default</span></a>&nbsp;&nbsp;
							<a class="css_button" tabindex="-1" onClick="setSPE();" href="javascript:;"><span>Clear Exam</span></a>
						</td>
					</tr>
					<tr><td colspan="11" style="margin: 8px;"></td></tr>
					<tr>
						<td class="wmtBodyR" style="width:130px"><?php echo ($vitals == 'i' || $vitals == 't')? 'Length' : 'Height' ?>: 
							<input name="form_vital_ht" id="form_vital_ht" class="wmtInput" type="text" style="width: 50px" value="<?php echo $dt{'form_vital_ht'}; ?>" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> /></td>
						<td class="wmtBodyR" style="width:130px">Weight:
							<input name="form_vital_wt" id="form_vital_wt" class="wmtInput" type="text" style="width: 50px" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_wt'}; ?>" /></td>
<?php if ($vitals != 'i' && $vitals != 't' && $vitals != 'p') { ?>
						<td class="wmtBodyR" style="width:160px">BP:
							<input name="form_vital_bps" id="form_vital_bps" class="wmtInput" type="text" style="width: 50px" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_bps'}; ?>" />&nbsp;/&nbsp;<input name="form_vital_bpd" id="form_vital_bpd" class="wmtInput" type="text" style="width: 50px" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_bpd'}; ?>" /></td>
<?php } ?>
						<td class="wmtBodyR" style="width:130px">Pulse:
							<input name="form_vital_hr" id="form_vital_hr" class="wmtInput" type="text" style="width: 50px" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_hr'}; ?>" onchange="NoDecimal('form_vital_hr')" /></td>
<?php if ($vitals == 'i') { ?>
						<td class="wmtBodyR" style="width:130px">HC:
							<input name="form_vital_hc" id="form_vital_hc" class="wmtInput" style="width: 50px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_hc'}; ?>" onchange="OneDecimal('form_vital_hc')" /></td>
<?php } ?>
						<td class="wmtBodyR" style="width:130px">Resp:
							<input name="form_vital_resp" id="form_vital_resp" class="wmtInput" style="width: 50px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_resp'}; ?>" onchange="NoDecimal('form_vital_resp')" /></td>
						<td class="wmtBodyR" style="width:130px">Temp:
							<input name="form_vital_temp" id="form_vital_temp" class="wmtInput" style="width: 50px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_temp'}; ?>" onchange="OneDecimal('form_vital_temp')" /></td>
						<td class="wmtBodyR" style="width:130px">POx:
							<input name="form_vital_pox" id="form_vital_pox" class="wmtInput" style="width: 50px" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_temp'}; ?>" onchange="OneDecimal('form_vital_pox')" /></td>
						<td></td>
					</tr>
				</table>
				<div class="DottedB"></div>

				<table class="wmtLBForm" style="width:100%">
					<!-- FOLLOWING LINE HELPS CONTROL COLUMN WIDTHS -->
					<tr><td style="width:10%"></td><td style="min-width:5%"></td><td style="min-width:20%"></td><td style="min-width:20%"></td></td><td style="width:45%"></td></tr>

<?php 			
		// loop through each layout record
		foreach ($this->pedi_data->layout_list AS $record) {
			if ($this->form_data->sex == 'Male' && strpos($record['group_name'], '(female)') > 0) continue;
			if ($this->form_data->sex == 'Male' && $record['field_id'] == 'chst_trnr_dd') continue; // breast tanner scale
			if ($this->form_data->sex == 'Female' && strpos($record['group_name'], '(male)') > 0) continue;
			if ($this->form_data->form_type != 'WCC9y' 
				&& $this->form_data->form_type != 'WCC11y' 
				&& $this->form_data->form_type != 'WCC15y' 
					&& $record['group_name'] == 'Chest') continue;
				
			if ($this->form_data->form_type != 'WCC11y' 
				&& $this->form_data->form_type != 'WCC15y' 
					&& $record['field_id'] == 'male_her_ck') continue; // male hernia
				
					$titlecols = $record['titlecols'];
			$datacols = $record['datacols'];
			$field_data = '';
				
			// start of new group
			if ($record['group_name'] != $this->last_group) {
				self::end_group(); 
				
				// check for right side textarea
				if ($record['data_type'] == 3 && $record['fld_rows'] > 0) { 
					$this->max_cells = 3; 
					$notes_key = 'form_'.$record['field_id']; ?>
				
					<tr>
						<td class="wmtGroup" colspan="<?php echo $this->max_cells ?>" <?php if ($this->last_group) echo 'style="height:22px;padding-top:10px;font-size:13px;"'?>>
							<?php echo substr($record['group_name'],1) ?>:
						</td>
						<td colspan='2' rowspan='<?php echo $record['fld_rows'] +1 ?>' style='padding-top:15px'>
							<?php echo $record['title']?>
							<textarea class='wmtBody wmtFullInput' ' rows='1' id='<?php echo $notes_key ?>' name='<?php echo $notes_key ?>'><?php echo $this->pedi_data->layout_data[$record['field_id']] ?></textarea>
						</td> 
					</tr>
					
<?php 			// normal group header
				} else { 
					$this->max_cells = 5; ?>
				
					<tr>
						<td class="wmtGroup" colspan="<?php echo $this->max_cells +1 ?>" <?php if ($this->last_group) echo 'style="height:22px;padding-top:10px;font-size:13px;"'?>>
							<?php echo substr($record['group_name'],1) ?>:
						</td>
					</tr>
					
<?php 
				} // end right side notes check 
				
				$this->last_group = $record['group_name'];
				$this->cell_count = 0;

				if ($this->max_cells == 3) continue;
			} // end group

			// Handle starting of a new row.
			if ($record['data_type'] == 2 || $record['data_type'] == 21) $this->max_cells = 5;
			self::end_check();

			// Must have at least on column
			if ($this->item_count == 0 && $datacols == 0) {
				$datacols = 1;
			}
		
			// Handle starting of a new data cell.
			if ($datacols > 0) {
				self::end_cell();
				$big_cell = ($record['max_length'] == 255 || $record['max_length'] == 0)? true: false;
				$this->cell_count += $datacols;
				$class = ($record['data_type'] == 31 || $record['data_type'] == 2 || $record['data_type'] == 21)? 'wmtStatic' : 'wmtRight';
				$field_data = $this->pedi_data->layout_data[$record['field_id']];
?>				
				  		<td class="<?php echo $class ?>" <?php if ($datacols) echo 'colspan="'.$datacols.'"' ?> <?php if ($this->big_cell) echo 'style="width:100%"' ?>>
<?php 
				// special for checkbox lists
				if ($record['data_type'] == 21) {
					$check_list = new Options($record['list_id']);
					$this->max_cells = 5;
					$left = '5';
					foreach ($check_list->list AS $check) {
						echo "<input name='form_".$record['field_id']."[]' type='checkbox' value='".$check['option_id']."' style='margin-left:".$left."px' ";
 						if (in_array($check['option_id'], $field_data)) echo " checked ";
						echo "/>&nbsp;" . $check['title'];
						$left = '15';
					}
				} else if ($record['data_type'] == 2) {
					$this->max_cells = 5;
					echo "<input name='form_".$record['field_id']."' type='text' value='".$field_data."' />\n";
				} else if ($this->form_data->id) { // not "new" record
					echo generate_form_field($record, $field_data);
				}
				else {  // "new" so use defaults
//					echo generate_form_field($record, $record['default_value']);
					echo generate_form_field($record, '');
				}
				echo "&nbsp;";
			}
			$this->item_count++;
				
			// Handle starting of a new row.
			self::end_check();

			// Handle starting of a new label cell.
			if ($titlecols > 0) {
				self::end_cell();
				$this->cell_count += $titlecols; ?>

							<td class='<?php echo ($record['data_type'] == 6)? 'wmtRight' :'wmtLeft' ?> <?php echo ($record['uor'] == 2)? 'wmtRequired' :'' ?>' <?php if ($titlecols) echo "colspan='".$titlecols."'" ?> >
								<?php echo ($record['title']) ? $record['title'] : "&nbsp;" ?>
<?php 
				// special for cardiac murmur
				if ($record['field_id'] == 'card_mur_dd') { ?>
								<span id='card_mur_pop' style='margin-left:5px;display:<?php echo (($field_data == 'YES') ? 'inline' : 'none') ?>;'><select name="form_card_mur_grade" id="form_card_mur_grade" class="wmtLeft"><?php $grade_list->showOptions($this->pedi_data->layout_data['card_mur_grade']); ?></select>&nbsp;&nbsp;/&nbsp;6</span>
<?php 			}
			}
			$this->item_count++;

		} // end foreach
?>
				</table>
			</div>
			<?php Display::bottom($this->title, $this->key, $open, $bottom); ?>
		</div>
<?php }


/**
 * Print a collapsable section in the report.
 *
 */
public function report() {
		$output = false;
		$last_title = '';
		$this->last_group = '';
		$in_group = false;
		$first_group = true;
		$rep_rows = array();
		$rep_list = array();
		
		$vital_name = 'Height';
		if ($this->form_data->form_type == 'WCC1' || $this->form_data->form_type == 'WCC2')
			$vital_name = 'Length';
		
		// build list of output records by group
		foreach ($this->pedi_data->layout_list AS $record) {
			if ($this->form_data->sex == 'Male' && strpos($record['group_name'], '_female') > 0) continue;
			if ($this->form_data->sex == 'Male' && strpos($record['group_name'], '_breast') > 0) continue;
			if ($this->form_data->sex == 'Female' && strpos($record['group_name'], '_male') > 0) continue;
			
			$field_data = trim($this->pedi_data->layout_data[$record['field_id']]);
			if (!$field_data && $record['data_type'] != 31 && $record['data_type'] != 21 && $record['data_type'] != 6) continue;
			
			$record['data'] = $field_data; // add data to record
			$rep_list[] = $record; // store record

			if ($record['data_type'] != 31 && $record['data_type'] != 21 && $record['data_type'] != 6) {
				$rep_rows[$record['group_name']]++; // update group rows
				$output = true;
			}
		}

		// no data, no print
		if (!$output) return; ?>
		
		<div class='wmtPrnMainContainer'>
			<div class='wmtPrnCollapseBar'>
				<span class='wmtPrnChapter'><?php echo $this->pedi_data->layout_title ?></span>
			</div>
			<div class='wmtPrnCollapseBox'>
				<table class='wmtPrnContent'>
					
<?php 			
		
		// loop through each layout record
		foreach ($rep_list AS $record) {
			$titlecols = $record['titlecols'];
			$datacols = $record['datacols'];
			$group_name = $record['group_name'];
			
			// start of new group
			if ($group_name != $this->last_group) {
				if ($in_group) { ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
<?php 			}

				if ($record['data_type'] == 3 && strpos($record['field_id'],'_notes') > 0) { 
					$right = true; ?>
					<tr>
						<td class="wmtPrnGroup" style="font-size:12px;font-weight:bold;vertical-align:top;<?php if (!$first_group) echo 'padding-top:10px;height:14px;' ?>">
							<?php echo substr($group_name, 1) ?>:
						</td>
						<td rowspan='2' style='padding-left:10px;padding-top:15px;width:60%'>
							<span class="wmtPrnLabel"><?php echo $record['title'] ?></span><br/>
							<span class="wmtPrnBody"><?php echo $record['data']; ?></span> 
						</td>
					<tr>
						<td style='vertical-align:top'>
							<table>
								<tr><td style="width:12px"></td><td style="width:20px"></td><td style="width:150px"></td><td></td></tr>
<?php			} else { 
					$right = false; ?>
					<tr>
						<td colspan='2' class="wmtPrnGroup" style="font-size:12px;font-weight:bold;vertical-align:top;<?php if (!$first_group) echo 'padding-top:10px;height:14px;' ?>">
							<?php echo substr($group_name, 1) ?>:
						</td>
					</tr>
					<tr>
						<td colspan='2' style='vertical-align:top'>
							<table>
								<tr><td style="width:12px"></td><td style="width:20px"></td><td style="width:150px"></td><td></td><td style="width:60%"></td></tr>
<?php  
				} ?>
<?php 
				$this->last_group = $group_name;
				$this->cell_count = 0;
				$first_group = false;
				$in_group = true;
				
				if ($right) continue;
				
			} // end group

			// Need the data
			if ($record['data_type'] == 6) $last_title = $record['title'];
			if ($this->item_count == 0 && $datacols == 0) $datacols = 1;
		
			// Handle starting of a new label cell.
			if ($titlecols > 0) {
				self::end_check();
				$titlecols++; // for spacer
				$this->cell_count += $titlecols; ?>

<?php 			
				if ($record['data_type'] == 1) { ?>
						<td><!-- spacer --></td>
						<td class="wmtPrnLabel" colspan="2" style="white-space:nowrap;text-align:left;padding-left:20px" >
<?php 
					echo $record['title'] . ": ";
				} else if ($record['data_type'] == 5) {
					$this->cell_count += $datacols; ?>
						<td><!-- spacer --></td>
						<td class="wmtPrnLabel" <?php if ($titlecols > 1) echo "colspan='".$titlecols."' "; ?> style="white-space:nowrap" >
							<i class="fa fa-fw fa-check"></i>
<?php 
					echo $record['title'];
				} else { ?>
						<td><!-- spacer --></td>
						<td class="wmtPrnLabel" <?php if ($titlecols > 1) echo "colspan='".$titlecols."' "; ?> style="white-space:nowrap;text-align:right" >
<?php 
					echo $record['title'] . ": ";
				}
				
				
			} else if ($last_title) {
				self::end_check();
				$this->cell_count += 3; ?>

						<td><!-- spacer --></td>
						<td colspan='2' class="wmtPrnLabel" <?php if ($titlecols > 1) echo "colspan='".$titlecols."' "; ?> style="white-space:nowrap;text-align:right;" >

<?php 
				echo $last_title . ': ';
				$last_title = '';
			}
			$this->item_count++;

			// Special for multi-checkbox
			if ($record['data_type'] == 21) {
				$check_list = new Options($record['list_id']);
				$check_data = $this->pedi_data->layout_data[$record['field_id']];
				
				self::end_row(); ?>
				
				<td colspan='3'></td>
				<td colspan='2' class="wmtPrnBody" style="white-space:normal" >
<?php 								
				foreach ($check_list->list AS $check) {
					if (in_array($check['option_id'], $check_data) == false) continue;
					echo "<span style='white-space:nowrap;margin-right:15px'/>";
					echo "<i class='fa fa-fw fa-check'></i>";
					echo $check['title'];
					echo "</span> ";
				}
				
			// Handle starting of a new data cell.
			} else if ($datacols > 0 && $record['data_type'] != 5) {
				self::end_check();
				$this->big_cell = ($record['max_length'] == 255)? true: false;
				if ($this->big_cell) {
					$datacols++; // span the padding cell
					$record['fld_length'] = 0;
				}
				$this->cell_count += $datacols;
				// spacer for comments
				if ($record['data_type'] == 3) {
					$datacols--;
					echo "<td></td>";
				} ?>
								
						<td class="wmtPrnBody" <?php if ($datacols > 1) echo 'colspan="'.$datacols.'"' ?>  style="white-space:normal" >
				  		
<?php			$field_data = $this->pedi_data->layout_data[$record['field_id']];
				if ($record['data_type'] == 31) {
					if ($record['description']) echo $record['description'];
				} else if ($record['data_type'] == 3) {
					echo $field_data;
				} else if ($record['data_type'] == 1 || $record['data_type'] == 26 || $record['data_type'] == 33) {
					if (empty($field_data)) $field_data = 'UNASSIGNED';
					echo generate_print_field($record, $field_data);
					// special for checkbox lists
				} else {
					echo generate_print_field($record, $field_data);
				}
			}
			$this->item_count++;

		} // end foreach
		
		if ($in_group) { ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
<?php 	} ?> 
					
				</table>

			</div> <!-- END COLLAPSE BOX -->
		</div> <!-- END MAIN CONTAINER -->
		
<?php }

	
	/**
	 * Store data from a form object into the database.
	 *
	 * @return int $id identifier for object
	 */
	public function store() {
		$id = &$this->form_data->id;
		$pid = &$this->form_data->pid;
		$encounter = &$this->form_data->encounter;
		$dt = &$_POST;

		// Push data to array
		$item_array = array();
		foreach ($this->pedi_data->layout_list AS $record) {
			$key = 'form_' . $record['field_id'];
			if (is_array($_POST[$key]))
				$value = $_POST[$key];
			else
				$value = strip_tags($_POST[$key]);
			$item_array[$record['field_id']] = $value;
		}

		// Store detail record
		$this->pedi_data->date = date('Y-m-d H:i:s');
		$this->pedi_data->pid = $pid;
		$this->pedi_data->user = $_SESSION['authUser'];
		$this->pedi_data->encounter = $encounter;
		$this->pedi_data->activity = 1;
		$this->pedi_data->layout_title = $this->title;
		$this->pedi_data->layout_data = $item_array;
		
		$this->pedi_data->store();
	}


	private	function end_cell() {
		if ($this->item_count > 0) {
			echo "</td>";
			$this->item_count = 0;
		}
	}
	
	private function end_row() {
		self::end_cell();
		if ($this->cell_count > 0) {
			for (; $this->cell_count < $this->max_cells; ++$this->cell_count) echo "<td></td>";
			$this->cell_count = 0;
		}
		$this->row_count++;
		echo "</tr>";
	}
	
	private function end_group() {
		if (strlen($this->last_group) > 0) {
			self::end_row();
		}
	}
	
	private function end_check() {
		if (($this->cell_count >= $this->max_cells) || $this->cell_count == 0) {
			self::end_row(); 
			echo "<tr>";
		}
	}

}
?>