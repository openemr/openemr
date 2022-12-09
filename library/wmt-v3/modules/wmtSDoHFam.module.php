<?php
use wmt\Options;
/** **************************************************************************
 *	wmtSDoHFam.module.php
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
class SDoHFamModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtSDoHFamModule::No module key provided for construct.');
	
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
											Place of Birth:
										</td>
										<td class="wmtLabel" style="width:250px">
											<input name="hx_birthplace" type="text" class="wmtFullInput" style="width:200px" value="<?php echo $hx_data->birthplace; ?>" />
										</td>
										<td class="wmtLabel" style="width:120px">
											Adopted:
										</td>
										<td class="wmtRadio">
											<input name="hx_adopted_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->adopted_flag)? ' checked':''); ?> value="0" />No
											<input name="hx_adopted_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->adopted_flag)? ' checked':''); ?> value="1" />Yes
										</td>
									</tr>
									
									<tr>
										<td class="wmtLabel">
											Siblings:
										</td>
										<td class="wmtLabel">
											<input name="hx_brothers" type="text" class="wmtInput" style="width:20px" value="<?php echo $hx_data->brothers; ?>" />
											<span style="margin-right:10px">brothers</span>											
											<input name="hx_sisters" type="text" class="wmtInput" style="width:20px" value="<?php echo $hx_data->sisters; ?>" />
											<span style="margin-right:10px">sisters</span>
										</td>											
										<td class="wmtLabel">
											Birth Order:
										</td>
										<td class="wmtLabel">
											<input name="hx_born_order" type="text" class="wmtInput" style="width:20px" value="<?php echo $hx_data->born_order; ?>" />
											<span style="margin-left:5px;margin-right:5px"> of </span>	
											<input name="hx_siblings" type="text" class="wmtInput" style="width:20px" value="<?php echo $hx_data->siblings; ?>" />
										</td>											
									</tr>
								</table>

								<table width="100%" border="0" cellspacing="0" cellpadding="2" style="margin-top:10px">
									<tr>
										<td class="wmtLabel" style="width:300px">
											Relationship Status of Parents During Childhood:
										</td>
										<td class="wmtLabel">
											<input name="hx_parent_marital" type="text" class="wmtFullInput" value="<?php echo $hx_data->parent_marital; ?>" />
										</td>
									</tr>

									<tr>
										<td class="wmtLabel">
											Current Relationship Status of Parents:
										</td>
										<td class="wmtLabel">
											<input name="hx_current_marital" type="text" class="wmtFullInput" value="<?php echo $hx_data->current_marital; ?>" />
										</td>
									</tr>
								</table>
								
								<fieldset>
									<legend>Family Dynamics & Function</legend>

									<table id="familyTable" width="100%" border="0" cellspacing="0" cellpadding="2">
										<tr>
											<td class="wmtHeader">Name</td>
											<td class="wmtHeader">Relationship</td>
											<td class="wmtHeader">Dynamic</td>
											<td class="wmtHeader">Comments</td>
										</tr>

<?php 
	$show = "";
	for ($i=0; $i < 10; $i++) {
		if (!$fdf_list[$i] && $i > 1) $show = "style='display:none'";
		$fdf_data = ($fdf_list[$i]) ? explode('^',$fdf_list[$i]) : array(); 
		$x = $i + 1;
?>
										<tr <?php echo $show ?>>
											<td class="wmtLabel">
												<input name="hx_family_name_<?php echo $x ?>" class="wmtInput" value="<?php echo $fdf_data[0] ?>"" />
											</td>
											<td class="wmtLabel">
												<select name="hx_family_relation_<?php echo $x ?>">
													<?php $fam_options->showOptions($fdf_data[1], '--select--'); ?>
												</select>
											</td>
											<td class="wmtLabel">
												<select name="hx_family_dynamic_<?php echo $x ?>">
													<?php $dyn_options->showOptions($fdf_data[1], '--select--'); ?>
												</select>
											</td>
											<td class="wmtLabel" style="width:100%">
												<input class="wmtFullInput" name="hx_family_notes_<?php echo $x ?>" value="<?php echo $fdf_data[4] ?>" />
											</td>
										</tr>
<?php } ?>
									</table>
									<input type="button" id="addFamily" value="Add Family" />
<script>
$("#addFamily").click(function(){
	$('tr:hidden:first','#familyTable').css('display','');
});
</script>									
								</fieldset>
								
								<table width="100%" border="0" cellspacing="0" cellpadding="2">
									<tr>
										<td class="wmtLabel" style="width:150px">
											Relationship Status:
										</td>
										<td class="wmtLabel" style="width:250px">
											<select name="hx_marital">
												<?php $fam_options->showOptions($hx_data->marital, '--select--'); ?>
											</select>
										</td>
										<td class="wmtLabel" width="150px">
											Sexual Orientation:
										</td>
										<td class="wmtLabel">
											<select name="hx_orientation">
												<?php $dyn_options->showOptions($hx_data->orientation, '--select--'); ?>
											</select>
										</td>
									</tr>
								</table>

								<table width="100%" border="0" cellspacing="0" cellpadding="2">
									<tr>
										<td class="wmtLabel" style="width:150px">
											Marriages / Partnerships:
										</td>
										<td class="wmtLabel" style="width:80px">
											<input name="hx_marriages" type="text" class="wmtFullInput" style="width:30px" value="<?php echo $hx_data->marriages; ?>" />
										</td>
										<td class="wmtLabel" style="width:140px">
											Divorces / Separations:
										</td>
										<td class="wmtLabel" style="width:80px">
											<input name="hx_divorces" type="text" class="wmtFullInput" style="width:30px" value="<?php echo $hx_data->divorces; ?>" />
										</td>
										<td class="wmtLabel" style="width:120px">
											Number of Children:
										</td>
										<td class="wmtLabel">
											<input name="hx_children" type="text" class="wmtFullInput" style="width:30px" value="<?php echo $hx_data->children; ?>" />
										</td>
									</tr>
								</table>
								
								<fieldset>
									<legend>Child Dynamics & Function</legend>

									<table id="childTable" width="100%" border="0" cellspacing="0" cellpadding="2">
										<tr>
											<td class="wmtHeader">Name</td>
											<td class="wmtHeader">Relationship</td>
											<td class="wmtHeader">Age</td>
											<td class="wmtHeader">Dynamic</td>
											<td class="wmtHeader">Comments</td>
										</tr>

<?php 
	$show = '';
	$cdf_list = explode('|',$hx_data->child_array);
	for ($i=0; $i < 10 ; $i++) {
		if (!$cdf_list[$i] && $i > 1) $show = "style='display:none'";
		$cdf_data = ($cdf_list[$i]) ? explode('^',$cdf_list[$i]) : array(); 
		$x = $i + 1;
?>
										<tr <?php echo $show ?>>
											<td class="wmtLabel">
												<input class="wmtInput" name="hx_child_name_<?php echo $x ?>" value="<?php echo $cdf_data[0] ?>" />
											</td>
											<td class="wmtLabel">
												<select name="hx_child_relation_<?php echo $x ?>">
													<?php $kid_options->showOptions($cdf_data[1], '--select--'); ?>
												</select>
											</td>
											<td class="wmtLabel">
												<input name="hx_child_age_<?php echo $x ?>" type="text" class="wmtFullInput" style="width:50px" value="<?php echo $cdf_data[2]; ?>" />
											</td>
											<td class="wmtLabel">
												<select name="hx_child_dynamic_<?php echo $x ?>">
													<?php $dyn_options->showOptions($cdf_data[3], '--select--'); ?>
												</select>
											</td>
											<td class="wmtLabel" style="width:100%">
												<input class="wmtFullInput" name="hx_child_notes_<?php echo $x ?>" value="<?php echo $cdf_data[5] ?>" />
											</td>
										</tr>
										
<?php } ?>
									</table>
									<input type="button" id="addChild" value="Add Child" />
<script>
$("#addChild").click(function(){
	$('tr:hidden:first','#childTable').css('display','');
});
</script>									
								</fieldset>
								
								<table width="100%" border="0" cellspacing="0" cellpadding="2">
									<tr>
										<td class="wmtLabel" style="width:450px">
											Currently Paying Child Support:
										</td>
										<td class="wmtRadio">
											<input name="hx_support_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->support_flag)? ' checked':''); ?> value="0" />No
											<input name="hx_support_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->support_flag)? ' checked':''); ?> value="1" />Yes
											<input name="hx_support_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->support_flag)? ' checked':''); ?> value="2" />N/A
										</td>
									</tr>

									<tr>
										<td class="wmtLabel" valign="top" style="padding-top:15px" colspan="6">
											Family & Relationship Comments:
											<textarea name="hx_family_comments" id="hx_family_comments" class="wmtFullInput" rows="4" style="height:97px"><?php echo $hx_data->family_comments; ?></textarea>
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