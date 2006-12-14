<?php
#######################################################
# Progress Notes Form created by Kam Sharifi	      #
# kam@sharmen.com				      #
#######################################################
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: progressnotes");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/progressnotes/save.php?mode=new" name="my_form">
<span class="title">Progress Notes</span><br><br>

<table width=100%>
<b>
<span class=text>P: </span><input size=3 type=entry name="prog_p" value="" >
<span class=text>R: </span><input size=3 type=entry name="prog_r" value="" >
<span class=text>BP: </span><input size=3 type=entry name="prog_bp" value="" >
<span class=text>HT: </span><input size=3 type=entry name="prog_ht" value="" >
<span class=text>WT: </span><input size=3 type=entry name="prog_wt" value="" >
<span class=text>TEMP: </span><input size=3 type=entry name="prog_temp" value="" >
<span class=text>LMP: </span><input size=3 type=entry name="prog_lmp" value="" >
<br><span class=text>Last Pap Smear: </span><input size=3 type=entry name="prog_last_pap_smear" value="" >
<span class=text>Last Td. Booster: </span><input size=3 type=entry name="prog_last_td_booster" value="" >
<span class=text>Allergies: </span><input size=3 type=entry name="prog_allergies" value="" >
<span class=text>Last Mammogram: </span><input size=3 type=entry name="prog_last_mammogram" value="" >
</b>
</table>
<br>

<span class=text><b>Present Complaint*:</b> </span><br><textarea cols=40 rows=8 wrap=virtual name="prog_present_complaint" ></textarea>

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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox2" TYPE=CHECKBOX NAME="prog_skin_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox8" TYPE=CHECKBOX NAME="prog_skin_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox7" TYPE=CHECKBOX NAME="prog_head_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox9" TYPE=CHECKBOX NAME="prog_head_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox10" TYPE=CHECKBOX NAME="prog_eyes_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox11" TYPE=CHECKBOX NAME="prog_eyes_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox12" TYPE=CHECKBOX NAME="prog_ears_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox13" TYPE=CHECKBOX NAME="prog_ears_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox14" TYPE=CHECKBOX NAME="prog_nose_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox16" TYPE=CHECKBOX NAME="prog_nose_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox17" TYPE=CHECKBOX NAME="prog_throat_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox15" TYPE=CHECKBOX NAME="prog_throat_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox19" TYPE=CHECKBOX NAME="prog_teeth_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox20" TYPE=CHECKBOX NAME="prog_teeth_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox18" TYPE=CHECKBOX NAME="prog_neck_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox21" TYPE=CHECKBOX NAME="prog_neck_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox22" TYPE=CHECKBOX NAME="prog_chest_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox23" TYPE=CHECKBOX NAME="prog_chest_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox24" TYPE=CHECKBOX NAME="prog_breast_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox25" TYPE=CHECKBOX NAME="prog_breast_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox26" TYPE=CHECKBOX NAME="prog_lungs_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox27" TYPE=CHECKBOX NAME="prog_lungs_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox28" TYPE=CHECKBOX NAME="prog_heart_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox29" TYPE=CHECKBOX NAME="prog_heart_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox30" TYPE=CHECKBOX NAME="prog_abdomen_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox31" TYPE=CHECKBOX NAME="prog_abdomen_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox32" TYPE=CHECKBOX NAME="prog_spine_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox33" TYPE=CHECKBOX NAME="prog_spine_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox34" TYPE=CHECKBOX NAME="prog_extremeities_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox35" TYPE=CHECKBOX NAME="prog_extremeities_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox36" TYPE=CHECKBOX NAME="prog_lowback_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox37" TYPE=CHECKBOX NAME="prog_lowback_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox38" TYPE=CHECKBOX NAME="prog_neuro_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox39" TYPE=CHECKBOX NAME="prog_neuro_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox40" TYPE=CHECKBOX NAME="prog_rectal_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox41" TYPE=CHECKBOX NAME="prog_rectal_ne" VALUE=""></TD>
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
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox42" TYPE=CHECKBOX NAME="prog_pelvic_abn" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=34>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT>
                    <TABLE WIDTH="100%" BORDER=0 CELLSPACING=0 CELLPADDING=0 NOF=TE>
                        <TR>
                            <TD ALIGN="CENTER"><INPUT ID="Forms Checkbox43" TYPE=CHECKBOX NAME="prog_pelvic_ne" VALUE=""></TD>
                        </TR>
                    </TABLE>
                    <FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"></FONT></TD>
            <TD WIDTH=324>
                <P><FONT FACE="Arial,Helvetica,Geneva,Sans-serif,sans-serif"><B>PELVIC:</B></FONT><B></B></P>
            </TD>
        </TR>
    </TABLE> 

<br>


<span class=text><b>HEALTH EDUCATION PROVIDED<br>ASSESSMENT:</b></span><br><textarea cols=40 rows=8 wrap=virtual name="prog_assessment" ></textarea>

<br><br>

<span class=text><b>Plan:</b></span><br><textarea cols=40 rows=8 wrap=virtual name="prog_plan" ></textarea>

<br><br>
<td><input size=3 type=entry name="prog_breast_se" value="" >&nbsp;<span class=text><b>Breast Self Examination </span></td><br></b>
<td><input size=3 type=entry name="prog_dental_h" value="" >&nbsp;<span class=text><b>Dental Health </span></td><br></b>
<td><input size=3 type=entry name="prog_diagnosis" value="" >&nbsp;<span class=text><b>Diagnosis/Prognosis </span></td><br></b>
<td><input size=3 type:entry name="prog_injur_p" value="" >&nbsp;<span class=text><b>Injury Prevention </span></td><br></b>
<td><input size=3 type=entry name="prog_new_treat" value="" >&nbsp;<span class=text><b>New Treatment/Medication </span></td><br></b>
<td><input size=3 type=entry name="prog_nutrition_e" value="" >&nbsp;<span class=text><b>Nutrition/Exercise </span></td><br></b>
<td><input size=3 type=entry name="prog_sexual_p" value="" >&nbsp;<span class=text><b>Sexual Practice </span></td><br></b>
<td><input size=3 type=entry name="prog_substance_a" value="" >&nbsp;<span class=text><b>Substance Abuse </span></td><br></b>


<br>
<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link">[Don't Save]</a>
</form>
<?php
formFooter();
?>
