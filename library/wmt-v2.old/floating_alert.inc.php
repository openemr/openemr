<?php
include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
if(isset($GLOBALS['wmt::pat_entry_portal'])) $portal_enabled = $GLOBALS['wmt::pat_entry_portal'];
$alerts = LoadList($frmdir.'_top_alert','active','seq');
?>

<div class="form-menu-list" style="position: fixed; top: 0; right: 0; z-index: 4999; vertical-align: top;">

	<ul class="form-meun-list">

	<!-- IF WE HAVE TASKS/ACTIONS DEFINED AS QUICK LINKS BUILD THAT LIST -->
	<?php if(count($alerts) > 1) { ?>
	<li><a href="javascript:;" tabindex="-1" onclick="alertOpen('quick_info');" >Quick Info</a>
		<div id="quick_info" style="display: none; width: 100%;"><table border="0" cellspacing="0" cellpadding="0" style="width: 100%;">
		<?php foreach($alerts as $alert) { ?>
			<tr><td style="border-top: 1px solid #000000; padding: 0px;">
			<?php 
			echo htmlspecialchars($alert['title'],ENT_QUOTES,'',FALSE);
			echo ':&nbsp;';
			include($GLOBALS['srcdir'].'/wmt-v2/quick_view/'.$alert['option_id'].'.inc');
			 ?>
			</td></tr>
		<?php } ?>
		</table></div>
	</li>
	<?php } else if(count($alerts) == 1) { ?>
	<li>
		<?php foreach($alerts as $alert) { ?>
			<span style="border-top: 1px solid #000000; padding: 0px;">
			<?php 
			echo htmlspecialchars($alert['title'],ENT_QUOTES,'',FALSE);
			echo ':&nbsp;';
			include($GLOBALS['srcdir'].'/wmt-v2/quick_view/'.$alert['option_id'].'.inc');
			 ?>
			</span>
		<?php } ?>
	</li>
	<?php } ?>

</div>
<script type="text/javascript">
//  THIS IS FOR THE LINK DROP DOWN OPTIONS
var timeout	= 500;
var closetimer	= 0;
var link	= 0;
var oldlink = 0;
var flag = 0;

function alertOpen(id)
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

function alertClose()
{
	if(flag == 10) {
		flag=11;
		return;
	}
	if(link) link.style.visibility = 'hidden';
	if(link) link.style.display = 'none';
}

// CLOSE THE LINK LAYER WHEN CLICK OUTSIDE
document.onclick = alertClose;
//=================================================
</script>

