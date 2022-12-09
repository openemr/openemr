<?php
include_once($GLOBALS['srcdir'].'/wmt-v2/printpat.class.php');
$patient = wmtPrintPat::getPatient($pid);
// echo "Searching for Form: $frmn ($id)  Patient: $pid  Encounter: $encounter<br>\n";
$content=GetFormFromRepository($pid, $encounter, $id, $frmn);
if(!isset($print_title)) { 
	$print_title = 'Examination Form';
	if(isset($ftitle)) $print_title = $ftitle;
}
if(!isset($pop_form)) $pop_form = false;
if(!isset($print_date) && isset($dt['form_dt'])) $print_date = $dt['form_dt'];
if(!$print_date) $print_date = date('Y-m-d');
if(!isset($print_instruct_href)) $print_instruct_href = '';
if(!isset($print_referral_href)) $print_referral_href = '';
if(!isset($print_summary_href)) $print_summary_href = '';

echo "<html>\n";
echo "<head>\n";
echo "<title>$print_title for $patient->full_name on $print_date</title>\n";
?>
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header']; ?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtreport.css" type="text/css">
<?php if(isset($print_css)) { ?>
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/<?php echo $print_css; ?>" type="text/css">
<?php } ?>
</head>
<body>

<?php echo $content; ?>
<br>
<div class="wmtNoPrint" style="float: left; padding-left: 10px;"><a class="css_button wmtNoPrint" tabindex="-1" href="<?php echo $print_href; ?>" <?php echo $pop_form ? '' : 'target="_blank" onclick="top.restoreSession();"'; ?>><span class="wmtNoPrint">Print Form</span></a></div>
<?php if($print_instruct_href) {; ?>
<div style="float: left; padding-left: 30px;"><a href="<?php echo $print_instruct_href; ?>" tabindex="-1" target="_blank" class="css_button"><span>Print Patient Instructions</span></a></div>
<?php } ?>

<?php if($print_referral_href) {; ?>
<div style="float: left; padding-left: 30px;"><a href="javascript:;" onclick="wmtOpen('<?php echo $print_referral_href; ?>', '_blank', 900, 900, 0);" tabindex="-1" class="css_button"><span>Print Referral Letter</span></a></div>
<?php } ?>
<?php if($print_summary_href) {; ?>
<div style="float: left; padding-left: 30px;"><a href="<?php echo $print_summary_href; ?>" tabindex="-1" target="_blank" class="css_button"><span>Print Patient Summary</span></a></div>
<?php } ?>
<div class="wmtNoPrint" style="float: right; padding-right: 10px;"><a href="<?php echo $pop_form ? 'javascript:;' : $GLOBALS['form_exit_url']; ?>" class="css_button wmtNoPrint" tabindex="-1" onclick="<?php echo $pop_form ? 'window.close();' : 'top.restoreSession();'; ?>" ><span class="wmtNoPrint">Cancel</span></a></div>
<br>
</body>
<script type="text/javascript" src="../../../library/wmt-v2/wmtpopup.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
</html>
