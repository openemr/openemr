<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
$local_fields = array( 'last_colon', 'last_fecal', 'last_barium', 
	'last_sigmoid', 'last_psa', 'last_rectal');

foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
$dated_doc_id = array();
$linked_docs = array('last_colon');
// FIND ANY LINKED DOCUMENTS
foreach($linked_docs as $tmp) {
	$dated_doc_id[$tmp] = getLinkedDocument($pid, $tmp);
}
?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Colon&nbsp;<?php echo $pat_sex == 'f' ? '' : '&amp;&nbsp;Prostate&nbsp;'; ?></legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Last Colonoscopy/Cologuard:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_colon" id="<?php echo $field_prefix; ?>last_colon" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_colon'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_colon" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_colon", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_colon"});
				</script>
			<td style="width: 22%;">Last Fecal Occult Blood Test:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_fecal" id="<?php echo $field_prefix; ?>last_fecal" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_fecal'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_fecal" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_fecal", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_fecal"});
				</script>
			<td style="width: 22%;">Last Barium Enema:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_barium" id="<?php echo $field_prefix; ?>last_barium" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_barium'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_barium" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_barium", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_barium"});
				</script>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_colon','last_fecal','last_barium');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_colon" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_colon']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_fecal" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_fecal']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_barium" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_barium']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>
		<tr>
		<?php // BEGINNING OF THE LAST COLONOSCOPY LINK HANDLER
		$href_text = '';
		if($dated_doc_id['last_colon']) { 
			$d = new Document($dated_doc_id['last_colon']);
			$url = $d->get_url();
			//strip url of protocol handler
			$url = preg_replace("|^(.*)://|","",$url);
			$from_all = explode("/", $url);
			$from_filename = array_pop($from_all);
			$category = $d->get_ccr_type($dated_doc_id['last_colon']);
			$href_text = $from_filename . "  (" . $category . ")";
		}
		?>
			<td colspan="2" id="link_<?php echo $field_prefix; ?>last_colon_doc">
			<?php if($href_text) { ?>
			Document: <a href="javascript:;" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/controller.php?document&retrieve&patient_id=<?php echo $pid; ?>&document_id=<?php echo $dated_doc_id['last_colon']; ?>&as_file=false', '_blank', 800, 600);"><i><?php echo htmlspecialchars($href_text, ENT_QUOTES); ?></i></a>
			<?php } else { ?>
			<i>No document is currently attached</i>
			<?php } ?>
			&nbsp;</td>
			<td><div id="img_<?php echo $field_prefix; ?>last_colon_div">
			<?php if($dated_doc_id['last_colon']) { ?>
			<img src="<?php echo $GLOBALS['webroot']; ?>/images/link_break.png" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_colon_doc_unlink" border="0" alt="[x]" style="vertical-align: bottom; cursor:pointer;" onclick="breakLink('<?php echo $dated_doc_id['last_colon']; ?>', '<?php echo $pid; ?>', 'last_colon', '<?php echo $field_prefix; ?>');" title"Click here to remove the link">&nbsp;
			<?php } else { ?>
			<img src="<?php echo $GLOBALS['webroot']; ?>/images/link_add.png" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_colon_doc" border="0" alt="[d]" style="vertical-align: bottom; cursor:pointer;" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/custom/document_popup.php?pid=<?php echo $pid; ?>&task=link&link_type=last_colon&prefix=<?php echo $field_prefix; ?>', '_blank', 800, 600);" title"Click here to link a document">&nbsp;
			<?php } ?>	
			</div></td>
			
			<td colspan="6">&nbsp;</td>
		</tr>

		<tr>
			<td>Last Flexible Sigmoidoscopy:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_sigmoid" id="<?php echo $field_prefix; ?>last_sigmoid" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_sigmoid'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
		 	<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_sigmoid" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_sigmoid", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_sigmoid"});
				</script>
			<?php if($pat_sex == 'f') { ?>
			<?php } else { ?>
			<td>Last PSA:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_psa" id="<?php echo $field_prefix; ?>last_psa" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_psa'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_psa" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_psa", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_psa"});
				</script>
				<td>Last Rectal Exam:</td>
				<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_rectal" id="<?php echo $field_prefix; ?>last_rectal" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_rectal'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
				<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_rectal" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_rectal", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_rectal"});
				</script>
			<?php } ?>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			if($pat_sex == 'f') {
				$keys = array('last_sigmoid');
			} else {
				$keys = array('last_sigmoid','last_psa','last_rectal');
			}
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_sigmoid" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_sigmoid']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<?php if($pat_sex == 'f') { ?>
				<?php } else { ?>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_psa" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_psa']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_rectal" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_rectal']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
					}
				}
			}
			?>
	</table>
</fieldset>
