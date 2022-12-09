<?php
if(isset($local_fields)) unset($local_fields);
$local_fields = array('ddoc_doc_id', 'ddoc_dt', 'ddoc_type', 'ddoc_title',
	'ddoc_nt');
include(FORM_BRICKS . 'module_setup.inc.php');
$note_field = '';
$portal_data_exists = FALSE;
if(!isset($unlink_allow)) $unlink_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients','med');
if($frmdir == 'dashboard') {
	$unlink_allow = FALSE;
	$encounter = '';
}
$dd_use_ajax = checkSettingMode('wmt::dated_documents_use_ajax','',$frmdir);
$module_tag = 'ddoc';
$item_id_tag = 'ddoc_id_';

if(($first_pass && $form_mode == 'new') || $frmdir == 'dashboard') {
	$ddoc = GetList($pid, 'dated_document');
} else {
	$ddoc = GetList($pid, 'dated_document', $encounter);
}

// BUILD THE BUTTONS HERE TO MAKE IT MORE LEGIBLE BELOW
// echo "Encounter: $encounter  Mode:: ",$dt['tmp_diag_window_mode'],"<br>\n";
if($dd_use_ajax) {
	// $base_action = $GLOBALS['rootdir']."/forms/$frmdir/save.php?mode=save&enc=$encounter&pid=$pid&wrap=$wrap_mode";
	if($id) $base_action .= "&id=$id";
	$link_btn = FORM_BUTTONS . 'btn_link_ajax.inc.php';
	$unlink_btn = FORM_BUTTONS . 'btn_unlink_ajax.inc.php';
	$show_all_btn = "<a class='css_button' tabindex='-1' onclick='return ajaxIssueRefresh(\"$encounter\",\"$pid\",\"$frmdir\",\"$id\",\"dated_document\",\"all\",\"Dated Document\",\"$target_container\",\"dd_\",\"$wrap_mode\",\"".$dt['tmp_dd_window_mode']."\");' href='javascript:;'><span>Show All</span></a>";
	$show_curr_btn = "<a class='css_button' tabindex='-1' onclick='return ajaxIssueRefresh(\"$encounter\",\"$pid\",\"$frmdir\",\"$id\",\"dated_document\",\"current\",\"Dated Document\",\"$target_container\",\"dd_\",\"$wrap_mode\",\"".$dt['tmp_dd_window_mode']."\");' href='javascript:;'><span>Show Current</span></a>";
	$show_enc_btn = "<a class='css_button' tabindex='-1' onclick='return ajaxIssueRefresh(\"$encounter\",\"$pid\",\"$frmdir\",\"$id\",\"dated_document\",\"encounter\",\"Dated Document\",\"$target_container\",\"dd_\",\"$wrap_mode\",\"".$dt['tmp_dd_window_mode']."\");' href='javascript:;'><span>Only This Encounter</span></a>";
	$add_btn = "<a class='css_button' tabindex='-1' onclick='return ajaxIssueRefresh(\"$encounter\",\"$pid\",\"$frmdir\",\"$id\",\"dated_document\",\"add\",\"Dated Document\",\"$target_container\",\"dd_\",\"$wrap_mode\",\"".$dt['tmp_dd_window_mode']."\");' href='javascript:;'><span>Add Another</span></a>";
	$unlink_all_btn = "<div style='float: right;'><a class='css_button' tabindex='-1' id='dd_link_all_btn' onclick='return ajaxIssueLinkAll(\"dd_\",\"$encounter\",\"$pid\",\"unlink\",\"dated_document\",\"tmp_dd_cnt\");' href='javascript:;'><span>Unlink ALL Documents</span></a></div>";
} else {
	$max = count($ddoc);
	$link_btn = FORM_BUTTONS . 'btn_link.inc.php';
	$unlink_btn = FORM_BUTTONS . 'btn_unlink.inc.php';
	$show_all_btn = "<a class='css_button' tabindex='-1' onclick='return ToggleDDocWindowMode(\"$base_action\",\"$wrap_mode\",\"$id\",\"all\");' href='javascript:;'><span>Show All</span></a>";
	$show_curr_btn = "<a class='css_button' tabindex='-1' onclick='return ToggleDDocWindowMode(\"$base_action\",\"$wrap_mode\",\"$id\",\"current\");' href='javascript:;'><span>Show Current</span></a>";
	$show_enc_btn = "<a class='css_button' tabindex='-1' onclick='return ToggleDDocWindowMode(\"$base_action\",\"$wrap_mode\",\"$id\",\"encounter\");' href='javascript:;'><span>Only This Encounter</span></a>";
	$add_btn = "<a class='css_button' tabindex='-1' onclick='return SubmitLinkBuilder(\"$base_action\",\"$wrap_mode\",\"\",\"$id\",\"ddoc\",\"ddoc_id_\",\"Dated Document\");' href='javascript:;'><span>Add Another</span></a>";
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="3" style="border-collapse: collapse;">
	<tr>
		<td style="width: 70px;">&nbsp;</td>
		<td style="width: 90px; padding-left: 12px;"><b><?php echo attr(xl('Date')); ?></b></td>
		<td style="padding-left: 12px;"><b><?php echo attr(xl('Type')); ?></b></td>
		<td><b><?php echo attr(xl('Title')); ?></b></td>
		<td><b><?php echo attr(xl('Notes')); ?></b></td>
<?php if($delete_allow && $unlink_allow) { ?>
		<td style="width: 175px">&nbsp;</td>
<?php } else if($unlink_allow) { ?>
		<td style="width: 115px">&nbsp;</td>
<?php } else if($delete_allow || $portal_mode) { ?>
		<td style="width: 115px">&nbsp;</td>
<?php } else { ?>
		<td style="width: 65px">&nbsp;</td>
<?php } ?>
	</tr>
<?php
$cnt = 0;
$bg = '#DBDBDB';
foreach($ddoc as $prev) {
?>
	<tr style="background-color: <?php echo $bg; ?>">
		<td><div style="float: left; padding-left: 6px;"><a class="css_button" href="javascript:;" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/controller.php?document&retrieve&patient_id=<?php echo $pid; ?>&document_id=<?php echo $prev['reinjury_id']; ?>&as_file=false', '_blank', 800, 600);"><span>View</span></a></div>
		</td>
		<td>&nbsp;<input name="ddoc_id_<?php echo $cnt; ?>" id="ddoc_id_<?php echo $cnt; ?>" type="hidden" value="<?php echo $prev['id']; ?>" /><input name="ddoc_doc_id_<?php echo $cnt; ?>" id="ddoc_doc_id_<?php echo $cnt; ?>" type="hidden" value="<?php echo $prev['reinjury_id']; ?>" /><input name="ddoc_num_links_<?php echo $cnt; ?>" id="ddoc_num_links_<?php echo $cnt; ?>" type="hidden" tabindex="-1" value="<?php echo $prev['num_links']; ?>" />
			<input name="ddoc_dt_<?php echo $cnt; ?>" id="ddoc_dt_<?php echo $cnt; ?>" class="wmtDateInput" type="text" value="<?php echo attr(oeFormatShortDate($prev['begdate'])); ?>" title="Enter Date As <?php echo $date_title_fmt; ?>" />
		</td>
		<td>&nbsp;<input name="ddoc_type_<?php echo $cnt; ?>" id="ddoc_type_<?php echo $cnt; ?>" class="wmtFullInput" tabindex="-1" type="text" readonly="readonly" value="<?php echo attr($prev['extrainfo']); ?>" />
		</td>
		<td>&nbsp;<input name="ddoc_title_<?php echo $cnt; ?>" id="ddoc_title_<?php echo $cnt; ?>" class="wmtFullInput" tabindex="-1" type="text" readonly="readonly" value="<?php echo attr($prev['title']); ?>" />
		</td>
		<td>
		<?php if ($portal_mode && ($prev['classification'] != 9)) { ?>
			<span>&nbsp;<?php echo attr($prev['comments']); ?></span>
		<?php } else { ?>
			<input name="ddoc_nt_<?php echo $cnt; ?>" id="ddoc_nt_<?php echo $cnt; ?>" class="wmtFullInput" type="text" value="<?php echo attr($prev['comments']); ?>" />
		<?php } ?>
		</td>
		<td>
			
		<?php if(!$portal_mode || ($portal_mode && ($prev['classification'] == 9))) { ?>
			<!-- div style="float: left; padding-left: 2px;"><a class="css_button_small" tabindex="-1" onClick="SubmitLinkBuilder('<?php // echo $base_action; ?>','<?php // echo $wrap_mode; ?>','<?php // echo $cnt; ?>','<?php // echo $id; ?>','updateddoc','ddoc_id_','Dated Document');" href="javascript:;"><span>Update</span></a></div -->
		<?php } ?>
		<?php if($unlink_allow) { ?>
			<div style="float: left; padding-left: 2px;"><a class="css_button_small" tabindex="-1" onClick="SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinkddoc','ddoc_id_','Document');" href="javascript:;"><span>Un-Link</span></a></div>
		<?php } ?>
		<?php if($delete_allow || ($portal_mode && $prev['classification'] == 9)) { ?>
			<div style="float: left; padding-left: 2px;"><a class="css_button_small" tabindex="-1" onClick="SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','delddoc','ddoc_id_','Document','<?php echo $prev['num_links']; ?>');" href="javascript:;"><span>Delete</span></a></div>
		<?php } ?>
		&nbsp;</td>
	</tr>
<?php
	if($prev['classification'] == 9) $portal_data_exists = true;
	$bg = ($bg == '#DBDBDB') ? '#F4F4F4' : '#DBDBDB';
	$cnt++;
}
?>

	<tr style="background-color: <?php echo $bg; ?>">
		<td><input name="tmp_ddoc_cnt" id="tmp_ddoc_cnt" type="hidden" tabindex="-1" value="<?php echo ($cnt - 1); ?>" /><div style="float: left; padding-left: 6px;"><a class="css_button" href="javascript:;" onclick="wmtOpen('../../../custom/document_popup.php?pid=<?php echo $pid; ?>&task=link&item_id=<?php echo $prev['id']; ?>&prefix=ddoc_&cnt=<?php echo $cnt; ?>', '_blank', 800, 600);"><span>Link</span></a></div>
		</td>
		<td><input name="ddoc_dt" id="ddoc_dt" type="text" class="wmtDateInput" value="<?php echo attr(oeFormatShortDate($dt['ddoc_dt'])); ?>" /><input name="ddoc_doc_id" id="ddoc_doc_id" type="hidden" value="<?php echo attr($dt['ddoc_doc_id']); ?>" /></td>
		<td><input name="ddoc_type" id="ddoc_type" readonly="readonly" type="text" style="width: 96%;" value="<?php echo attr($dt['ddoc_type']); ?>" />
		</td>
		<td><input name="ddoc_title" id="ddoc_title" readonly="readonly" type="text" style="width: 96%;" value="<?php echo attr($dt['ddoc_title']); ?>" />
		</td>
		<td><input name="ddoc_nt" id="ddoc_nt" type="text" style="width: 96%;" value="<?php echo attr($dt['ddoc_nt']); ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr class="wmtColorBar">
		<td colspan="2"><div style="float: left;"><a class="css_button" onClick="SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','ddoc');" href="javascript:;"><span>Add Another</span></a></div>
		<?php if(!$portal_mode) { ?>
		<?php } ?>
		&nbsp;</td>
		<td>
		<?php if(!$portal_mode && $portal_data_exists) { ?>
		<div style="float: right; padding-right: 12px;"><b><i>** Highlighted items have been entered through the portal, 'Update' to Verify/Accept</i></b></div>
		<?php } ?>
		&nbsp;</td>
		<td colspan="3">&nbsp;</td>
	</tr>
</table>

<?php include($GLOBALS['srcdir'].'/wmt-v2/list_note_section.inc.php'); ?>
