<?php //Templates List
use OpenEMR\Core\Header;

require_once("../../globals.php");
	require_once("$srcdir/options.inc.php");

	$option_id = (isset($_GET['option_id']) && $_GET['option_id'] != '' ? $_GET['option_id'] : '');

	$option_title_que = sqlFetchArray(sqlStatement("SELECT title FROM list_options WHERE list_id = ? AND option_id = ?", array('template_location', $option_id)));
	$option_title = (isset($option_title_que) && isset($option_title_que['title']) && $option_title_que['title'] != "" ? $option_title_que['title'] : false)
//	$option_content = (isset($_GET['content']) && $_GET['content'] != '' ? $_GET['content'] : '');
?>
<html>
	<head>
		<TITLE><?php echo xl('Demographics Form Option'); ?></TITLE>
		<?php Header::setupHeader(['opener']); ?>
		<style>
			.search-template-wrap{
				position: relative;
				margin: -8px -8px 3px -8px;
			}
			.search-template-wrap input{
				width: 180px;
			}
			.search-template-wrap button{

			}
		</style>
	</head>
	<body class="body_top">
		<div class="demographics_form_option_wrap">
			<div id="demographics_form_option_inner">
				<span class="title"><?php
				if($option_title == "History of Present Illnes") {
					echo "History of Present Illness Template";
				} else {
					echo xl($option_title . ' Template');
				}
				?></span>
				<table>
					<tr>
						<td style="width:250px;">
							<!--<div style="width:250px; float:left; overflow-x: hidden; overflow-y:scroll; height:345px;">-->
							<div class="search-template-wrap">
								<input type="text" id="search-template-wrap-field" placeholder="Search by template name" />
								<button type="button" class='css_button_small' onClick="clearSearchField();"><?php echo xl('Clear'); ?></button>
							</div>
							<ul  id="available-temps-list" class="template_names">
							<?php
								$availableTemps = Array();
								$templateSt = sqlStatement("SELECT * FROM systems_template WHERE template_location LIKE '%".$option_title."%'", array());
								if (sqlNumRows($templateSt)) {
									while ($tmpRow = sqlFetchArray($templateSt)) {
									$availableTemps[] = $tmpRow['template_name'];
							?>
								<li id="demographics_form_li_<?php echo $tmpRow['template_id']; ?>">
									<label>
										<input type="checkbox" class="demographics_form_option_checkbox" name="demographics_form_option_<?php echo $tmpRow['template_id']; ?>" value="<?php echo $tmpRow['field_description']; ?>" onchange="updateSelectedValue($(this));" data-location="<?php echo $tmpRow['template_location']; ?>" /> &nbsp;
										<span><?php echo $tmpRow['template_name']; ?></span>
									</label>
									<span class="edit_btn" onclick="editThisTemplate(<?php echo $tmpRow['template_id']; ?>)"><?php echo xl('Edit'); ?></span>
								</li>
							<?php
									}
								}
							?>
							</ul>
							<!--</div>-->
						</td>

						<td>
							<div style="text-align:right;padding:10px 0px;border-bottom: 1px solid #303030;">
								<button type="button" class='css_button_small css_btn_edit' onClick="toggleAddEditTemplate();"><?php echo xl('Add New Template'); ?></button> &nbsp; <br/>
							</div>
							<textarea name="demographics_form_option_txt" id="demographics_form_option_txt"></textarea>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="text-center">
							<button type="button" onclick="copyContent('<?php echo $option_id; ?>');"><?php
							 if($option_title == "History of Present Illnes") {
                                        			echo "Save History of Present Illness Template";
                                			} else {
								echo xl('Save ' . $option_title);
							} ?>
							</button>
						</td>
					</tr>
				</table>
			</div>
			<div class="system_template_list_wrap" id="add_edit_template_wrap" style="display:none;">
				<form id="system_template_list_form" action="#" onSubmit="submitAddEditForm($(this));" method="post">
					<table class="system_template_table">
						<tbody>
							<tr>
								<td colspan="2" rowspan="2" style="height:250px;background:#feffca;">
									<label><?php echo xl('Template Location'); ?></label><br/>
									<select name="template_location[]" id="template_location" class="template_location" multiple="multiple">
										<?php
											$templateLocSt = sqlStatement("SELECT * FROM list_options WHERE list_id = ?", array('template_location'));
											$template_location_selected = [];
											if(isset($edit_data['template_location']) && $edit_data['template_location'] != "") $template_location_selected = explode(',', $edit_data['template_location']);
											while ($tmpRow = sqlFetchArray($templateLocSt)) {
												$selected = '';
												if($option_title === $tmpRow['title']) $selected = 'selected = "selected" readonly = "readonly"';
												echo '<option value="'.$tmpRow['title'].'" '.$selected.'>'.$tmpRow['title'].'</option>';
											}
										?>
									</select>
								</td>
								<td>
									<label><?php echo xl('Template Name'); ?></label><br/>
									<input type="text" name="template_name" id="template_name" value="" />
								</td>
							</tr>
							<tr>
								<td>
									<label><?php echo xl('Field Description'); ?></label><br/>
									<textarea class="field_description" name="field_description" id="field_description" style="min-height:200px;"></textarea>
								</td>
							</tr>
							<tr>
								<td>
									<div class="error_msg" style="color:red;padding:10px;"></div>
									<button type="submit" class="css_button"><?php echo xl('Save Template'); ?></button> &nbsp;
									<button type="button" class="css_button css_button_delete" onClick="resetAddEditForm();"><?php echo xl('Cancel'); ?></button>
									<input type="hidden" name="save_templates" id="save_templates" value="add" />
									<input type="hidden" name="from_assessment_form" id="from_assessment_form" value="1" />
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		</div>
		<script>
			$(document).ready(function(){
				var textareaVal = window.opener.document.getElementById('<?php echo $option_id ?>').value;
				$('#demographics_form_option_txt').val(textareaVal);
				$("#system_template_list_form").on("submit", function(e){
					e.preventDefault();
					var template_location = $("#template_location").val();
					var template_name = $("#template_name").val();
					var field_description = $("#field_description").val();
					$(".error_msg").text("");

					if( template_location == null ||  (template_location != null && template_location.length <= 0) || template_name == null || (template_name != null && template_name.trim() == "") || field_description == null || (field_description != null && field_description.trim() == "")){
						$(".error_msg").text("Please fill all fields!");
						return false;
					}

					$.ajax({
						url: "../../templates/add_edit_templates.php",
						type: "POST",
						data: $("#system_template_list_form").serialize(),
						success: function(data){
							var jsonData = $.parseJSON(data);
							var tmpLocation = (jsonData.template_location != "" ? (jsonData.template_location).split(",") : []);

							var tmpLocationAvailable = tmpLocation.indexOf('<?php echo $option_title; ?>');

							if(tmpLocationAvailable >= 0){
								if(jsonData.type == "add"){
									var appendValue = "";
									appendValue += '<li id="demographics_form_li_' + jsonData.template_id + '">';
									appendValue += '<label>';
									appendValue += '<input type="checkbox" class="demographics_form_option_checkbox" name="demographics_form_option_' + jsonData.template_id + '" value="' + jsonData.field_description + '" onchange="updateSelectedValue();" data-location="' + jsonData.template_location + '" /> &nbsp;';
									appendValue += '<span>' + jsonData.template_name + '</span>';
									appendValue += '</label>';
									appendValue += '<span class="edit_btn" onclick="editThisTemplate(' + jsonData.template_id + ')"><?php echo xl('Edit'); ?></span></li>';
									$(".template_names").append(appendValue);
								}else{
									$("#demographics_form_li_" + jsonData.template_id).find(".demographics_form_option_checkbox").attr("value", jsonData.field_description);
								}
							}else{
								$("#demographics_form_li_" + jsonData.template_id).remove();
							}
							//updateSelectedValue();
							resetAddEditForm();
						},
						error: function(err){
							console.log(err);
						}
					});
				});

				$("#template_location").on("change", function(){
					$(this).find('option[value="<?php echo $option_title; ?>"]').prop("selected", true);
				});
				$("#search-template-wrap-field").on("keyup", function(){
					var searchVal = $(this).val();
					var searchArray = <?php echo json_encode($availableTemps); ?>;
					if(searchVal && searchVal != ""){
						$("#available-temps-list li").hide();
						$.each( searchArray, function( index, vals ) {
							var valsLc = vals.toLowerCase();
							var searchValLc = searchVal.toLowerCase();
							if(valsLc.indexOf(searchValLc) >= 0){
								$("#available-temps-list li:nth-child("+(index + 1)+")").show();
							}
						});
					}else{
						$("#available-temps-list li").show();
					}
				});
			});

			function clearSearchField(){
				$("#search-template-wrap-field").val("");
				$("#available-temps-list li").show();
			}
			function editThisTemplate(_id){
				var field_description = $("#demographics_form_li_" + _id).find("label .demographics_form_option_checkbox").val();
				var template_location = $("#demographics_form_li_" + _id).find("label .demographics_form_option_checkbox").attr("data-location");
				var _template_location = (template_location != "" ? template_location.split(",") : []);
				var template_name = $("#demographics_form_li_" + _id).find("label span").text();

				$.each(_template_location, function(index, item){
					$("#template_location").find('option[value="'+item+'"]').prop("selected", true);
				});

				$("#save_templates").val(_id);
				$("#template_name").val(template_name);
				$("#field_description").val(field_description);

				$("#demographics_form_option_inner").hide();
				$("#add_edit_template_wrap").show();
			}

			function updateSelectedValue(obj){
				var finalText = '';

				var cursorPos = $('#demographics_form_option_txt').prop('selectionStart');
				var v = $('#demographics_form_option_txt').val();
				var currentVal = '';
				if(obj.prop('checked') === true){
					currentVal = obj.val();
				}else{
					var currentValText = encodeURI(obj.val()) + '%0A';
					v = encodeURI(v);
					var replacedVal = new RegExp(currentValText, 'g');
					v = decodeURI(v.replace(replacedVal, ""));
				}
				var textBefore = v.substring(0,  cursorPos);
				var textAfter  = v.substring(cursorPos, v.length);

				if(textBefore.trim() == ""){
					finalText = currentVal + "\n";
				}else if(textAfter.trim() == "") {
					finalText = currentVal + "\n";
				}else if(currentVal != ""){
					finalText = "\n" + currentVal;
				}

				// $('ul.template_names li').each(function(){
					// if($(this).find('.demographics_form_option_checkbox').prop('checked') === true){
						// finalText += $(this).find('.demographics_form_option_checkbox').val() + "\n";
					// }
				// });
				$('#demographics_form_option_txt').val(textBefore + finalText + textAfter);
			}

			function copyContent(Id){
				var parentId = Id;
				var value = $('textarea#demographics_form_option_txt').val();
				window.opener.updateValue(parentId, value);
				dlgclose();
			}

			function toggleAddEditTemplate(){
				$("#demographics_form_option_inner").hide();
				$("#add_edit_template_wrap").show();
			}

			function submitAddEditForm(obj){
				return false;
			}

			function resetAddEditForm(){
				$("#add_edit_template_wrap select, #add_edit_template_wrap input, #add_edit_template_wrap textarea").val("").prop("selected", false);
				$("#demographics_form_option_inner").show();
				$("#add_edit_template_wrap").hide();
				$("#save_templates").val("add");
				$("#from_assessment_form").val("1");
				$("#template_location").find('option[value="<?php echo $option_title; ?>"]').prop("selected", true);
				return false;
			}
		</script>
	</body>
</html>
