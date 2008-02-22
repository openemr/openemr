<?

# file new.php 

# this file made by andres@paglayan.com on 2004-05-03

# custom file for WHS forms.

# Habit form, part of main intake 

###################

###  Habit Form ###

###################



 include_once("../../globals.php");

 include_once("../../../library/api.inc");

 formHeader("Habits");

 

?>

<html><head>
<? html_header_show();?>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">





</head>

<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>



<!--REM note that every input method has the same name as a valid column, this will make things easier in save.php -->



<!-- main form starts here -->

<table>

<form method='post' action="<?echo $rootdir;?>/forms/habits/save.php?mode=new" name='habits_form'>



<TR>

	<TD>

		<!-- habits -->

		<TABLE span class=text>

		<th><H2><U>Habits</U></H2></th>

		<TR>

			<TD><b>Caffeine:</b></TD>

			<TD><INPUT TYPE="checkbox" NAME="coffee" VALUE="YES"> Coffee &nbsp;&nbsp;</TD>

			<TD><INPUT TYPE="checkbox" NAME="tea" VALUE="YES"> Tea</TD>

			<TD><INPUT TYPE="checkbox" NAME="soft_drinks" VALUE="YES"> Soft Drinks</TD>

			<TD><INPUT TYPE="checkbox" NAME="other_caffeine" VALUE="YES"> Other</TD>

			<TD><INPUT TYPE="text" NAME="caffeine_per_day" SIZE="2"> # per day</TD>

		</TR>

		<TR>

			<TD><B>Salt Usage:</B></TD>

			<TD><INPUT TYPE="radio" NAME="salt_usage" VALUE="Heavy"> Heavy</TD>

			<TD><INPUT TYPE="radio" NAME="salt_usage" VALUE="Moderate"> Moderate</TD>

			<TD><INPUT TYPE="radio" NAME="salt_usage" VALUE="Light"> Light</TD>

			<TD><INPUT TYPE="radio" NAME="salt_usage" VALUE="No salt"> No added salt</TD>

			<TD></TD>

		</TR>

		<TR>

			<TD><B>Sugar Usage</B></TD>

			<TD><INPUT TYPE="radio" NAME="sugar_usage" VALUE="Heavy"> Heavy </TD>

			<TD><INPUT TYPE="radio" NAME="sugar_usage" VALUE="Moderate"> Moderate </TD>

			<TD><INPUT TYPE="radio" NAME="sugar_usage" VALUE="Light"> Light </TD>

			<TD><INPUT TYPE="radio" NAME="sugar_usage" VALUE="No sugar"> No added sugar </TD>

			<TD></TD>

		</TR>

		<TR>

			<TD colspan="2"><B>You feel your diet is:</B> </TD>

			<TD><INPUT TYPE="radio" NAME="diet" VALUE="Healthy"> Healthy</TD>

			<TD><INPUT TYPE="radio" NAME="diet" VALUE="Fair"> Fair</TD>

			<TD><INPUT TYPE="radio" NAME="diet" VALUE="Poor"> Poor</TD>

			<TD></TD>

		</TR>

		<TR>

			<TD><B>Coments:</B> </TD>

			<TD Colspan="5"><INPUT TYPE="text" size="50" NAME="diet_comments"></TD>

		</TR>

		<TR>

			<TD><B>Alcohol:</B> </TD>

			<TD colspan="2"><INPUT TYPE="text" size="2" NAME="alc_per_day"> Glasses per day</TD>

			<TD colspan="2"><INPUT TYPE="text" size="2" NAME="alc_per_week"> Glasses per week</TD>

			<TD></TD>

		</TR>

		<TR>

			<TD colspan="2"><B>Recreational Drugs:</B> </TD>

			<TD colspan="2">Which Drugs? <INPUT TYPE="text" size="20" NAME="recr_drugs"></TD>

			<TD colspan="2">How often? <INPUT TYPE="text" size="20" NAME="recr_drugs_often"></TD>

		</TR>

		<TR>

			<TD colspan="4"><B>Do you feel you have a problem with alcohol or drugs?</B>

				<SELECT NAME="alc_drug_problem"><option><option>YES<OPTION>NO</SELECT>

			</TD>

			<TD colspan="2">Explain:<INPUT TYPE="text" size="20" NAME="alc_drug_problem_explain"></TD>

		</TR>

		<TR>

			<TD colspan="2"><B>Tobacco:</B> </TD>

			<TD colspan="2">Do you smoke? <SELECT NAME="current_smoke"><option><option>YES<OPTION>NO</SELECT> </TD>

			<TD colspan="2">Have you ever smoked? <SELECT NAME="ever_smoked"><option><option>YES<OPTION>NO</SELECT> </TD>

		</TR>

		<TR>

			<TD><INPUT TYPE="text" size="2" NAME="cig_per_day_now"> per day now.</TD>

			<TD colspan="2"><INPUT TYPE="text" size="2" NAME="cig_per_day_past"> per day in the past.</TD>

			<TD colspan="3">How long have you been smoking? <INPUT TYPE="text" size="2" NAME="how_long_smoke">Years </TD>

		</TR>

		<TR>

			<TD colspan="4">If you no longer smoke, when did you quit? <INPUT TYPE="text" size="10" maxlength="10" NAME="smoke_quit" VALUE="mm/dd/yyyy" maxlength="10">

				

						

			</TD>

			<TD colspan="2">Would you like to quit? <SELECT NAME="like_to_quit"><option><option>YES<OPTION>NO</SELECT></TD>

		</TR>

		<TR>

			<TD><B>exercise:</B></TD>

			<TD colspan="2">Do you exercise regularly? <SELECT NAME="exercise_reg"><option><option>YES<OPTION>NO</SELECT></TD>

			<TD colspan ="3">What types? <INPUT TYPE="text" size="40" NAME="exercise_types"></TD>

		</TR>

		<TR>

			<TD></TD>

			<TD colspan="2">How many times per week? <INPUT TYPE="text" size="2" NAME="exercise_per_week"></TD>

			<TD colspan ="2">For how long each time? <INPUT TYPE="text" size="3" NAME="exercise_minutes">Minutes</TD>

		</TR>

		<TR>

			<TD><B>Seat belt use:</B></TD>

			<TD colspan="5"><SELECT NAME="seat_belt"><option><option>Always<OPTION>Usually<OPTION>Seldom<OPTION>Never</SELECT></TD>

		</TR>

		<TR>

			<TD colspan="5">Have you ever been pushed, shoved, slapped, hit, or verbally abused by anyone?</TD>

			<TD><SELECT NAME="ever_been_molested"><option><option>YES<OPTION>NO</SELECT></TD>

		</TR>

		<TR>

			<TD colspan="5">Have you ever pushed, shoved, slapped, hit, or verbally abused another individual?</TD>

			<TD><SELECT NAME="ever_molested_other"><option><option>YES<OPTION>NO</SELECT></TD>

		</TR>

		</TABLE>

		<!-- eof habits -->



	</TD>

</TR>

<TR>

	<TD>

<a href="javascript:top.restoreSession();document.habits_form.submit();" class="link_submit">[Save]</a>

<br>



<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>



	</TD>

</TR>

<TR>

	<TD>

	</TD>

</TR>

</form>

</table>

<!-- ends main form -->



<?php

formFooter();

?>

