<?php

/*
Pediatric GI assesment
This form works as new and editing,
*/
?>
<TABLE span class=text>
<th><H2><U>Pediatric GI Form</U></H2></th>

<TR>
	<TD>Has Diarrhea:</TD>
	<TD>
		<SELECT NAME="diarrhea">
		<option <?php if ($obj["diarrhea"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["diarrhea"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD>Every bowel movement:</TD>
	<TD>
		<SELECT NAME="with_every_bowel_movement">
		<option <?php if ($obj["with_every_bowel_movement"]=='n/a') print 'selected'; ?>>n/a
		<option <?php if ($obj["with_every_bowel_movement"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["with_every_bowel_movement"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD>After Every meal:</TD>
	<TD>
		<SELECT NAME="after_every_meal">
		<option <?php if ($obj["after_every_meal"]=='n/a') print 'selected'; ?>>n/a
		<option <?php if ($obj["after_every_meal"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["after_every_meal"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
</TD>
<TR>
	<TD>Blood or mocus in stool:</TD>
	<TD>
		<SELECT NAME="blood_or_mucus_in_stool">
		<option <?php if ($obj["blood_or_mucus_in_stool"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["blood_or_mucus_in_stool"]=='blood') print 'selected'; ?>>blood
		<option <?php if ($obj["blood_or_mucus_in_stool"]=='mucus') print 'selected'; ?>>mucus
		<option <?php if ($obj["blood_or_mucus_in_stool"]=='both') print 'selected'; ?>>both
		</SELECT>
	</TD>
	<TD>Onset:</TD>
	<TD>
		<SELECT NAME="diarrhea_onset">
		<option <?php if ($obj["diarrhea_onset"]=='one hour') print 'selected'; ?>>one hour
		<option <?php if ($obj["diarrhea_onset"]=='twelve hours') print 'selected'; ?>>twelve hours
		<option <?php if ($obj["diarrhea_onset"]=='one day') print 'selected'; ?>>one day
		<option <?php if ($obj["diarrhea_onset"]=='two days') print 'selected'; ?>>two days
		<option <?php if ($obj["diarrhea_onset"]=='three days') print 'selected'; ?>>three days
		<option <?php if ($obj["diarrhea_onset"]=='more than 3 days') print 'selected'; ?>>more than 3 days
		</SELECT>
	</TD>
	<TD>Worms:</TD>
	<TD>
		<SELECT NAME="worms">
		<option <?php if ($obj["worms"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["worms"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
</TR>

<TR>
	<TD>Vomiting:</TD>
	<TD>
		<SELECT NAME="vomits">
		<option <?php if ($obj["vomits"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["vomits"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD>Duration:</TD>
	<TD>
		<SELECT NAME="duration">
		<option <?php if ($obj["duration"]=='one hour') print 'selected'; ?>>one hour
		<option <?php if ($obj["duration"]=='twelve hours') print 'selected'; ?>>twelve hours
		<option <?php if ($obj["duration"]=='one day') print 'selected'; ?>>one day
		<option <?php if ($obj["duration"]=='two days') print 'selected'; ?>>two days
		<option <?php if ($obj["duration"]=='three days') print 'selected'; ?>>three days
		<option <?php if ($obj["duration"]=='more than 3 days') print 'selected'; ?>>more than 3 days
		</SELECT>
	</TD>
	<TD >Proyectile:</TD>
	<TD>
		<SELECT NAME="projectile">
		<option <?php if ($obj["projectile"]=='n/a') print 'selected'; ?>>n/a
		<option <?php if ($obj["projectile"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["projectile"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
</TR>
<TR>
	<TD >+ often than 2 hours:</TD>
	<TD>
		<SELECT NAME="more_often_than_2_hours">
		<option <?php if ($obj["more_often_than_2_hours"]=='n/a') print 'selected' ;?> >n/a
		<option <?php if ($obj["more_often_than_2_hours"]=='no') print 'selected' ;?> >no
		<option <?php if ($obj["more_often_than_2_hours"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD >After every meal:</TD>
	<TD>
		<SELECT NAME="vomit_after_every_meal">
		<option <?php if ($obj["vomit_after_every_meal"]=='n/a') print 'selected' ;?> >n/a
		<option <?php if ($obj["vomit_after_every_meal"]=='no') print 'selected' ;?> >no
		<option <?php if ($obj["vomit_after_every_meal"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD >Blood in vomitus:</TD>
	<TD>
		<SELECT NAME="blood_in_vomitus">
		<option <?php if ($obj["blood_in_vomitus"]=='n/a') print 'selected' ;?> >n/a
		<option <?php if ($obj["blood_in_vomitus"]=='no') print 'selected' ;?> >no
		<option <?php if ($obj["blood_in_vomitus"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
</TR>

<TR>
	<TD>Takink Medication:</TD>
	<TD>
		<SELECT NAME="taking_medication">
		<option <?php if ($obj["taking_medication"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["taking_medication"]=='yes') print 'selected'; ?>>yes
	</TD>
	<TD>Oral Rehidration solution:</TD>
	<TD>
		<SELECT NAME="oral_rehydration">
		<option <?php if ($obj["taking_oral_rehydration_solution"]=='yes') print 'selected'; ?>>yes
		<option <?php if ($obj["taking_oral_rehydration_solution"]=='no') print 'selected'; ?>>no
	</TD>
	<TD>Eating Solid Food</TD>
	<TD>
		<SELECT NAME="eating_solid_food">
		<option <?php if ($obj["eating_solid_food"]=='yes') print 'selected'; ?>>yes
		<option <?php if ($obj["eating_solid_food"]=='no') print 'selected'; ?>>no
	</TD>
</TR>
<TR>
	<TD>Fever: </TD>
	<TD>
		<SELECT NAME="fever">
		<option <?php if ($obj["fever"]=='no') print 'selected' ;?> >no
		<option <?php if ($obj["fever"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD >Pain:</TD>
	<TD>
		<SELECT NAME="pain">
		<option <?php if ($obj["pain"]=='no pain') print 'selected' ;?> >no pain
		<option <?php if ($obj["pain"]=='moderate') print 'selected'; ?>>moderate
		<option <?php if ($obj["pain"]=='intense') print 'selected'; ?>>intense
		<option <?php if ($obj["pain"]=='severe') print 'selected'; ?>>severe
		</SELECT>
	</TD>
	<TD >Lethargy:</TD>
	<TD>
		<SELECT NAME="lethargy">
		<option <?php if ($obj["lethargy"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["lethargy"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
</TR>
<!-- common -->
<TR>
	<TD>Oral hydration capable:</TD>
	<TD>
		<SELECT NAME="oral_hydration_capable">
		<option <?php if ($obj["oral_hydration_capable"]=='yes') print 'selected'; ?>>yes
		<option <?php if ($obj["oral_hydration_capable"]=='no') print 'selected'; ?>>no
		</SELECT>
	</TD>
	<TD>Urine output at least every 6 hrs.</TD>
	<TD>
		<SELECT NAME="urine_output_last_6_hours">
		<option <?php if ($obj["urine_output_last_6_hours"]=='yes') print 'selected'; ?>>yes
		<option <?php if ($obj["urine_output_last_6_hours"]=='no') print 'selected'; ?>>no
		</SELECT>
	<TD>With Pain?:</TD>
	<TD>
		<SELECT NAME="pain_with_urination">
		<option <?php if ($obj["pain_with_urination"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["pain_with_urination"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
</TR>
<TR>
	<TD>Cough or difficulty breathing:</TD>
	<TD>
		<SELECT NAME="cough_or_breathing_difficulty">
		<option <?php if ($obj["cough_or_breathing_difficulty"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["cough_or_breathing_difficulty"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD>Sleeps confortably?:</TD>
	<TD>
		<SELECT NAME="able_to_sleep">
		<option <?php if ($obj["able_to_sleep"]=='yes') print 'selected'; ?>>yes
		<option <?php if ($obj["able_to_sleep"]=='no') print 'selected'; ?>>no
		</SELECT>
	</TD>
</TR>
<TR>
	<TD>Nasal Discharge:</TD>
	<TD>
		<SELECT NAME="nasal_discharge">
		<option <?php if ($obj["nasal_discharge"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["nasal_discharge"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD>Prior Hospitalization</TD>
	<TD>
		<SELECT NAME="previous_hospitalization">
		<option <?php if ($obj["previous_hospitalization"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["previous_hospitalization"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD>Siblings affected?:</TD>
	<TD>
		<SELECT NAME="siblings_affected">
		<option <?php if ($obj["siblings_affected"]=='no') print 'selected'; ?>>no
		<option <?php if ($obj["siblings_affected"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
</TR>
<TR>
	<TD valign="top">Immunization up to date:</TD>
	<TD valign="top">
		<SELECT NAME="immunization_up_to_date">
		<option <?php if ($obj["immunization_up_to_date"]=='yes') print 'selected'; ?>>yes
		<option <?php if ($obj["immunization_up_to_date"]=='no') print 'selected'; ?>>no
		</SELECT>
	</TD>
	<TD colspan=4 align=left valign=top>Notes: 
	<TEXTAREA NAME="notes" ROWS="3" COLS="40"><?php if ($obj["notes"]!='') print $obj["notes"]; ?></TEXTAREA></TD>
</TR>
</TABLE>


