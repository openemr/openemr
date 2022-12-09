<?php
use wmt\Options;
/** **************************************************************************
 *	wmtSDoHFin.module.php
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
class SDoHFinModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtSDoHFinModule::No module key provided for construct.');
	
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

		<script>
		// define total wages function
		var income = function() {
			var income = 0;
			
			var wages 	= $.trim($('#family_wages').val());
			wages = wages.replace(/\D+/g, '');
			if (wages != '') income += parseInt(wages);
			$('#family_wages').val(wages);
			
			var ssi		= $.trim($('#family_ssi').val());
			ssi = ssi.replace(/\D+/g, '');
			if (ssi != '') income += parseInt(ssi);
			$('#family_ssi').val(ssi);
	
			var assist	= $.trim($('#family_assist').val());
			assist = assist.replace(/\D+/g, '');
			if (assist != '') income += parseInt(assist);
			$('#family_assist').val(assist);
	
			var support	= $.trim($('#family_support').val());
			support = support.replace(/\D+/g, '');
			if (support != '') income += parseInt(support);
			$('#family_support').val(support);
	
			var unemp	= $.trim($('#family_unemp').val());
			unemp = unemp.replace(/\D+/g, '');
			if (unemp != '') income += parseInt(unemp);
			$('#family_unemp').val(unemp);
	
			var other	= $.trim($('#family_other').val());
			other = other.replace(/\D+/g, '');
			if (other != '') income += parseInt(other);
			$('#family_other').val(other);
	
			$('#family_income').val(income);
		}
	
		// initialize
		$(document).ready(function() {
			$(".money").bind("keyup change", income);
		});

		</script>
		<div class='wmtMainContainer wmtColorMain'>
			<?php Display::chapter($this->title, $this->key, $open); ?>
			<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
			
				<table width="100%">
					<tr>
						<td class="wmtLabel" colspan="5">
							Household Monthly Income Information (okay to estimate or enter 0):
						</td>
					</tr>
					<tr>
						<td class="wmtHeader" style="width:14%">
							Wages (in dollars)<br/>
							<input class="wmtFullInput money" type="text" id="family_wages" min="0" name="family_wages" style="width:95%" value="<?php echo $data['wages'] ?>" />
						</td>
						<td class="wmtHeader" style="width:14%">
							Social Security / SSI<br/>
							<input class="wmtFullInput money" type="text" id="family_ssi" min="0" name="family_ssi" style="width:95%" value="<?php echo $data['wages'] ?>" />
						</td>
						<td class="wmtHeader" style="width:14%">
							State / Federal Assistance<br/>
							<input class="wmtFullInput money" type="text" id="family_assist" min="0" name="family_assist" style="width:95%" value="<?php echo $data['wages'] ?>" />
						</td>
						<td class="wmtHeader" style="width:14%">
							Alimony / Child Support<br/>
							<input class="wmtFullInput money" type="text" id="family_support" min="0" name="family_support" style="width:95%" value="<?php echo $data['wages'] ?>" />
						</td>
						<td class="wmtHeader" style="width:14%">
							Unemployment<br/>
							<input class="wmtFullInput money" type="text" id="family_unemp" min="0" name="family_unemp" style="width:95%" value="<?php echo $data['wages'] ?>" />
						</td>
						<td class="wmtHeader" style="width:14%">
							Other Income<br/>
							<input class="wmtFullInput money" type="text" id="family_other" min="0" name="family_other" style="width:95%" value="<?php echo $data['wages'] ?>" />
						</td>
						<td class="wmtHeader">
							Calculated Total<br/>
							<input class="wmtFullInput money" type="text" id="family_income" name="family_income" disabled style="width:100%" 
								value="<?php echo $data['wages'] + $data['ssi'] + $data['unemp'] + $data['other'] ?>" />
						</td>
					</tr>
				</table>
					
				<hr style="border-color:#eee" />
					
				<table width="100%">
					<tr>
						<td class="wmtBody" colspan="4">
							In the 30 days, has patient or any family member living with them been unable to get any of the following when it was really needed (check all that apply)?
						</td>
					</tr><tr>
						<td class="wmtLabel" style="width:160px;vertical-align:top">
							Necessities Access:
						</td>
						<td class="wmtLabel">
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check1" name="check1" />
								<label for="check1">Food</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check2" name="check2" style="margin-left:20px" />
								<label for="check2">Utilities</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check3" name="check3" style="margin-left:20px" />
								<label for="check3">Phone</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check4" name="check4" style="margin-left:20px" />
								<label for="check4">Clothing</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check5" name="check5" style="margin-left:20px" />
								<label for="check5">Rent/Mortgage</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check6" name="check6" style="margin-left:20px" />
								<label for="check6">Child Care</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check7" name="check7" style="margin-left:20px" />
								<label for="check7">Other</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check8" name="check8" style="margin-left:20px" />
								<label for="check8">Declines to Answer</label>
							</span>
						</td>
					</tr>
				</table>

				<hr style="border-color:#eee" />
					
				<table width="100%">
					<tr>
						<td class="wmtBody" colspan="4">
							Has the lack of transportation kept patient from any of the following (check all that apply)?
						</td>
					</tr><tr>
						<td class="wmtLabel" style="width:160px;vertical-align:top">
							Transportation Availability:
						</td>
						<td class="wmtLabel">
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check1" name="check1" />
								<label for="check1">Medical Appointments</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check2" name="check2" style="margin-left:10px" />
								<label for="check2">Medications</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check3" name="check3" style="margin-left:10px" />
								<label for="check3">Non-Medical Meetings</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check4" name="check4" style="margin-left:10px" />
								<label for="check4">Appointments</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check5" name="check5" style="margin-left:10px" />
								<label for="check5">Work</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check6" name="check6" style="margin-left:10px" />
								<label for="check6">Daily Errands</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check7" name="check7" style="margin-left:10px" />
								<label for="check7">Nothing Reported</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check8" name="check8" style="margin-left:10px" />
								<label for="check8">Declines to Answer</label>
							</span>
						</td>
					</tr>
				</table>

				<hr style="border-color:#eee" />
					
				<table width="100%">
					<tr>
						<td class="wmtBody" colspan="4">
							Does the patient have any current legal concern (check all that apply)?
						</td>
					</tr><tr>
						<td class="wmtLabel" style="width:160px;vertical-align:top">
							Legal Issues:
						</td>
						<td class="wmtLabel">
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check1" name="check1" />
								<label for="check1">Parole</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check2" name="check2" style="margin-left:10px" />
								<label for="check2">Probation</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check3" name="check3" style="margin-left:10px" />
								<label for="check3">Supervised Release</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check4" name="check4" style="margin-left:10px" />
								<label for="check4">Completed Sentence</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check5" name="check5" style="margin-left:10px" />
								<label for="check5">Drug Court</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check6" name="check6" style="margin-left:10px" />
								<label for="check6">DUI Arrest</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check7" name="check7" style="margin-left:10px" />
								<label for="check7">Community Service</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check8" name="check8" style="margin-left:10px" />
								<label for="check8">Nothing Reported</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check9" name="check9" style="margin-left:10px" />
								<label for="check9">Declines to Answer</label>
							</span>
						</td>
					</tr>
				</table>

				<hr style="border-color:#eee" />
					
				<table width="100%">
					<tr>
						<td class="wmtBody" colspan="4">
							Does the patient have other concerns regarding their legal rights (check all that apply)?
						</td>
					</tr><tr>
						<td class="wmtLabel" style="width:160px;vertical-align:top">
							Legal Rights:
						</td>
						<td class="wmtLabel">
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check1" name="check1" />
								<label for="check1">Divorce</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check2" name="check2" style="margin-left:10px" />
								<label for="check2">Custody</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check3" name="check3" style="margin-left:10px" />
								<label for="check3">Guardianship</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check4" name="check4" style="margin-left:10px" />
								<label for="check4">Visitation</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check5" name="check5" style="margin-left:10px" />
								<label for="check5">Eviction</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check6" name="check6" style="margin-left:10px" />
								<label for="check6">Housing Issues</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check7" name="check7" style="margin-left:10px" />
								<label for="check7">Bankruptcy</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check8" name="check8" style="margin-left:10px" />
								<label for="check8">Public Benefits</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check9" name="check9" style="margin-left:10px" />
								<label for="check9">Imigration</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check10" name="check10" style="margin-left:10px" />
								<label for="check10">Tax Issues</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check11" name="check11" style="margin-left:10px" />
								<label for="check11">Life Planning</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check12" name="check12" style="margin-left:10px" />
								<label for="check12">Nothing Reported</label>
							</span>
							<span  style="white-space:nowrap">
								<input type="checkbox" class="wmtCheckbox" id="check13" name="check13" style="margin-left:10px" />
								<label for="check13">Declines to Answer</label>
							</span>
						</td>
					</tr>
				</table>

				<table style="width:100%;margin-top:12px">
					<tr>
						<td class="wmtLabel" valign="top" colspan="6">
							Financial &amp; Legal Comments:
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