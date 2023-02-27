<?php
/** ************************************************************************************
 *	EDIT_TEMPLATE.PHP
 *
 *	Copyright (c)2018 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package utilities
 *  @version 1.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 ************************************************************************************* */

require_once("../globals.php");
require_once("$srcdir/wmt-v3/wmt.globals.php");
$pop = FALSE;

use OpenEMR\Core\Header;

/* --------------------------------------------------------------------------- *
 * Initialize form defaults
 * --------------------------------------------------------------------------- */
$form_name = "edit_template";
$form_title = 'Form Template Editor';

/* --------------------------------------------------------------------------- *
 * Store input parameters
 * --------------------------------------------------------------------------- */
$tmpl_mode = 'find';
foreach ( $_REQUEST AS $key => $value ) {
	if ( strpos($key, 'tmpl_') !== false )
		${$key} = filter_var($value, FILTER_SANITIZE_STRING);
}

/* --------------------------------------------------------------------------- *
 * Retrieve existing record
 * --------------------------------------------------------------------------- */
if (empty($tmpl_id) || $tmpl_id == 'NEW') $tmpl_id = false;
$tmpl_data = new wmt\Template($tmpl_id);

/* --------------------------------------------------------------------------- *
 * Retrieve substitution tags
 * --------------------------------------------------------------------------- */
$tags = wmt\Grab::listTags();

/* --------------------------------------------------------------------------- *
 * Delete record
 * --------------------------------------------------------------------------- */
if ( $tmpl_mode == 'delete' ) {
	$tmpl_data->delete();
	
	$tmpl_id = false;
	$tmpl_data = new wmt\Template();
	$tmpl_mode = 'find';
}

/* --------------------------------------------------------------------------- *
 * Save record changes
 * --------------------------------------------------------------------------- */
if ( $tmpl_mode == 'save' ) {
	$tmpl_data->title = $tmpl_title;
	$tmpl_data->name = $tmpl_name;
	$tmpl_data->language = $tmpl_language;
	$tmpl_data->description = $tmpl_description;
	$tmpl_data->html_body = $_REQUEST['tmpl_content']; 
	$text_body = $tmpl_data->html_body;
	$text_body = str_replace("<br>", "\n", $text_body);
	$text_body = str_replace("<br />", "\n", $text_body);
	$tmpl_data->text_body = strip_tags($text_body);

	if ($tmpl_data->name == 'contacts') $tags = array(); // special tags
	if ($tmpl_data->name == 'patient') $tags = array(); // special tags
	if ($tmpl_data->name == 'financial') $tags = array(); // special tags
	if ($tmpl_data->name == 'family') $tags = array(); // special tags
	if ($tmpl_data->name == 'enrollment') $tags = array(); // special tags
	if ($tmpl_data->name == 'portal_enroll') $tags = array(); // special tags
	if ($tmpl_data->name == 'registration') $tags = array(); // special tags
	if ($tmpl_data->name == 'options') $tags = array(); // special tags
	if ($tmpl_data->name == 'referral') $tags = array(); // special tags
	if ($tmpl_data->name == 'care_plan') $tags = array(); // special tags
	
	$alert = '';
	$html_tags = array();
	if (empty($tmpl_data->name)) {
		$alert = "No template name provided, template not stored...";
	} elseif (empty($tmpl_data->html_body)) {
		$alert = "No template content provided, template not stored...";
	} else {
		$count = preg_match_all("/\[([^\]]*)\]/", $tmpl_data->html_body, $html_tags);
		$tag_array = ($count) ? $html_tags[1] : array();
		$bad = '';
		
		// only validate if tags defined for this form
		if (count($tags) > 0) {
			foreach ($tag_array AS $tag) {
				if (!array_key_exists($tag, $tags)) {
					if (!empty($bad)) $bad .= ', ';
					$bad .= '[' . $tag . ']';
				}
			}
			if (!empty($bad)) $alert = "Invalid tag found in content " . $bad . ", please correct and resubmit...";
		}
		
	}
	
	if (empty($alert)) {
		$tmpl_id = $tmpl_data->store();
		$tmpl_mode = 'find';
	} else {
		$tmpl_mode = 'update';
	}
}

?>
<html>
<head>
	<title><?php echo $form_title; ?></title>
	<link type="text/css" rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmt.default.css" />

	<?php Header::setupHeader(['main-theme', 'jquery', 'jquery-ui', 'jquery-ui-base', 'fontawesome', 'bootstrap']); ?>
	<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/tinymce/tinymce.min.js"></script>

	<script>
		function continueClicked() {
			if ( $("#tmpl_id").val() == '' ) {
				alert('You must select a template before continuing.');
				return;
			}

			<?php if (!$pop) { ?>top.restoreSession(); <?php } ?>
			$("#<?php echo $form_name ?>").submit();
			}

		function saveClicked() {
			var notice = "";
			$("input,textarea,select").filter("[required]:visible").each(function() {
				if ( $(this).val() == '' ) {
					$(this).css('border','2px solid red');
					notice = "Please complete all indicated fields before submitting.";
				}
			});

			if ( notice ) {
				alert( notice );
				return; 
			}

			<?php if (!$pop) { ?>top.restoreSession(); <?php } ?>
			$("#<?php echo $form_name ?>").submit();
			}

		function deleteClicked() {
			if ( confirm('<?php xlt('Are you sure to wish to remove this template?') ?>') ) {
				<?php if (!$pop) { ?>top.restoreSession(); <?php } ?>
				$("#tmpl_mode").val('delete');
				$("#<?php echo $form_name ?>").submit();
			}
			}

		function cancelClicked() {
			<?php if (!$pop) { ?>
				top.restoreSession(); 
				location.href='<?php echo $cancel_url ?>';
			<?php } else { ?>
				var winid = window.open('','_SELF');
				winid.close();
			<?php } ?>
			}
	</script>
</head>

<body class="body_top">
	<form method='post' action="edit_template.php"
		id='<?php echo $form_name; ?>' 'name='<?php echo $form_name; ?>'>
		<div class="wmtTitle">
			<input type='hidden' name='pop' value='<?php if ($pop) echo '1' ?>' />
			<span class='title'>New <?php echo $form_title; ?></span>
		</div>

		<!-- BEGIN FORM -->

		<!--  Start of Editor -->
		<div class="wmtMainContainer wmtColorMain">
			<div class="wmtCollapseBar wmtColorBar" id="TmplCollapseBar">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td>&nbsp;</td>
						<td class="wmtChapter" style="text-align: center">
								<?php echo $form_title ?>
							</td>
						<td style="text-align: right">&nbsp;</td>
					</tr>
				</table>
			</div>
				
<?php if ( $tmpl_mode == 'find' ) { ?>

			<input type='hidden' id='tmpl_mode' name='tmpl_mode' value='edit' />
			<div class="wmtCollapseBox" id="TmplBox" style="padding: 40px 20px">
				<table>
					<tr>
						<td class="wmtLabel" style="width: 300px; white-space: nowrap">
							<?php echo xlt('Create or Select Template'); ?>:&nbsp;</td>
					</tr>
					<tr>
						<td class="wmtLabel"><select id="tmpl_id" name="tmpl_id" class="form-control">
								<option value="NEW"><?php echo xlt('Create New Template'); ?></option>
<?php
$query = "SELECT `id`,`name`,`title`,`language` FROM `templates` ORDER BY `title`";
$tmpl_result = sqlStatement($query);
while ($tmpl = sqlFetchArray($tmpl_result)) {
	$tmpName = $tmpl['name'] ? " - ".$tmpl['name'] : "";
	echo "<option value = '".$tmpl['id']."'>".$tmpl['title'].$tmpName." (".$tmpl['language'].")";
	echo "</option>\n";
}
?>
								</select></td>
						<td style="padding-left: 5px"><a class="btn btn-primary" tabindex="-1"
							href="javascript:continueClicked()"><span><?php echo xlt('Continue'); ?></span></a> 
							<!--  a class="btn btn-primary" tabindex="-1"
							href="javascript:cancelClicked()"><span>Cancel</span></a --></td>
					</tr>
				</table>
			</div>

<?php } else { ?>

			<input type='hidden' id='tmpl_mode' name='tmpl_mode' value='save' />
			<input type='hidden' name='tmpl_id' value='<?php echo $tmpl_id ?>' />
			<div class="wmtCollapseBox" id="PreBox" style="padding: 10px 20px 40px 20px">
				<table style="width: 90%">
<?php if ($alert) { ?>
					<tr>
						<td class="wmtLabel" colspan="3" style="font-weight: bold; color: red">
							<?php echo $alert ?>
						</td>
					</tr>
<?php } ?>
					<tr>
						<td class="wmtLabel" style="width: 200px; white-space: nowrap">
							<?php echo xlt('Template Key'); ?>:<br> 
							<input required class="wmtInput form-control wmtDisabled" type="text" 
							name="tmpl_name" id="tmpl_name" style="width:200px" 
							value="<?php echo $tmpl_data->name ?>"
							<?php if ($tmpl_id) echo "readonly" ?>/>
						</td>
						<td class="wmtLabel" style="width: 200px; white-space: nowrap">
							<?php echo xlt('Language'); ?>:<br> 
							<?php if ($tmpl_id) { ?>
							<input required class="wmtInput form-control" type="text"
							name="tmpl_language" id="tmpl_language" style="width:80px" 
							value="<?php echo $tmpl_data->language ?>" readonly />
							<?php } else { ?>
							<select required class="wmtInput form-control" name="tmpl_language" id="tmpl_language"
							style="width:100px">
								<option value='English'>English</option>
								<option value='Spanish'>Spanish</option>
							</select>
							<?php }?>
						</td>
						<td style="padding-left: 40px">
							<a class="btn btn-primary" tabindex="-1"
								href="javascript:saveClicked()"><span><?php echo xlt('Save'); ?></span></a> 
							<a class="btn btn-primary" tabindex="-1"
								href="javascript:deleteClicked()"><span><?php echo xlt('Delete'); ?></span></a>
							<a class="btn btn-primary" tabindex="-1"
								href="javascript:cancelClicked()"><span><?php echo xlt('Cancel'); ?></span></a>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="wmtLabel" style="width: 300px; white-space: nowrap">
							<?php echo xlt('Template Name'); ?>:<br /> 
							<input required class="wmtFullInput form-control"
							type="text" name="tmpl_title" id="tmpl_title"
							value="<?php echo $tmpl_data->title ?>" />
						</td>
						<td></td>
					</tr>
					<tr>
						<td class="wmtLabel" colspan="3"
							style="width: 200px; white-space: nowrap"><?php echo xlt('Template Description'); ?>:<br />
							<textarea required class="wmtFullInput form-control" name="tmpl_description"
								id="tmpl_description" rows="2"><?php echo $tmpl_data->description ?></textarea>
						</td>
					</tr>
<?php 
$tag_count = count($tags);
if ($tag_count > 0) {
	$tag_split = ceil($tag_count / 3);
?>
					<tr>
						<td class="wmtLabel" colspan="3"
							style="width: 200px;"><?php echo xlt('Available Template Tags'); ?>:<br>
							<table class="wmtFullInput" style="height: auto !important;">
								<tr>
									<td style="white-space:nowrap">
<?php 
$tag_current = 1;
foreach ($tags AS $tag => $value) {
	echo '<div style="width:160px;display:inline-block;font-weight:bold">['.$tag.']</div> '.$value.'<br>';
	if ($tag_current++ >= $tag_split) {
		echo '</td><td style="white-space:nowrap">';
		$tag_current = 1;
	}
}
?>									
									</td>
								</tr>
							</table>
						</td>
					</tr>
<?php 
} ?>
					<tr>
						<td class="wmtLabel" colspan="3" style="width: 200px; white-space: nowrap">Template Content<br />
							<textarea required name="tmpl_content" id="tmpl_content" style="width: 90%;"
								rows="30"><?php echo $tmpl_data->html_body ?></textarea>
						</td>
					</tr>
				</table>
			</div>
				
<?php } ?>

			</div>
	</form>
</body>

<script>
		tinymce.init({
			entity_encoding : "raw",
			selector: "#tmpl_content",
			theme : "modern",
			mode : "exact",
			br_in_pre : false,
			force_br_newlines : true,
			force_p_newlines : false,
			forced_root_block : false,
			content_css : "<?php echo $GLOBALS['web_root'] ?>/interface/reports/portal/tinymce.css",
			relative_urls : false,
			document_base_url : "<?php echo $GLOBALS['web_root'] ?>/",
			plugins  : "visualblocks visualchars image link media template code codesample table hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools contextmenu colorpicker textpattern",
			toolbar1 : "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
			toolbar2 : "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | insertdatetime preview | forecolor backcolor",
			toolbar3 : "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | visualchars visualblocks nonbreaking template pagebreak restoredraft | code",
//			toolbar1 : "formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat",
			toolbar_items_size : "small",
			templates : [
				{ title: 'PDF Document', description: 'Default layout for PDF documents', url: 'pdf_template.html' }
			],
			menubar : false
		});
	</script>
</html>
