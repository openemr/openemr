<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($pat_entries_exist)) $pat_entries_exist = false;

$local_fields = array( 'last_db_screen', 'last_db_eye', 'last_db_foot',
	'last_glaucoma', 'last_db_dbsmt' );
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}

$dated_doc_id = array();
$linked_docs = array('last_db_eye', 'last_db_foot');
// FIND ANY LINKED DOCUMENTS
foreach($linked_docs as $tmp) {
	$dated_doc_id[$tmp] = getLinkedDocument($pid, $tmp);
}
?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Diabetes Related&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Last Diabetes Screening:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_db_screen" id="<?php echo $field_prefix; ?>last_db_screen" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_db_screen'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_db_screen" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_db_screen", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_db_screen"});
			</script>
			<td style="width: 22%;">Last Diabetic Eye Exam:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_db_eye" id="<?php echo $field_prefix; ?>last_db_eye" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_db_eye'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_db_eye" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_db_eye", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_db_eye"});
			</script>
			<td style="width: 22%;">Last Diabetic Foot Exam:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_db_foot" id="<?php echo $field_prefix; ?>last_db_foot" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_db_foot'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_db_foot" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_db_foot", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_db_foot"});
			</script>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_db_screen','last_db_eye','last_db_foot');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_db_screen" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_db_screen']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_db_eye" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_db_eye']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_db_foot" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_db_foot']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>
		<tr>
			<td colspan="2">&nbsp;</td>
			<td>&nbsp;</td>
		<?php // BEGINNING OF THE LAST DIABETIC EYE EXAM LINK HANDLER
		$href_text = '';
		if($dated_doc_id['last_db_eye']) { 
			$d = new Document($dated_doc_id['last_db_eye']);
			$url = $d->get_url();
			//strip url of protocol handler
			$url = preg_replace("|^(.*)://|","",$url);
			$from_all = explode("/", $url);
			$from_filename = array_pop($from_all);
			$category = $d->get_ccr_type($dated_doc_id['last_db_eye']);
			$href_text = $from_filename . "  (" . $category . ")";
		}
		?>
			<td colspan="2" id="link_<?php echo $field_prefix; ?>last_db_eye_doc">
			<?php if($href_text) { ?>
			Document: <a href="javascript:;" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/controller.php?document&retrieve&patient_id=<?php echo $pid; ?>&document_id=<?php echo $dated_doc_id['last_db_eye']; ?>&as_file=false', '_blank', 800, 600);"><i><?php echo htmlspecialchars($href_text, ENT_QUOTES); ?></i></a>
			<?php } else { ?>
			<i>No document is currently attached</i>
			<?php } ?>
			&nbsp;</td>
			<td><div id="img_<?php echo $field_prefix; ?>last_db_eye_div">
			<?php if($dated_doc_id['last_bone']) { ?>
			<img src="<?php echo $GLOBALS['webroot']; ?>/images/link_break.png" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_db_eye_doc_unlink" border="0" alt="[x]" style="vertical-align: bottom; cursor:pointer;" onclick="breakLink('<?php echo $dated_doc_id['last_db_eye']; ?>', '<?php echo $pid; ?>', 'last_db_eye', '<?php echo $field_prefix; ?>');" title"Click here to remove the link">&nbsp;
			<?php } else { ?>
			<img src="<?php echo $GLOBALS['webroot']; ?>/images/link_add.png" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_db_eye_doc" border="0" alt="[d]" style="vertical-align: bottom; cursor:pointer;" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/custom/document_popup.php?pid=<?php echo $pid; ?>&task=link&link_type=last_db_eye&prefix=<?php echo $field_prefix; ?>', '_blank', 800, 600);" title"Click here to link a document">&nbsp;
			<?php } ?>	
			</div></td>

		<?php // BEGINNING OF THE LAST DIABETIC FOOT EXAM LINK HANDLER
		$href_text = '';
		if($dated_doc_id['last_db_foot']) { 
			$d = new Document($dated_doc_id['last_db_foot']);
			$url = $d->get_url();
			//strip url of protocol handler
			$url = preg_replace("|^(.*)://|","",$url);
			$from_all = explode("/", $url);
			$from_filename = array_pop($from_all);
			$category = $d->get_ccr_type($dated_doc_id['last_db_foot']);
			$href_text = $from_filename . "  (" . $category . ")";
		}
		?>
			<td colspan="2" id="link_<?php echo $field_prefix; ?>last_db_foot_doc">
			<?php if($href_text) { ?>
			Document: <a href="javascript:;" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/controller.php?document&retrieve&patient_id=<?php echo $pid; ?>&document_id=<?php echo $dated_doc_id['last_db_foot']; ?>&as_file=false', '_blank', 800, 600);"><i><?php echo htmlspecialchars($href_text, ENT_QUOTES); ?></i></a>
			<?php } else { ?>
			<i>No document is currently attached</i>
			<?php } ?>
			&nbsp;</td>
			<td><div id="img_<?php echo $field_prefix; ?>last_db_foot_div">
			<?php if($dated_doc_id['last_db_foot']) { ?>
			<img src="<?php echo $GLOBALS['webroot']; ?>/images/link_break.png" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_db_foot_doc_unlink" border="0" alt="[x]" style="vertical-align: bottom; cursor:pointer;" onclick="breakLink('<?php echo $dated_doc_id['last_db_foot']; ?>', '<?php echo $pid; ?>', 'last_db_foot', '<?php echo $field_prefix; ?>');" title"Click here to remove the link">&nbsp;
			<?php } else { ?>
			<img src="<?php echo $GLOBALS['webroot']; ?>/images/link_add.png" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_db_foot_doc" border="0" alt="[d]" style="vertical-align: bottom; cursor:pointer;" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/custom/document_popup.php?pid=<?php echo $pid; ?>&task=link&link_type=last_db_foot&prefix=<?php echo $field_prefix; ?>', '_blank', 800, 600);" title"Click here to link a document">&nbsp;
			<?php } ?>	
			</div></td>
		</tr>

		<tr>
			<td>Last Glaucoma Screening:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_glaucoma" id="<?php echo $field_prefix; ?>last_glaucoma" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_glaucoma'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_glaucoma" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_glaucoma", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_glaucoma"});
			</script>
			<td>Last Self-Management Training:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_db_dbsmt" id="<?php echo $field_prefix; ?>last_db_dbsmt" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_db_dbsmt'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_db_dbsmt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_db_dbsmt", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_db_dbsmt"});
			</script>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_glaucoma','last_db_dbsmt');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_glaucoma" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_glaucoma']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_db_dbsmt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_db_dbsmt']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>
	</table>
</fieldset>
