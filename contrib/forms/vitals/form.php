<?
/*

This form works as new and editing,
*/
?>
<TABLE span class=text>
<th><H2><U>Vital Signs</U></H2></th>
<TR>
	<TD >Blood Presure:</TD>
	<TD>
	<INPUT TYPE="text" NAME="bps" size="3" value="<? if ($obj["bps"]!='') print $obj["bps"]; ?>">/<INPUT TYPE="text" NAME="bpd" size="3" value="<? if ($obj["bpd"]!='') print $obj["bpd"]; ?>">
	</TD>
	<TD >Weight (pounds):</TD>
	<TD><INPUT TYPE="text" NAME="weight" size="6" value="<? if ($obj["weight"]!='') print $obj["weight"]; ?>"></TD>
	<TD >Height (inches):</TD>
	<TD><INPUT TYPE="text" NAME="height" size="6" value="<? if ($obj["height"]!='') print $obj["height"]; ?>"></TD>
</TR>
<TR>
	<TD >Temperature:</TD>
	<TD><INPUT TYPE="text" NAME="temperature" size="6" value="<? if ($obj["temperature"]!='') print $obj["temperature"]; ?>">
		<select name="temp_method">
		<option <? if ($obj["temp_method"]=='ear') print 'selected'; ?>>ear
		<option <? if ($obj["temp_method"]=='axillary') print 'selected'; ?>>axillary
		<option <? if ($obj["temp_method"]=='mouth') print 'selected'; ?>>mouth
		<option <? if ($obj["temp_method"]=='rectal') print 'selected'; ?>>rectal
		</SELECT>
	</TD>
	<TD >Pulse (bps):</TD>
	<TD><INPUT TYPE="text" NAME="pulse" size="3" value="<? if ($obj["pulse"]!='') print $obj["pulse"]; ?>"></TD>
	<TD >Respiration:</TD>
	<TD><INPUT TYPE="text" NAME="respiration" size="2" value="<? if ($obj["respiration"]!='') print $obj["respiration"]; ?>"></TD>
</TR>
<TR>
	<TD>Note:</TD>
	<TD><INPUT TYPE="text" NAME="note" value="<? if ($obj["note"]!='') print $obj["note"]; ?>"></TD>
	<TD>Body Mass Index:</TD>
	<TD><INPUT TYPE="text" NAME="BMI" size="6" value="<? if ($obj["BMI"]!='') print $obj["BMI"]; ?>"><? if ($obj["BMI_status"]!='') print $obj["BMI_status"]; ?></TD>
	<TD>Waist circ (inch):</TD>
	<TD><INPUT TYPE="text" NAME="waist_circ" size="6" value="<? if ($obj["waist_circ"]!='') print $obj["waist_circ"]; ?>"></TD>
</TR>
</TABLE>
