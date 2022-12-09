<?php
use wmt\Options;
/** **************************************************************************
 *	wmtSDoHHea.module.php
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
class SDoHHeaModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtSDoHHeaModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		
		return;
	}
	
	
	/**
	 * Display a collapsable section in the form.
	 *
	 */
	public function display($open=false, $bottom=false) {
		$this->toggle = ($open)? 'block' : 'none'; 
		
		$coverage_options = new Options('Portal_Coverage');
		$affordable_options = new Options('Portal_Affordable'); ?>
		
		<div class='wmtMainContainer wmtColorMain'>
			<?php Display::chapter($this->title, $this->key, $open); ?>
			<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
			
				<table width="100%">
					<tr>
						<td class="wmtBody" colspan="3">
							In the past year, has the patient been covered by a health insurance plan?
						</td>
					</tr><tr>
						<td class="wmtLabel" style="width:160px">
							Insurance Coverage:
						</td>
						<td class="wmtLabel">
							<select name="hx_education">
								<?php $coverage_options->showOptions($fdf_data[1], '--select--'); ?>
							</select>
						</td>
					</tr>
				</table>
					
				<hr style="border-color:#eee" />
					
				<table width="100%">
					<tr>
						<td class="wmtBody" colspan="4">
							In the past year, has patient had trouble affording health insurance (premiums, deductibles, co-payments, etc)?
						</td>
					</tr>
					<tr>
						<td class="wmtLabel" style="width:160px">
							Insurance Affordability:
						</td>
						<td class="wmtLabel" style="width:200px">
							<select name="hx_education">
								<?php $affordable_options->showOptions($fdf_data[1], '--select--'); ?>
							</select>
						</td>
						<td class="wmtLabel" style="width:70px">
							Explain:
						</td>
						<td class="wmtLabel">
							<input name="hx_study" type="text" class="wmtFullInput" value="" />
						</td>
					</tr>
				</table>
					
				<hr style="border-color:#eee" />
					
				<table width="100%">
					<tr>
						<td class="wmtBody" colspan="4">
							In the past year, has patient had trouble getting access to any of the following when it was needed (check all that apply)?
						</td>
					</tr><tr>
						<td class="wmtLabel" style="width:160px;vertical-align:top">
							Health Care Access:
						</td>
						<td class="wmtLabel">
							<input type="checkbox" class="wmtCheckbox" id="check1" name="check1" />
							<label for="check1">Health Insurance</label>
							<input type="checkbox" class="wmtCheckbox" id="check2" name="check2" style="margin-left:10px" />
							<label for="check2">Medical Care</label>
							<input type="checkbox" class="wmtCheckbox" id="check3" name="check3" style="margin-left:10px" />
							<label for="check3">Dental Care</label>
							<input type="checkbox" class="wmtCheckbox" id="check4" name="check4" style="margin-left:10px" />
							<label for="check4">Mental Health Care</label>
							<input type="checkbox" class="wmtCheckbox" id="check5" name="check5" style="margin-left:10px" />
							<label for="check5">Declines to Answer</label>
						</td>
					</tr>
				</table>

				<hr style="border-color:#eee" />
					
				<table width="100%">
					<tr>
						<td class="wmtBody" colspan="4">
							In the past year, has patient had trouble paying for health care costs or medications (check all that apply)?
						</td>
					</tr><tr>
						<td class="wmtLabel" style="width:160px;vertical-align:top">
							Health Care Costs:
						</td>
						<td class="wmtLabel">
							<input type="checkbox" class="wmtCheckbox" id="check1" name="check1" />
							<label for="check1">Trouble paying for health care cost</label>
							<input type="checkbox" class="wmtCheckbox" id="check2" name="check2" style="margin-left:10px" />
							<label for="check2">Trouble paying for medications</label>
							<input type="checkbox" class="wmtCheckbox" id="check3" name="check3" style="margin-left:10px" />
							<label for="check3">No payment problems</label>
							<input type="checkbox" class="wmtCheckbox" id="check5" name="check5" style="margin-left:10px" />
							<label for="check5">Declines to Answer</label>
						</td>
					</tr>
				</table>

				<table style="width:100%;margin-top:12px">
					<tr>
						<td class="wmtLabel" valign="top" colspan="6">
							Health Care &amp; Insurance Comments:
							<textarea name="hx_family_comments" id="hx_family_comments" class="wmtFullInput" rows="4" style="height:97px"><?php echo $hx_data->family_comments; ?></textarea>
						</td>
					</tr>
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
		if ($this->form_data->con_ask1_none) $output = true;
		else if ($this->form_data->con_ask1_dash) $output = true;
		else if ($this->form_data->con_ask1_hist) $output = true;
		else if ($this->form_data->con_ask1_notes) $output = true;
		else if ($this->form_data->con_ask2_none) $output = true;
		else if ($this->form_data->con_ask2_notes) $output = true;
		else if ($this->form_data->con_ask3_none) $output = true;
		else if ($this->form_data->con_ask3_notes) $output = true;
		
		if (!$output) return; ?>
		
		<div class='wmtPrnMainContainer'>
			<div class='wmtPrnCollapseBar'>
				<span class='wmtPrnChapter'><?php echo $this->title ?></span>
			</div>
			<div class='wmtPrnCollapseBox'>
				<table class='wmtPrnContent' style="margin:6px">
					
					<tr>
						<td style='line-height:14px' colspan="2">
							<span class='wmtPrnLabel'>Interval History:
<?php 
if ($this->form_data->con_ask1_none) { 
			echo "<i style='margin-left:10px' class='fa fa-fw fa-check'></i>";
			echo 'NONE';
} 
if ($this->form_data->con_ask1_dash) {
	echo "<i style='margin-left:10px' class='fa fa-fw fa-check'></i>";
	echo 'History updated in dashboard';
}
if ($this->form_data->con_ask1_hist) {
	echo "<i style='margin-left:10px' class='fa fa-fw fa-check'></i>";
	echo 'See new patient history form';
}
?>
							</span>
						</td>
					</tr><tr>
						<td style="width:25px"></td><td style='line-height:14px'>
							<span class='wmtPrnBody'><?php echo $this->form_data->con_ask1_notes . "\n"; ?></span>
						</td>
					</tr>
			
					<tr>
						<td style='line-height:14px;padding-top:10px' colspan="2">
							<span class='wmtPrnLabel'>Visits to Other Healthcare Providers:<?php 
if ($this->form_data->con_ask2_none) { 
			echo "<i style='margin-left:10px' class='fa fa-fw fa-check'></i>";
			echo 'NONE';
} ?>
							</span>
						</td>
					</tr><tr>
						<td style="width:25px"></td><td style='line-height:14px'>
							<span class='wmtPrnBody'><?php echo $this->form_data->con_ask2_notes . "\n"; ?></span>
						</td>
					</tr>
					
			
					<tr>
						<td style='line-height:14px;padding-top:10px' colspan="2">
							<span class='wmtPrnLabel'>Behavioral Health Issues:<?php 
if ($this->form_data->con_ask3_none) { 
			echo "<i style='margin-left:10px' class='fa fa-fw fa-check'></i>";
			echo 'NONE';
} ?>
							</span>
						</td>
					</tr><tr>
						<td style="width:25px"></td><td style='line-height:14px'>
							<span class='wmtPrnBody'><?php echo $this->form_data->con_ask3_notes . "\n"; ?></span>
						</td>
					</tr>
				</table>
			</div> <!-- END COLLAPSE BOX -->
		</div> <!-- END MAIN CONTAINER -->
		
<?php }


	/**
	 * Stores data from a form object into the database.
	 *
	 */
	public function store() {
		$dt = &$_POST;

		// Push data to form
		$this->form_data->con_ask1_none = strip_tags($dt['con_ask1_none']);
		$this->form_data->con_ask1_none = strip_tags($dt['con_ask1_dash']);
		$this->form_data->con_ask1_none = strip_tags($dt['con_ask1_hist']);
		$this->form_data->con_ask1_notes = strip_tags($dt['con_ask1_notes']);
		$this->form_data->con_ask2_none = strip_tags($dt['con_ask2_none']);
		$this->form_data->con_ask2_notes = strip_tags($dt['con_ask2_notes']);
		$this->form_data->con_ask3_none = strip_tags($dt['con_ask3_none']);
		$this->form_data->con_ask3_notes = strip_tags($dt['con_ask3_notes']);
		
		return;
	}
		
}
?>