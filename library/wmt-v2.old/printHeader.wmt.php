<?php 
include_once($GLOBALS['srcdir'].'/formatting.inc.php');
include_once($GLOBALS['srcdir'].'/translation.inc.php');
if(!isset($ftitle)) $ftitle = '';
if(!isset($suppress_head)) $suppress_head = '';
if(!isset($include_patient_info)) $include_patient_info = true;
$date_lbl = 'Visit Date';
if(!isset($form_date)) $form_date = '';
if($form_date == '') {
	$form_date = date('Y-m-d');
	$date_lbl = 'Date';
}
if(!isset($GLOBALS['wmt::print_phone_and_fax'])) $GLOBALS['wmt::print_phone_and_fax'] = false;
if(!isset($GLOBALS['wmt::print_website'])) $GLOBALS['wmt::print_website'] = false;
if(!isset($GLOBALS['wmt::print_email'])) $GLOBALS['wmt::print_email'] = false;
if(!isset($GLOBALS['wmt::print_ethnicity'])) $GLOBALS['wmt::print_ethnicity'] = false;
?>

<?php if(!$suppress_head) { ?>
<head>
<title>
<?php 
echo $ftitle . '&nbsp;for&nbsp;' . htmlspecialchars($patient->full_name, ENT_QUOTES) . '&nbsp;DOB:&nbsp;';
echo htmlspecialchars(oeFormatShortDate($patient->DOB), ENT_QUOTES);
echo '&nbsp;on&nbsp;';
echo htmlspecialchars(oeFormatShortDate($dt{'form_dt'}), ENT_QUOTES);
?>
</title>
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header']; ?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtprint.bkk.css" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtprint.css" type="text/css">
</head>
<?php } ?>

<body style="background: transparent;">
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
	<?php if($ftitle != '') { ?>
	<tr>
		<td class="wmtPrnLarge" style="font-weight: bold;"><?php echo $ftitle; ?></td>
	</tr>
	<?php } ?>
	<tr>
		<td class="wmtPrnLarge"><?php echo htmlspecialchars($facility->facility, ENT_QUOTES); ?></td>
	</tr>
  <tr>
    <td class="wmtPrnLarge"><?php echo htmlspecialchars($facility->addr, ENT_QUOTES); ?></td>
  </tr>
  <tr>
    <td class="wmtPrnLarge"><?php echo htmlspecialchars($facility->csz, ENT_QUOTES); ?></td>
  </tr>
	<?php if($GLOBALS['wmt::print_phone_and_fax']) { ?>
  <tr>
    <td class="wmtPrnLarge"><?php echo htmlspecialchars($facility->phone_fax, ENT_QUOTES); ?></td>
  </tr>
	<?php } ?>
	<?php if($GLOBALS['wmt::print_website']) { ?>
  <tr>
    <td class="wmtPrnBody wmtPrnC">Website Address: <?php echo htmlspecialchars($facility->website, ENT_QUOTES); ?></td>
  </tr>
	<?php } ?>
	<?php if($GLOBALS['wmt::print_email']) { ?>
  <tr>
    <td class="wmtPrnBody wmtPrnC">Email: <?php echo htmlspecialchars($facility->email, ENT_QUOTES); ?></td>
  </tr>
	<?php } ?>
</table>
<br>

<?php if($include_patient_info) { ?>
<div class="wmtPrnContainer">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="wmtPrnLabel"><?php echo $date_lbl; ?>:&nbsp;</td>
    <td class="wmtPrnLabel"><?php echo xl('Patient'); ?>:&nbsp;</td>
    <td class="wmtPrnLabel"><?php echo xl('DOB'); ?>:&nbsp;</td>
    <td class="wmtPrnLabel"><?php echo xl('Age'); ?>:&nbsp;</td>
		<?php if($GLOBALS['wmt::print_ethnicity']) { ?>
    <td class="wmtPrnLabel"><?php echo xl('Ethnicity'); ?>:&nbsp;</td>
		<?php } ?>
    <td class="wmtPrnLabel"><?php echo xl('Sex'); ?>:&nbsp;</td>
    <td class="wmtPrnLabel">ID:&nbsp;</td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars(oeFormatShortDate($form_date), ENT_QUOTES); ?>&nbsp;</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($patient->full_name, ENT_QUOTES); ?>&nbsp;</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars(oeFormatShortDate($patient->DOB), ENT_QUOTES);?>&nbsp;</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($patient->age, ENT_QUOTES);?>&nbsp;</td>
		<?php if($GLOBALS['wmt::print_ethnicity']) { ?>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($patient->ethnicity, 'ethnicity', 'Not Specified'); ?>&nbsp;</td>
		<?php } ?>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($patient->sex, ENT_QUOTES);?>&nbsp;</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($patient->pubpid, ENT_QUOTES); ?>&nbsp;</td>
  </tr>
</table>
</div>
<?php } ?>
