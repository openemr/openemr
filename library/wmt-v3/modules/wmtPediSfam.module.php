<?php
/** **************************************************************************
 *	wmtPediSfam.module.php
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
class PediSfamModule extends BaseModule {
	// these must be class variables to 
	// pass between class functions
	private $max_cells = 5;
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
			throw new \Exception('wmtPediSfamModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		$this->layout_key = $form_data->form_type.'_'.$sec_data['prefix'];
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
		$this->cell_count = 0;
		$this->item_count = 0;
		$big_cell = false; ?>

		<div class='wmtMainContainer wmtColorMain'>
			<?php Display::chapter($this->title, $this->key, $open); ?>
			<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
				<table class="wmtLBForm" style="width:100%">
					<!-- FOLLOWING LINE HELPS CONTROL COLUMN WIDTHS -->
					<tr><td style="width:10%"></td><td style="min-width:5%"></td><td style="min-width:20%"></td><td style="min-width:20%"></td><td style="min-width:20%"></td><td colspan="1"></td></tr>

<?php 			
		// loop through each layout record
		foreach ($this->pedi_data->layout_list AS $record) {
			$titlecols = $record['titlecols'];
			$datacols = $record['datacols'];

			// start of new group
			if ($record['group_name'] != $this->last_group) {
				self::end_group(); ?>
				
					<tr>
						<td class="wmtGroup" colspan="<?php echo $this->max_cells +1 ?>" <?php if ($this->last_group) echo 'style="padding-top:10px"'?>>
							<?php echo substr($record['group_name'],1) ?>:
						</td>
					</tr>
<?php 
				$this->last_group = $record['group_name'];
				$this->cell_count = 0;
			} // end group

			// Handle starting of a new row.
			if (($titlecols > 0 && $this->cell_count >= $this->max_cells) || $this->cell_count == 0) {
				self::end_row();
				echo "<tr>";
			}

			// Must have at least on column
			if ($this->item_count == 0 && $datacols == 0) {
				$datacols = 1;
			}
		
			// Handle starting of a new data cell.
			if ($datacols > 0) {
				self::end_cell();
				$this->big_cell = ($record['max_length'] == 255)? true: false;
				if ($this->big_cell) {
					$datacols++; // span the padding cell
					$record['fld_length'] = 0;
				}
				$this->cell_count += $datacols;
?>				
				  		<td class="<?php echo ($record['data_type'] == 31)? 'wmtStatic' :'wmtRight' ?>" <?php if ($datacols) echo 'colspan="'.$datacols.'"' ?> <?php if ($this->big_cell) echo 'style="width:100%"' ?>>
<?php 
				if ($this->form_data->id) { // not "new" record
					echo generate_form_field($record, $this->pedi_data->layout_data[$record['field_id']]);
				}
				else {  // "new" so use defaults
					echo generate_form_field($record, $record['default_value']);
//					echo generate_form_field($record, '');
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
							<td class='<?php echo ($record['data_type'] == 6)? 'wmtRight' :'wmtLeft' ?> <?php echo ($record['uor'] == 2)? 'wmtRequired' :'' ?>' <?php if ($titlecols) echo "colspan='".$titlecols."'" ?> >
								<?php echo ($record['title']) ? $record['title'] : "&nbsp;" ?>
<?php 
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
		
		// build list of output records by group
		foreach ($this->pedi_data->layout_list AS $record) {
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
					
					<tr><td style="min-width:12px"></td><td style="width:20%"></td><td style="width:5%"></td><td style="width:5%"></td></td><td colspan="2"></td></tr>
<?php 			
		// loop through each layout record
		foreach ($rep_list AS $record) {
			$titlecols = $record['titlecols'];
			$datacols = $record['datacols'];

			// start of new group
			if ($record['group_name'] != $this->last_group) {
				self::end_group(); ?>
				
					<tr>
						<td class="wmtPrnLabel" colspan="<?php echo $this->max_cells +1 ?>" <?php if ($this->last_group) echo 'style="padding-top:10px"'?>>
							<?php echo substr($record['group_name'],1) ?>:
						</td>
					</tr>
<?php 
				$this->last_group = $record['group_name'];
				$this->cell_count = 0;
			} // end group

			// Handle starting of a new row.
			if (($titlecols > 0 && $this->cell_count >= $this->max_cells) || $this->cell_count == 0) {
				self::end_row();
				echo "<tr><td></td>";
			}

			// Must have at least one column
			if ($this->item_count == 0 && $datacols == 0) {
				$datacols = 1;
			}
		
			// Handle starting of a new label cell.
			if ($titlecols > 0) {
				self::end_cell();
				$this->cell_count += $titlecols; ?>
				
							<td class="wmtPrnLabel" <?php if ($titlecols) echo "colspan='".$titlecols."'" ?> style="white-space:nowrap" >

<?php 			if ($record['data_type'] != 5 || $this->pedi_data->layout_data[$record['field_id']]) {
					echo ($record['title']) ? $record['title'] : "";
				}
			}
			$this->item_count++;

			// Handle starting of a new data cell.
			if ($datacols > 0) {
				self::end_cell();
				$this->big_cell = ($record['max_length'] == 255)? true: false;
				if ($this->big_cell) {
					$datacols++; // span the padding cell
					$record['fld_length'] = 0;
				}
				$this->cell_count += $datacols;  ?>
								
				  		<td class="wmtPrnBody" <?php if ($datacols) echo 'colspan="'.$datacols.'"' ?>  style="white-space:normal;<?php if ($this->big_cell) echo 'width:100%' ?>" >
				  		
<?php			$field_data = $this->pedi_data->layout_data[$record['field_id']];
				if ($record['data_type'] == 5) {
					if ($field_data) {
						echo "<img src='".$GLOBALS['webroot']."/images/checkmark.png' />";
						echo generate_print_field($record, $field_data);
					}
				} else if ($record['data_type'] == 1 || $record['data_type'] == 26 || $record['data_type'] == 33) {
					if (empty($field_data)) $field_data = 'UNASSIGNED';
					echo generate_print_field($record, $field_data);
				} else {
					echo generate_print_field($record, $field_data);
				}
			}
			$this->item_count++;

		} // end foreach ?>
					
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
		if (!$this->active) return;
		
		$id = &$this->form_data->id;
		$pid = &$this->form_data->pid;
		$encounter = &$this->form_data->encounter;
		$dt = &$_POST;

		// Push data to array
		$item_array = array();
		foreach ($this->pedi_data->layout_list AS $record) {
			$key = 'form_' . $record['field_id'];
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