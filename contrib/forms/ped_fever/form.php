<?php

/*
Pediatric Fever form
This form works as new and editing,
*/
?>
<TABLE span class=text>
<th><H2><U>Pediatric Fever Form</U></H2></th>
<TR>
    <TD>Fever: </TD>
    <TD>
    <INPUT TYPE="text" NAME="fever_temp" value="<?php if ($obj["fever_temp"] != '') {
        print text($obj["fever_temp"]);
                                                } ?>">
    </TD>
    <TD>Measured:</TD>
    <TD>
        <SELECT NAME="measured">
        <option <?php if ($obj["measured"] == 'axillary') {
            print 'selected' ;
                }?> >axillary
        <option <?php if ($obj["measured"] == 'oral') {
            print 'selected' ;
                }?> >oral
        <option <?php if ($obj["measured"] == 'rectal') {
            print 'selected' ;
                }?> >rectal
        <option <?php if ($obj["measured"] == 'not measured') {
            print 'selected' ;
                }?> >not measured
        </SELECT>
    </TD>
    <TD>Duration:</TD>
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
</TR>
<TR>
    <TD>Takink Medication:</TD>
    <TD>
        <SELECT NAME="taking_medication">
        <option <?php if ($obj["taking_medication"] == 'no') {
            print 'selected';
                } ?>>no
        <option <?php if ($obj["taking_medication"] == 'yes') {
            print 'selected';
                } ?>>yes
    </TD>
    <TD>Responds to Tylenol:</TD>
    <TD>
        <SELECT NAME="responds_to_tylenol">
        <option <?php if ($obj["responds_to_tylenol"] == 'n/a') {
            print 'selected';
                } ?>>n/a
        <option <?php if ($obj["responds_to_tylenol"] == 'no') {
            print 'selected';
                } ?>>no
        <option <?php if ($obj["responds_to_tylenol"] == 'yes') {
            print 'selected';
                } ?>>yes
    </TD>
    <TD>Responds to Molrtin</TD>
    <TD>
        <SELECT NAME="responds_to_moltrin">
        <option <?php if ($obj["responds_to_moltrin"] == 'n/a') {
            print 'selected';
                } ?>>n/a
        <option <?php if ($obj["responds_to_moltrin"] == 'no') {
            print 'selected';
                } ?>>no
        <option <?php if ($obj["responds_to_moltrin"] == 'yes') {
            print 'selected';
                } ?>>yes

    </TD>
</TR>
</TR>
    <TD >Pain:</TD>
    <TD>
        <SELECT NAME="pain">
        <option <?php if ($obj["pain"] == 'no pain') {
            print 'selected' ;
                }?> >no pain
        <option <?php if ($obj["pain"] == 'moderate') {
            print 'selected';
                } ?>>moderate
        <option <?php if ($obj["pain"] == 'intense') {
            print 'selected';
                } ?>>intense
        <option <?php if ($obj["pain"] == 'severe') {
            print 'selected';
                } ?>>severe
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
    <TD colspan=2 valign="top">Immunization up to date:
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


