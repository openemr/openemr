<?php
/** **************************************************************************
 *	wmtROS.module.php
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
class PediExamModule extends BaseModule {
	// these must be class variables to 
	// pass between class functions
	private	$last_group = '';
	private $last_cat = '';
	private $cell_count = 0;
	private $item_count = 0;
	private $max_cells = 7;
	
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtPediExamModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		$this->layout_key = $sec_data['prefix'];
		if ($form_data->form_version) $this->title .= " - " . $form_data->form_version; 
			
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
		$big_cell = false; ?>
	
			<script>
				function setPE(group,task) {
					if (group == '' || group == 'all') group = 'area_PE';
					
					$('[id^='+group+'] :input[type=checkbox]').each(function() {
						$(this).attr('checked',false);
						if (task == 'default') {
							if ($(this).attr('default') == 1) $(this).attr('checked','checked'); 
						}
	
						$(this).change(); // trigger change method
					});
				}

				function foldPE(type) {
<?php foreach ($this->pedi_data->layout_cats AS $record) { // get each cat_id into $key ?>
					$('#button_<?php echo $record['cat_id'] ?>').each(function() {
						if (type == 'open') $(this).attr('checked',true);
						else $(this).attr('checked',false);
								
						$(this).change(); // trigger fade in/out
					});
<?php } ?>
				}

				function normCheck(group) {
					var normal = "normal";
					$('#area_'+group+' :input[type=checkbox]').each(function() {
						if ($(this).attr('default') == 1 && this.checked == false) normal = ""; 
						if ($(this).attr('default') != 1 && this.checked == true) normal = ""; 
					});
					$('#flag_'+group).html(normal);
				}

				// Post initialization setup
				$(document).ready(function(){
<?php foreach ($this->pedi_data->layout_cats AS $record) { // get each cat_id into $key ?>
				    $('#button_<?php echo $record['cat_id'] ?>').change(function(){
				        if(this.checked)
				            $('#area_<?php echo $record['cat_id'] ?>').fadeIn('fast');
				        else
				            $('#area_<?php echo $record['cat_id'] ?>').fadeOut('fast');
		    		});

					$('[id^=area_<?php echo $record['cat_id'] ?>]').each(function() {
						$(this).find(':input').change(function() {
							normCheck('<?php echo $record['cat_id'] ?>');
				    });
						normCheck('<?php echo $record['cat_id'] ?>'); // initial load settings
					});
<?php } ?>

				}); // end document ready
				
			</script>
	
			<div class='wmtMainContainer wmtColorMain'>
				<?php Display::chapter($this->title, $this->key, $open); ?>
				<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
					<table style='width:80%'>
						<tr>
							<td style="width:30%">
								<input type="button" value="Set All Default" onclick="setPE('all','default')" />
								<input type="button" value="Clear All Data" onclick="setPE('all','clear')" />
							</td>
							<td>
								<input type="button" value="Collapse Exams" onclick="foldPE('close')" />
								<input type="button" value="Expand Exams" onclick="foldPE('open')" />
							</td>
						</tr>
					</table>

<?php 
		// loop through each layout record
		foreach ($this->pedi_data->layout_list AS $record) {
			if ($this->form_data->sex == 'Male' && $record['cat_id'] == 'PE_female') continue;
			if ($this->form_data->sex == 'Male' && $record['cat_id'] == 'PE_breast') continue;
			if ($this->form_data->sex == 'Female' && $record['cat_id'] == 'PE_male') continue;
			
			$titlecols = $record['titlecols'];
			$datacols = $record['datacols'];
			
			// processing new category
			$new_cat = $record['cat_id'];
					
			if ($new_cat != $this->last_cat) {
				self::end_group();
				if ($this->last_cat) {
					$cat_name = str_replace('PE_','',$this->last_cat);
					echo "</table></div>";
				} // end first time check
?>
					<table class="wmtLBForm" style="width:100%">
						<tr><td style="width:10%"></td><td style="min-width:5%"></td><td style="min-width:20%"></td><td colspan="5"></td></tr>
						<tr>
							<td class="wmtGroup" colspan="<?php echo $this->max_cells +1 ?>">
								<input type="checkbox" class='nolock' id="button_<?php echo $new_cat ?>" value="1" />
								<?php echo strtoupper($record['cat_name']) ?>:&nbsp;
								<span class="wmtRed" id="flag_<?php echo $new_cat ?>"></span>
							</td>
						</tr>
					</table>
					<div id="area_<?php echo $new_cat ?>" class="wmtLBData wmtColorBase" style="display:none;margin:0 30px">
						<table>
							<tr><td style="width:5%"></td><td style="min-width:20%"></td><td style="min-width:10%"></td><td style="min-width:10%"></td><td style="min-width:10%"></td><td style="min-width:10%"></td><td style="min-width:10%"></td><td></td></tr>
						<tr>
							<td colspan="8">
								<input type="button" class="css_button_small" value="Set Default" style="margin-left:0" onclick="setPE('area_<?php echo $new_cat ?>','default')" />
								<input type="button" class="css_button_small" value="Clear Data" onclick="setPE('area_<?php echo $new_cat ?>','clear')" />
							</td>
						</tr>
<?php 
				$this->last_cat = $new_cat;
				$this->last_group = ""; // clear group
				$this->cell_count = 0;
			} // end cat break
					
			if ($record['group_name'] != $this->last_group) {
				self::end_group(); ?>
				
						<tr>
							<td class="wmtLabel" colspan="<?php echo $this->max_cells +1 ?>" <?php if ($this->last_group) echo 'style="padding-top:10px"'?>>
								<?php echo substr($record['group_name'], 1); ?>
							</td>
						</tr>
<?php 
				$this->last_group = $record['group_name'];
				$last_title = "";
				$this->cell_count = 0;
			} // end group

			// Handle starting of a new row.
			if (($titlecols > 0 && $this->cell_count >= $this->max_cells) || $this->cell_count == 0) {
				self::end_row();
				echo "<tr>";
			}

			if ($this->item_count == 0 && $datacols == 0) {
				$datacols = 1;
			}
			
			// Handle starting of a new data cell.
			if ($datacols > 0) {
				self::end_cell();
				$big_cell = ($record['max_length'] == 255)? true: false;
				if ($big_cell) $datacols++;
				$this->cell_count += $datacols;
?>				
						  		<td class='<?php echo ($record['data_type'] == 31)? 'wmtStatic' :'wmtRight' ?>' <?php if ($datacols) echo "colspan='".$datacols."'" ?> <?php if ($big_cell) echo "style='width:100%'" ?>>
<?php 
				if ($form_data->id) { // not "new"
					echo generate_form_field($record, $lbf_data[$record['field_id']]); 
				}
				else { // "new" so use defaults
					echo generate_form_field($record, $record['default_value']);
//					echo generate_form_field($record, '');
				}
				
				echo "&nbsp;";
			}
			$this->item_count++;

			// Handle starting of a new label cell.
			if ($titlecols > 0) {
				self::end_cell();
				$this->cell_count += $titlecols;
?>
							<td class='<?php echo ($record['data_type'] == 6)? 'wmtRight' :'wmtLeft' ?> <?php echo ($record['uor'] == 2)? 'wmtRequired' :'' ?>' <?php if ($titlecols) echo "colspan='".$titlecols."'" ?> >
								<?php echo ($record['title']) ? $record['title'] : "&nbsp;" ?>
<?php 
			}
			$this->item_count++;

		} // end foreach
		$cat_name = str_replace('PE_','',$this->last_cat);
?>
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
			throw new \Exception("wmtPediExamModule: Category [$cat] does not exist in [list_options] forms table.");
	
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
		}
		
		if (strlen($this->last_cat) > 0) {
			self::end_row();
		}
	}
	
}
?>