<?php

#######################################################

# Progress Notes Form created by Kam Sharifi	      #

# kam@sharmen.com				      #

#######################################################

include_once("../../globals.php");

?>

<html><head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>

<body class="body_top">

<?php

include_once("$srcdir/api.inc");

$obj = formFetch("form_progressnotes", $_GET["id"]);

?>

<form method=post action="<?php echo $rootdir?>/forms/progressnotes/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form">

<span class="title">Progress Notes</span><Br><br>



<table width=100%>

<b>

<span class=text>P: </span><input size=3 type=entry name="prog_p" value="<?php echo $obj{"prog_p"};?>" >

<span class=text>R: </span><input size=3 type=entry name="prog_r" value="<?php echo $obj{"prog_r"};?>" >

<span class=text>BP: </span><input size=3 type=entry name="prog_bp" value="<?php echo $obj{"prog_bp"};?>" >

<span class=text>HT: </span><input size=3 type=entry name="prog_ht" value="<?php echo $obj{"prog_ht"};?>" >

<span class=text>WT: </span><input size=3 type=entry name="prog_wt" value="<?php echo $obj{"prog_wt"};?>" >

<span class=text>TEMP: </span><input size=3 type=entry name="prog_temp" value="<?php echo $obj{"prog_temp"};?>" >

<span class=text>LMP: </span><input size=3 type=entry name="prog_lmp" value="<?php echo $obj{"prog_lmp"};?>" >

<br><span class=text>Last Pap Smear: </span><input size=3 type=entry name="prog_last_pap_smear" value="<?php echo $obj{"prog_last_pap_smear"};?>" >

<span class=text>Last Td. Booster: </span><input size=3 type=entry name="prog_last_td_booster" value="<?php echo $obj{"prog_last_td_booster"};?>" >

<span class=text>Allergies: </span><input size=3 type=entry name="prog_allergies" value="<?php echo $obj{"prog_allergies"};?>" >

<span class=text>Last Mammogram: </span><input size=3 type=entry name="prog_last_mammogram" value="<?php echo $obj{"prog_last_mammogram"};?>" >

</b>

</table>

<br>



<span class=text><b>Present Complaint*:</b> </span><br><textarea cols=40 rows=8 wrap=virtual name="prog_present_complaint" ><?php echo $obj{"prog_present_complaint"};?></textarea>



<br><br>

<b>Past Medical History</b>



    <TABLE ID="Table1" BORDER=1 CELLSPACING=2 CELLPADDING=1 WIDTH="100%" >

        <TR>

            <TD WIDTH=53>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>ABN</B></FONT><B></B></P>

            </TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>NE</B></FONT><B></B></P>

            </TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>PHYSICAL EXAMINATION -Comments</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD HEIGHT=14>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox2" TYPE=CHECKBOX NAME="prog_skin_abn" <?if ($obj{"prog_skin_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox8" TYPE=CHECKBOX NAME="prog_skin_ne" <?if ($obj{"prog_skin_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>SKIN: no significant lesions</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD HEIGHT=14>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox7" TYPE=CHECKBOX NAME="prog_head_abn" <?if ($obj{"prog_head_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox9" TYPE=CHECKBOX NAME="prog_head_ne" <?if ($obj{"prog_head_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>HEAD: normocephalic. no headache</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD HEIGHT=11>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox10" TYPE=CHECKBOX NAME="prog_eyes_abn" <?if ($obj{"prog_eyes_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox11" TYPE=CHECKBOX NAME="prog_eyes_ne" <?if ($obj{"prog_eyes_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>EYES: perla. eom satisfactory</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox12" TYPE=CHECKBOX NAME="prog_ears_abn" <?if ($obj{"prog_ears_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox13" TYPE=CHECKBOX NAME="prog_ears_ne" <?if ($obj{"prog_ears_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>EARS: drums intact</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD HEIGHT=19>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox14" TYPE=CHECKBOX NAME="prog_nose_abn" <?if ($obj{"prog_nose_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox16" TYPE=CHECKBOX NAME="prog_nose_ne" <?if ($obj{"prog_nose_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>NOSE: no abnormality</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox17" TYPE=CHECKBOX NAME="prog_throat_abn" <?if ($obj{"prog_throat_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox15" TYPE=CHECKBOX NAME="prog_throat_ne" <?if ($obj{"prog_throat_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>THROAT: dear, no infection</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD HEIGHT=18>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox19" TYPE=CHECKBOX NAME="prog_teeth_abn" <?if ($obj{"prog_teeth_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox20" TYPE=CHECKBOX NAME="prog_teeth_ne" <?if ($obj{"prog_teeth_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>TEETH: good repair, no dentures</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox18" TYPE=CHECKBOX NAME="prog_neck_abn" <?if ($obj{"prog_neck_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox21" TYPE=CHECKBOX NAME="prog_neck_ne" <?if ($obj{"prog_neck_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>NECK: supple, no adenopathy</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox22" TYPE=CHECKBOX NAME="prog_chest_abn" <?if ($obj{"prog_chest_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox23" TYPE=CHECKBOX NAME="prog_chest_ne" <?if ($obj{"prog_chest_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>CHEST: symmetrical, no pain</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox24" TYPE=CHECKBOX NAME="prog_breast_abn" <?if ($obj{"prog_breast_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox25" TYPE=CHECKBOX NAME="prog_breast_ne" <?if ($obj{"prog_breast_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>BREAST: no masses</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox26" TYPE=CHECKBOX NAME="prog_lungs_abn" <?if ($obj{"prog_lungs_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox27" TYPE=CHECKBOX NAME="prog_lungs_ne" <?if ($obj{"prog_lungs_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>LUNGS: dear to P&amp;a. no mono, no rales</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox28" TYPE=CHECKBOX NAME="prog_heart_abn" <?if ($obj{"prog_heart_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox29" TYPE=CHECKBOX NAME="prog_heart_ne" <?if ($obj{"prog_heart_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>HEART: rsr. no cardiomegaly</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox30" TYPE=CHECKBOX NAME="prog_abdomen_abn" <?if ($obj{"prog_abdomen_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox31" TYPE=CHECKBOX NAME="prog_abdomen_ne" <?if ($obj{"prog_abdomen_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>ABDOMEN: non-tender, soft, no masses</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox32" TYPE=CHECKBOX NAME="prog_spine_abn" <?if ($obj{"prog_spine_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox33" TYPE=CHECKBOX NAME="prog_spine_ne" <?if ($obj{"prog_spine_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>SPINE: no abnormalities</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox34" TYPE=CHECKBOX NAME="prog_extremeities_abn" <?if ($obj{"prog_extremeities_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox35" TYPE=CHECKBOX NAME="prog_extremeities_ne" <?if ($obj{"prog_extremeities_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>EXTREMEITIES: no abnormalities</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox36" TYPE=CHECKBOX NAME="prog_lowback_abn" <?if ($obj{"prog_lowback_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox37" TYPE=CHECKBOX NAME="prog_lowback_ne" <?if ($obj{"prog_lowback_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>LOW BACK: rom normal</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox38" TYPE=CHECKBOX NAME="prog_neuro_abn" <?if ($obj{"prog_neuro_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox39" TYPE=CHECKBOX NAME="prog_neuro_ne" <?if ($obj{"prog_neuro_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>NEURO: d(r&gt;&gt;2&gt;&gt;. no abnormal findings</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox40" TYPE=CHECKBOX NAME="prog_rectal_abn" <?if ($obj{"prog_rectal_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox41" TYPE=CHECKBOX NAME="prog_rectal_ne" <?if ($obj{"prog_rectal_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>RECTAL: no abnormalities</B></FONT><B></B></P>

            </TD>

        </TR>

        <TR>

            <TD>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif">&nbsp;</FONT></P>

            </TD>

            <TD WIDTH=40>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox42" TYPE=CHECKBOX NAME="prog_pelvic_abn" <?if ($obj{"prog_pelvic_abn"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=34>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>

                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>

                        <TR>

                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox43" TYPE=CHECKBOX NAME="prog_pelvic_ne" <?if ($obj{"prog_pelvic_ne"} == "on") {echo "checked";};?>></TD>

                        </TR>

                    </TABLE>

                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>

            <TD WIDTH=324>

                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>PELVIC:</B></FONT><B></B></P>

            </TD>

        </TR>

    </TABLE> 



<br>





<span class=text><b>HEALTH EDUCATION PROVIDED<br>ASSESSMENT:</b></span><br><textarea cols=40 rows=8 wrap=virtual name="prog_assessment" ><?php echo $obj{"prog_assessment"};?></textarea>



<br><br>



<span class=text><b>Plan:</b></span><br><textarea cols=40 rows=8 wrap=virtual name="prog_plan" ><?php echo $obj{"prog_plan"};?></textarea>



<br><br>

<td><input size=3 type=entry name="prog_breast_se" value="<?php echo $obj{"prog_breast_se"};?>" >&nbsp;<span class=text><b>Breast Self Examination </span></td><br></b>

<td><input size=3 type=entry name="prog_dental_h" value="<?php echo $obj{"prog_dental_h"};?>" >&nbsp;<span class=text><b>Dental Health </span></td><br></b>

<td><input size=3 type=entry name="prog_diagnosis" value="<?php echo $obj{"prog_diagnosis"};?>" >&nbsp;<span class=text><b>Diagnosis/Prognosis </span></td><br></b>

<td><input size=3 type:entry name="prog_injur_p" value="<?php echo $obj{"prog_injur_p"};?>" >&nbsp;<span class=text><b>Injury Prevention </span></td><br></b>

<td><input size=3 type=entry name="prog_new_treat" value="<?php echo $obj{"prog_new_treat"};?>" >&nbsp;<span class=text><b>New Treatment/Medication </span></td><br></b>

<td><input size=3 type=entry name="prog_nutrition_e" value="<?php echo $obj{"prog_nutrition_e"};?>" >&nbsp;<span class=text><b>Nutrition/Exercise </span></td><br></b>

<td><input size=3 type=entry name="prog_sexual_p" value="<?php echo $obj{"prog_sexual_p"};?>" >&nbsp;<span class=text><b>Sexual Practice </span></td><br></b>

<td><input size=3 type=entry name="prog_substance_a" value="<?php echo $obj{"prog_substance_a"};?>" >&nbsp;<span class=text><b>Substance Abuse </span></td><br></b>







<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>

<br>

<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>

</form>

<?php

formFooter();

?>

