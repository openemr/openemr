<?php 
include_once($GLOBALS['srcdir'].'/formatting.inc.php');
include_once($GLOBALS['srcdir'].'/translation.inc.php');
if(!isset($suppress_head)) $suppress_head = false;
if(!isset($include_patient_info)) $include_patient_info = true;
if(!isset($ftitle)) $ftitle = '';
$date_lbl = 'Visit Date';
if(!isset($form_date)) $form_date = '';
if($form_date == '') {
	$date_lbl = 'Date';
	$form_date = date('Y-m-d');
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
echo $ftitle, '&nbsp;for&nbsp;', $patient->full_name, '&nbsp;DOB:&nbsp;';
echo oeFormatShortDate($patient->DOB);
echo '&nbsp;on&nbsp;';
echo oeFormatShortDate($dt{'form_dt'}); 
?></title>
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header']; ?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtprint.bkk.css" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtprint.css" type="text/css">
</head>
<?php } ?>

<body style="background: transparent;">
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
	<?php if($ftitle != '') { ?>
	<tr>
		<td class="bkkPrnLarge" style="font-weight: bold;"><?php echo $ftitle; ?></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<?php } ?>
	<tr>
		<td class="bkkPrnLarge"><?php echo $facility->facility; ?></td>
	</tr>
  <tr>
    <td class="bkkPrnLarge"><?php echo $facility->addr; ?></td>
  </tr>
  <tr>
    <td class="bkkPrnLarge"><?php echo $facility->csz; ?></td>
  </tr>
	<?php if($GLOBALS['wmt::print_phone_and_fax']) { ?>
  <tr>
    <td class="bkkPrnLarge"><?php echo $facility->phone_fax; ?></td>
  </tr>
	<?php } ?>
	<?php if($GLOBALS['wmt::print_website']) { ?>
  <tr>
    <td class="bkkPrnBody bkkPrnC">Website Address: <?php echo $facility->website; ?></td>
  </tr>
	<?php } ?>
	<?php if($GLOBALS['wmt::print_email']) { ?>
  <tr>
    <td class="bkkPrnBody bkkPrnC">Email: <?php echo $facility->email; ?></td>
  </tr>
	<?php } ?>
</table>
<br>
<?php if($include_patient_info) { ?>
<div class="bkkPrnContainer">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><span class="bkkPrnLabel"><?php echo $date_lbl; ?>:&nbsp;</span>
		<span class="bkkPrnBody"><?php echo oeFormatShortDate($form_date); ?>&nbsp;</span></td>
    <td><span class="bkkPrnLabel">Patient:&nbsp;</span>
		<span class="bkkPrnBody"><?php echo $patient->full_name; ?>&nbsp;</span></td>
    <td><span class="bkkPrnLabel">DOB:&nbsp;</span>
		<span class="bkkPrnBody"><?php echo oeFormatShortDate($patient->DOB); ?>&nbsp;</span></td>
    <td><span class="bkkPrnLabel">Age:&nbsp;</span> 
		<span class="bkkPrnBody"><?php echo $patient->age; ?>&nbsp;</span></td>
		<?php if($GLOBALS['wmt::print_ethnicity']) { ?>
    <td><span class="bkkPrnLabel">Ethnicity:&nbsp;</span>
		<span class="bkkPrnBody"><?php echo ListLook($patient->ethnicity, 'ethnicity', 'Not Specified'); ?>&nbsp;</span></td>
		<?php } ?>
    <td><span class="bkkPrnLabel">Sex:&nbsp;</span>
		<span class="bkkPrnBody"><?php echo $patient->sex;?>&nbsp;</span></td>
    <td><span class="bkkPrnLabel">ID:&nbsp;</span>
		<span class="bkkPrnBody"><?php echo $patient->pubpid; ?>&nbsp;</span></td>
  </tr>
</table>
</div>
<?php } ?>
