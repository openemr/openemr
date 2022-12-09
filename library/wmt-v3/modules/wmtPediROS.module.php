<?php
/** **************************************************************************
 *	wmtPediROS.module.php
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
include($GLOBALS['srcdir'].'/wmt-v2/ros_functions.inc');
include($GLOBALS['srcdir'].'/wmt-v2/wmtprint.inc');
class PediROSModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of form class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtPediROSModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'] . ' - Pediatric';
		$this->layout_key = 'ROS';
			
		// load layout information
		$this->pedi_data = PediLayout::fetchEncounter($this->layout_key, $this->form_data->pid, $this->form_data->encounter);
			
		return;
	}
	
	
	/**
	 * Display a collapsable section in the form.
	 *
	 * @param boolean $toggle - true section open, false section collapsed
	 */
	public function display($open=false, $bottom=false) {
		$this->toggle = ($open)? 'block' : 'none';
	
		echo "<script>";
		include($GLOBALS['srcdir'] . '/wmt-v2/ros_functions.js');
		echo "</script>";
		
		echo "<div class='wmtMainContainer wmtMainColor'>\n";
		Display::chapter($this->title, $this->key, $open);
		echo "<div id='".$this->key."Box' class='wmtCollapseBox wmtColorBox' style='padding:10px;display: ".$this->toggle.";'>\n";
	
		// CONTENT GOES HERE !!!
		$ros_cats = $this->pedi_data->layout_cats;
		$ros_options = $this->pedi_data->layout_list;
		$rs = $this->pedi_data->layout_data['rs_data'];
		$ros = $this->pedi_data->layout_data['ros_data'];
		$pat_sex = $this->form_data->sex; 

		
		$split = round(count($ros_cats) / 2);
		$count = 1; ?>
			
	    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtLabel">Please mark yes or no:</td>
				<td>
					<div style="width: 60%; float: right; padding-right: 12px;">
						<div style="float: left;" style="padding-right: 20px">
							<a class="css_button" tabindex="-1" onclick="return toggleNoProblem();" href="javascript:;">
								<span>Check All 'No Problems Indicated'</span>
							</a>
						</div>
						<div style="float: right;" style="padding-right: 1px;">
							<a class="css_button" tabindex="-1" onclick="return toggleROStoNull();" href="javascript:;">
								<span>Clear All</span>
							</a>
						</div>
					</div>
				</td>
      		</tr>

			<tr>
				<td style="width: 50%; vertical-align: top;">
					<table style="width:100%"> 
<?php
		// Process each category
		foreach ($ros_cats AS $category) {
			$rs_key = 'rs_'.$category['option_id'];
			$ros_key = $category['notes'];
			$cat_key = $category['option_id'];
			$cat_title = $category['title'] . ': ';  
			
			if ($count++ == $split)
				echo "</table></td><td style='width:50%;vertical-align:top;'><table style='width:100%'>"; ?>

						<tr>
							<td class="wmtLabel" colspan="2"><?php echo $cat_title ?></td>
							<td class="wmtBody wmtR">
								<input name="<?php echo $ros_key ?>_hpi" id="<?php echo $ros_key ?>_hpi" type="checkbox" value="1" 
									<?php echo ($ros[$ros_key.'_hpi'] == '1')?' checked ':''; ?> onchange="toggleROSTypeToNull(this,'<?php echo $ros_key ?>','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" />
							</td>
							<td class="wmtBody">
								<label for="<?php echo $ros_key ?>_hpi">Refer to HPI</label>
							</td>
						</tr>
						<tr>
							<td class="wmtBody wmtR" style="padding-left:35px;">
								<input name="<?php echo $ros_key?>_none" id="<?php echo $ros_key?>_none" type="checkbox" value="1" 
									<?php echo (($ros[$ros_key.'_none'] == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'<?php echo $ros_key ?>','<?php echo $client_id; ?>');" />
							</td>
							<td class="wmtBody" style="width:25%"><label for="<?php echo $ros_key?>_none">No Problems Indicated</label></td>
						</tr>
<?php 
			foreach ($ros_options AS $record) {
				$key = $record['option_id'];

				if (strpos($key, $rs_key) === false) continue; // wrong category
				GenerateROSLine($key, $record['title'], $rs[$key], $rs[$key.'_nt'], $cat_key);
			} ?>
			
						<tr><!-- For spacing only -->
							<td>&nbsp;</td>
						</tr>
<?php 
		} ?>
					</table>
				</td>
			</tr>
			<tr>
				<td class="wmtLabel">History of Present Illness (HPI):</td>
			</tr>
			<tr>
				<td colspan="3">
					<textarea name="ros_nt" id="ros_nt" class="wmtFullInput" rows="4"><?php echo htmlspecialchars($ros['ros_nt'], ENT_QUOTES, '', FALSE); ?></textarea>
				</td>
			</tr>
		</table>

<?php /*		$ros_options = $this->pedi_data->layout_list;
		$rs = $this->pedi_data->layout_data['rs_data'];
		$wmt_ros = $this->pedi_data->layout_data['ros_data'];
		$wmt_ros['ros_nt'] = $this->form_data->ros_notes;
		$pat_sex = $this->form_data->sex;
		$base_action = $GLOBALS['base_action'];
		$pedi_notes_label = 'Review Notes';
		if ($this->form_data->form_type)	
			$pedi_notes_label = 'History of Present Illness (HPI)';
		
		include($GLOBALS['srcdir'] . '/wmt-v2/form_modules/ros2_module.inc.php');
*/	
		echo "	</div>\n";
		Display::bottom($this->title, $this->key, $open, $bottom);
		echo "</div>\n";
	}


	/**
	 * Print a collapsable section in the report.
	 *
	 */
	public function report() {
		$ros_cats = $this->pedi_data->layout_cats;
		$ros_options = $this->pedi_data->layout_list;
		$rs = $this->pedi_data->layout_data['rs_data'];
		$ros = $this->pedi_data->layout_data['ros_data'];
		$pat_sex = $this->form_data->sex;

		$rs_none = array();
		$rs_yes = array();
		$rs_no = array();
		
		foreach ($ros_options AS $record) {
			$key = $record['option_id'];

			if (strtolower($rs[$key]) == 'y') {
				$rs_yes[$key] = $record['title'];
			} else if (strtolower($rs[$key]) == 'n') {
				$rs_no[$key] = $record['title'];
			} else {
				$rs_none[$key] = $record['title'];
			}
		}
			
		$chp_printed = false;

		// Process each category
		foreach ($ros_cats AS $category) {
			
			$hdr_printed = false;
		
			$cat_key = 'rs_' . $category['option_id'];
			$cat_title = $category['title'] . ': ';
			if ($ros[$cat_key.'_hpi'] == '1') {
			    $chp_printed = PrintChapter('Review of Systems', $chp_printed);
				$hdr_printer = PrintHeader($cat_title . "&nbsp;&nbsp;Refer to HPI for Details", $hdr_printed);
			}
			if ($ros[$cat_key.'_none'] == '1') {
			    $chp_printed = PrintChapter('Review of Systems', $chp_printed);
				$hdr_printed = PrintHeader($cat_title . "&nbsp;&nbsp;No Problems Indicated", $hdr_printed);
			}
			
			// LIST ALL COMMENTS WITHOUT YES/NO
			foreach ($rs_none AS $key => $title) {
				if (strpos($key, $cat_key) === false) continue; // wrong category

				$note = $rs[$key.'_nt'];
				if (!$note) continue;  // nothing to print
				
			    $chp_printed = PrintChapter('Review of Systems', $chp_printed);
    			$hdr_printed = PrintHeader($cat_title, $hdr_printed);

    			echo "  	<tr>\n";
				echo "			<td class='wmtPrnIndentBody' style='width: 30%'>$title</td>\n";
				echo "			<td class='wmtPrnBody' style='width: 5%'>&nbsp;</td>\n";
				echo "  		<td class='wmtPrnBody'>$note</td>\n";
				echo "		</tr>\n";
			}
		
			// NOW LIST ALL THE 'NO' CHOICES WITH NO COMMENT
			$data = array();
			foreach ($rs_no AS $key => $title) {
				if (strpos($key, $cat_key) === false) continue; // wrong category
				if ($rs[$key.'_nt']) continue; // has comment
				
				$data[] .= $title;
			}
			
			if (!empty($data)) {
				$content = 'Patient Denies: ';
				$content .= implode(', ', $data);

				$chp_printed = PrintChapter('Review of Systems', $chp_printed);
				$hdr_printed = PrintHeader($cat_title, $hdr_printed);

				echo "  	<tr>\n";
				echo "			<td class='wmtPrnIndentText' colspan='3'>$content</td>\n";
				echo "		</tr>\n";
			}
				
			// NOW LIST ALL THE 'YES' CHOICES WITH NO COMMENT
			$data = array();
			foreach ($rs_yes AS $key => $title) {
				if (strpos($key, $cat_key) === false) continue; // wrong category
				if ($rs[$key.'_nt']) continue; // has comment
				
				$data[] .= $title;
			}
			
			if (!empty($data)) {
				$content = 'Patient Indicates: ';
				$content .= implode(', ', $data);

				$chp_printed = PrintChapter('Review of Systems', $chp_printed);
				$hdr_printed = PrintHeader($cat_title, $hdr_printed);

				echo "  	<tr>\n";
				echo "			<td class='wmtPrnIndentText' colspan='3'>$content</td>\n";
				echo "		</tr>\n";
			}
				
			// NOW LIST ALL THE 'NO' CHOICES WITH A COMMENT
			foreach ($rs_no AS $key => $title) {
				if (strpos($key, $cat_key) === false) continue; // wrong category
				
				$note = $rs[$key.'_nt'];
				if (!$note) continue;  // nothing to print
				
			    $chp_printed = PrintChapter('Review of Systems', $chp_printed);
    			$hdr_printed = PrintHeader($cat_title, $hdr_printed);

    			echo "  	<tr>\n";
				echo "			<td class='wmtPrnIndentBody' style='width: 30%'>$title</td>\n";
				echo "			<td class='wmtPrnBody' style='width: 5%'>NO</td>\n";
				echo "  		<td class='wmtPrnBody'>$note</td>\n";
				echo "		</tr>\n";
			}			
			
			// NOW LIST ALL THE 'YES' CHOICES WITH A COMMENT
			foreach ($rs_yes AS $key => $title) {
				if (strpos($key, $cat_key) === false) continue; // wrong category
				
				$note = $rs[$key.'_nt'];
				if (!$note) continue;  // nothing to print
				
			    $chp_printed = PrintChapter('Review of Systems', $chp_printed);
    			$hdr_printed = PrintHeader($cat_title, $hdr_printed);

    			echo "  	<tr>\n";
				echo "			<td class='wmtPrnIndentBody' style='width: 30%'>$title</td>\n";
				echo "			<td class='wmtPrnBody' style='width: 5%'>YES</td>\n";
				echo "  		<td class='wmtPrnBody'>$note</td>\n";
				echo "		</tr>\n";
			}			
			
		}
		
		if ($this->form_data->ros_notes) {
		    $chp_printed = PrintChapter('Review of Systems', $chp_printed);
		    $hdr_printed = PrintHeader('General Notes:', false);

		    echo "  	<tr>\n";
			echo "			<td class='wmtPrnIndentText' colspan='3'>" . $this->form_data->ros_notes . "</td>\n";
			echo "		</tr>\n";
		}
		
		if($chp_printed) { CloseChapter(); }
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

		// Push data to array
		$rs_data = array();
		$ros_data = array();
		
		$this->form_data->ros_notes = (isset($_POST['ros_nt'])) ? strip_tags($_POST['ros_nt']) : '';
		
		foreach ($this->pedi_data->layout_cats AS $record) {
			$key = $record['notes'];

			// Store ROS data
			$ros_data[$key.'_none'] = (isset($_POST[$key.'_none'])) ? strip_tags($_POST[$key.'_none']) : '';
			$ros_data[$key.'_hpi'] = (isset($_POST[$key.'_hpi'])) ? strip_tags($_POST[$key.'_hpi']) : '';
				
		}
		
		$ros_no = array();
		$ros_yes = array();
		foreach ($this->pedi_data->layout_list AS $record) {
			$key = $record['option_id'];
			
			// Store RS data
			$rs_data[$key] = (isset($_POST[$key])) ? strip_tags($_POST[$key]) : '';
			$rs_data[$key.'_nt'] = (isset($_POST[$key.'_nt'])) ? strip_tags($_POST[$key.'_nt']) : '';
			if (strtolower($_POST[$key]) == 'y') $ros_yes[] = $key;  // add key to 'yes' array
			if (strtolower($_POST[$key]) == 'n') $ros_no = $key; // add key to 'no' array
			
		}
		
		// Store the YES and NO arrays
		$ros_data['ros_yes'] = implode('|', $ros_yes);
		$ros_data['ros_no'] = implode('|', $ros_no);
		
		// Store in object
		$this->pedi_data->layout_data['ros_data'] = $ros_data;
		$this->pedi_data->layout_data['rs_data'] = $rs_data;
		
		// Store the data		
		$this->pedi_data->date = date('Y-m-d H:i:s');
		$this->pedi_data->pid = $pid;
		$this->pedi_data->user = $_SESSION['authUser'];
		$this->pedi_data->encounter = $encounter;
		$this->pedi_data->activity = 1;
		$this->pedi_data->layout_title = $this->title;
		
		$this->pedi_data->store();
		
	}

}
?>