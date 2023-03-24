<?php //Templates List
require_once("../../../interface/globals.php");
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
	
if(!isset($_GET['module'])) $_GET['module'] = '';
$module = strip_tags($_GET['module']);
if(!isset($_GET['frmdir'])) $_GET['frmdir'] = '';
$frmdir = strip_tags($_GET['frmdir']);
if(!isset($_GET['fld'])) $_GET['fld'] = '';
$target = strip_tags($_GET['fld']);
if(!$target) $target = $module;
$allow_add = \OpenEMR\Common\Acl\AclMain::aclCheckCore('snippets', 'add');
$allow_edit = \OpenEMR\Common\Acl\AclMain::aclCheckCore('snippets', 'edit');
$allow_delete = \OpenEMR\Common\Acl\AclMain::aclCheckCore('snippets', 'delete');
echo "Add ($allow_add)  Edit [$allow_edit] Delete -$allow_delete-<br>\n";
	
$template_names = LoadList('Templated_Fields');
$module_title = GetListTitleByKey($module, $frmdir . '_modules');
?>
<html>
<head>
	<title><?php xl($module_title,'e'); ?></title>
	<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
	<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt/templates.css" type="text/css">
<?php if($v_major > 4) { ?>
	<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-9-1/index.js"></script>
<?php } else { ?>
	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-1.7.2.min.js"></script>
<?php } ?>
</head>
<body class="body_top">
	<div class="snippet_outer_wrap">
		<div id="snippet_select_inner">
			<!-- span class="title"><?php // xl($module_title . ' Snippets', 'e'); ?></span --> 				
			<table>				
				<tr>
					<td style="width:300px;">
						<div style="width:300px; float:left; overflow-x: hidden; overflow-y:scroll; height:345px;">
						<ul class="snippet_names">
						<?php 						
						$snips = getModuleSnippets($module);
						foreach($snips as $snip) {
						?>
							<li id="snippet_li_<?php echo $snip['id']; ?>">
								<label>
									<input type="checkbox" class="snippet_checkbox" name="snippet_chk_<?php echo $snip['id']; ?>" value="<?php echo $snip['snippet']; ?>" onchange="updateSelectedValue();" data-location="<?php echo $snip['module']; ?>" /> &nbsp;
									<span><?php echo $snip['category']; ?></span>
								</label>
								<?php if($allow_edit) { ?>
								<span class="edit_btn" onclick="editThisSnippet(<?php echo $snip['id']; ?>)"><?php echo xl('Edit'); ?></span>
								<?php } ?>
							</li>
						<?php
						}
						?>
						</ul>
						</div>
					</td>
					
					<td style="padding-left: 12px; border-left: solid 1px black;">
						<div style="text-align:right;padding:10px 0px;border-bottom: 1px solid #303030;">
						<?php if($allow_add) { ?>
							<button type="button" class='css_button_small css_btn_edit' onClick="toggleAddEditSnippet();"><?php echo xl('Add New Snippet'); ?></button> &nbsp; <br/>
						<?php } ?>
						</div>
						<textarea name="snippet_result" id="snippet_chk_txt"></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="text-center">
						<button type="button" onclick="copyContent('<?php echo $target; ?>');"><?php echo xl('Save ' . $module_title); ?></button>
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
								<label><?php echo xl('Template/Module Location'); ?></label><br/>
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
				$("#system_template_list_form").on("submit", function(e){
					e.preventDefault();
					var template_location = $("#template_location").val();
					var template_name = $("#template_name").val();
					var field_description = $("#field_description").val();
					$(".error_msg").text("");
					
					if( template_location == null || 
						(template_location != null && template_location.length <= 0) || 
						template_name == null || 
						(template_name != null && template_name.trim() == "") || 
						field_description == null || 
						(field_description != null && field_description.trim() == "")) {
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
									appendValue += '<li id="snippet_li_' + jsonData.template_id + '">';
									appendValue += '<label>';
									appendValue += '<input type="checkbox" class="snippet_checkbox" name="snippet_chk_' + jsonData.template_id + '" value="' + jsonData.field_description + '" onchange="updateSelectedValue();" data-location="' + jsonData.template_location + '" /> &nbsp;';
									appendValue += '<span>' + jsonData.template_name + '</span>';
									appendValue += '</label>';
								}else{
									$("#snippet_li_" + jsonData.template_id).find(".snippet_checkbox").attr("value", jsonData.field_description);
								}
							}else{
								$("#snippet_li_" + jsonData.template_id).remove();
							}
							updateSelectedValue();
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
			});
			
			function editThisSnippet(_id){
				var field_description = $("#snippet_li_" + _id).find("label .snippet_checkbox").val();
				var template_location = $("#snippet_li_" + _id).find("label .snippet_checkbox").attr("data-location");
				var _template_location = (template_location != "" ? template_location.split(",") : []);
				var template_name = $("#snippet_li_" + _id).find("label span").text();
				
				$.each(_template_location, function(index, item){
					$("#template_location").find('option[value="'+item+'"]').prop("selected", true);
				});
				
				$("#save_templates").val(_id);
				$("#template_name").val(template_name);
				$("#field_description").val(field_description);
				
				$("#snippet_select_inner").hide();
				$("#add_edit_template_wrap").show();
			}
			
			function updateSelectedValue(){
				var finalText = '';
				$('ul.snippet_names li').each(function(){
					if($(this).find('.snippet_checkbox').prop('checked') === true){
						finalText += $(this).find('.snippet_checkbox').val() + "\n";
					}
				});
				$('#snippet_result').val(finalText);
			}
			
			function copyContent(Id){
				var parentId = Id;
				var value = $('textarea#snippet_result').val();
				window.parent.updateValue(parentId, value);
				window.close();
				// parent.$.fn.fancybox.close();
			}
			
			function toggleAddEditSnippet(){
				$("#snippet_select_inner").hide();
				$("#add_edit_template_wrap").show();
			}
			
			function submitAddEditForm(obj){
				return false;
			}
			
			function resetAddEditForm(){
				$("#add_edit_template_wrap select, #add_edit_template_wrap input, #add_edit_template_wrap textarea").val("").prop("selected", false);
				$("#snippet_select_inner").show();
				$("#add_edit_template_wrap").hide();
				$("#save_templates").val("add");
				$("#from_assessment_form").val("1");
				$("#template_location").find('option[value="<?php echo $option_title; ?>"]').prop("selected", true);
				return false;
			}
		</script>
	</body>
</html>
