<body class="bgcolor2" onLoad="<?php echo $load; ?>">
<div id="save-notification" class="notification" style="left: 45%; top: 40%; <?php echo $save_notification_display; ?>"><?php xl('Processing','e'); ?>....</div>

<div id="pageLoader" class="loaderContainer fixedContainer backWhite" style="display:none;">
	<div class="spinner-border"></div>
</div>

<div id="overDiv" style="position:absolute; visibility: hidden; z-index:3000;"></div>
<form action="<?php echo $GLOBALS['rootdir'].$save_style; ?>" method="post" enctype="multipart/form-data" name="<?php echo $frmdir; ?>" id="<?php echo $frmdir; ?>" class="m-0" >
<input name="tmp_scroll_top" id="tmp_scroll_top" type="hidden" value="<?php echo $dt['tmp_scroll_top']; ?>" />
<div class='small-form'><!-- THIS IS THE OVERALL BODY START -->

<?php include($GLOBALS['srcdir'].'/wmt-v2/floating_menu_bs.inc.php'); ?>

<div class="mx-3 my-0"><!-- THIS IS THE OVERALL INNER CONTAINER START -->

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtstandard.popup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/cpt.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/lists.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/diagnosis.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<?php include($GLOBALS['srcdir'].'/wmt-v2/form_top_title_bs.inc.php'); ?>

<!-- Commented -->
<input name="proc_type" id="proc_type" type="hidden" value="CPT4">
<input name="proc_on_fee" id="proc_on_fee" type="hidden" value="1">
<input name="proc_title" id="proc_title" class="wmtFullInput" type="text" value="" title="Enter a brief description of the procedure here">
<input name="proc_code" id="proc_code" class="wmtFullInput" type="text" value="" onclick="get_cpt(&quot;proc_code&quot;,&quot;proc_title&quot;, &quot;&quot;, &quot;&quot;, &quot;&quot;, &quot;proc_type&quot;);" title="Click to select a procedure">

<a href="javascript:;" tabindex="-1" onclick="wmtOpen('../../../custom/DFT_queue_popup.php?pid=<?php echo $pid; ?>&enc=171', '_blank', 800, 600);" >Resend HL7</a>


<input name="code" id="code" type="text" class="wmtAltInput" style="width: 89px; " value="" onclick="get_diagnosis('code', 'title');" title="Click to Open The Code Search Box">
<select name="grp" id="grp" class="wmtAltInput"><option value="">&nbsp;</option><option value="general">General</option></select>
<input name="title" id="title" type="text" class="wmtAltInput" style="width: 98%; " value="">

<input name="case_header_case_guarantor_pid" id="case_header_case_guarantor_pid" type="text" class="form-control input-sm" onclick="<?php echo "select_patient('" . $GLOBALS['webroot'] . "');" ?>" value="16">

<a class="css_button_small btn btn-primary" tabindex="-1" onclick="get_hpi('hpi');" href="javascript:;"><span>Select Another HPI</span></a>

<a href="javascript: refresh_links('do_cc','do_hpi','do_img','do_sh','do_all','do_ps','do_meds','do_med_hist','do_imm','do_well_full','do_hosp','do_pmh','do_fh','do_ros2','do_ortho_exam','do_review_nt','do_general_exam2','do_instruct','do_assess','do_diag');" tabindex="-1" class="floating-menu-link">Re-Link</a>

<?php
$when_href = $GLOBALS['webroot'].
	  '/custom/otc_when_popup.php?list=Portal_OTC_Times';
?>

<a href="#" onclick="build_when_link('<?php echo $when_href; ?>','<?php echo "1"; ?>');">Test Link</a>

<a class="css_button_small btn btn-primary" tabindex="-1" onclick="get_pin('hpi');" href="javascript:;"><span>get_pin</span></a>

<script type="text/javascript">
	function get_hpi() {
	 wmtOpen('../../../custom/hpi_choice_popup.php', '_blank', 800, 400);
	}

	// This invokes the refresh popup.
	function refresh_links() {
	 var link_base = '<?php echo $GLOBALS['webroot']; ?>/custom/link_refresh_popup.php?encounter=<?php echo $encounter; ?>&pid=<?php echo $pid; ?>';
	 for (var cnt = 0; cnt < arguments.length; cnt++ ) {
			link_base = link_base + '&' + arguments[cnt] + '=true';
	 }
	 SetScrollTop();
	 wmtOpen(link_base, '_blank', 400, 300);
	 var refresh_action='<?php echo $base_action; ?>&mode=relink&wrap=<?php echo $wrap_mode; ?>';
	 <?php
	 if($id) {
		echo "refresh_action = refresh_action + '&id=$id';\n";
	 }
	 ?>
	 document.forms[0].action= refresh_action;
	 delayedHideDiv();
	 document.forms[0].submit();
	 window.location.reload;
	}

	function build_when_link(base, itemID) {
	var lbl = 'otc_extrainfo';
	if(itemID) lbl += '_' + itemID;
	//base += '&extra=' + document.getElementById(lbl).value;
	base += '&extra=fgfg';
	if(itemID) base += '&item=' + itemID;
	wmtOpen(base,'_blank',400,400);
}

function get_pin()
{
 //document.forms[0].elements['pin_verified'].value='';
 var target = '../../../custom/pin_check_popup.php?username=user';
 dlgopen(target, '_blank', 300, 200);
}

</script>
