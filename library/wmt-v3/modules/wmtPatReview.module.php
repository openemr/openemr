<?php
/** **************************************************************************
 *	wmtPatReview.module.php
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
class PatReviewModule extends BaseModule {
	/**
	 * Display a collapsable section in the form.
	 *
	 */
	public function display($open=false, $bottom=false) {
		$this->toggle = ($toggle)? 'block' : 'none';
		
		// Retrieve list information
		$sex_list = new Options('sex');
		$relation_list = new Options('sub_relationship');
		$result_list = new Options('proc_res_status');
		$state_list = new Options('state');
		
		// Retrieve patient associated with this form
		$pid = ($this->form->pid)? $this->form->pid : $_SESSION['pid'];
		$pat_data = Patient::getPidPatient($pid);
		
		if ($popup) { ?>
		
			<div class="wmtPatient" style="text-align:center;border:solid 1px red;margin:10px 3px 3px 3px;padding:3px 6px;">
				<div style="float:left">
					<span class="wmtLabel">Date:</span>
					<input name="ee1_form_dt" type="text" class="wmtInput InputBordB" disabled="disabled" value="<?php echo $form_data->created ?>" />
				</div>
				<span class="wmtLabel">Patient Name:</span>
				<input name="pname" type="text" class="wmtInput InputBordB" style="width:33%" disabled="disabled" value="<?php echo $patient->format_name; ?>" />
				<div style="float:right">
					<span class="wmtLabel">ID No:</span>
					<input name="pid" type="text" class="wmtInputBorderB" disabled="disabled" value="<?php echo $pat_data->pubpid; ?>">
				</div>
			</div>
			
		<?php } else { ?>

			<div class='wmtMainContainer wmtColorMain'>
				<?php Display::chapter($this->title, $this->key, $open); ?>
				<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
					<table style="width:100%">	
						<tr>
							<!-- Left Side -->
							<td style="width:50%" class="wmtInnerLeft">
								<table style="width:99%">
							        <tr>
										<td style="width:20%">
											<span class='wmtBody4'>Patient First</span>
											<input name="pat_fname" type="text" class="wmtFullInput" readonly value="<?php echo ($completed)?$form_data->pat_fname:$pat_data->fname; ?>">
											<input name="pat_race" type="hidden" value="<?php echo $pat_data->race; ?>">
											<input name="pricelevel" type="hidden" value="<?php echo $pat_data->pricelevel; ?>">
											<input name="pid" type="hidden" value="<?php echo $pat_data->pid; ?>">
											<input name="pubpid" type="hidden" value="<?php echo $pat_data->pubpid; ?>">
										</td>
										<td style="width:10%">
											<span class='wmtBody4'>Middle</span>
											<input name="pat_mname" type="text" class="wmtFullInput" readonly value="<?php echo ($completed)?$form_data->pat_mname:$pat_data->mname; ?>">
										</td>
										<td>
											<span class='wmtBody4'>Last Name</span>
											<input name="pat_lname" type="text" class="wmtFullInput" readonly value="<?php echo ($completed)?$form_data->pat_lname:$pat_data->lname; ?>">
										</td>
										<td style="width:20%">
											<span class='wmtBody4'>Patient Id</span>
											<input name="pat_pubpid" type="text" class="wmtFullInput" readonly value="<?php echo ($completed)?$form_data->pat_pubpid:$pat_data->pubpid; ?>">
										</td>
										<td colspan="2" style="width:20%">
											<span class='wmtBody4'>Social Security</span>
											<input name="pat_ss" type"text" class="wmtFullInput" readonly value="<?php echo ($completed)?$form_data->pat_ss:$pat_data->ss ?>">
										</td>
									</tr>
	
									<tr>
										<td colspan="3">
											<span class='wmtBody4'>Email Address</span>
											<input name="pat_email" type="text" class="wmtFullInput" readonly value="<?php echo ($completed)?$form_data->pat_email:$pat_data->email; ?>"></td>
										<td style="width:20%">
											<span class='wmtBody4'>Birth Date</span>
											<input name="pat_DOB" type="text" class="wmtFullInput" readonly value="<?php echo $pat_data->birth_date; ?>">
										</td>
										<td style="width:5%">
											<span class='wmtBody4'>Age</span>
											<input name="pat_age" type="text" class="wmtFullInput" readonly value="<?php echo $pat_data->age; ?>">
										</td>
										<td style="width:15%">
											<span class='wmtBody4'>Gender</span>
											<input name="pat_sex" type="hidden" value="<?php echo $pat_data->sex ?>" />
											<input type="text" class="wmtFullInput" readonly value="<?php $sex_list->showItem($pat_data->sex); ?>">
										</td>
									</tr>
	
									<tr>
										<td colspan="3">
											<span class='wmtBody4'>Primary Address</span>
											<input name="pat_street" type="text" class="wmtFullInput" readonly value="<?php echo $pat_data->street; ?>">
										</td>
										<td>
											<span class='wmtBody4'>Mobile Phone</span>
											<input name="pat_mobile" id="ex_phone_mobile" type="text" class="wmtFullInput" readonly value="<?php echo $pat_data->phone_cell; ?>"></td>
										<td colspan="2">
											<span class='wmtBody4'>Home Phone</span>
											<input name="pat_phone" type="text" class="wmtFullInput" readonly value="<?php echo $pat_data->phone_home; ?>"></td>
									</tr>
	
									<tr>
										<td colspan="3" style="width:50%">
											<span class='wmtBody4'>City</span>
											<input name="pat_city" type="text" class="wmtFullInput" readonly value="<?php echo $pat_data->city; ?>">
										</td>
										<td>
											<span class='wmtBody4'>State</span>
											<input type="text" class="wmtFullInput" readonly value="<?php $state_list->showItem($pat_data->state); ?>">
											<input type="hidden" name="pat_state" value="<?php echo $pat_data->state ?>" />
										</td>
										<td colspan="2">
											<span class='wmtBody4'>Postal Code</span>
											<input name="pat_zip" type="text" class="wmtFullInput" readonly value="<?php echo $pat_data->postal_code; ?>">
										</td>
									</tr>
								</table>
							</td>
							
							<!-- Right Side -->
							<td style="width:50%" class="wmtInnerRight">
								<table style="width:99%">
									<tr>
										<td style="width:20%">
											<span class='wmtBody4'>Insured First</span>
											<input name="ins_fname" type="text" class="wmtFullInput" readonly value="<?php echo $ins_list[0]->subscriber_fname; ?>">
										</td>
										<td style="width:10%">
											<span class='wmtBody4'>Middle</span>
											<input name="ins_mname" type"text" class="wmtFullInput" readonly value="<?php echo $ins_list[0]->subscriber_mname; ?>">
										</td>
										<td>
											<span class='wmtBody4'>Last Name</span>
											<input name="ins_lname" type"text" class="wmtFullInput" readonly value="<?php echo $ins_list[0]->subscriber_lname; ?>">
										</td>
										<td style="width:20%">
											<span class='wmtBody4'>Birth Date</span>
											<input name="ins_DOB" type="text" class="wmtFullInput" readonly value="<?php echo $ins_list[0]->subscriber_birth_date; ?>">
										</td>
										<td style="width:20%">
											<span class='wmtBody4'>Relationship</span>
											<input name="ins_relation" type="text" class="wmtFullInput" readonly value="<?php $relation_list->showItem($ins_list[0]->subscriber_relationship); ?>">
											<input name="ins_ss" type="hidden" value="<?php echo $ins_list[0]->subscriber_ss ?>" />
											<input name="ins_sex" type="hidden" value="<?php echo $ins_list[0]->subscriber_sex ?>" />
										</td>
									</tr>
									<tr>
										<td colspan="3">
											<span class='wmtBody4'>Primary Insurance</span>
											<input type="text" class="wmtFullInput" readonly value="<?php echo ($ins_list[0]->company_name)?$ins_list[0]->company_name:'No Insurance'; ?>">
											<input id="ins_primary" name="ins_primary" type="hidden" value="<?php echo $ins_list[0]->id ?>"/>
											<input id="ins_primary_lab" name="ins_primary_lab" type="hidden" value="<?php echo $ins_primary_lab ?>"/>
										</td>
										<td>
											<span class='wmtBody4'>Policy #</span>
										<input name="ins_primary_policy" type="text" class="wmtFullInput" readonly value="<?php echo $ins_list[0]->policy_number; ?>"></td>
										<td>
											<span class='wmtBody4'>Group #</span>
											<input name="ins_primary_group" type="text" class="wmtFullInput" readonly value="<?php echo $ins_list[0]->group_number; ?>"></td>
									</tr>
									<tr>
										<td colspan="3">
											<span class='wmtBody4'>Secondary Insurance</span>
											<input type="text" class="wmtFullInput" readonly value="<?php echo $ins_list[1]->company_name; ?>">
											<input id="ins_secondary" name="ins_secondary" type="hidden" value="<?php echo $ins_list[1]->id ?>"/>
											<input id="ins_secondary_lab" name="ins_secondary_lab" type="hidden" value="<?php echo $ins_secondary_lab ?>"/>
										</td>
										<td>
											<span class='wmtBody4'>Policy #</span>
											<input name="ins_secondary_policy" type="text" class="wmtFullInput" readonly value="<?php echo $ins_list[1]->policy_number; ?>"></td>
										<td>
											<span class='wmtBody4'>Group #</span>
											<input name="ins_secondary_group" type="text" class="wmtFullInput" readonly value="<?php echo $ins_list[1]->group_number; ?>"></td>
									</tr>
									<tr>
										<td style="width:20%">
											<span class='wmtBody4'>Guarantor First</span>
											<input name="guarantor_fname" type="text" class="wmtFullInput" readonly value="<?php echo ($ins_list[0]->subscriber_lname)?$ins_list[0]->subscriber_fname:$pat_data->fname; ?>">
											<input name="guarantor_phone" type="hidden" value="<?php echo ($ins_list[0]->subscriber_phone)?$ins_list[0]->subscriber_phone:$pat_data->phone_home ?>" />
											<input name="guarantor_street" type="hidden" value="<?php echo ($ins_list[0]->subscriber_street)?$ins_list[0]->subscriber_street:$pat_data->street ?>" />
											<input name="guarantor_city" type="hidden" value="<?php echo ($ins_list[0]->subscriber_street)?$ins_list[0]->subscriber_city:$pat_data->city ?>" />
											<input name="guarantor_state" type="hidden" value="<?php echo ($ins_list[0]->subscriber_street)?$ins_list[0]->subscriber_state:$pat_data->state ?>" />
											<input name="guarantor_zip" type="hidden" value="<?php echo ($ins_list[0]->subscriber_street)?$ins_list[0]->subscriber_postal_code:$pat_data->postal_code ?>" />
										</td>
										<td style="width:10%">
											<span class='wmtBody4'>Middle</span>
											<input name="guarantor_mname" type="text" class="wmtFullInput" readonly value="<?php echo ($ins_list[0]->subscriber_lname)?$ins_list[0]->subscriber_mname:$pat_data->mname; ?>">
										</td>
										<td style="width:20%">
											<span class='wmtBody4'>Last Name</span>
											<input name="guarantor_lname" type="text" class="wmtFullInput" readonly value="<?php echo ($ins_list[0]->subscriber_lname)?$ins_list[0]->subscriber_lname:$pat_data->lname; ?>">
										</td>
										<td>
											<span class='wmtBody4'>SS#</span>
											<input name="guarantor_ss" type="text" class="wmtFullInput" readonly value="<?php echo ($ins_list[0]->subscriber_ss)?$ins_list[0]->subscriber_ss:$pat_data->ss; ?>"></td>
										<td>
											<span class='wmtBody4'>Relationship</span>
											<input name="guarantor_relation" type="text" class="wmtFullInput" readonly value="<?php $relation_list->showItem($ins_list[0]->subscriber_relationship,'Self'); ?>">
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
				<?php Display::bottom($this->title, $this->key, $open, $bottom); ?>
			</div>
		<?php } 
	}
	
	/**
	 * Print a collapsable section in the report.
	 *
	 */
	public function report() {
		// DO NOT REPORT
	}
	
}
?>