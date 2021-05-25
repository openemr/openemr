<?php //Templates List

use OpenEMR\Core\Header;

require_once("../../globals.php");
	require_once("$srcdir/options.inc.php");

	$option_id = (isset($_GET['option_id']) && $_GET['option_id'] != '' ? $_GET['option_id'] : '');

	$option_title_que = sqlFetchArray(sqlStatement("SELECT title FROM list_options WHERE list_id = ? AND option_id = ?", array('template_location', $option_id)));
	$option_title = (isset($option_title_que) && isset($option_title_que['title']) && $option_title_que['title'] != "" ? $option_title_que['title'] : false)
?>
<html>
	<head>
		<TITLE><?php echo xl('Demographics Form Option'); ?></TITLE>
		<?php Header::setupHeader(['opener']);?>
		<style>
		.family_history_option_wrap{
			font-size: 13px;
		}
			.family_history_table{
				width: 100%;
			}

			input[type="text"], textarea, select{
				display: block;
				width: 100%;
				border: 1px solid #DDD;
				padding: 5px;
				border-radius:4px;
			}
		 .auto_complete{
			display: none;
			position: absolute;
			left: 0px;
			right: 0px;
			top: 50px;
			background: #EFEFEF;
			border: 1px solid #DDD;
		 }
		 .auto_complete ul{
			padding: 0px;
			margin: 0px;
			list-style: none;
		 }
		 .auto_complete ul li{
			padding: 0px;
			margin: 0px;
			border-bottom: 1px solid #DDD;
			padding: 5px;
			cursor: pointer;
		 }
		 #dignosis_search_table, #fm_history_table{
			 border-left: 1px solid #303030;
			 border-top: 1px solid #303030;
			 border-spacing: 0px;
			 width: 100%;
		 }
		 #dignosis_search_table td, #dignosis_search_table th,
		 #fm_history_table td, #fm_history_table th{
			 border-right: 1px solid #303030;
			 border-bottom: 1px solid #303030;
			 text-align: left;
		 }
		 #fm_history_table .selected td, #fm_history_table .selected th{
			 background: #EFEFEF;
		 }
		 #dignosis_search_table thead,
		 #fm_history_table thead{
			 background: #DDD;
		 }
		</style>
	</head>
	<body class="body_top">
		<span class="title"><?php echo xl('Family History Structured Form'); ?></span><br/>
		<div class="family_history_option_wrap">
			<div id="family_history_option_inner">
				<form id="family_history_list_form" action="#" onSubmit="submitAddEditForm($(this));" method="post">
				<fieldset>
					<legend><?php echo xl('Overall Family History Notes'); ?></legend>
					<textarea name="overall_family_history_notes" id="overall_family_history_notes" rows="4" cols="45"></textarea>
				</fieldset>
				<fieldset>
					<legend><?php echo xl('Family Health History'); ?></legend>
					<label>
						<input type="checkbox" name="family_health_history_1" id="family_health_history_1" value="1" /> &nbsp;
						<?php echo xl('Patient reports their family members, have no significant health history.'); ?>
					</label><br/>
					<label>
						<input type="checkbox" name="family_health_history_2" id="family_health_history_2" value="2" /> &nbsp;
						<?php echo xl('Patient does not know their family\'s health history.'); ?>
					</label><br/><br/>
					<table id="fm_history_table">
						<thead>
							<tr>
								<th><?php echo xl('Relation'); ?></th>
								<th><?php echo xl('Health History'); ?></th>
							</tr>
						</thead>
						<tbody>	</tbody>
					</table>
				</fieldset><br/>
				<div class="fm_btn_add_delte_wrap">
					<button type="button" class="css_btn_small"  onclick="deleteSelectedRow();"><?php echo xl('Delete'); ?></button>
					<!-- <button type="button" class="css_btn_small" ><?php echo xl('Edit'); ?></button> -->
					<button type="button" class="css_btn_small" onclick="addSelectedRow();"><?php echo xl('Add'); ?></button>
					<button type="submit" class="css_btn" style="float:right;" onclick="updatescContent();"><?php echo xl('Save'); ?></button>
				</div><div style="clear:both;"></div>
				</form>
			</div>
			<div class="family_history_list_wrap" id="family_history_list_wrap" style="display:none;">

					<table class="family_history_table">
						<tbody>
							<tr>
								<td>
									<label><?php echo xl('Family Member'); ?></label>
									<select name="family_member" id="family_member">
										<option value=''><?php echo xl('--Select--'); ?></option>
										<option><?php echo xl('Parent'); ?></option>
										<option><?php echo xl('Father'); ?></option>
										<option><?php echo xl('Mother'); ?></option>
										<option><?php echo xl('Sibling'); ?></option>
										<option><?php echo xl('Sister'); ?></option>
										<option><?php echo xl('Brother'); ?></option>
										<option><?php echo xl('Fraternal twin brother'); ?></option>
										<option><?php echo xl('Fraternal twin sister'); ?></option>
										<option><?php echo xl('Identical twin brother'); ?></option>
										<option><?php echo xl('Identical twin sister'); ?></option>
										<option><?php echo xl('Half-sister'); ?></option>
										<option><?php echo xl('Half-brother'); ?></option>
										<option><?php echo xl('Child'); ?></option>
										<option><?php echo xl('Son'); ?></option>
										<option><?php echo xl('Daughter'); ?></option>
										<option><?php echo xl('Grandparent'); ?></option>
										<option><?php echo xl('Maternal Grandmother'); ?></option>
										<option><?php echo xl('Maternal Grandfather'); ?></option>
										<option><?php echo xl('Paternal Grandmother'); ?></option>
										<option><?php echo xl('Paternal Grandfather'); ?></option>
										<option><?php echo xl('Family Member'); ?></option>
									</select>
								</td>
								<td>
									<label><?php echo xl('Name (Optional)'); ?></label>
									<input type="text" name="family_member_name" id="family_member_name" />
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<label><?php echo xl('History Notes'); ?></label><br/>
									<textarea class="field_description" name="field_description" id="field_description" style="min-height:100px;"></textarea>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<fieldset>
										<legend><?php echo xl('Health History'); ?></legend>
										<label>
											<input type="checkbox" name="no_significant_health_history" id="no_significant_health_history" value="1" /> &nbsp; <?php echo xl('No Significant Health History'); ?>
										</label><br/>
										<label>
											<input type="checkbox" name="unknown_health_history" id="unknown_health_history" value="1" /> &nbsp; <?php echo xl('Unknown Health History'); ?>
										</label><br/>
										</label><br/>
										<div style="position:relative;">
											<label><?php echo xl('Diagnosis Search'); ?></label><br/>
											<input type="text" name="dignosis_search" id="dignosis_search"  onkeyup="changeDiagonisis($(this))" placeholder="Search by code..." />
											<div class="auto_complete" id="auto_complete"><ul></ul></div><br/>
											<table id="dignosis_search_table">
												<thead>
													<tr>
														<th style="width:30%;"><?php echo xl('Diagnosis'); ?></th>
														<th><?php echo xl('Diagnosis Notes'); ?></th>
													</tr>
												</thead>
												<tbody></tbody>
											</table>
										</div>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table><br/>
					<div class="fm_btn_add_delte_wrap">
						<button type="button" class="css_btn_small"  onclick="closeDiagnosis();"><?php echo xl('Close'); ?></button>
						<button type="button" class="css_btn_small" onclick="saveDiagnosis();"><?php echo xl('Save'); ?></button>
					</div><div style="clear:both;"></div>
			</div>
		</div>
		<script>
			$(document).ready(function(){
				$("#template_location").on("change", function(){
					$(this).find('option[value="<?php echo $option_title; ?>"]').prop("selected", true);
				});
			});

			function changeDiagonisis(obj){
				$('#auto_complete ul li').remove();
				$.ajax({
					url: '../../forms/assessment_form/assessment_form_search_code.php?withcode=1&search_code=' + obj.val(),
					type: "get",
					success: function(result){
						var resultObj = JSON.parse(result);
						$('#auto_complete ul li').remove();
						resultObj.forEach(function(item) {
							$('#auto_complete ul').append('<li onclick="selectTheVale($(this))">'+item.code+' - '+item.code_text+'</li>');
						});

						if(resultObj.length > 0){
							$('#auto_complete').show();
						}
					}
				});
				//$('#diagnoses_link').attr("href", '../../forms/assessment_form/assessment_form_search_code.php?search_code = ' + obj.val());
			}

			function selectTheVale(obj){
				$('#auto_complete').hide();
				var obj_text = (obj.text()).split(" - ");
				var appendText = '';
				appendText += '<tr>';
				appendText += '<td><input type="text" name="diagnoses_code[]" class="diagnoses_code" value="'+obj_text[1]+'" style="width:300px;" /></td>';
				appendText += '<td><input type="text" name="diagnoses_desc[]"" class="diagnoses_desc" style="width:400px; value="" /></td>';
				appendText += '</tr>';
				$("#dignosis_search_table tbody").append(appendText);
			}

			function closeDiagnosis(){
				$("#family_history_option_inner").show();
				$("#family_history_list_wrap").hide();
				$("#family_history_list_wrap input, #family_history_list_wrap select, #family_history_list_wrap textarea").val("");
				$("#family_history_list_wrap input[type=\"checkbox\"]").prop("checked", false);
				$("#dignosis_search_table tbody tr").remove();
			}

			function saveDiagnosis(){
				if(validateInput()){
					var alltxt;
					var family_member = $("#family_member").val();
					var family_member_name = $("#family_member_name").val() ? ' - '+$("#family_member_name").val() : '';
					var nosig = $('#no_significant_health_history').prop('checked') ? ",No Significant Health History" : "";
					var unknown = $('#unknown_health_history').prop('checked') ? ",Unknown Health History" : "";
					var history_notes = $('#field_description').val() ? ","+$('#field_description').val() : "";
					if($("#dignosis_search_table tbody tr").length > 0){
						$("#dignosis_search_table tbody tr").each(function(){
							var diagnoses_desc = $(this).find('.diagnoses_desc').val();
							var diagnoses_code = $(this).find('.diagnoses_code').val();
							alltxt = nosig+unknown+','+diagnoses_code+'('+diagnoses_desc+')'+history_notes;
							alltxt = alltxt.replace(/^,/, '');
							alltxt = alltxt.replace(/,\s*$/, "");
							var appendText = '';
							appendText += '<tr onclick="$(this).toggleClass(\'selected\');">';
							appendText += '<td>'+family_member+family_member_name+ '<input type="text" name="relation[]" value="'+family_member+family_member_name+ '" style="display:none;"  /></td>';
							appendText += '<td>'+alltxt+'<input type="text" name="health_history[]" value="'+family_member+family_member_name+' '+alltxt+'" style="display:none;" /></td>';
							appendText += '<input type="hidden" name="health_history_hidden[]" value="'+family_member+'#'+family_member_name+'#'+history_notes+'#'+nosig+'#'+unknown+'#'+diagnoses_code+'#'+diagnoses_desc+'"/>'
							appendText += '</tr>';
							$("#fm_history_table tbody").append(appendText);
						});
					}else{
						alltxt = nosig+unknown+history_notes;
						alltxt = alltxt.replace(/^,/, '');
						alltxt = alltxt.replace(/,\s*$/, "");
						var appendText = '';
						appendText += '<tr onclick="$(this).toggleClass(\'selected\');">';
						appendText += '<td>'+family_member+family_member_name+ '<input type="text" name="relation[]" value="'+family_member+family_member_name+ '" style="width:250px;display:none;"  /></td>';
						appendText += '<td>'+alltxt+'<input type="text" name="health_history[]" value="'+family_member+family_member_name+' '+alltxt+'" style="display:none;"/></td>';
						appendText += '<input type="hidden" name="health_history_hidden[]" value="'+family_member+'#'+family_member_name+'#'+history_notes+'#'+nosig+'#'+unknown+'##'+'"/>'
						appendText += '</tr>';
						$("#fm_history_table tbody").append(appendText);
					}

					closeDiagnosis();
				}
			}

			function submitAddEditForm(){
				var formData = $('#family_history_list_form').serialize();
				window.opener.updateFamilyHistoryValue(formData);
				dlgclose();
				return false;
			}

			function addSelectedRow(){
				$("#family_history_option_inner").hide();
				$("#family_history_list_wrap").show();
			}

			function deleteSelectedRow(){
				$('#fm_history_table tbody tr.selected').remove();
			}

			function validateInput(){
				if(!$('#family_member').val()){
					alert('Please select the Family Member');
					return false;
				}
				return true;
			}

			function updatescContent(){
				var alltxt = '';
				var fh_overall = $('#overall_family_history_notes').val();
				var no_sig = $('#family_health_history_1').prop('checked') ? "\nPatient reports their family members, have no significant health history" : "";
				var hh_unknown = $('#family_health_history_2').prop('checked') ? "\nPatient does not know their family`s health history" : "";
				alltxt = fh_overall+no_sig+hh_unknown;
				$('input[name="health_history[]"]').each(function() {
					alltxt = alltxt ? alltxt+"\n"+$(this).val() : alltxt+$(this).val();
				});
				window.opener.updateValue("family_history", alltxt);
			}

		</script>
	</body>
</html>
