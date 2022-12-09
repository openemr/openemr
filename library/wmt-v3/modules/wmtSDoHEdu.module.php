<?php
use wmt\Options;
/** **************************************************************************
 *	wmtSDoHPers.module.php
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
 *  @package modules
 *  @subpackage SDoH
 *  @version 1.0
 *  @category Module Class
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
class SDoHEduModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtSDoHEduModule::No module key provided for construct.');
	
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
		
		$ynd_options = new Options('YN_Decline'); 
		$edu_options = new Options('Education'); 
		$edu_options = new Options('Education'); 
		$job_options = new Options('Portal_Job_Stability'); 
		$branch_options = new Options('Military_Branch'); 
		$employ_options = new Options('Employment_Status'); ?>

		<div class='wmtMainContainer wmtColorMain'>
			<?php Display::chapter($this->title, $this->key, $open); ?>
			<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
			
				<table width="100%">
					<tr>
						<td class="wmtLabel" style="width:200px">
							Level of Education:
						</td>
						<td class="wmtLabel" style="width:210px">
							<select name="sdoh_edu_level">
								<?php $edu_options->showOptions($this->form_data->edu_level, '--select--'); ?>
							</select>
						</td>
						<td class="wmtLabel" style="width:100px">
							Area of Study:
						</td>
						<td class="wmtLabel">
							<input name="sdoh_edu_study" type="text" class="wmtFullInput" value="<?php echo $this->form_data->edu_study ?>" />
						</td>
					</tr>
						
					<tr>
						<td class="wmtLabel">
							Barriers to Learning:
						</td>
						<td class="wmtRadio">
							<input name="sdoh_edu_barrier" class="wmtRadio" type="radio"  <?php if ($this->form_data->edu_barrier == 0) echo "checked" ?> value="0" />No
							<input name="sdoh_edu_barrier" class="wmtRadio" type="radio" <?php if ($this->form_data->edu_barrier == 1) echo "checked" ?> style="margin-left:10px"  value="1" />Yes
						</td>
						<td class="wmtLabel">
							Explain:
						</td>
						<td class="wmtLabel">
							<input name="sdoh_edu_barrier_notes" type="text" class="wmtFullInput" value="<?php echo $this->form_data->edu_barrier_notes ?>" />
						</td>
					</tr>
					<tr>
						<td class="wmtLabel">
							Desire to Continue Education:
						</td>
						<td class="wmtRadio">
							<input name="sdoh_edu_continue" class="wmtRadio" type="radio"  <?php if ($this->form_data->edu_continue == 0) echo "checked" ?> value="0" />No
							<input name="sdoh_edu_continue" class="wmtRadio" type="radio" style="margin-left:10px"  <?php if ($this->form_data->edu_continue == 1) echo "checked" ?> value="1" />Yes
							<input name="sdoh_edu_continue" class="wmtRadio" type="radio" style="margin-left:10px"  <?php if ($this->form_data->edu_continue == 2) echo "checked" ?> value="2" />Not Sure
						</td>
					</tr>	
				</table>
					
				<hr style="border-color:#eee" />
					
				<table width="100%">
					<tr>
						<td class="wmtLabel" style="width:200px">
							Current Employment:
						</td>
						<td class="wmtLabel" style="width:210px">
							<select name="sdoh_job_type">
								<?php $employ_options->showOptions($this->form_data->job_type, '--select--'); ?>
							</select>
						</td>
						<td class="wmtLabel" style="width:100px">
							Number of Jobs:
						</td>
						<td class="wmtLabel" style="width:100px">
							<input name="sdoh_job_count" type="text" class="wmtFullInput" style="width:30px" value="<?php echo $this->form_data->job_count ?>" />
						</td>
						<td class="wmtLabel" style="width:100px">
							Hours Per Week:
						</td>
						<td class="wmtLabel" style="width:100px">
							<input name="sdoh_job_hours" type="text" class="wmtFullInput" style="width:70px" value="<?php echo $this->form_data->job_hours ?>" />
						</td>
						<td class="wmtLabel" style="width:100px">
							Trade or Skill:
						</td>
						<td class="wmtLabel">
							<input name="sdoh_job_trade" type="text" class="wmtFullInput" style="min-width:120px" value="<?php echo $this->form_data->job_trade ?>" />
						</td>
					</tr>
					
					<tr>
						<td class="wmtLabel">
							Current / Last Employer:
						</td>
						<td class="wmtLabel">
							<input name="sdoh_job_employer" type="text" class="wmtFullInput" style="width:200px" value="<?php echo $this->form_data->job_employer ?>" />
						</td>
						<td class="wmtLabel">
							Job Duration:
						</td>
						<td class="wmtLabel">
							<input name="sdoh_job_duration" type="text" class="wmtFullInput" style="width:80px" value="<?php echo $this->form_data->job_duration ?>" />
						</td>
						<td class="wmtLabel">
							Stability:
						</td>
						<td class="wmtLabel">
							<select name="sdoh_job_stability">
								<?php $job_options->showOptions($this->form_data->job_stability, '--select--'); ?>
							</select>
					</td>
						<td class="wmtLabel" style="width:80px">
							Position:
						</td>
						<td class="wmtLabel">
							<input name="sdoh_job_position" type="text" class="wmtFullInput" style="min-width:120px" value="<?php echo $this->form_data->job_position ?>" />
						</td>
					</tr>
				</table>

				<hr style="border-color:#eee" />

				<table width="100%">
					<tr>
						<td class='wmtBody' colspan='4'>
							At any point in the past 2 years, has seasonal or migrant farm work been the family's main source of income?
						</td>
					</tr>
					<tr>
						<td class='wmtLabel' style='width:200px'>
							Migrant / Seasonal Farm Work:
						</td>
						<td class='wmtLabel' style='width:210px'>
							<select name='sdoh_job_farm'>
								<?php $ynd_options->showOptions($this->form_data->job_farm, '--select-- '); ?>
							</select>
						</td>
						<td class='wmtLabel' style='width:100px'>
							Comment / Notes: 
						</td>
						<td>
							<input type='text' class='wmtFullInput' name='sdoh_job_farm_notes' value='<?php echo $this->form_data->job_farm_notes ?>' /> 
						</td>
					</tr>
				</table>

				<hr style="border-color:#eee" />
													
				<table width="100%" border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td class="wmtLabel" style="width:130px">
							Military Service:
						</td>
						<td class="wmtRadio" style="width:120px">
							<input name="sdoh_mil_service" class="wmtRadio" type="radio" <?php if ($this->form_data->mil_service == 0) echo "checked" ?> value="0" />No
							<input name="sdoh_mil_service" class="wmtRadio" type="radio" style="margin-left:10px" <?php if ($this->form_data->mil_service == 1) echo "checked" ?> value="1" />Yes
						</td>
						<td class="wmtLabel" style="width:90px">
							Years Served:
						</td>
						<td class="wmtLabel" style="width:60px">
							<input name="sdoh_mil_served" type="text" class="wmtInput" style="width:30px" value="<?php echo $this->form_data->mil_served ?>" />
						</td>
						<td class="wmtLabel" style="width:50px">
							Branch:
						</td>
						<td class="wmtLabel" style="width:170px">
							<select name='sdoh_mil_branch'>
								<?php $branch_options->showOptions($this->form_data->mil_branch, '--select-- '); ?>
							</select>
						</td>
						<td class="wmtLabel" style="width:105px">
							Discharge Type:
						</td>
						<td class="wmtLabel">
							<select name='sdoh_mil_discharge'>
								<?php $disc_options->showOptions($this->form_data->mil_discharge, '--select-- '); ?>
							</select>
						</td>
					</tr>
					
					<tr>
						<td class="wmtRadio" style="padding-left:25px" colspan="8">
							<span style='font-weight:normal'>If patient served in the military, qualifies for VA Benefits:</span>
							<input name="sdoh_mil_va" class="wmtRadio" type="radio" <?php if ($this->form_data->mil_va == 0) echo "checked" ?> value="0" />No
							<input name="sdoh_mil_va" class="wmtRadio" type="radio" style="margin-left:10px" <?php if ($this->form_data->mil_service == 1) echo "checked" ?> value="1" />Yes
						</td>
					</tr>
					
					<tr>
						<td class="wmtLabel" valign="top" style="padding-top:15px" colspan="8">
							Education & Employment Comments:
							<textarea name="sdoh_edu_comments" class="wmtFullInput" rows="4" style="height:97px"></textarea>
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