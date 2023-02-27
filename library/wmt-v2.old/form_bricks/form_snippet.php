<?php
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
	
$template_fields = LoadList('Templated_Fields');
$module_title = GetListTitleByKey($module, 'Templated_Fields');
?>

<html>
	<head>
		<title><?php echo xl('Snippet Manager'); ?></title>
		<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
		<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt/templates.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmt.default.css" type="text/css">
<?php if($v_major > 4) { ?>
		<!-- <script type="text/javascript" src="<?php //echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-9-1/index.js"></script> -->
<?php } else { ?>
		<!-- <script type="text/javascript" src="<?php //echo $GLOBALS['webroot']; ?>/library/js/jquery-ui-1.7.1.custom.min.js"></script> -->
<?php } ?>
	</head>
	<body class="body_top" style="height: 96vh">
		<div class="demographics_form_option_wrap">
			<div id="demographics_form_option_inner">
				<!-- span class="title"><?php // echo xl($module_title . ' Snippets'); ?></span -->
				<table style="width: 90%;">				
					<tr>
						<td style="width:350px; text-align: left;">
							<div style="width:350px; float:left; overflow-x: hidden; overflow-y:scroll; min-height:75px;">
							<ul class="category_list">
							<?php 						
								$snips = getModuleSnippets($module);
								foreach($snips as $snip) {
									$attached_to = getSnippetModules($snip['category'],'',1,1,'string');
							?>
								<li id="demographics_form_li_<?php echo $snip['id']; ?>">
									<label>
										<input type="hidden" name="global_user_<?php echo $snip['id']; ?>" id="global_user_<?php echo $snip['id']; ?>" value="<?php echo $snip['global_user']; ?>" />
										<input type="checkbox" class="demographics_form_option_checkbox" name="demographics_form_option_<?php echo $snip['id']; ?>" value="<?php echo $snip['snippet']; ?>" onchange="updateSelectedValue();" data-location="<?php echo $attached_to; ?>" /> &nbsp;
										<span id="category_label_<?php echo $snip['id']; ?>"><?php echo $snip['category']; ?></span>
									</label>
									<?php 
									if($allow_edit) {
										if($snip['user_id'] == $_SESSION['authUserID']) {
									?>
									<div style="float: right;">
									<a href="javascript:;" class="css_button_small" onclick="editThisTemplate(<?php echo $snip['id']; ?>)"><?php echo xl('Edit'); ?></a>
									</div>
									<?php 
										}
									}
									?>
								</li>
							<?php
							}
							?>
							</ul>
							</div>
						</td>
						
						<td style="padding-left: 12px; border-left: solid 1px black;">
							<div style="padding:10px; margin-bottom: 10px;">
								<div style="float: left;">
								<a href="javascript:;" class="css_button" onclick="copyContent('<?php echo $target; ?>');"><span><?php echo xl('Save ' . $module_title); ?></span></a>
								</div>
								<?php if($allow_add) { ?>
								<div style="float: right;">
								<a href="javascript:;" class="css_button" onClick="toggleAddEditTemplate();"><span><?php echo xl('Add New Snippet'); ?></span></a> &nbsp;
								</div>
								<?php } ?>
							</div><br>
							<textarea name="demographics_form_option_txt" id="demographics_form_option_txt" rows="16"></textarea>
						</td>
					</tr>
				</table>
			</div><!-- THIS IS THE END OF THE TEXT BUILDER VIEW OF THE FORM -->

			<div class="system_template_list_wrap bgcolor2" id="add_edit_template_wrap" style="display:none; height: 100vh;">
				<form id="system_template_list_form" action="#" onSubmit="submitAddEditForm($(this));" method="post">
					<table class="system_template_table" style="width: 90%;">
						<tbody>
							<tr>
								<td rowspan="4" class="bgcolor" style="width: 30%; border-right: solid 1px black; margin-right: 12px;">
									<label><?php echo xl('Snippet Location'); ?></label><br/>
									<select name="modules[]" id="modules" class="modules" multiple="multiple" size="10" style="width: 98%; height: 35%;">
										<?php 
											foreach($template_fields as $fld) {
												$selected = '';
												if($module === $fld['option_id']) $selected = 'selected="selected" readonly="readonly"';
												echo '<option value="'.$fld['option_id'].'" '.$selected.'>'.$fld['title'].'</option>';
											}
										?>
									</select>
								</td>
								<td style="padding-left: 12px;">
									<label><?php echo xl('Snippet Name'); ?></label><br>
									<input type="text" name="category" id="category" value="" />
								</td>
							</tr>
							<tr>
								<td style="padding-left: 12px;">
									<input name="global_user" id="global_user" type="checkbox" value="1" style="width: 16px;" /><label for="global_user">&nbsp;<?php echo xl('Global Snippet (visible to all users)'); ?></label>
								</td>
							</tr>
							<tr>
								<td style="padding-left: 12px;">
									<label><?php echo xl('Snippet Contents'); ?></label><br/>
									<textarea class="snippet" name="snippet" id="snippet" rows="16"></textarea>
								</td>
							</tr>
							<tr>
								<td style="padding-left: 12px;">
									<div class="error_msg" style="color:red;padding:10px;"></div>
									<button type="submit"><?php echo xl('Save Snippet'); ?></button>&nbsp;&nbsp;&nbsp;
									<a href="javascript:;" class="css_button" onClick="resetAddEditForm();"><?php echo xl('Cancel'); ?></a>
									<input type="hidden" name="save_templates" id="save_templates" value="add" />
									<input type="hidden" name="from_form" id="from_form" value="1" />
									<input type="hidden" name="caller" id="caller" value="<?php echo $module; ?>" />
									<input type="hidden" name="snippet_id" id="snippet_id" value="" />
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
					var modules = $("#modules").val();
					var category = $("#category").val();
					var snippet = $("#snippet").val();
					$(".error_msg").text("");
					
					if( modules == null || (modules != null && modules.length <= 0) ) {
						$(".error_msg").text("Select at least one location for this snippet");
						return false;
					}
					if(category == null || (category != null && category.trim() == "")) {
						$(".error_msg").text("The snippet title can not be blank");
						return false;
					} 
					if(snippet == null || (snippet != null && snippet.trim() == "")) {
						$(".error_msg").text("The snippet text can not be blank");
						return false;
					}
					
					$.ajax({
						url: "<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/ajax/add_edit_snippets.ajax.php",
						type: "POST",
						data: $("#system_template_list_form").serialize(),
						success: function(data){
							console.log(data);
							var jsonData = $.parseJSON(data);
							var tmpLocation = (jsonData.modules != "" ? (jsonData.modules).split(",") : []);
							
							var tmpLocationAvailable = tmpLocation.indexOf('<?php echo $module; ?>');
							console.log('Available: '+tmpLocationAvailable);
							
							if(tmpLocationAvailable >= 0 || true){
								if(jsonData.type == "add"){
									if(!jsonData.id) {
										$(".error_msg").text("That Snippet Title is Already In Use");
										alert('Duplicate Snippet Titles Not Allowed');
										return false;
									} else {
										var appendValue = "";
										appendValue += '<li id="demographics_form_li_' + jsonData.id + '">';
										appendValue += '<label>';
										appendValue += '<input type="hidden" name="global_user_' + jsonData.id + '" id="global_user_' + jsonData.id + '" value="' + jsonData.global_user + '" />';
										appendValue += '<input type="checkbox" class="demographics_form_option_checkbox" name="demographics_form_option_' + jsonData.id + '" value="' + jsonData.snippet + '" onchange="updateSelectedValue();" data-location="' + jsonData.modules + '" /> &nbsp;';
										appendValue += '<span id="category_label_' + jsonData.id + '">' + jsonData.category + '</span>';
										appendValue += '</label>';
										appendValue += '<div style="float:right;"><a href="javascript:;" class="css_button_small" onclick="editThisTemplate(' + jsonData.id + ')"><span><?php echo xl('Edit'); ?></span></a></div></li>';
										$(".category_list").append(appendValue);
									}
								}else{
									$("#category_label_" + jsonData.id).text(jsonData.category);
									$("#demographics_form_li_" + jsonData.id).find(".demographics_form_option_checkbox").attr("value", jsonData.snippet);
									$("#global_user_" + jsonData.id).prop("value", jsonData.global_user);
								}
							}else{
								$("#demographics_form_li_" + jsonData.id).remove();
							}
							updateSelectedValue();
							resetAddEditForm();
						},
						error: function(err){
							console.log(err);
						}
					});
				});
				
				$("#modules").on("change", function(){
					$(this).find('option[value="<?php echo $module; ?>"]').prop("selected", true);
				});
			});
			
			function editThisTemplate(_id){
				var snippet = $("#demographics_form_li_" + _id).find("label .demographics_form_option_checkbox").val();
				var template_location = $("#demographics_form_li_" + _id).find("label .demographics_form_option_checkbox").attr("data-location");
				var _template_location = (template_location != "" ? template_location.split(",") : []);
				var category = $("#demographics_form_li_" + _id).find("label span").text();
				
				$.each(_template_location, function(index, item){
					$("#modules").find('option[value="'+item+'"]').prop("selected", true);
				});
				
				var glb = $("#global_user_" + _id).val();
				$("#global_user").prop("checked", false);
				if(glb != 0) $("#global_user").prop("checked", true);
				$("#snippet_id").val(_id);
				$("#category").val(category);
				$("#snippet").val(snippet);
				$("#save_templates").val(_id);
				$("#caller").val('<?php echo $module; ?>');
				
				$("#demographics_form_option_inner").hide();
				$("#add_edit_template_wrap").show();
			}
			
			function updateSelectedValue(){
				var finalText = '';
				$('ul.category_list li').each(function(){
					if($(this).find('.demographics_form_option_checkbox').prop('checked') === true){
						finalText += $(this).find('.demographics_form_option_checkbox').val() + "\n";
					}
				});
				$('#demographics_form_option_txt').val(finalText);
			}
			
			function copyContent(Id){
				var parentId = Id;
				var value = $('textarea#demographics_form_option_txt').val();
				window.opener.updateValue(parentId, value);
				window.close();
				// parent.$.fn.fancybox.close();
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
				$("#from_form").val("1");
				$("#caller").val('<?php echo $module; ?>');
				$("#modules").find('option[value="<?php echo $module; ?>"]').prop("selected", true);
				return false;
			}
		</script>
	</body>
</html>
