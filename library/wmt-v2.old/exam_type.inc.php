<?php
include_once($GLOBALS['srcdir'].'/translation.inc.php');
if(!isset($frmdir)) $frmdir = '';
if(!isset($prefix)) $prefix = '';
if(!isset($dt{$prefix.'form_type'})) $dt{$prefix.'form_type'} = '';
$filter = 'AND (UPPER(notes) LIKE "%ALL FORMS%"';
if($frmdir) $filter .= ' OR notes LIKE "%'.$frmdir.'%"'; 
$filter .= ') ';
$types = LoadList('Exam_Form_Visit_Types', 'active', 'seq', '', $filter);
?>
<div style="margin: 28px 6px 6px 12px;">
  <table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
			<td class="bkkLabel" style="width: 100px;"><?php xl('Exam Type','e'); ?>:&nbsp;&nbsp;&nbsp;</td>
			<td class="bkkBody">
			<?php foreach($types as $type) { ?>
				&nbsp;&nbsp;<input type="radio" name="<?php echo $prefix; ?>form_type" id="<?php echo $prefix; ?>form_type_<?php echo $type['option_id']; ?>" value="<?php echo $type['option_id']; ?>" <?php echo $dt[$prefix.'form_type'] == $type['option_id'] ? 'checked' : ''; ?> onchange="loadExamType('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>','<?php echo $prefix; ?>','<?php echo $type['option_id']; ?>','<?php echo $type['title']; ?>');"/>
				&nbsp;<label for="<?php echo $prefix; ?>form_type_<?php echo $type['option_id']; ?>"><?php xl($type['title'], 'e'); ?></label>
			<?php } ?>
			</td>
	</tr>
	</table>
</div>

<script type="text/javascript">

function loadExamType(base, wrap, formID, prefix, type, title) {
	if(document.forms[0].elements[prefix + 'form_type_' + type].checked == false) return false;
	response = confirm("Load the data from the most recent " + title + 
		" Exam into this form?\n\n\t\tCurrent data in the form will be overwritten.");
	if(response == false) return false;
	var myAction = base + '&mode=new&continue=true&type='+type+'&wrap='+wrap;
	if(formID != '' && formID != 0) myAction = myAction+'&id='+formID;
	document.forms[0].action = myAction;
	document.forms[0].submit();
}

</script>
