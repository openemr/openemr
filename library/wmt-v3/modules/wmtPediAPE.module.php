<?php
/** **************************************************************************
 *	wmtPediAPE.module.php
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
class PediAPEModule extends BaseModule {
	// class variables required for class functions
	private	$last_group = '';
	private $last_cat = '';
	private $cell_count = 0;
	private $item_count = 0;
	private $max_cells = 5;
	
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtPediAPEModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		$this->layout_key = $sec_data['prefix'];
		$this->title .= " - Pediatric"; 
			
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
	
		// initialize 
		$this->last_group = '';
		$this->last_cat = '';
		$this->cell_count = 0;
		$this->item_count = 0;
		$big_cell = false;

		$vitals = 'g'; // grade school & up
		if ($this->form_data->age < 4) $vitals = 't'; // toddler
		if ($this->form_data->age < 1) $vitals = 'i'; // infant 
		
		$wrap_mode = ($this->form_data->id) ? 'update' : 'new'; ?>
		
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

			function setPE(group,task) {
				var selector = "#area_"+group;
				if (group == '' || group == 'all') selector = ".wmtLBData";
				
				$("#apeBox " + selector).each(function() {
					group = $(this).attr('id').slice(5);

					$(this).find(":input").each(function() {
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
									$(this).prop('checked',false);
									if (task == 'default' && $(this).attr('default') == 1) $(this).prop('checked',true);
									break;
							}
						}
					});
				
					normCheck(group); // norm check
				});
			}

			function foldPE(type) {
				$("#apeBox [id^='button_']").each(function() {
					var key = $(this).attr('id').slice(7);

					if (type == 'open') $(this).prop('checked',true);
					else $(this).prop('checked',false);

					if(this.checked)
			            $('#area_'+key).fadeIn('fast');
			        else
			            $('#area_'+key).fadeOut('fast');
				});

			}

			function normCheck(group) {
				var normal = "normal";
				var selector = "#area_"+group;

				$("#apeBox " + selector).each(function() {
					$(this).find(":input").each(function() {
						if ($(this).attr('id') && $(this).attr('id').substr(0,10) != 'form_vital') {
							switch(this.type) {
								case 'text':
								case 'textarea':
								case 'select-one':
								case 'select-multiple':
									if ($(this).is('[default]') && $(this).val() != $(this).attr('default')) normal = "";
									if ($(this).val() != '' && $(this).attr('default') == '') normal = "";
									if ($(this).val() != '' && !$(this).is('[default]')) normal = "";
									break;
								case 'checkbox':
								case 'radio':
									if ($(this).prop('checked') == false && $(this).attr('default') == 1) normal = "";
									if ($(this).prop('checked') == true && $(this).attr('default') == '') normal = "";
									if ($(this).prop('checked') == true && !$(this).is('[default]')) normal = "";
									break;
							}
						}
					});

					$('#flag_'+group).html(normal);
				});
			}

			// Post initialization setup
			$(document).ready(function() {
				
				// open/close sections
				$("#apeBox [id^='button_']").change(function() {
					var key = $(this).attr('id').slice(7);
					if (this.checked)
			            $('#area_'+key).fadeIn('fast');
			        else
			            $('#area_'+key).fadeOut('fast');
	    		});

				// trigger normal check
				$("#apeBox [id^='area_']").each(function() {
					var key = $(this).attr('id').slice(5);
					$(this).find(':input').change(function() {
						normCheck(key);
			    	});
					normCheck(key); // initial load settings
					
				});

			}); // end document ready
				
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
					        	<a href="javascript:;" class="css_button" tabindex="-1" onclick="get_vitals()"><span>Retrieve Vitals</span></a>
							</td>
						</tr>
						<tr><td colspan="11" style="margin: 8px;"></td></tr>
						<tr>
							<td class="wmtBodyR" style="width:130px"><?php echo ($vitals == 'i' || $vitals == 't')? 'Length' : 'Height' ?>: 
								<input name="form_vital_ht" id="form_vital_ht" class="wmtInput" type="text" style="width: 50px" value="<?php echo $dt{'form_vital_ht'}; ?>" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> /></td>
							<td class="wmtBodyR" style="width:130px">Weight:
								<input name="form_vital_wt" id="form_vital_wt" class="wmtInput" type="text" style="width: 50px" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_wt'}; ?>" /></td>
<?php if ($vitals != 'i' && $vitals != 't') { ?>
							<td class="wmtBodyR" style="width:160px">BP:
								<input name="form_vital_bps" id="form_vital_bps" class="wmtInput" type="text" style="width: 50px" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_bps'}; ?>" />&nbsp;/&nbsp;<input name="form_vital_bpd" id="form_vital_bpd" class="wmtInput" type="text" style="width: 50px" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_bpd'}; ?>" /></td>
<?php } ?>
							<td class="wmtBodyR" style="width:130px">Pulse:
								<input name="form_vital_hr" id="form_vital_hr" class="wmtInput" type="text" style="width: 50px" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_hr'}; ?>" onchange="NoDecimal('form_vital_hr')" /></td>
<?php if ($vitals == 'i') { ?>
							<td class="wmtBodyR" style="width:130px">HC:
								<input name="form_vital_hc" id="form_vital_hc" class="wmtInput" style="width: 50px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_hc'}; ?>" onchange="OneDecimal('form_vital_hc')" /></td>
<?php } ?>
<?php if ($vitals == 't') { ?>
							<td class="wmtBodyR" style="width:130px">Resp:
								<input name="form_vital_resp" id="form_vital_resp" class="wmtInput" style="width: 50px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_resp'}; ?>" onchange="NoDecimal('form_vital_resp')" /></td>
<?php } ?>
							<td class="wmtBodyR" style="width:130px">Temp:
								<input name="form_vital_temp" id="form_vital_temp" class="wmtInput" style="width: 50px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_temp'}; ?>" onchange="OneDecimal('form_vital_temp')" /></td>
							<td class="wmtBodyR" style="width:130px">POx:
								<input name="form_vital_pox" id="form_vital_pox" class="wmtInput" style="width: 50px" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo $dt{'form_vital_temp'}; ?>" onchange="OneDecimal('form_vital_pox')" /></td>
							<td></td>
						</tr>
					</table>

					<hr style='border:1px dashed'/>

					<table style='width:100%'>
						<tr>
							<td style="width:50%;padding-left:100px">
								<input type="button" value="Set All Default" onclick="setPE('all','default')" />
								<input type="button" value="Clear All Data" onclick="setPE('all','clear')" />
							</td>
							<td style="width:50%;padding-right:100px;text-align:right">
								<input type="button" value="Collapse Exams" onclick="foldPE('close')" />
								<input type="button" value="Expand Exams" onclick="foldPE('open')" />
							</td>
						</tr>
					</table>

<?php 
		// loop through each layout record
		foreach ($this->pedi_data->layout_list AS $record) {

			if ($this->form_data->sex == 'Male' && ($record['group_name'] == 'Breasts')) continue;
			if ($this->form_data->sex == 'Male' && strpos($record['group_name'], '(female)') > 0) continue;
			if ($this->form_data->sex == 'Female' && strpos($record['group_name'], '(male)') > 0) continue;
			if ($this->form_data->sex == 'Female' && $record['field_id'] == 'rect_size_dd') continue;
			if ($this->form_data->sex == 'Female' && $record['field_id'] == 'rect_size_nt') continue;
				
			$titlecols = $record['titlecols'];
			$datacols = $record['datacols'];
			$field_data = '';
				
			// start of new group
			if ($record['group_name'] != $this->last_group) {
				$group_key = preg_split('/[^\w]/', $record['group_name'])[0]; // break on first non-alphanumeric 
				self::end_cat(); 
?>
					<table class="wmtLBForm" style="width:100%">
						<tr>
							<td class="wmtGroup" colspan="<?php echo $this->max_cells ?>">
								<input type="checkbox" class='nolock' id="button_<?php echo $group_key ?>" value="1" />
								<?php echo strtoupper(substr($record['group_name'],1)) ?>:&nbsp;
								<span class="wmtRed" id="flag_<?php echo $group_key ?>"></span>
							</td>
						</tr>
					</table>
					<div id="area_<?php echo $group_key ?>" class="wmtLBData wmtColorBase" style="display:none;margin:0 30px">
						<table style="width:100%">
							<tr><td style="width:20px"></td><td style="width:7%"></td><td style="width:15%"></td><td style="width:30%"></td><td></td></tr>
							<tr>
								<td colspan="8">
									<input type="button" class="css_button_small" value="Set Default" style="margin-left:0" onclick="setPE('<?php echo $group_key ?>','default')" />
									<input type="button" class="css_button_small" value="Clear Data" onclick="setPE('<?php echo $group_key ?>','clear')" />
								</td>
							</tr>
<?php 
				$this->last_group = $record['group_name'];
				$this->cell_count = 0;
			} // end category

			// Handle first time
			if (($titlecols > 0 && $this->cell_count >= $this->max_cells) || $this->cell_count == 0) {
				self::end_row();
				echo "<tr>";
			}

			// Handle starting of a new row.
			self::end_check();

			// Special processing for certain types
			if ($record['data_type'] == '3' && strpos($record['field_id'], 'notes') !== false) { // notes textarea ?>
							<td class='wmtLabel' colspan='<?php echo $this->max_cells ?>'">
								<?php echo $record['title']; ?>
							</td>
						</tr><tr>
							<td colspan='<?php echo $this->max_cells ?>'">
<?php 
				echo generate_form_field($record, $lbf_data[$record['field_id']]);
				continue;
			}

			if ($record['data_type'] == '21') { // checkbox list ?>
							<td class='wmtRight' <?php if ($titlecols) echo "colspan='".$titlecols."'" ?> ">
								<?php echo $record['title']; ?>: 
							</td>
							<td <?php if ($datacols) echo "colspan='".$datacols."'" ?> ">
<?php 
				${$record['list_id']} = new Options($record['list_id']);
				${$record['list_id']}->showChecks();
				echo "</td>\n";
				continue;
			}

			// Handle starting of a new data cell.
			if ($datacols > 0) {
				self::end_cell();
				$big_cell = ($record['max_length'] == 255)? true: false;
				$this->cell_count += $datacols;
?>				
							<td class='<?php echo ($record['data_type'] == 31)? 'wmtStatic' :'wmtRight' ?>' <?php if ($datacols) echo "colspan='".$datacols."'" ?> <?php if ($big_cell) echo "style='width:100%'" ?>>
<?php 
				if ($form_data->id) { // not "new"
					echo generate_form_field($record, $lbf_data[$record['field_id']]); 
				}
				else { // "new" so use defaults
					echo generate_form_field($record, $record['default_value']);
//						echo generate_form_field($record, '');
				}
				
				echo "&nbsp;";
			}
			$this->item_count++;

			// Handle starting of a new row.
			self::end_check();

			// Handle starting of a new label cell.
			if ($titlecols > 0) {
				self::end_cell();
				$this->cell_count += $titlecols;
?>
							<td class='<?php echo ($record['data_type'] == 6)? 'wmtLabel' : 'wmtLeft' ?> <?php echo ($record['uor'] == 2)? 'wmtRequired' :'' ?>' <?php if ($titlecols) echo "colspan='".$titlecols."'" ?> >
								<?php echo ($record['title']) ? $record['title'] : "&nbsp;" ?>
							</td>
<?php 
			}
			$this->item_count++;

		} // end foreach field
?>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<?php Display::bottom($this->title, $this->key, $open, $bottom); ?>
		</div><!-- End of Exam -->
<?php 
	}


	/**
	 * Print a collapsable section in the report.
	 *
	 */
	public function report() {
		$output = false;
		$last_title = '';
		$this->last_cat = '';
		$in_cat = false;
		$this->last_group = '';
		$in_group = false;
		$norm_list = array();
		$first_cat = true;
		$rep_rows = array();
		$rep_list = array();
		
		// build list of output records by group
		foreach ($this->pedi_data->layout_list AS $record) {
			if ($this->form_data->sex == 'Male' && $record['cat_id'] == 'PE_female') continue;
			if ($this->form_data->sex == 'Male' && $record['cat_id'] == 'PE_breast') continue;
			if ($this->form_data->sex == 'Female' && $record['cat_id'] == 'PE_male') continue;
			
			
			$field_data = $this->pedi_data->layout_data[$record['field_id']];
			if (!$field_data && $record['data_type'] != 31 && $record['data_type'] != 21) continue;
			
			$record['data'] = $field_data; // add data to record
			$rep_list[] = $record; // store record

			if ($record['data_type'] != 31 && $record['data_type'] != 21) {
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
					
					<tr><td style="width:15%"></td><td style="width:10%"></td><td style="width:1%"></td><td style="width:10%"></td><td style="width:1%"></td><td style="width:10%"></td><td colspan="3"></td></tr>
<?php 			
		// now print results for each record
		foreach ($rep_list AS $record) {
				
			$titlecols = $record['titlecols'];
			$datacols = $record['datacols'];

			// start of new group
			if ($record['group_name'] != $this->last_group) {
				if ($in_group) {
					self::end_group();
					$in_group = false; 
				}

				$group_label = substr($record['group_name'],1);
				$this->last_group = $record['group_name'];
				$this->cell_count = 0;
			} // end group

			// processing new category
			if ($record['cat_id'] != $this->last_cat) {
				if ($in_cat) {
					self::end_cat();
					$in_cat = false;
				}
			
				$cat_label = strtoupper($record['cat_name']);
				$this->last_cat = $record['cat_id'];
				$this->cell_count = 0;
				
				$norm_list[$record['cat_id']] = $record['cat_name'];
			} // end category
				
				
			// Need the data
			if ($record['data_type'] == 6) { 
				$last_title = $record['title'];
				continue;
			}
			
			$field_data = $this->pedi_data->layout_data[$record['field_id']];
			
			if (empty($field_data)) {
				$last_title = '';
				continue;
			}
			if ($record['data_type'] == 5) {
				if ($record['default_value'] == $field_data) {
					$last_title = '';
					continue; // set as default, skip
				} else {
					$norm_list[$record['cat_id']] = ''; // not default
				}
			}

			// Category label needed?
			if ($cat_label) { // first data for this group 
				self::end_row(); ?>
			
					<tr>
						<td class="wmtPrnLabel" colspan="<?php echo $this->max_cells +1 ?>" <?php if (!$first_cat) echo 'style="padding-top:10px"'?>>
							<span style='text-decoration:underline;font-weight:bold;font-size:12px'><?php echo $cat_label ?></span>
						</td>
					</tr>
				
<?php 
				$in_cat = true;
				$this->cell_count = 0;
				$new_cat = true;
				$cat_label = ''; // only once per category
				$first_cat = false;
			}
			
			// Group label needed?
			if ($group_label) { // first data for this group 
				self::end_row(); ?>
			
					<tr>
						<td class="wmtPrnLabel" colspan="<?php echo $this->max_cells +1 ?>" <?php if ($this->last_group && !$new_cat) echo 'style="padding-top:10px"'?>>
							<?php echo $group_label ?>:
						</td>
					</tr>
				
<?php 
				$this->cell_count = 0;
				$group_label = ''; // only once per group
				$in_group = true;
			}
			
			// Must have at least on column
			if ($this->item_count == 0 && $datacols == 0) {
				$datacols = 1;
			}
		
			// Handle starting of a new label cell
			if ($titlecols > 0) {
				self::end_check();
				$this->cell_count += $titlecols; 
				
				// Special data		
				if ($record['data_type'] == 5) {
					$this->cell_count += $datacols; ?>
						<td class="wmtPrnLabel" style="white-space:nowrap;text-align:left;padding-left:25px;" >
							<i class="fa fa-fw fa-check"></i>
<?php
					echo ($record['title']) ? $record['title'] : "";
				} else { ?>
						<td class="wmtPrnLabel" style="white-space:nowrap;text-align:right;padding-left:50px;" >
<?php
					echo $record['title'] . ": ";
				}
				
			} else if ($last_title) {
				self::end_check();
				$this->cell_count += 1; ?>

						<td class="wmtPrnLabel" style="white-space:nowrap;text-align:right;padding-left:50px;" >
							<?php echo $last_title . ': '; ?>

<?php 
			}
			$this->item_count++;

			// Handle starting of a new data cell.
			if ($datacols > 0 && $record['data_type'] != 5) {
				self::end_check();
				$this->big_cell = ($record['max_length'] == 255 || $record['data_type'] == 3)? true: false;
				if ($this->big_cell) {
					$datacols++; // span the padding cell
					$record['fld_length'] = 0;
				}
				$this->cell_count += $datacols;  ?>
											
						<td class="wmtPrnBody" <?php if ($this->big_cell) echo "colspan='6'"; ?> style="white-space:normal" >
							  		
<?php
				if ($record['data_type'] == 1 || $record['data_type'] == 26 || $record['data_type'] == 33) {
					if (empty($field_data)) $field_data = 'UNASSIGNED';
				}

				if ($record['data_type'] == 3) 
					echo $field_data; // textarea
				else
					echo generate_print_field($record, $field_data);

				$this->item_count++;
			}
		} // end foreach 
		
		self::end_row(); ?>
					
<?php  if (!empty($norm_list)) {
			$content = implode(", ",$norm_list); ?>
					<tr>
						<td class="wmtPrnLabel" colspan="<?php echo $this->max_cells +1 ?>" style="padding-top:10px">
							<span style='text-decoration:underline;font-weight:bold;font-size:12px'>NORMAL AREAS</span>
						</td>
					</tr>
					<tr>
						<td class="wmtPrnLabel" colspan="<?php echo $this->max_cells +1 ?>" >
							<?php echo $content ?>
						</td>
					</tr>
<?php 	} ?>				
				</table>
			</div> <!-- END COLLAPSE BOX -->
		</div> <!-- END MAIN CONTAINER -->
		
<?php }

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
	
		// Push data to array
		$this->pedi_data->layout_data = array();
		foreach ($this->pedi_data->layout_list AS $record) {
			$key = 'form_' . $record['field_id'];
			$value = strip_tags($_POST[$key]);
			$this->pedi_data->layout_data[$record['field_id']] = $value;
		}
	
		// Store detail record
		$this->pedi_data->date = date('Y-m-d H:i:s');
		$this->pedi_data->pid = $pid;
		$this->pedi_data->user = $_SESSION['authUser'];
		$this->pedi_data->encounter = $encounter;
		$this->pedi_data->activity = 1;
		$this->pedi_data->layout_title = $this->title;
	
		$this->pedi_data->store();
	}
	
	
	private static function getCategories($cat) {
		$layout_cats = array();
		$valid = false;
	
		$cat .= "_Forms";
	
		$query = "SELECT option_id AS cat_id, title AS cat_name FROM list_options ";
		$query .= "WHERE list_id = ? ORDER BY seq ";
	
		$result = sqlStatement($query, array($cat));
		while ($record = sqlFetchArray($result)) {
			$valid = true;
			$layout_cats[$record['cat_id']] = $record['cat_name'];
		}
		if (!$valid) 
			throw new \Exception("wmtPediAPEModule: Category [$cat] does not exist in [list_options] forms table.");
	
		return $layout_cats;
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
			echo "</tr>";
		}
	}
	
	private function end_check() {
		if (($this->cell_count >= $this->max_cells) || $this->cell_count == 0) {
			self::end_row(); 
			echo "<tr>";
		}
	}

	private function end_group() {
		if (strlen($this->last_group) > 0) {
			self::end_row();
		}
	}
	
	private function end_cat() {
		if (strlen($this->last_group) > 0) {
			self::end_row();
			echo "</table></div>";
		}
	}
	
}
?>