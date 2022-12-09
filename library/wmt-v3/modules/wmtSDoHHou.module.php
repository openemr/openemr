<?php
use wmt\Options;
/** **************************************************************************
 *	wmtSDoHHou.module.php
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
class SDoHHouModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtSDoHHouModule::No module key provided for construct.');
	
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
		$house_options = new Options('Portal_Housing');
		$stable_options = new Options('Portal_Stability');
		$temp_options = new Options('Portal_Temporary');
		$ten_options = new Options('Zero_to_10');
		$state_options = new Options('state');
		$county_options = new Options('Texas_County_Codes');
		
		// Retrieve patient associated with this form
		$pid = ($this->form_data->pid)? $this->form_data->pid : $_SESSION['pid'];
		$pat_data = Patient::getPidPatient($pid);
		
		$fam_options = new Options('PSYC_Family');
		$dyn_options = new Options('PSYC_Dynamics');
		$mar_options = new Options('marital');
		$ori_options = new Options('PSYC_Orientation');
		$kid_options = new Options('PSYC_Children');
		?>

		<div class='wmtMainContainer wmtColorMain'>
			<?php Display::chapter($this->title, $this->key, $open); ?>
			<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
			
				<table width="100%">
					<tr>
						<td>
							<table width="100%" border="0" cellspacing="0" cellpadding="2">
								<tr>
									<td class="wmtLabel" style="width:150px">
										Housing Situation:
									</td>
									<td class="wmtLabel" style="width:250px">
										<select class="wmtSelect" name="hx_family_dynamic_<?php echo $x ?>">
											<?php $house_options->showOptions($fdf_data[1], '--select--'); ?>
										</select>
									</td>
									<td class="wmtLabel" style="width:170px">
										People Living with Patient:
									</td>
									<td class="wmtRadio">
										<select class="wmtSelect" name="hx_family_dynamic_<?php echo $x ?>">
											<?php $ten_options->showOptions($fdf_data[1], '--select--'); ?>
										</select>
									</td>
								</tr>
									
								<tr>
									<td class="wmtLabel">
										Situation Stability:
									</td>
									<td class="wmtLabel">
										<select class="wmtSelect" name="hx_family_dynamic_<?php echo $x ?>">
											<?php $stable_options->showOptions($fdf_data[1], '--select--'); ?>
										</select>
									</td>											
									<td class="wmtLabel">
										Explain Situation:
									</td>
									<td class="wmtLabel">
										<input name="hx_parent_marital" type="text" class="wmtFullInput" value="<?php echo $hx_data->parent_marital; ?>" />
									</td>
								</tr>
							</table>
						
							<fieldset style="padding-top:0">
								<legend>Current Address Maintenance</legend>

								<table width="100%">
									<tr>
										<td class="wmtHeader">
											Street Address<br/>
											<input type="text" class="wmtFullInput" id="register_street" name="register_street" style="width:98%" require value="<?php echo $pat_data->street ?>" />
										</td>
										<td  class="wmtHeader" style="width:20%;min-width:180px">
											Status<br/>
											<select id="register_state"  class="wmtSelect" require name="register_state" style="width:95%">
												<?php $temp_options->showOptions($pat_data->addr_status,'--select--'); ?>
											</select>
										</td>
										<td class="wmtHeader">
											County of Residence<br/>
											<select id="register_county"  class="wmtSelect" require name="register_county">
												<?php $county_options->showOptions($pat_data->county,'--select--'); ?>
											</select>
										</td>
									</tr>
									<tr>
										<td class="wmtHeader" style="width:60%">
											City<br/>
											<input type="text" class="wmtFullInput" id="register_city" name="register_city" style="width:98%" require value="<?php echo $pat_data->city ?>" />
										</td>
										<td  class="wmtHeader" style="width:20%;min-width:180px">
											State<br/>
											<select id="register_state"  class="wmtSelect" require name="register_state" style="width:95%">
												<?php $state_options->showOptions($pat_data->state,'--select--'); ?>
											</select>
										</td>
										<td class="wmtHeader" style="width:20%;min-width:120px">
											Postal Code<br/>
											<input type="text" class="wmtFullInput" id="register_zip" name="register_zip" style="width:100%" require value="<?php echo $pat_data->postal_code ?>" />
										</td>
									</tr>
								</table>
							</fieldset>
								
						</td>
					</tr>
					<tr>
						<td class="wmtLabel" valign="top" colspan="6">
							Home &amp; Living Situation Comments:
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