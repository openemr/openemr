<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: misc_billing_options");
?>
<html><head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<form method=post action="<?php echo $rootdir;?>/forms/misc_billing_options/save.php?mode=new" name="my_form">
<span class="title">Misc Billing Options for HCFA-1500</span><Br><br>
<span class=text><?php xl('Checked box = yes ,  empty = no', 'e');?><br><br>
<span class=text><?php xl('BOX 10 A. Employment related ','e'); ?>: </span><input type=checkbox name="employment_related" value="1" <?php if ($obj['employment_related'] == "1") echo "checked";?>><br><br>
<span class=text><?php xl('BOX 10 B. Auto Accident ','e'); ?>: </span><input type=checkbox name="auto_accident" value="1" <?php if ($obj['auto_accident'] == "1") echo "checked";?>>
<span class=text>State: </span><input type=entry name="accident_state" size=1 value="<?php echo $obj{"accident_state"};?>" ><br><br>
<span class=text><?php xl('BOX 10 C. Other Accident ','e'); ?>: </span><input type=checkbox name="other_accident" value="1" <?php if ($obj['other_accident'] == "1") echo "checked";?>><br><br>
<span class=text><?php xl('BOX 16. Date unable to work from (yyyy-mm-dd):','e');?> </span><input type=entry size=9 name="off_work_from" value="<?php echo $obj{"off_work_from"};?>" >
<span class=text><?php xl('BOX 16. Date unable to work to (yyyy-mm-dd):','e');?> </span><input type=entry size=9 name="off_work_to" value="<?php echo $obj{"off_work_to"};?>" ><br><br>
<span class=text><?php xl('BOX 18. Hospitalization date from (yyyy-mm-dd): ','e');?></span><input type=entry size=9 name="hospitalization_date_from" value="<?php echo $obj{"hospitalization_date_from"};?>" >
<span class=text><?php xl('BOX 18. Hospitalization date to (yyyy-mm-dd): ','e');?></span><input type=entry size=9 name="hospitalization_date_to" value="<?php echo $obj{"hospitalization_date_to"};?>" ><br><br>
<span class=text><?php xl('BOX 20. Is Outside Lab used?','e'); ?>: </span><input type=checkbox name="outside_lab" value="1" <?php if ($obj['outside_lab'] == "1") echo "checked";?>>
<span class=text>Amount Charges: </span><input type=entry size=7 align='right' name="lab_amount" value="<?php echo $obj{"lab_amount"};?>" ><br><br>
<span class=text><?php xl('BOX 22. Medicaid Resubmission Code (ICD-9) ','e');?></span><input type=entry size=9 name="medicaid_resubmission_code" value="<?php echo $obj{"medicaid_resubmission_code"};?>" >
<span class=text><?php xl(' Medicaid Original Reference No. ','e');?></span><input type=entry size=15 name="medicaid_original_reference" value="<?php echo $obj{"medicaid_original_reference"};?>" ><br><br>
<span class=text><?php xl('BOX 23. Prior Authorization No. ','e');?></span><input type=entry size=15 name="prior_auth_number" value="<?php echo $obj{"prior_auth_number"};?>" ><br>


<table>
<tr>
<td valign=top>
<span class=text>Additional Notes: </span><br><textarea cols=40 rows=8 wrap=virtual name="comments" ><?php echo $obj{"comments"};?></textarea><br>
</td>
</table>
<br>
</tr>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo "$rootdir/patient_file/encounter/patient_encounter.php";?>"
 class="link" target="Main" onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
