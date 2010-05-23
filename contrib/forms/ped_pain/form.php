<?
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
		<option <? if ($obj["location"]=='ear') print 'selected' ;?> >ear
		<option <? if ($obj["location"]=='troat') print 'selected'; ?>>troat
		<option <? if ($obj["location"]=='stomach') print 'selected'; ?>>stomach
		<option <? if ($obj["location"]=='head') print 'selected'; ?>>head
		</SELECT>
	</TD>
	<TD >Duration:</TD>
	<TD>
		<SELECT NAME="duration">
		<option <? if ($obj["duration"]=='one hour') print 'selected'; ?>>one hour
		<option <? if ($obj["duration"]=='twelve hours') print 'selected'; ?>>twelve hours
		<option <? if ($obj["duration"]=='one day') print 'selected'; ?>>one day
		<option <? if ($obj["duration"]=='two days') print 'selected'; ?>>two days
		<option <? if ($obj["duration"]=='three days') print 'selected'; ?>>three days
		<option <? if ($obj["duration"]=='more than 3 days') print 'selected'; ?>>more than 3 days
		</SELECT>
	</TD>
	<TD >Severity:</TD>
	<TD>
		<SELECT NAME="severity">
		<option <? if ($obj["severity"]=='consolable') print 'selected'; ?>>consolable
		<option <? if ($obj["severity"]=='consolable with difficulty') print 'selected'; ?>>consolable with difficulty
		<option <? if ($obj["severity"]=='inconsolable') print 'selected'; ?>>inconsolable
		</SELECT>
	</TD>
</TR>
<TR>
	<TD >Fever:</TD>
	<TD>
		<SELECT NAME="fever">
		<option <? if ($obj["fever"]=='no') print 'selected'; ?>>no
		<option <? if ($obj["fever"]=='yes') print 'selected'; ?>>yes
		<option <? if ($obj["fever"]=='98') print 'selected'; ?>>98
		<option <? if ($obj["fever"]=='98,5') print 'selected'; ?>>98,5
		<option <? if ($obj["fever"]=='99') print 'selected'; ?>>99
		<option <? if ($obj["fever"]=='99,5') print 'selected'; ?>>99,5
		<option <? if ($obj["fever"]=='100') print 'selected'; ?>>100
		<option <? if ($obj["fever"]=='100,5') print 'selected'; ?>>100,5
		<option <? if ($obj["fever"]=='101') print 'selected'; ?>>101
		<option <? if ($obj["fever"]=='101,5') print 'selected'; ?>>101,5
		<option <? if ($obj["fever"]=='102') print 'selected'; ?>>102
		<option <? if ($obj["fever"]=='102,5') print 'selected'; ?>>102,5
		<option <? if ($obj["fever"]=='103') print 'selected'; ?>>103
		<option <? if ($obj["fever"]=='103,5') print 'selected'; ?>>103,5
		<option <? if ($obj["fever"]=='104') print 'selected'; ?>>104
		<option <? if ($obj["fever"]=='104,5') print 'selected'; ?>>104,5
		<option <? if ($obj["fever"]=='105 ') print 'selected'; ?>>105
		</SELECT>
	</TD>
	<TD >Lethargy:</TD>
	<TD>
		<SELECT NAME="lethargy">
		<option <? if ($obj["lethargy"]=='no') print 'selected'; ?>>no
		<option <? if ($obj["lethargy"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD >Vomiting:</TD>
	<TD>
		<SELECT NAME="vomiting">
		<option <? if ($obj["vomiting"]=='no') print 'selected'; ?>>no
		<option <? if ($obj["vomiting"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
</TR>
<TR>
	<TD colspan=2 >Able to take oral hydration:</TD>
	<TD>
		<SELECT NAME="oral_hydration_capable">
		<option <? if ($obj["oral_hydration_capable"]=='yes') print 'selected'; ?>>yes
		<option <? if ($obj["oral_hydration_capable"]=='no') print 'selected'; ?>>no
		</SELECT>
	</TD>
	<TD colspan=2 >Urine output at least every 6 hrs.</TD>
	<TD>
		<SELECT NAME="urine_output_last_6_hours">
		<option <? if ($obj["urine_output_last_6_hours"]=='yes') print 'selected'; ?>>yes
		<option <? if ($obj["urine_output_last_6_hours"]=='no') print 'selected'; ?>>no
		</SELECT>
		With Pain?:
		<SELECT NAME="pain_with_urination">
		<option <? if ($obj["pain_with_urination"]=='no') print 'selected'; ?>>no
		<option <? if ($obj["pain_with_urination"]=='yes') print 'selected'; ?>>yes
		</SELECT>

	</TD>
</TR>
<TR>
	<TD colspan=2 >Cough or difficulty breathing:</TD>
	<TD>
		<SELECT NAME="cough_or_breathing_difficulty">
		<option <? if ($obj["cough_or_breathing_difficulty"]=='no') print 'selected'; ?>>no
		<option <? if ($obj["cough_or_breathing_difficulty"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD colspan=2 >Able to sleep confortably</TD>
	<TD>
		<SELECT NAME="able_to_sleep">
		<option <? if ($obj["able_to_sleep"]=='yes') print 'selected'; ?>>yes
		<option <? if ($obj["able_to_sleep"]=='no') print 'selected'; ?>>no
		</SELECT>
	</TD>
</TR>
<TR>
	<TD>Nasal Discharge:</TD>
	<TD>
		<SELECT NAME="nasal_discharge">
		<option <? if ($obj["nasal_discharge"]=='no') print 'selected'; ?>>no
		<option <? if ($obj["nasal_discharge"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD>Prior Hospitalization</TD>
	<TD>
		<SELECT NAME="previous_hospitalization">
		<option <? if ($obj["previous_hospitalization"]=='no') print 'selected'; ?>>no
		<option <? if ($obj["previous_hospitalization"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
	<TD>Siblings affected?:</TD>
	<TD>
		<SELECT NAME="siblings_affected">
		<option <? if ($obj["siblings_affected"]=='no') print 'selected'; ?>>no
		<option <? if ($obj["siblings_affected"]=='yes') print 'selected'; ?>>yes
		</SELECT>
	</TD>
</TR>
<TR>
	<TD colspan=2>Immunization up to date:
		<SELECT NAME="immunization_up_to_date">
		<option <? if ($obj["immunization_up_to_date"]=='yes') print 'selected'; ?>>yes
		<option <? if ($obj["immunization_up_to_date"]=='no') print 'selected'; ?>>no
		</SELECT>
	</TD>
	<TD colspan=4 align=left valign=top>Notes: 
	<TEXTAREA NAME="notes" ROWS="3" COLS="40">
	<? if ($obj["notes"]!='') print $obj["notes"]; ?>
	</TEXTAREA></TD>
</TR>
</TABLE>
