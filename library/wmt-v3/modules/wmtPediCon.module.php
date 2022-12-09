<?php
/** **************************************************************************
 *	wmtPediCon.module.php
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
class PediConModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtPediConModule::No module key provided for construct.');
	
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
		$this->toggle = ($open)? 'block' : 'none'; ?>

		<div class='wmtMainContainer wmtColorMain'>
			<?php Display::chapter($this->title, $this->key, $open); ?>
			<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
				<table style="width:100%">
					<tr>
						<td class="wmtLabel" colspan="2">
							Interval History:
							<input class="wmtCheck"  style="margin-left:20px" type="checkbox" name="con_ask1_none" id="con_ask1_none" value="1" <?php if ($this->form_data->con_ask1_none) echo 'checked' ?> /><label class="wmtCheck">None</label>
							<input class="wmtCheck"  style="margin-left:20px" type="checkbox" name="con_ask1_dash" id="con_ask1_dash" value="1" <?php if ($this->form_data->con_ask1_dash) echo 'checked' ?> /><label class="wmtCheck">History updated in dashboard</label>
							<input class="wmtCheck"  style="margin-left:20px" type="checkbox" name="con_ask1_hist" id="con_ask1_hist" value="1" <?php if ($this->form_data->con_ask1_hist) echo 'checked' ?> /><label class="wmtCheck">See new patient history form</label>
							<textarea name="con_ask1_notes" id="con_ask1_notes" class="wmtFullInput" rows="2"><?php echo $this->form_data->con_ask1_notes; ?></textarea>
						</td>
					</tr>
					<tr>
						<td class="wmtLabel" colspan="2">
							Visits to Other Healthcare Providers:
							<input class="wmtCheck"  style="margin-left:20px" type="checkbox" name="con_ask2_none" id="con_ask2_none" value="1" <?php if ($this->form_data->con_ask2_none) echo 'checked' ?> /><label class="wmtCheck">None</label>
							<textarea name="con_ask2_notes" id="con_ask2_notes" class="wmtFullInput" rows="2"><?php echo $this->form_data->con_ask2_notes; ?></textarea>
						</td>
					</tr>
					<tr>
						<td class="wmtLabel" colspan="2">
							Behavioral Health Issues:
							<input class="wmtCheck"  style="margin-left:20px" type="checkbox" name="con_ask3_none" id="con_ask3_none" value="1" <?php if ($this->form_data->con_ask3_none) echo 'checked' ?> /><label class="wmtCheck">None</label>
							<textarea name="con_ask3_notes" id="con_ask3_notes" class="wmtFullInput" rows="2"><?php echo $this->form_data->con_ask3_notes; ?></textarea>
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