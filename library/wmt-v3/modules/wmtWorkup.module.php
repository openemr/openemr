<?php
/** ******************************************************************************************
 *	wmtWorkup.module.php
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
class WorkupModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtWorkupModule::No module key provided for construct.');
	
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
		$wrap_mode = ($this->form_data->vital_id) ? 'update' : 'new'; 
		if ($this->readonly) $wrap_mode = 'readonly';
		$vitals = &$this->form_data->vitals; ?>

		<script>
			// Get vitals popup
			function get_vitals(pid) {
				wmtOpen('../../../custom/vital_choice_popup.php?pid='+pid, '_blank', 500, 400);
			}

			// To clear vitals
			function clear_vitals() {
				$('#vital_id').val('');
				$('#vital_height').val('');
				$('#vital_weight').val('');
				$('#vital_bps').val('');
				$('#vital_bpd').val('');
				$('#vital_pulse').val('');
				$('#vital_BMI').val('');
				$('#vital_BMI_status').val('');
				$('#vital_date').val('');
				$('#vital_arm').val('');
				$('#vital_prone_bps').val('');
				$('#vital_prone_bpd').val('');
				$('#vital_standing_bps').val('');
				$('#vital_standing_bpd').val('');
				$('#vital_diabetes_accucheck').val('');
				$('#vital_oxygen_saturation').val('');
				$('#vital_respiration').val('');
				$('#vital_temperature').val('');
				$('#vital_note').val('');

				// enable fields
				$('#vital_height').attr('readonly',false);
				$('#vital_weight').attr('readonly',false)
				$('#vital_bps').attr('readonly',false)
				$('#vital_bpd').attr('readonly',false)
				$('#vital_pulse').attr('readonly',false)
				$('#vital_BMI').attr('readonly',false)
				$('#vital_arm').attr('readonly',false)
				$('#vital_prone_bps').attr('readonly',false)
				$('#vital_prone_bpd').attr('readonly',false)
				$('#vital_standing_bps').attr('readonly',false)
				$('#vital_standing_bpd').attr('readonly',false)
				$('#vital_diabetes_accucheck').attr('readonly',false)
				$('#vital_oxygen_saturation').attr('readonly',false)
				$('#vital_respiration').attr('readonly',false)
				$('#vital_temperature').attr('readonly',false)
				$('#vital_note').attr('readonly',false)
			}

			// This is for callback by the vitals popup.
			function set_vitals(Vals) {
				var test = Vals.length;
				var vid = Vals[0];
				if (vid) {
					$('#vital_id').val(vid);
					$('#vital_height').val(Vals[1]);
					$('#vital_weight').val(Vals[2]);
					$('#vital_bps').val(Vals[3]);
					$('#vital_bpd').val(Vals[4]);
					$('#vital_pulse').val(Vals[5]);
					$('#vital_BMI').val(Vals[6]);
					$('#vital_BMI_status').val(Vals[7]);
					$('#vital_date').val(Vals[8]);
						if (Vals.length > 9) $('#vital_arm').val(Vals[9]);
						if (Vals.length > 10) $('#vital_prone_bps').val(Vals[10]);
						if (Vals.length > 11) $('#vital_prone_bpd').val(Vals[11]);
						if (Vals.length > 12) $('#vital_standing_bps').val(Vals[12]);
						if (Vals.length > 13) $('#vital_standing_bpd').val(Vals[13]);
						if (Vals.length > 14) $('#vital_diabetes_accucheck').val(Vals[14]);
						if (Vals.length > 15) $('#vital_oxygen_saturation').val(Vals[15]);
						if (Vals.length > 16) $('#vital_respiration').val(Vals[16]);
						if (Vals.length > 17) $('#vital_temperature').val(Vals[17]);
						if (Vals.length > 20) $('#vital_note').val(Vals[20]);

					// disable fields
					$('#vital_height').attr('readonly',true);
					$('#vital_weight').attr('readonly',true)
					$('#vital_bps').attr('readonly',true)
					$('#vital_bpd').attr('readonly',true)
					$('#vital_pulse').attr('readonly',true)
					$('#vital_BMI').attr('readonly',true)
					$('#vital_BMI_status').attr('readonly',true)
					$('#vital_date').attr('readonly',true)
					$('#vital_arm').attr('readonly',true)
					$('#vital_prone_bps').attr('readonly',true)
					$('#vital_prone_bpd').attr('readonly',true)
					$('#vital_standing_bps').attr('readonly',true)
					$('#vital_standing_bpd').attr('readonly',true)
					$('#vital_diabetes_accucheck').attr('readonly',true)
					$('#vital_oxygen_saturation').attr('readonly',true)
					$('#vital_respiration').attr('readonly',true)
					$('#vital_temperature').attr('readonly',true)
					$('#vital_note').attr('readonly',true)

				}
			}

		</script>
		
		<div class='wmtMainContainer wmtColorMain'>
			<?php Display::chapter($this->title, $this->key, $open); ?>
			<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
				<table style="width:100%">
					<tr>
						<td class="wmtLabel" colspan="2">
							<span style="display:inline-block;width:150px;color:red;font-weight:bold">Critical Patient Needs:</span>
							<input class="wmtCheck"  style="margin-left:20px" type="checkbox" name="critical_none" id="critical_none" value="1"  <?php if ($this->readonly) echo "disabled" ?> <?php if ($this->form_data->critical_none) echo 'checked' ?> /><label class="wmtCheck">None</label>
							<textarea name="critical_notes" id="critical_notes" class="wmtFullInput" rows="2" <?php if ($this->readonly) echo "disabled" ?>><?php echo $this->form_data->critical_notes; ?></textarea>
						</td>
					</tr>
				</table>
			
				<hr style='border:1px dashed;margin-top:20px'/>
				
<?php /*
				$use_abnormal = false;
				if (file_exists ( $GLOBALS ['incdir'] . '/forms/vitals/abnormal_vitals.inc' )) {
					require_once ($GLOBALS ['incdir'] . '/forms/vitals/abnormal_vitals.inc');
					include_once ($GLOBALS ['srcdir'] . '/patient.inc');
					$use_abnormal = true;
				}
				if (checkSettingMode ( 'wmt::supress_abnormal_vitals', '', $frmdir ))
					$use_abnormal = false;
				$suppress_decimal = checkSettingMode ( 'wmt::suppress_vital_decimal' );
				$hr_accent = $rsp_accent = $temp_accent = $bp_accent = $accent = '';
				if ($use_abnormal) {
					if (strpos ( $patient->age, ' ' ) !== false) {
						list ( $num, $frame ) = split ( ' ', $patient->age );
					} else {
						$num = $patient->age;
						$frame = 'year';
					}
					$hr_accent = isAbnormalPulse ( $num, $frame, $vitals->pulse );
					$rsp_accent = isAbnormalRespiration ( $num, $frame, $vitals->respiration );
					$temp_accent = isAbnormalTemperature ( $num, $frame, $vitals->temperature );
					$bp_accent = isAbnormalBps ( $num, $frame, $vitals->bps );
					if (! $bp_accent)
						$bp_accent = isAbnormalBpd ( $num, $frame, $vitals->bpd );
					if ($hr_accent || $rsp_accent || $temp_accent || $bp_accent)
						$accent = 'style="color: red; "';
				}
*/ ?>
				<table style="margin:6px;width:100%">
					<tr>
						<td class="wmtLabel">Vitals Taken:</td>
						<td colspan="4">
							<input name="vital_id" id="vital_id" type="hidden" 
								value="<?php echo htmlspecialchars($vitals->id, ENT_QUOTES, '', FALSE); ?>" />
							<input name="vital_date" id="vital_date" type="text"
								class="wmtLabel <?php echo $accent ? 'wmtRed' : ''; ?>"
								<?php echo ($this->readonly)? "disabled" : "readonly" ?> value="<?php echo $vitals->date ?>" />
						</td>
						<td colspan="9" style="text-align:right">
<?php if (!$this->readonly) { ?>
							<a class="css_button" tabindex="-1"
								onClick="clear_vitals('<?php echo $pid; ?>');" href="javascript:;"><span>Clear Vitals</span></a>
							<a class="css_button" tabindex="-1"
								onClick="get_vitals('<?php echo $pid; ?>');" href="javascript:;"><span>Retrieve Vitals</span></a>
<?php } ?>
						</td>
					</tr>
					<tr>
						<td class="wmtBody">Height:</td>
						<td>
							<input name="vital_height" id="vital_height" class="wmtInput"
								style="width: 60px" type="text" <?php if ($this->readonly) echo "disabled" ?>
								<?php echo ($wrap_mode != 'new') ? 'readonly ' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->height, ENT_QUOTES, '', FALSE); ?>"
								onchange="UpdateBMI('vital_height', 'vital_weight', 'vital_BMI', 'vital_BMI_status'); TimeStamp('vital_date');" />
						</td>
						<td class="wmtBody">Weight:</td>
						<td>
							<input name="vital_weight" id="vital_weight" class="wmtInput"
								style="width: 60px" type="text" <?php if ($this->readonly) echo "disabled" ?>
								<?php echo ($wrap_mode != 'new') ? 'readonly ' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->weight, ENT_QUOTES, '', FALSE); ?>"
								onchange="UpdateBMI('vital_height', 'vital_weight', 'vital_BMI', 'vital_BMI_status'); TimeStamp('vital_date');" />
						</td>
						<td class="wmtBody">BMI:</td>
						<td colspan="3">
							<input name="vital_BMI" id="vital_BMI" class="wmtInput"
								style="width: 60px" type="text" <?php if ($this->readonly) echo "disabled" ?>
								<?php echo ($wrap_mode != 'new') ? 'readonly ' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->BMI, ENT_QUOTES, '', FALSE); ?>"
								onchange="OneDecimal('bmi'); TimeStamp('vital_date');" />
							<input name="vital_BMI_status" id="vital_BMI_status"
								class="wmtInput" type="text" <?php echo ($this->readonly)? "disabled" : "readonly" ?> 
								value="<?php echo htmlspecialchars($vitals->BMI_status, ENT_QUOTES, '', FALSE); ?>" />
						</td>
						<td class="wmtBody">Pulse:</td>
						<td>
							<input name="vital_pulse" id="vital_pulse"
								class="wmtInput <?php echo $hr_accent ? 'wmtRed' : ''; ?>"
								style="width: 50px" type="text" <?php if ($this->readonly) echo "disabled" ?>
								<?php echo ($wrap_mode != 'new') ? 'readonly' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->pulse, ENT_QUOTES, '', FALSE); ?>"
								onchange="TimeStamp('vital_date');" />
						</td>
						<td class="wmtBody">Resp:</td>
						<td>
							<input name="vital_respiration" id="vital_respiration"
								class="wmtInput <?php echo $rsp_accent ? 'wmtRed' : ''; ?>"
								style="width: 60px" type="text" <?php if ($this->readonly) echo "disabled" ?>
								<?php echo ($wrap_mode != 'new') ? 'readonly ' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->respiration, ENT_QUOTES, '', FALSE); ?>"
								onchange="TimeStamp('vital_date');" />
						</td>
						<td class="wmtBody">Temp:</td>
						<td>
							<input name="vital_temperature" id="vital_temperature"
								class="wmtInput <?php echo $temp_accent ? 'wmtRed' : ''; ?>"
								style="width: 60px" type="text" <?php if ($this->readonly) echo "disabled" ?>
								<?php echo ($wrap_mode != 'new') ? 'readonly' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->temperature, ENT_QUOTES, '', FALSE); ?>"
								onchange="TimeStamp('vital_date');" />
						</td>
						<td style="width: 1%">&nbsp;</td>
					</tr>
					<tr>
						<td class="wmtBody">Seated BP:</td>
						<td>
							<input name="vital_bps" id="vital_bps"
								class="wmtInput <?php echo $bp_accent ? 'wmtRed' : ''; ?>"
								style="width: 30px" type="text" <?php if ($this->readonly) echo "disabled" ?>
								<?php echo ($wrap_mode != 'new') ? 'readonly' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->bps, ENT_QUOTES, '', FALSE); ?>"
								onchange="TimeStamp('vital_date');" />/<input name="vital_bpd"
								id="vital_bpd"
								class="wmtInput <?php echo $bp_accent ? 'wmtRed' : ''; ?>"
								style="width: 30px" type="text" <?php if ($this->readonly) echo "disabled" ?>
								<?php echo ($wrap_mode != 'new') ? 'readonly' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->bpd, ENT_QUOTES, '', FALSE); ?>"
								onchange="TimeStamp('vital_date');" />
						</td>
						<td class='wmtBody'>Prone BP:</td>
						<td>
							<input name="vital_prone_bps" id="vital_prone_bps"
								class="wmtInput" style="width: 30px" type="text" <?php if ($this->readonly) echo "disabled" ?>
								<?php echo ($wrap_mode != 'new') ? 'readonly' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->prone_bps, ENT_QUOTES, '', FALSE); ?>"
								onchange="TimeStamp('vital_timestamp');" />/<input
								name="vital_prone_bpd" id="vital_prone_bpd" class="wmtInput"
								style="width: 30px" type="text" <?php if ($this->readonly) echo "disabled" ?>
								<?php echo ($wrap_mode != 'new') ? 'readonly' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->prone_bpd, ENT_QUOTES, '', FALSE); ?>"
								onchange="TimeStamp('vital_date');" />
						</td>
						<td class="wmtBody">Standing BP:</td>
						<td>
							<input name="vital_standing_bps" id="vital_standing_bps"
								class="wmtInput" style="width: 30px" type="text" <?php if ($this->readonly) echo "disabled" ?>
								<?php echo ($wrap_mode != 'new') ? 'readonly' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->standing_bps, ENT_QUOTES, '', FALSE); ?>"
								onchange="TimeStamp('vital_date');" />/<input <?php if ($this->readonly) echo "disabled" ?>
								name="vital_standing_bpd" id="vital_standing_bpd" class="wmtInput"
								style="width: 30px" type="text"
								<?php echo ($wrap_mode != 'new') ? 'readonly' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->standing_bpd, ENT_QUOTES, '', FALSE); ?>"
								onchange="TimeStamp('vital_date');" />
						</td>
						<td class="wmtBody">Arm:</td>
						<td>
							<input name="vital_arm" id="vital_arm" class="wmtInput" <?php if ($this->readonly) echo "disabled" ?>
								style="width: 60px" <?php echo ($wrap_mode != 'new') ? 'readonly' : ''; ?>
								value="<?php echo ListLook($vitals->arm,'Vital_Arm'); ?>" />
						</td>
						<td class="wmtBody">O<sub>2</sub> Sat:</td>
						<td>
							<input name="vital_oxygen_saturation" <?php if ($this->readonly) echo "disabled" ?>
								id="vital_oxygen_saturation" class="wmtInput" style="width: 60px"
								type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->oxygen_saturation, ENT_QUOTES, '', FALSE); ?>"
								onchange="TimeStamp('vital_date');" />
						</td>
						<td class="wmtBody">Finger Stick:</td>
						<td>
							<input name="vital_diabetes_accucheck"
								id="vital_diabetes_accucheck" class="wmtFullInput"
								type="text" style="width: 60px" <?php if ($this->readonly) echo "disabled" ?>
								<?php echo ($wrap_mode != 'new') ? 'readonly' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->diabetes_accucheck, ENT_QUOTES, '', FALSE); ?>"
								onchange="TimeStamp('vital_date');" />
						</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td class="wmtBody">Vitals Note:&nbsp;</td>
						<td colspan="13">
							<input name="vital_note" id="vital_note" <?php if ($this->readonly) echo "disabled" ?>
								class="wmtFullInput" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : ''; ?>
								value="<?php echo htmlspecialchars($vitals->note, ENT_QUOTES, '', FALSE); ?>" />
						</td>
					</tr>
				</table>

				<hr style='border:1px dashed;margin-top:20px'/>

				<table style="width:100%">
					<tr>
						<td class="wmtLabel" colspan="2">
							<span style="display:inline-block;width:150px">Medications:</span>
							<input class="wmtCheck" style="margin-left:20px" type="checkbox" name="meds_none" id="meds_none" value="1" <?php if ($this->form_data->meds_none) echo 'checked' ?>  <?php if ($this->readonly) echo "disabled" ?>/><label class="wmtCheck">None</label>
							<textarea name="meds_notes" id="meds_notes" class="wmtFullInput" rows="2" <?php if ($this->readonly) echo "disabled" ?>><?php echo $this->form_data->meds_notes; ?></textarea>
						</td>
					</tr>
				</table>

				<hr style='border:1px dashed;margin-top:20px'/>

				<table style="width:100%">
					<tr>
						<td class="wmtLabel" colspan="2">
							<span style="display:inline-block;width:150px">Allergies:</span>
							<input class="wmtCheck"  style="margin-left:20px" type="checkbox" name="allergy_none" id="allergy_none" value="1"  <?php if ($this->readonly) echo "disabled" ?> <?php if ($this->form_data->allergy_none) echo 'checked' ?> /><label class="wmtCheck">None</label>
							<textarea name="allergy_notes" id="allergy_notes" class="wmtFullInput" rows="2" <?php if ($this->readonly) echo "disabled" ?>><?php echo $this->form_data->allergy_notes; ?></textarea>
						</td>
					</tr>
				</table>

				<hr style='border:1px dashed;margin-top:20px'/>

				<table style="width:100%">
					<tr>
						<td class="wmtLabel" colspan="2">
							<span style="display:inline-block;width:150px">Surgeries:</span>
							<input class="wmtCheck"  style="margin-left:20px" type="checkbox" name="surgery_none" id="surgery_none" value="1" <?php if ($this->readonly) echo "disabled" ?> <?php if ($this->form_data->surgery_none) echo 'checked' ?> /><label class="wmtCheck">None</label>
							<textarea name="surgery_notes" id="surgery_notes" class="wmtFullInput" rows="2" <?php if ($this->readonly) echo "disabled" ?>><?php echo $this->form_data->surgery_notes; ?></textarea>
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
		if ($this->form_data->vital_id) $output = true;
		else if ($this->form_data->critical_none) $output = true;
		else if ($this->form_data->critical_notes) $output = true;
		else if ($this->form_data->meds_none) $output = true;
		else if ($this->form_data->meds_notes) $output = true;
		else if ($this->form_data->allergy_none) $output = true;
		else if ($this->form_data->allergy_notes) $output = true;
		else if ($this->form_data->surgery_none) $output = true;
		else if ($this->form_data->surgery_notes) $output = true;
		
		if (!$output) return; 
		$vitals = $this->form_data->vitals; ?>
		
		<div class='wmtPrnMainContainer'>
			<div class='wmtPrnCollapseBar'>
				<span class='wmtPrnChapter'><?php echo $this->title ?></span>
			</div>
			<div class='wmtPrnCollapseBox'>
				<table class='wmtPrnContent'>
					
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
if ($this->form_data->surgery_none) { 
			echo "<i style='margin-left:10px' class='fa fa-fw fa-check'></i>";
			echo 'NONE';
} ?>
							</span>
						</td>
					</tr><tr>
						<td style="width:25px"></td><td style='line-height:14px'>
							<span class='wmtPrnBody'><?php echo $this->form_data->surgery_notes . "\n"; ?></span>
						</td>
					</tr>
				</table>

				<hr style='border:1px dashed;margin-top:20px'/>

				<table style="width:100%">
					<tr>
						<td class="wmtPrnLabel" style="white-space:nowrap">Vitals Taken:</td>
						<td colspan="4">
							<span class='wmtPrnBody'><?php echo $vitals->date  . "\n"; ?></span>
						</td>
						<td colspan="9">&nbsp;</td>
					</tr>
					<tr>
						<td class="wmtPrnLabel" style="white-space:nowrap;width:7%">Height:</td>
						<td style="text-align:left;white-space:nowrap">
							<span class='wmtPrnBody' style="text-align:left;min-width:150px"><?php echo $vitals->height  . "\n"; ?></span>
						</td>
						<td class="wmtPrnLabel" style="white-space:nowrap;width:7%">Weight:</td>
						<td style="text-align:left;white-space:nowrap">
							<span class='wmtPrnBody'><?php echo $vitals->weight  . "\n"; ?></span>
						</td>
						<td class="wmtPrnLabel" style="white-space:nowrap;width:7%">BMI:</td>
						<td colspan="3" style="text-align:left">
							<span class='wmtPrnBody'><?php echo $vitals->BMI  . "\n"; ?></span>
						</td>
						<td class="wmtPrnLabel" style="white-space:nowrap;width:7%">Pulse:</td>
						<td style="text-align:left;white-space:nowrap">
							<span class='wmtPrnBody'><?php echo $vitals->pulse  . "\n"; ?></span>
						</td>
						<td class="wmtPrnLabel" style="white-space:nowrap;width:7%">Resp:</td>
						<td style="text-align:left;white-space:nowrap">
							<span class='wmtPrnBody'><?php echo $vitals->respiration  . "\n"; ?></span>
						</td>
						<td class="wmtPrnLabel" style="white-space:nowrap;width:7%">Temp:</td>
						<td style="text-align:left;white-space:nowrap">
							<span class='wmtPrnBody'><?php echo $vitals->temperature  . "\n"; ?></span>
						</td>
					</tr>
					<tr>
						<td class="wmtPrnLabel" style="white-space:nowrap;width:7%">Seated BP:</td>
						<td style="text-align:left;white-space:nowrap">
							<span class='wmtPrnBody'><?php echo $vitals->bps ."/". $vitals->bpd . "\n"; ?></span>
						</td>
						<td class='wmtPrnLabel' style="white-space:nowrap;width:7%">Prone BP:</td>
						<td style="text-align:left;white-space:nowrap">
							<span class='wmtPrnBody'><?php echo $vitals->prone_bps ."/". $vitals->prone_bpd . "\n"; ?></span>
						</td>
						<td class="wmtPrnLabel" style="white-space:nowrap;width:7%">Standing BP:</td>
						<td style="text-align:left;white-space:nowrap">
							<span class='wmtPrnBody'><?php echo $vitals->standing_bps ."/". $vitals->standing_bpd . "\n"; ?></span>
						</td>
						<td class="wmtPrnLabel" style="white-space:nowrap;width:7%">Arm:</td>
						<td  style="text-align:left;white-space:nowrap">
							<span class='wmtPrnBody'><?php echo $vitals->arm . "\n"; ?></span>
						</td>
						<td class="wmtPrnLabel" style="white-space:nowrap;width:7%">O<sub>2</sub> Sat:</td>
						<td style="text-align:left;white-space:nowrap">
							<span class='wmtPrnBody'><?php echo $vitals->oxygen_saturation . "\n"; ?></span>
						</td>
						<td class="wmtPrnLabel" style="white-space:nowrap;width:7%">Finger Stick:</td>
						<td style="text-align:left;white-space:nowrap">
							<span class='wmtPrnBody'><?php echo $vitals->diabetes_accucheck . "\n"; ?></span>
						</td>
					</tr>
					<tr>
						<td class="wmtPrnLabel" style="white-space:nowrap">Vitals Note:&nbsp;</td>
						<td colspan="12">
							<span class='wmtPrnBody'><?php echo $vitals->note . "\n"; ?></span>
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

		$vital_id = strip_tags($dt['vital_id']);
		if (empty($vital_id) && $dt['vital_date']) {
			$vitals = new Vitals();
			foreach ($dt AS $key => $value) {
				if (strpos($key, 'vital_') !== false) {
					$key = str_ireplace('vital_', '', $key);
					$vitals->{$key} = $value;
				}
			}
			$vitals->pid = $this->form_data->pid;
			$vital_id = $vitals->store();
		}

		// Push data to form
		$this->form_data->vital_id = $vital_id;
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