<?php
/** **************************************************************************
 *	wmtSoapWork.module.php
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
class SoapWorkModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtSoapWorkModule::No module key provided for construct.');
	
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

		</script>
		
		<div class='wmtMainContainer wmtColorMain'>
			<?php Display::chapter($this->title, $this->key, $open); ?>
			<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
				<table style="width:100%">
					<tr>
						<td class="wmtLabel" colspan="2">
							<span style="display:inline-block;width:200px;color:red;font-weight:bold">Critical Patient Needs:</span>
							<input class="wmtCheck"  style="margin-left:20px" type="checkbox" name="critical_none" disabled id="critical_none" value="1" <?php if ($this->form_data->critical_none) echo 'checked' ?> /><label class="wmtCheck">None</label>
							<textarea name="critical_notes" id="critical_notes" class="wmtFullInput" rows="2" disabled><?php echo $this->form_data->critical_notes; ?></textarea>
						</td>
					</tr>
				</table>
			
				<hr style='border:1px dashed'/>

				<table style="width:100%">
					<tr>
						<td class="wmtLabel">
							Vital Signs:
							<input type='hidden' id='form_vid' value='' />
						</td>
						<td class='wmtLabelRed' colspan='2'>Vitals Taken: <input name='form_vital_timestamp' id='form_vital_timestamp' type='text' class='wmtLabelRed' disabled style='font-size:12px' tabstop='-1' value="<?php echo $dt['form_vital_timestamp']; ?>" /></td>
					</tr>
					<tr><td colspan="11" style="margin: 8px;"></td></tr>
					<tr>
						<td class="wmtBodyR" style="width:130px">Height: 
							<input name="form_vital_ht" id="form_vital_ht" class="wmtInput" type="text" style="width: 50px" value="<?php echo $dt{'form_vital_ht'}; ?>" disabled /></td>
						<td class="wmtBodyR" style="width:130px">Weight:
							<input name="form_vital_wt" id="form_vital_wt" class="wmtInput" type="text" style="width: 50px" disabled value="<?php echo $dt{'form_vital_wt'}; ?>" /></td>
						<td class="wmtBodyR" style="width:160px">BP:
							<input name="form_vital_bps" id="form_vital_bps" class="wmtInput" type="text" style="width: 50px" disabled value="<?php echo $dt{'form_vital_bps'}; ?>" />&nbsp;/&nbsp;<input name="form_vital_bpd" id="form_vital_bpd" class="wmtInput" type="text" style="width: 50px" <?php echo (($wrap_mode != 'new')?' disabled ':''); ?> value="<?php echo $dt{'form_vital_bpd'}; ?>" /></td>
						<td class="wmtBodyR" style="width:130px">Pulse:
							<input name="form_vital_hr" id="form_vital_hr" class="wmtInput" type="text" style="width: 50px" disabled value="<?php echo $dt{'form_vital_hr'}; ?>" onchange="NoDecimal('form_vital_hr')" /></td>
						<td class="wmtBodyR" style="width:130px">Resp:
							<input name="form_vital_resp" id="form_vital_resp" class="wmtInput" style="width: 50px" type="text" disabled value="<?php echo $dt{'form_vital_resp'}; ?>" onchange="NoDecimal('form_vital_resp')" /></td>
						<td class="wmtBodyR" style="width:130px">Temp:
							<input name="form_vital_temp" id="form_vital_temp" class="wmtInput" style="width: 50px" type="text" disabled value="<?php echo $dt{'form_vital_temp'}; ?>" onchange="OneDecimal('form_vital_temp')" /></td>
						<td class="wmtBodyR" style="width:130px">POx:
							<input name="form_vital_pox" id="form_vital_pox" class="wmtInput" style="width: 50px" disabled value="<?php echo $dt{'form_vital_temp'}; ?>" onchange="OneDecimal('form_vital_pox')" /></td>
						<td>
					</tr>
				</table>

				<hr style='border:1px dashed'/>

				<table style="width:100%">
					<tr>
						<td class="wmtLabel" colspan="2">
							<span style="display:inline-block;width:150px">Medications:</span>
							<input class="wmtCheck" style="margin-left:20px" type="checkbox" name="meds_none" id="meds_none" disabled value="1" <?php if ($this->form_data->meds_none) echo 'checked' ?> /><label class="wmtCheck">None</label>
							<textarea name="meds_notes" id="meds_notes" class="wmtFullInput" rows="2" disabled ><?php echo $this->form_data->meds_notes; ?></textarea>
						</td>
					</tr>
				</table>

				<hr style='border:1px dashed'/>

				<table style="width:100%">
					<tr>
						<td class="wmtLabel" colspan="2">
							<span style="display:inline-block;width:150px">Allergies:</span>
							<input class="wmtCheck"  style="margin-left:20px" type="checkbox" name="allergy_none" id="allergy_none" value="1" disabled <?php if ($this->form_data->allergy_none) echo 'checked' ?> /><label class="wmtCheck">None</label>
							<textarea name="allergy_notes" id="allergy_notes" class="wmtFullInput" rows="2" disabled ><?php echo $this->form_data->allergy_notes; ?></textarea>
						</td>
					</tr>
				</table>

				<hr style='border:1px dashed'/>

				<table style="width:100%">
					<tr>
						<td class="wmtLabel" colspan="2">
							<span style="display:inline-block;width:150px">Surgeries:</span>
							<input class="wmtCheck"  style="margin-left:20px" type="checkbox" disabled name="surgery_none" id="surgery_none" value="1" <?php if ($this->form_data->surgery_none) echo 'checked' ?> /><label class="wmtCheck">None</label>
							<textarea name="surgery_notes" id="surgery_notes" class="wmtFullInput" disabled rows="2"><?php echo $this->form_data->surgery_notes; ?></textarea>
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
		if ($this->form_data->critical_none) $output = true;
		else if ($this->form_data->critical_notes) $output = true;
		else if ($this->form_data->meds_none) $output = true;
		else if ($this->form_data->meds_notes) $output = true;
		else if ($this->form_data->allergy_none) $output = true;
		else if ($this->form_data->allergy_notes) $output = true;
		else if ($this->form_data->surgery_none) $output = true;
		else if ($this->form_data->surgery_notes) $output = true;
		
		if (!$output) return; ?>
		
		<div class='wmtPrnMainContainer'>
			<div class='wmtPrnCollapseBar'>
				<span class='wmtPrnChapter'><?php echo $this->title ?></span>
			</div>
			<div class='wmtPrnCollapseBox'>
				<table class='wmtPrnContent' style="margin:6px">
					
					<tr>
						<td style='line-height:14px' colspan="2">
							<span class='wmtPrnLabel'>Critical Patient Needs:<?php 
if ($this->form_data->critical_none) { 
			echo "<i style='margin-left:10px' class='fa fa-fw fa-check'></i>";
			echo 'NONE';
} ?>
							</span>
						</td>
					</tr><tr>
						<td style="width:25px"></td><td style='line-height:14px'>
							<span class='wmtPrnBody'><?php echo $this->form_data->critical_notes . "\n"; ?></span>
						</td>
					</tr>
			
					<tr>
						<td style='line-height:14px;padding-top:10px' colspan="2">
							<span class='wmtPrnLabel'>Medications:<?php 
if ($this->form_data->meds_none) { 
			echo "<i style='margin-left:10px' class='fa fa-fw fa-check'></i>";
			echo 'NONE';
} ?>
							</span>
						</td>
					</tr><tr>
						<td style="width:25px"></td><td style='line-height:14px'>
							<span class='wmtPrnBody'><?php echo $this->form_data->meds_notes . "\n"; ?></span>
						</td>
					</tr>
					
					<tr>
						<td style='line-height:14px;padding-top:10px' colspan="2">
							<span class='wmtPrnLabel'>Allergies:<?php 
if ($this->form_data->allergy_none) { 
			echo "<i style='margin-left:10px' class='fa fa-fw fa-check'></i>";
			echo 'NONE';
} ?>
							</span>
						</td>
					</tr><tr>
						<td style="width:25px"></td><td style='line-height:14px'>
							<span class='wmtPrnBody'><?php echo $this->form_data->allergy_notes . "\n"; ?></span>
						</td>
					</tr>
					
					<tr>
						<td style='line-height:14px;padding-top:10px' colspan="2">
							<span class='wmtPrnLabel'>Surgeries:<?php 
if ($this->form_data->surgeries_none) { 
			echo "<i style='margin-left:10px' class='fa fa-fw fa-check'></i>";
			echo 'NONE';
} ?>
							</span>
						</td>
					</tr><tr>
						<td style="width:25px"></td><td style='line-height:14px'>
							<span class='wmtPrnBody'><?php echo $this->form_data->surgeries_notes . "\n"; ?></span>
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
		$this->form_data->critical_none = strip_tags($dt['critical_none']);
		$this->form_data->critical_notes = strip_tags($dt['critical_notes']);
		$this->form_data->meds_none = strip_tags($dt['meds_none']);
		$this->form_data->meds_notes = strip_tags($dt['meds_notes']);
		$this->form_data->allergy_none = strip_tags($dt['allergy_none']);
		$this->form_data->allergy_notes = strip_tags($dt['allergy_notes']);
		$this->form_data->surgery_none = strip_tags($dt['surgery_none']);
		$this->form_data->surgery_notes = strip_tags($dt['surgery_notes']);
		
		return;
	}
		
}
?>
