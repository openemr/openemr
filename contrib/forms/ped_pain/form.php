<?php

/*
pediatric pain form
This form works as new and editing,
*/
?>
<TABLE span class=text>
<th><H2><U>Pediatric Pain Form</U></H2></th>
<TR>
    <TD >Location:</TD>
    <TD>
        <SELECT NAME="location">
        <option <?php if ($obj["location"] == 'ear') {
            print 'selected' ;
                }?> >ear
        <option <?php if ($obj["location"] == 'troat') {
            print 'selected';
                } ?>>troat
        <option <?php if ($obj["location"] == 'stomach') {
            print 'selected';
                } ?>>stomach
        <option <?php if ($obj["location"] == 'head') {
            print 'selected';
                } ?>>head
        </SELECT>
    </TD>
    <TD >Duration:</TD>
    <TD>
        <SELECT NAME="duration">
        <option <?php if ($obj["duration"] == 'one hour') {
            print 'selected';
                } ?>>one hour
        <option <?php if ($obj["duration"] == 'twelve hours') {
            print 'selected';
                } ?>>twelve hours
        <option <?php if ($obj["duration"] == 'one day') {
            print 'selected';
                } ?>>one day
        <option <?php if ($obj["duration"] == 'two days') {
            print 'selected';
                } ?>>two days
        <option <?php if ($obj["duration"] == 'three days') {
            print 'selected';
                } ?>>three days
        <option <?php if ($obj["duration"] == 'more than 3 days') {
            print 'selected';
                } ?>>more than 3 days
        </SELECT>
    </TD>
    <TD >Severity:</TD>
    <TD>
        <SELECT NAME="severity">
        <option <?php if ($obj["severity"] == 'consolable') {
            print 'selected';
                } ?>>consolable
        <option <?php if ($obj["severity"] == 'consolable with difficulty') {
            print 'selected';
                } ?>>consolable with difficulty
        <option <?php if ($obj["severity"] == 'inconsolable') {
            print 'selected';
                } ?>>inconsolable
        </SELECT>
    </TD>
</TR>
<TR>
    <TD >Fever:</TD>
    <TD>
        <SELECT NAME="fever">
        <option <?php if ($obj["fever"] == 'no') {
            print 'selected';
                } ?>>no
        <option <?php if ($obj["fever"] == 'yes') {
            print 'selected';
                } ?>>yes
        <option <?php if ($obj["fever"] == '98') {
            print 'selected';
                } ?>>98
        <option <?php if ($obj["fever"] == '98,5') {
            print 'selected';
                } ?>>98,5
        <option <?php if ($obj["fever"] == '99') {
            print 'selected';
                } ?>>99
        <option <?php if ($obj["fever"] == '99,5') {
            print 'selected';
                } ?>>99,5
        <option <?php if ($obj["fever"] == '100') {
            print 'selected';
                } ?>>100
        <option <?php if ($obj["fever"] == '100,5') {
            print 'selected';
                } ?>>100,5
        <option <?php if ($obj["fever"] == '101') {
            print 'selected';
                } ?>>101
        <option <?php if ($obj["fever"] == '101,5') {
            print 'selected';
                } ?>>101,5
        <option <?php if ($obj["fever"] == '102') {
            print 'selected';
                } ?>>102
        <option <?php if ($obj["fever"] == '102,5') {
            print 'selected';
                } ?>>102,5
        <option <?php if ($obj["fever"] == '103') {
            print 'selected';
                } ?>>103
        <option <?php if ($obj["fever"] == '103,5') {
            print 'selected';
                } ?>>103,5
        <option <?php if ($obj["fever"] == '104') {
            print 'selected';
                } ?>>104
        <option <?php if ($obj["fever"] == '104,5') {
            print 'selected';
                } ?>>104,5
        <option <?php if ($obj["fever"] == '105 ') {
            print 'selected';
                } ?>>105
        </SELECT>
    </TD>
    <TD >Lethargy:</TD>
    <TD>
        <SELECT NAME="lethargy">
        <option <?php if ($obj["lethargy"] == 'no') {
            print 'selected';
                } ?>>no
        <option <?php if ($obj["lethargy"] == 'yes') {
            print 'selected';
                } ?>>yes
        </SELECT>
    </TD>
    <TD >Vomiting:</TD>
    <TD>
        <SELECT NAME="vomiting">
        <option <?php if ($obj["vomiting"] == 'no') {
            print 'selected';
                } ?>>no
        <option <?php if ($obj["vomiting"] == 'yes') {
            print 'selected';
                } ?>>yes
        </SELECT>
    </TD>
</TR>
<TR>
    <TD colspan=2 >Able to take oral hydration:</TD>
    <TD>
        <SELECT NAME="oral_hydration_capable">
        <option <?php if ($obj["oral_hydration_capable"] == 'yes') {
            print 'selected';
                } ?>>yes
        <option <?php if ($obj["oral_hydration_capable"] == 'no') {
            print 'selected';
                } ?>>no
        </SELECT>
    </TD>
    <TD colspan=2 >Urine output at least every 6 hrs.</TD>
    <TD>
        <SELECT NAME="urine_output_last_6_hours">
        <option <?php if ($obj["urine_output_last_6_hours"] == 'yes') {
            print 'selected';
                } ?>>yes
        <option <?php if ($obj["urine_output_last_6_hours"] == 'no') {
            print 'selected';
                } ?>>no
        </SELECT>
        With Pain?:
        <SELECT NAME="pain_with_urination">
        <option <?php if ($obj["pain_with_urination"] == 'no') {
            print 'selected';
                } ?>>no
        <option <?php if ($obj["pain_with_urination"] == 'yes') {
            print 'selected';
                } ?>>yes
        </SELECT>

    </TD>
</TR>
<TR>
    <TD colspan=2 >Cough or difficulty breathing:</TD>
    <TD>
        <SELECT NAME="cough_or_breathing_difficulty">
        <option <?php if ($obj["cough_or_breathing_difficulty"] == 'no') {
            print 'selected';
                } ?>>no
        <option <?php if ($obj["cough_or_breathing_difficulty"] == 'yes') {
            print 'selected';
                } ?>>yes
        </SELECT>
    </TD>
    <TD colspan=2 >Able to sleep confortably</TD>
    <TD>
        <SELECT NAME="able_to_sleep">
        <option <?php if ($obj["able_to_sleep"] == 'yes') {
            print 'selected';
                } ?>>yes
        <option <?php if ($obj["able_to_sleep"] == 'no') {
            print 'selected';
                } ?>>no
        </SELECT>
    </TD>
</TR>
<TR>
    <TD>Nasal Discharge:</TD>
    <TD>
        <SELECT NAME="nasal_discharge">
        <option <?php if ($obj["nasal_discharge"] == 'no') {
            print 'selected';
                } ?>>no
        <option <?php if ($obj["nasal_discharge"] == 'yes') {
            print 'selected';
                } ?>>yes
        </SELECT>
    </TD>
    <TD>Prior Hospitalization</TD>
    <TD>
        <SELECT NAME="previous_hospitalization">
        <option <?php if ($obj["previous_hospitalization"] == 'no') {
            print 'selected';
                } ?>>no
        <option <?php if ($obj["previous_hospitalization"] == 'yes') {
            print 'selected';
                } ?>>yes
        </SELECT>
    </TD>
    <TD>Siblings affected?:</TD>
    <TD>
        <SELECT NAME="siblings_affected">
        <option <?php if ($obj["siblings_affected"] == 'no') {
            print 'selected';
                } ?>>no
        <option <?php if ($obj["siblings_affected"] == 'yes') {
            print 'selected';
                } ?>>yes
        </SELECT>
    </TD>
</TR>
<TR>
    <TD colspan=2>Immunization up to date:
        <SELECT NAME="immunization_up_to_date">
        <option <?php if ($obj["immunization_up_to_date"] == 'yes') {
            print 'selected';
                } ?>>yes
        <option <?php if ($obj["immunization_up_to_date"] == 'no') {
            print 'selected';
                } ?>>no
        </SELECT>
    </TD>
    <TD colspan=4 align=left valign=top>Notes:
    <TEXTAREA NAME="notes" ROWS="3" COLS="40">
    <?php if ($obj["notes"] != '') {
        print text($obj["notes"]);
    } ?>
    </TEXTAREA></TD>
</TR>
</TABLE>
