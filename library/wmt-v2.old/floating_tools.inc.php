<?php
include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
$actions = LoadList($frmdir.'_actions','active','seq','','AND UPPER(notes) LIKE "%MENU%"');
?>

<div class="form-menu" style="position: fixed; top: 0; right: 0; z-index: 5000; vertical-align: top;">

	<ul class="form-menu-list">
	<?php if(checkSettingMode('wmt::float_tool_hl7_DFT_queue','',$frmdir)) { ?>
	<li><a href="javascript:;" tabindex="-1" onclick="wmtOpen('../../../custom/DFT_queue_popup.php?pid=<?php echo $pid; ?>&enc=<?php echo $encounter; ?>', '_blank', 800, 600);" >Resend HL7</a></li>
	<?php } ?>

</div>

<script type="text/javascript">
//  THIS IS FOR THE LINK DROP DOWN OPTIONS
var timeout	= 500;
var closetimer	= 0;
var link	= 0;
var oldlink = 0;
var flag = 0;

function tool_open(id)
{
	flag=10;

	oldlink = link;
	link = document.getElementById(id);
	if((link.style.visibility == '') || (link.style.visibility == 'hidden')) {
		if(oldlink) oldlink.style.visibility = 'hidden';
		if(oldlink) oldlink.style.display = 'none';
		link.style.visibility = 'visible';
		link.style.display = 'block';
	} else {
		link.style.visibility = 'hidden';
		link.style.display = 'none';
	}
}

function link_close()
{
	if(flag == 10) {
		flag=11;
		return;
	}
	if(link) link.style.visibility = 'hidden';
	if(link) link.style.display = 'none';
}

// CLOSE THE LINK LAYER WHEN CLICK OUTSIDE
document.onclick = link_close;
//=================================================

</script>

