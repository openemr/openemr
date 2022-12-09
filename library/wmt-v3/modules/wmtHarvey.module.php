<?php
use wmt\Options;
/** **************************************************************************
 *	wmtHarvey.module.php
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
 *  @subpackage harvey
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
class HarveyModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtHarveyModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
		
		// store patient data
		$this->pat_data = Patient::getPidPatient($this->form_data->pid);
				
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
		
		$yn_options = new Options('yesno'); 
		$own_options = new Options('home_o_r'); 
		$days_options = new Options('days_shelter'); 
		$what_options = new Options('what_shelter'); 
		$deep_options = new Options('fw_ex_deep'); 
		$time_options = new Options('fw_ex_time'); ?>

		<div class='wmtMainContainer wmtColorMain'>
			<?php Display::chapter($this->title, $this->key, $open); ?>
			<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
			
				<table width="100%">
					<tr>
						<td class="wmtLabel" style="width:230px">
							Own or rent home:
						</td>
						<td class="wmtSelect" style="width:200px">
							<select name="own_rent" <?php if ($this->readonly) echo "disabled" ?> >
								<?php $own_options->showOptions($this->pat_data->own_rent, '--select--'); ?>
							</select>
						</td>
						<td class="wmtLabel" style="width:200px">
							Was property flooded:
						</td>
						<td class="wmtSelect" >
							<select name="flooded" <?php if ($this->readonly) echo "disabled" ?>>
								<?php $yn_options->showOptions($this->pat_data->flooded, '--select--'); ?>
							</select>
						</td>
					</tr>
						
					<tr>
						<td class="wmtLabel">
							Patient evacuated:
						</td>
						<td class="wmtSelect">
							<select name="evacuated" <?php if ($this->readonly) echo "disabled" ?> >
								<?php $yn_options->showOptions($this->pat_data->evacuated, '--select--'); ?>
							</select>
						</td>
						<td class="wmtLabel">
							Able to return home:
						</td>
						<td class="wmtSelect">
							<select name="return_home" <?php if ($this->readonly) echo "disabled" ?> >
								<?php $yn_options->showOptions($this->pat_data->return_home, '--select--'); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="wmtLabel">
							Days in shelter:
						</td>
						<td class="wmtSelect">
							<select name="days_shelter" <?php if ($this->readonly) echo "disabled" ?>>
								<?php $days_options->showOptions($this->pat_data->days_shelter, '--select--'); ?>
							</select>
						</td>
						<td class="wmtLabel">
							Shelter or facility:
						</td>
						<td style="padding-left:0">
							<input type='text' class='wmtFullInput' name='what_shelter' value='<?php echo $this->pat_data->what_shelter ?>'  <?php if ($this->readonly) echo "disabled" ?> /> 
						</td>
					</tr>	
				</table>
					
				<hr style="border-color:#eee" />
					
				<table width="100%">
					<tr>
						<td class="wmtLabel" style="width:230px">
							Car flooded / distroyed:
						</td>
						<td class="wmtSelect" style="width:200px">
							<select name="car_flooded" <?php if ($this->readonly) echo "disabled" ?>>
								<?php $yn_options->showOptions($this->pat_data->car_flooded, '--select--'); ?>
							</select>
						</td>
						<td class="wmtLabel" style="width:200px">
							Transportation available:
						</td>
						<td class="wmtSelect">
							<select name="transport" <?php if ($this->readonly) echo "disabled" ?>>
								<?php $yn_options->showOptions($this->pat_data->transport, '--select--'); ?>
							</select>
						</td>
					</tr>
				</table>

				<hr style="border-color:#eee" />

				<table width="100%">
					<tr>
						<td class='wmtLabel' style='width:230px'>
							Patient exposed to flood water:
						</td>
						<td class='wmtSelect' style='width:200px'>
							<select name='exposed_fl_water' <?php if ($this->readonly) echo "disabled" ?>>
								<?php $yn_options->showOptions($this->pat_data->exposed_fl_water, '--select-- '); ?>
							</select>
						</td>
						<td class='wmtLabel' style='width:200px'>
							Explain situation:
						</td>
						<td style="padding-left:0">
							<input type='text' class='wmtFullInput' name='fw_ex_notes' value='<?php echo $this->pat_data->fw_ex_notes ?>'  <?php if ($this->readonly) echo "disabled" ?>/> 
						</td>
					</tr>
					<tr>
						<td class='wmtLabel'>
							Flood water depth:
						</td>
						<td class='wmtSelect'>
							<select name='fw_ex_deep' <?php if ($this->readonly) echo "disabled" ?>>
								<?php $deep_options->showOptions($this->pat_data->fw_ex_deep, '--select-- '); ?>
							</select>
						</td>
						<td class='wmtLabel'>
							Length of exposure:
						</td>
						<td class='wmtSelect'>
							<select name='fw_ex_time' <?php if ($this->readonly) echo "disabled" ?>>
								<?php $time_options->showOptions($this->pat_data->fw_ex_time, '--select-- '); ?>
							</select>
						</td>
					</tr>
				</table>
				
				<hr style="border-color:#eee" />

				<table width="100%">
					<tr>
						<td class='wmtLabel'>
							Comment / Notes:<br/> 
							<textarea class="wmtFullInput" name="harvey_notes" rows="4" <?php if ($this->readonly) echo "disabled" ?>><?php echo $this->pat_data->harvey_notes ?></textarea>
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
		$yn_options = new Options('yesno'); 
		$own_options = new Options('home_o_r'); 
		$days_options = new Options('days_shelter'); 
		$what_options = new Options('what_shelter'); 
		$deep_options = new Options('fw_ex_deep'); 
		$time_options = new Options('fw_ex_time'); ?>
		
		<div class='wmtPrnMainContainer'>
			<div class='wmtPrnCollapseBar'>
				<span class='wmtPrnChapter'><?php echo $this->title ?></span>
			</div>
			<div class='wmtPrnCollapseBox'>
				<table class='wmtPrnContent' style="padding:0">
					
					<tr>
						<td class='wmtPrnLabel' style="width:25%">
							Own or rent home:
						</td>
						<td style="width:25%">
							<span class='wmtPrnBody'><?php echo $own_options->showItem($this->pat_data->own_rent); ?></span>
						</td>
						<td class='wmtPrnLabel' style="width:25%">
							Was property flooded:
						</td>
						<td style="width:25%">
							<span class="wmtPrnBody"><?php echo $yn_options->showItem($this->pat_data->flooded); ?></span>
						</td>
					</tr>
			
					<tr>
						<td class="wmtPrnLabel">
							Patient evacuated:
						</td>
						<td>
							<span class="wmtPrnBody"><?php echo $yn_options->showItem($this->pat_data->evacuated); ?></span>
						</td>
						<td class="wmtPrnLabel">
							Able to return home:
						</td>
						<td>
							<span class="wmtPrnBody"><?php echo $yn_options->showItem($this->pat_data->return_home); ?></span>
						</td>
					</tr>

					<tr>
						<td class="wmtPrnLabel">
							Days in shelter:
						</td>
						<td>
							<span class="wmtPrnBody"><?php echo $days_options->showItem($this->pat_data->days_shelter); ?></span>
						</td>
						<td class="wmtPrnLabel">
							Shelter or facility:
						</td>
						<td>
							<span class="wmtPrnBody"><?php echo $what_options->showItem($this->pat_data->what_shelter); ?></span>
						</td>
					</tr>	
				</table>
					
				<hr style="border-color:#eee" />
					
				<table width="100%">
					<tr>
						<td class="wmtPrnLabel" style="width:25%">
							Car flooded / distroyed:
						</td>
						<td style="width:25%">
							<span class="wmtPrnBody"><?php echo $yn_options->showItem($this->pat_data->car_flooded); ?></span>
						</td>
						<td class="wmtPrnLabel" style="width:25%">
							Transportation available:
						</td>
						<td style="width:25%">
							<span class="wmtPrnBody"><?php echo $yn_options->showItem($this->pat_data->transport); ?></span>
						</td>
					</tr>
				</table>

				<hr style="border-color:#eee" />

				<table width="100%">
					<tr>
						<td class='wmtPrnLabel' style="width:25%">
							Patient exposed to flood water:
						</td>
						<td style="width:25%">
							<span class="wmtPrnBody"><?php echo $yn_options->showItem($this->pat_data->exposed_fl_water); ?></span>
						</td>
						<td class='wmtPrnLabel' style="width:25%">
							Explain situation:
						</td>
						<td style="width:25%">
							<span class="wmtPrnBody"><?php echo $this->pat_data->fw_ex_notes ?> </span>
						</td>
					</tr>
					<tr>
						<td class='wmtPrnLabel' style="width:25%">
							Flood water depth:
						</td>
						<td style="width:25%">
							<span class="wmtPrnBody"><?php echo $deep_options->showItem($this->pat_data->fw_ex_deep); ?></span>
						</td>
						<td class='wmtPrnLabel' style="width:25%">
							Length of exposure (minutes):
						</td>
						<td style="width:25%">
							<span class="wmtPrnBody"><?php echo $time_options->showItem($this->pat_data->fw_ex_time); ?></span>
						</td>
					</tr>
				</table>
				
				<hr style="border-color:#eee" />

				<table width="100%">
					<tr>
						<td class='wmtPrnLabel' style="vertical-align:top">
							Comments / Notes:
						</td>
						<td style="width:25px"></td><td>
							<span class="wmtPrnBody"><?php echo $this->pat_data->harvey_notes ?></span>
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
		$this->pat_data->flooded = strip_tags($dt['flooded']);
		$this->pat_data->own_rent = strip_tags($dt['own_rent']);
		$this->pat_data->evacuated = strip_tags($dt['evacuated']);
		$this->pat_data->return_home = strip_tags($dt['return_home']);
		$this->pat_data->days_shelter = strip_tags($dt['days_shelter']);
		$this->pat_data->what_shelter = strip_tags($dt['what_shelter']);
		$this->pat_data->car_flooded = strip_tags($dt['car_flooded']);
		$this->pat_data->transport = strip_tags($dt['transport']);
		$this->pat_data->exposed_fl_water = strip_tags($dt['exposed_fl_water']);
		$this->pat_data->fw_ex_notes = strip_tags($dt['fw_ex_notes']);
		$this->pat_data->fw_ex_deep = strip_tags($dt['fw_ex_deep']);
		$this->pat_data->fw_ex_time = strip_tags($dt['fw_ex_time']);
		$this->pat_data->harvey_notes = strip_tags($dt['harvey_notes']);
		
		// Save changes
		$this->pat_data->store();
		
		return;
	}
		
}
?>