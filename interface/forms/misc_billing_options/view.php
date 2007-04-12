<?php
include_once("../../globals.php");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_misc_billing_options", $_GET["id"]);
?>
<form method=post action="<?echo $rootdir?>/forms/misc_billing_options/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title">Misc Billing Options for HCFA-1500</span><Br><br>
<span class=text><? xl('Checked box = yes ,  empty = no', 'e');?><br><br>
<span class=text><? xl('BOX 10 A. Employment related ','e'); ?>: </span><input type=checkbox name="employment_related" value="1" <?if ($obj['employment_related'] == "1") echo "checked";?>><br><br>
<span class=text><? xl('BOX 10 B. Auto Accident ','e'); ?>: </span><input type=checkbox name="auto_accident" value="1" <?if ($obj['auto_accident'] == "1") echo "checked";?>>
<span class=text>State: </span><input type=entry name="accident_state" size=1 value="<?echo $obj{"accident_state"};?>" ><br><br>
<span class=text><? xl('BOX 10 C. Other Accident ','e'); ?>: </span><input type=checkbox name="other_accident" value="1" <?if ($obj['other_accident'] == "1") echo "checked";?>><br><br>
<span class=text><? xl('BOX 16. Date unable to work from (yyyy-mm-dd):','e');?> </span><input type=entry size=9 name="off_work_from" value="<?echo $obj{"off_work_from"};?>" >
<span class=text><? xl('BOX 16. Date unable to work to (yyyy-mm-dd):','e');?> </span><input type=entry size=9 name="off_work_to" value="<?echo $obj{"off_work_to"};?>" ><br><br>
<span class=text><? xl('BOX 18. Hospitalization date from (yyyy-mm-dd): ','e');?></span><input type=entry size=9 name="hospitalization_date_from" value="<?echo $obj{"hospitalization_date_from"};?>" >
<span class=text><? xl('BOX 18. Hospitalization date to (yyyy-mm-dd): ','e');?></span><input type=entry size=9 name="hospitalization_date_to" value="<?echo $obj{"hospitalization_date_to"};?>" ><br><br>
<span class=text><? xl('BOX 20. Is Outside Lab used?','e'); ?>: </span><input type=checkbox name="outside_lab" value="1" <?if ($obj['outside_lab'] == "1") echo "checked";?>>
<span class=text>Amount Charges: </span><input type=entry size=7 align='right' name="lab_amount" value="<?echo $obj{"lab_amount"};?>" ><br><br>
<span class=text><? xl('BOX 22. Medicaid Resubmission Code (ICD-9) ','e');?></span><input type=entry size=9 name="medicaid_resubmission_code" value="<?echo $obj{"medicaid_resubmission_code"};?>" >
<span class=text><? xl(' Medicaid Original Reference No. ','e');?></span><input type=entry size=15 name="medicaid_original_reference" value="<?echo $obj{"medicaid_original_reference"};?>" ><br><br>
<span class=text><? xl('BOX 23. Prior Authorization No. ','e');?></span><input type=entry size=15 name="prior_auth_number" value="<?echo $obj{"prior_auth_number"};?>" ><br>


<table>
<tr>
<td valign=top>
<span class=text>Additional Notes: </span><br><textarea cols=40 rows=8 wrap=virtual name="comments" ><?echo $obj{"comments"};?></textarea><br>
</td>
</table>
<br>
</tr>

<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link" target=Main>[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
