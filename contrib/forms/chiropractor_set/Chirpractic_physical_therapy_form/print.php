<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: Chirpractic_physical_therapy_form");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/Chirpractic_physical_therapy_form/save.php?mode=new" name="my_form" onSubmit="return top.restoreSession()">
<h1> Chiropractic physical therapy form</h1>
<hr>
<input type="submit" name="submit form" value="submit form" /> <a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <? xl("[do not save]",'e') ?> </a><br>
<br>
<table cellspacing="0" cellpadding="0" style="width: 100%">

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="center" colspan="4" valign="top">

                    <h3>

                        CONFIDENTIAL PATIENT CASE HISTORY</h3>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" valign="top">

<table>

<tr><td class="text" > Date:</td> <td class="text" ><input type="text" name="_date"  /></td></tr>

</table>
                </td>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top">

<table>

<tr><td class="text" > Social Security #:</td> <td class="text" ><input type="text" name="_social_security_number"  /></td></tr>

</table>
                </td>

                <td class="text"  style="border: solid 1px #000000" align="left" valign="top">

<table>

<tr><td class="text" > Drivers License #:</td> <td class="text" ><input type="text" name="_drivers_license_number"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top">

<table>

<tr><td class="text" > Name:</td> <td class="text" ><input type="text" name="_name"  /></td></tr>

</table>
                </td>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top">

<table>

<tr><td class="text" > Address:</td> <td class="text" ><input type="text" name="_address"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top">

<table>

<tr><td class="text" > City:</td> <td class="text" ><input type="text" name="_city"  /></td></tr>

</table>
                </td>

                <td class="text"  style="border: solid 1px #000000" align="left" valign="top">

<table>

<tr>
  <td class="text" > State</td> 
  <td class="text" ><input type="text" name="_state"  /></td></tr>

</table>
                </td>

                <td class="text"  style="border: solid 1px #000000" align="left" valign="top">

<table>

<tr><td class="text" > Zip:</td> <td class="text" ><input type="text" name="_zip"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top">

<table>

<tr><td class="text" > Home Phone:</td> <td class="text" ><input type="text" name="_home_phone"  /></td></tr>

</table>
                </td>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top">

<table>

<tr><td class="text" > Cell Phone:</td> <td class="text" ><input type="text" name="_cell_phone"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" valign="top">

<table>

<tr><td class="text" > Birth Date:</td> <td class="text" ><input type="text" name="_birth_date"  /></td></tr>

</table>
                </td>

                <td class="text"  style="border: solid 1px #000000" align="left" valign="top">

<table>

<tr><td class="text" > Age:</td> <td class="text" ><input type="text" name="_age"  /></td></tr>

</table>
                </td>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top">

<table>

<tr><td class="text" > Sex:</td> <td class="text" ><label><input type="checkbox" name="_sex[]" value="Male" /> <? xl("Male",'e') ?> </label> <label><input type="checkbox" name="_sex[]" value="Female" /> <? xl("Female",'e') ?> </label></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top">

<table>

<tr><td class="text" > Business/Employer:</td> <td class="text" ><input type="text" name="_business_or_employer"  /></td></tr>

</table>
                </td>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top">

<table>

<tr><td class="text" > Type oOf Work:</td> <td class="text" ><input type="text" name="_type_of_work"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > Business Address and Phone Number:</td> <td class="text" ><input type="text" name="_business_address_and_phone_number"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > Check One</td> <td class="text" ><label><input type="checkbox" name="_check_one[]" value="Married" /> <? xl("Married",'e') ?> </label> <label><input type="checkbox" name="_check_one[]" value="Single" /> <? xl("Single",'e') ?> </label> <label><input type="checkbox" name="_check_one[]" value="Widowed" /> <? xl("Widowed",'e') ?> </label> <label><input type="checkbox" name="_check_one[]" value="Divorced" /> <? xl("Divorced",'e') ?> </label> <label><input type="checkbox" name="_check_one[]" value="Separated" /> <? xl("Separated",'e') ?> </label></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > # of Children:</td> <td class="text" ><input type="text" name="_number_of_children"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > Name and # Of  Emergency Contact:</td> <td class="text" ><input type="text" name="_name_and_number_of_emergency_contact"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top">

<table>

<tr><td class="text" > Spouse Name:</td> <td class="text" ><input type="text" name="_spouse_name"  /></td></tr>

</table>
                </td>

                <td class="text"  style="border: solid 1px #000000" align="left" valign="top">

<table>

<tr><td class="text" > Occupation:</td> <td class="text" ><input type="text" name="_occupation"  /></td></tr>

</table>
                </td>

                <td class="text"  style="border: solid 1px #000000" align="left" valign="top">

<table>

<tr><td class="text" > Employer:</td> <td class="text" ><input type="text" name="_employer"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > Who Is Responsible For Your Bill:</td> <td class="text" ><label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Self" /> <? xl("Self",'e') ?> </label> <label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Spouse" /> <? xl("Spouse",'e') ?> </label> <label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Workmans Comp" /> <? xl("Workmans Comp",'e') ?> </label> <label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Medicaid" /> <? xl("Medicaid",'e') ?> </label> <label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Medicare" /> <? xl("Medicare",'e') ?> </label> <label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Auto Insurance" /> <? xl("Auto Insurance",'e') ?> </label> <label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Personal health insurance " /> <? xl("Personal health insurance ",'e') ?> </label></td></tr>

</table>

<table>

<tr><td class="text" > Other:</td> <td class="text" ><input type="text" name="_other"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="center" colspan="4" valign="top">

                    <h3>

                        CURRENT HEALTH CONDITION</h3>
                </td>
            </tr>


            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > Purpose Of This Appointment:</td> <td class="text" ><input type="text" name="_purpose_of_this_appointment"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > Other Doctors Seen For This Condition:</td> <td class="text" ><input type="text" name="_other_doctors_seen_for_this_condition"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > When Did This Condition Begin:</td> <td class="text" ><input type="text" name="_when_did_this_condition_begin"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > check</td> <td class="text" ><label><input type="checkbox" name="_check[]" value="Gradual Onset" /> <? xl("Gradual Onset",'e') ?> </label> <label><input type="checkbox" name="_check[]" value="Job Related" /> <? xl("Job Related",'e') ?> </label> <label><input type="checkbox" name="_check[]" value="Auto Related" /> <? xl("Auto Related",'e') ?> </label></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > Medication You Now Take:</td> <td class="text" ><label><input type="checkbox" name="_medication_you_now_take[]" value="Nerve Pills" /> <? xl("Nerve Pills",'e') ?> </label> <label><input type="checkbox" name="_medication_you_now_take[]" value="Pain Killers or Muscle relaxers" /> <? xl("Pain Killers or Muscle relaxers",'e') ?> </label> <label><input type="checkbox" name="_medication_you_now_take[]" value="Insulin" /> <? xl("Insulin",'e') ?> </label> <label><input type="checkbox" name="_medication_you_now_take[]" value="Blood pressure medicine " /> <? xl("Blood pressure medicine ",'e') ?> </label></td></tr>

</table>

<table>

<tr><td class="text" > Others</td> <td class="text" ><input type="text" name="_others"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="center" colspan="4" valign="top">

                    <h3>

                        PAST HEALTH HISTORY</h3>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > Major Surgery Or Operations:</td> <td class="text" ><label><input type="checkbox" name="_major_surgery_or_operations[]" value="Appendectomy" /> <? xl("Appendectomy",'e') ?> </label> <label><input type="checkbox" name="_major_surgery_or_operations[]" value="Tonsillectomy" /> <? xl("Tonsillectomy",'e') ?> </label> <label><input type="checkbox" name="_major_surgery_or_operations[]" value="Gall Bladder" /> <? xl("Gall Bladder",'e') ?> </label> <label><input type="checkbox" name="_major_surgery_or_operations[]" value="Hernia" /> <? xl("Hernia",'e') ?> </label> <label><input type="checkbox" name="_major_surgery_or_operations[]" value="BrokenBone" /> <? xl("BrokenBone",'e') ?> </label></td></tr>

</table>

<table>

<tr><td class="text" > Otherone</td> <td class="text" ><input type="text" name="_otherone"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > Major Accidents or Falls:</td> <td class="text" ><input type="text" name="_major_accidents_or_falls"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > Hospitalization if Other Than Above:</td> <td class="text" ><input type="text" name="_hospitalization_if_other_than_above"  /></td></tr>

</table>
                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top">

<table>

<tr><td class="text" > Previous Chiropractic Care:</td> <td class="text" ><label><input type="checkbox" name="_previous_chiropractic_care[]" value="None " /> <? xl("None ",'e') ?> </label> Doctors Name:</td> <td class="text" ><input type="text" name="_doctors_name"  /> Appox Date of Last Visit:<input type="text" name="_appox_date_of_last_visit"  /></td></tr>

</table>
                </td>
            </tr>

  </table>

        <table cellspacing="0" cellpadding="0" width="100%">

            <tr>

                <td class="text"  style="border: solid 1px #000000" colspan="2" align="center">

                    <h3>

                        Indicate ability to perform the following activities:</h3>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > Coughing Or Sneezing</td> <td class="text" ><select name="_coughing_or_sneezing" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > Climbing</td> <td class="text" ><select name="_climbing" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > Getting In And Out Of A Car</td> <td class="text" ><select name="_getting_in_and_out_of_a_car" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > Kneeling</td> <td class="text" ><select name="_kneeling" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" width="33%">

<table>

<tr><td class="text" > Bending Forward To Brush Teeth</td> <td class="text" ><select name="_bending_forward_to_brush_teeth" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000" width="33%">

<table>

<tr><td class="text" > Balancing</td> <td class="text" ><select name="_balancing" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > Turing Over In Bed</td> <td class="text" ><select name="_turing_over_in_bed" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > Dressing Self</td> <td class="text" ><select name="_dressing_self" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > Walking Short Distance</td> <td class="text" ><select name="_walking_short_distance" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > Sleeping</td> <td class="text" ><select name="_sleeping" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Standing More Than One Hour</td> <td class="text" ><select name="_standing_more_than_one_hour" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Stooping</td> <td class="text" ><select name="_stooping" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Sitting At Table</td> <td class="text" ><select name="_sitting_at_table" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Gripping</td> <td class="text" ><select name="_gripping" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Lying On Back</td> <td class="text" ><select name="_lying_on_back" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Pushing</td> <td class="text" ><select name="_pushing" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Lying Flat On Stomach</td> <td class="text" ><select name="_lying_flat_on_stomach" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Pulling</td> <td class="text" ><select name="_pulling" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Lying On Side With Knees Bent</td> <td class="text" ><select name="_lying_on_side_with_knees_bent" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Reaching</td> <td class="text" ><select name="_reaching" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Bending Over Forward</td> <td class="text" ><select name="_bending_over_forward" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Sexual Activity</td> <td class="text" ><select name="_sexual_activity" >
<option value=" "> </option>
<option value="U-unable"> <? xl("U-unable",'e') ?> </option>
<option value="P-painful"> <? xl("P-painful",'e') ?> </option>
<option value="D-Diificult"> <? xl("D-Diificult",'e') ?> </option>
<option value="L-Limited"> <? xl("L-Limited",'e') ?> </option>
<option value="N-Normal"> <? xl("N-Normal",'e') ?> </option>
</select></td></tr>
</table>                </td>
            </tr>
			<tr>
			<td class="text"  colspan="2">Checking Symptoms Of Nervous Systems
			</td>
			
			</tr>
            <tr>
              <td class="text"  style="border: solid 1px #000000" ><label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="Blurring Vision" /> <? xl("Blurring Vision",'e') ?> </label> <label><br><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="buzzing or ringing in ears" /> <? xl("Buzzing Or Ringing In Ears",'e') ?> </label> <label><br><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="confusion" /> <? xl("Confusion",'e') ?> </label> <label><br><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="convulsions" /> <? xl("Convulsions",'e') ?> </label> <label><br><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="depression or crying spells" /> <? xl("depression or crying spells",'e') ?> </label> <label><br><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="Dizziness" /> <? xl("dizziness",'e') ?> </label> <label><br><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="fainting" /> <? xl("Fainting",'e') ?> </label></td>
              <td class="text"  style="border: solid 1px #000000"  valign="top"> <label>
              <input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="paralysis" /> <? xl("Paralysis",'e') ?> </label> <label><br><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="loss of sleep" /> <? xl("Loss Of Sleep",'e') ?> </label> <label><br><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="low resistance" /> <? xl("Low Resistance",'e') ?> </label> <label><br><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="muscle jerking" /> <? xl("Muscle Jerking",'e') ?> </label> <label><br><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="headaches" /> <? xl("Headaches",'e') ?> </label> <br>How Often Do You Have Headaches <input type="text" name="_how_often_do_you_have_headaches"  /></td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000"  colspan="2">



           </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Symptoms Are Better In</td> <td class="text" ><label><input type="checkbox" name="_symptoms_are_better_in[]" value="AM" /> <? xl("AM",'e') ?> </label> <label><input type="checkbox" name="_symptoms_are_better_in[]" value="Midday" /> <? xl("Midday",'e') ?> </label> <label><input type="checkbox" name="_symptoms_are_better_in[]" value="PM" /> <? xl("PM",'e') ?> </label></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Symptoms Are Worse In</td> <td class="text" ><label><input type="checkbox" name="_symptoms_are_worse_in[]" value="AM" /> <? xl("AM",'e') ?> </label> <label><input type="checkbox" name="_symptoms_are_worse_in[]" value="Midday" /> <? xl("Midday",'e') ?> </label> <label><input type="checkbox" name="_symptoms_are_worse_in[]" value="PM" /> <? xl("PM",'e') ?> </label></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" colspan="2">

<table>

<tr><td class="text" > Symptoms Do Not Change With Time Of Day</td> <td class="text" ><label><input type="checkbox" name="_symptoms_do_not_change_with_time_of_day" value="yes" /></label></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000"  colspan="2">

                    For woman only                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Are You Pregnant</td> <td class="text" ><label><input type="checkbox" name="_are_you_pregnant[]" value="Yes" /> <? xl("Yes",'e') ?> </label> <label><input type="checkbox" name="_are_you_pregnant[]" value="No" /> <? xl("No",'e') ?> </label></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > Date Of Onset Of Last Menstrual Cycle</td> <td class="text" ><input type="text" name="_date_of_onset_of_last_menstrual_cycle"  /></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" >

<table>

<tr><td class="text" > Give Date Of Last Xray</td> <td class="text" ><input type="text" name="_give_date_of_last_xray"  /></td></tr>
</table>                </td>

                <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > What Body Part Were They Taken Of</td> <td class="text" ><input type="text" name="_what_body_part_were_they_taken_of"  /></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" colspan="2">
                Family History:</td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" colspan="2">

<table>

<tr><td class="text" > Cancer</td> <td class="text" ><label><input type="checkbox" name="_cancer[]" value="Mother" /> <? xl("Mother",'e') ?> </label> <label><input type="checkbox" name="_cancer[]" value="Father" /> <? xl("Father",'e') ?> </label> <label><input type="checkbox" name="_cancer[]" value="Brother" /> <? xl("Brother",'e') ?> </label> <label><input type="checkbox" name="_cancer[]" value="Sister" /> <? xl("Sister",'e') ?> </label> <label><input type="checkbox" name="_cancer[]" value="None" /> <? xl("None",'e') ?> </label></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" colspan="2">

<table>

<tr><td class="text" > Diabetes</td> <td class="text" ><label><input type="checkbox" name="_diabetes[]" value="Mother" /> <? xl("Mother",'e') ?> </label> <label><input type="checkbox" name="_diabetes[]" value="Father" /> <? xl("Father",'e') ?> </label> <label><input type="checkbox" name="_diabetes[]" value="Brother" /> <? xl("Brother",'e') ?> </label> <label><input type="checkbox" name="_diabetes[]" value="Sister" /> <? xl("Sister",'e') ?> </label> <label><input type="checkbox" name="_diabetes[]" value="None" /> <? xl("None",'e') ?> </label></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" colspan="2">

<table>

<tr><td class="text" > Heart Problems</td> <td class="text" ><label><input type="checkbox" name="_heart_problems[]" value="Mother" /> <? xl("Mother",'e') ?> </label> <label><input type="checkbox" name="_heart_problems[]" value="Father" /> <? xl("Father",'e') ?> </label> <label><input type="checkbox" name="_heart_problems[]" value="Brother" /> <? xl("Brother",'e') ?> </label> <label><input type="checkbox" name="_heart_problems[]" value="Sister" /> <? xl("Sister",'e') ?> </label> <label><input type="checkbox" name="_heart_problems[]" value="None" /> <? xl("None",'e') ?> </label></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" colspan="2">

<table>

<tr><td class="text" > Back Or Neck Problems</td> <td class="text" ><label><input type="checkbox" name="_back_or_neck_problems[]" value="Mother" /> <? xl("Mother",'e') ?> </label> <label><input type="checkbox" name="_back_or_neck_problems[]" value="Father" /> <? xl("Father",'e') ?> </label> <label><input type="checkbox" name="_back_or_neck_problems[]" value="Brother" /> <? xl("Brother",'e') ?> </label> <label><input type="checkbox" name="_back_or_neck_problems[]" value="Sister" /> <? xl("Sister",'e') ?> </label> <label><input type="checkbox" name="_back_or_neck_problems[]" value="None" /> <? xl("None",'e') ?> </label></td></tr>
</table>                </td>
            </tr>

            <tr>

                <td class="text"  style="border: solid 1px #000000" colspan="2">

                    <table cellspacing="0" cellpadding="0" width="100%">

                        <tr>

                            <td class="text"  style="border: solid 1px #000000" colspan="2" align="center">

                                <h3>

                                    Accident Information</h3>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000" width="50%">

<table>

<tr><td class="text" > Have You Retained An Attorney</td> <td class="text" ><label><input type="checkbox" name="_have_you_retained_an_attorney[]" value="Yes" /> <? xl("Yes",'e') ?> </label> <label><input type="checkbox" name="_have_you_retained_an_attorney[]" value="no" /> <? xl("no",'e') ?> </label></td></tr>
</table>                            </td>

                            <td class="text"  style="border: solid 1px #000000" width="50%"> <? xl("&nbsp;
                                ",'e') ?> </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000">
                              <? xl("If yes",'e') ?> </td>

                            <td class="text"  style="border: solid 1px #000000"> <? xl("&nbsp;
                                ",'e') ?> </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > Name</td> <td class="text" ><input type="text" name="_attorney_name"  /></td></tr>
</table>                            </td>

                            <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > Address</td> <td class="text" ><input type="text" name="_attorney_address"  /></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000">

<table>

<tr><td class="text" > Phone</td> <td class="text" ><input type="text" name="_attorney_phone"  /></td></tr>
</table>                            </td>

                            <td class="text"  style="border: solid 1px #000000">&nbsp;                          </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000" colspan="2">

<table>

<tr><td class="text" > Number Of People In Vechicle And Their Name</td> <td class="text" ><input type="text" name="_number_of_people_in_vechicle_and_their_name"  /></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000" colspan="2">

<table>

<tr><td class="text" > Were The Policy Notified</td> <td class="text" ><label><input type="checkbox" name="_were_the_policy_notified[]" value="Yes" /> <? xl("Yes",'e') ?> </label> <label><input type="checkbox" name="_were_the_policy_notified[]" value="no" /> <? xl("no",'e') ?> </label></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000"  colspan="2">

<table>

<tr><td class="text" > What Direction Were You Headed</td> <td class="text" ><label><input type="checkbox" name="_what_direction_were_you_headed[]" value="North" /> <? xl("North",'e') ?> </label> <label><input type="checkbox" name="_what_direction_were_you_headed[]" value="East" /> <? xl("East",'e') ?> </label> <label><input type="checkbox" name="_what_direction_were_you_headed[]" value="South" /> <? xl("South",'e') ?> </label> <label><input type="checkbox" name="_what_direction_were_you_headed[]" value="West" /> <? xl("West",'e') ?> </label></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000"  colspan="2">

<table>

<tr><td class="text" > What Direction Was Other Vechicle</td> <td class="text" ><label><input type="checkbox" name="_what_direction_was_other_vechicle[]" value="North" /> <? xl("North",'e') ?> </label> <label><input type="checkbox" name="_what_direction_was_other_vechicle[]" value="East" /> <? xl("East",'e') ?> </label> <label><input type="checkbox" name="_what_direction_was_other_vechicle[]" value="South" /> <? xl("South",'e') ?> </label> <label><input type="checkbox" name="_what_direction_was_other_vechicle[]" value="Wst" /> <? xl("Wst",'e') ?> </label></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000"  colspan="2">

<table>

<tr><td class="text" > Name Of Street Or Town</td> <td class="text" ><input type="text" name="_name_of_street_or_town"  /></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000"  colspan="2">

<table>

<tr><td class="text" > Were You Struck From</td> <td class="text" ><label><input type="checkbox" name="_were_you_struck_from[]" value="behind" /> <? xl("behind",'e') ?> </label> <label><input type="checkbox" name="_were_you_struck_from[]" value="front" /> <? xl("front",'e') ?> </label> <label><input type="checkbox" name="_were_you_struck_from[]" value="left side" /> <? xl("left side",'e') ?> </label> <label><input type="checkbox" name="_were_you_struck_from[]" value="right side" /> <? xl("right side",'e') ?> </label></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000"  colspan="2">

<table>

<tr><td class="text" > In Your Own Words Please Describe Accident</td> <td class="text" ><textarea name="_in_your_own_words_please_describe_accident"  rows="4" cols="40"></textarea></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000"  colspan="2">

<table>

<tr><td class="text" > Please Complaints And Symptoms</td> <td class="text" ><textarea name="_please_complaints_and_symptoms"  rows="4" cols="40"></textarea></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000"  colspan="2">

<table>

<tr><td class="text" > Did You Lose Any Time From Work</td> <td class="text" ><label><input type="checkbox" name="_did_you_lose_any_time_from_work[]" value="Yes" /> <? xl("Yes",'e') ?> </label> <label><input type="checkbox" name="_did_you_lose_any_time_from_work[]" value="No" /> <? xl("No",'e') ?> </label></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000"  colspan="2">

<table>

<tr><td class="text" > Date When You Lose From Work</td> <td class="text" ><input type="text" name="_date_when_you_lose_from_work"  /></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000"  colspan="2">

<table>

<tr><td class="text" > Type Of Employment</td> <td class="text" ><input type="text" name="_type_of_employment"  /></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000"  colspan="2">

<table>

<tr><td class="text" > Where Were You Taken Immediately Following Accident</td> <td class="text" ><input type="text" name="_where_were_you_taken_immediately_following_accident"  /></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000"  colspan="2">

<table>

<tr><td class="text" > If Taken To The Hospital Did You</td> <td class="text" ><label><input type="checkbox" name="_if_taken_to_the_hospital_did_you[]" value="Go by ambulance" /> <? xl("Go by ambulance",'e') ?> </label> <label><input type="checkbox" name="_if_taken_to_the_hospital_did_you[]" value="Drove self" /> <? xl("Drove self",'e') ?> </label> <label><input type="checkbox" name="_if_taken_to_the_hospital_did_you[]" value="Taken by someone else" /> <? xl("Taken by someone else",'e') ?> </label></td></tr>
</table>                            </td>
                        </tr>

                        <tr>

                            <td class="text"  style="border: solid 1px #000000"  colspan="2">

<table>

<tr><td class="text" > Have You Ever Been Involved In An Accident Before</td> <td class="text" ><label><input type="checkbox" name="_have_you_ever_been_involved_in_an_accident_before[]" value="yes" /> <? xl("yes",'e') ?> </label> <label><input type="checkbox" name="_have_you_ever_been_involved_in_an_accident_before[]" value="no" /> <? xl("no",'e') ?> </label></td></tr>
</table>                            </td>
                        </tr>
                    </table>                </td>
            </tr>
        </table>

<table></table><input type="submit" name="submit form" value="submit form" /> <a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <? xl("[do not save]",'e') ?> </a>
</form>
<?php
formFooter();
?>
