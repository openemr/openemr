<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: Forms3_Cardiology");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <?php xl("[do not save]",'e') ?> </a>
<form method=post action="<?echo $rootdir;?>/forms/Forms3_Cardiology/save.php?mode=new" name="Forms3_Cardiology" onSubmit="return top.restoreSession()">
<hr>
<h1> <?php xl("Forms3_Cardiology",'e') ?> </h1>
<hr>
<input type="submit" name="submit form" value="submit form" />

<table width="100%" cellpadding="0" cellspacing="0">

    <tr>

        <td class='text'   valign="top">

            <table width="100%" cellpadding="0" cellspacing="0">

                <tr>

                    <td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > date</td> <td class='text'  ><input type="text" name="_date"  /></td></tr>

</table>
                    </td>

                    <td class='text'    style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > name</td> <td class='text'  ><input type="text" name="_name"  /></td></tr>

</table>
                    </td>

                    
                </tr>

		<tr>

			<td class='text'   colspan='2' style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > chief complaint</td> <td class='text'  ><input type="text" name="_chief_complaint"  /></td></tr>

</table>
            </td></tr>

            </table>
        </td>
    </tr>

    <tr>

        <td class='text'   valign="top" style="border: 1px #000000 solid; height: 15px;">

            <table width="100%" cellpadding="0" cellspacing="0">                

                <tr>

                    

                    <td class='text'   colspan="2" style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > wt</td> <td class='text'  ><input type="text" name="_wt"  /></td></tr>

</table>
                    </td>

                    <td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > bp</td> <td class='text'  ><input type="text" name="_bp"  /></td></tr>

</table>
                    </td>

                     <td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > p</td> <td class='text'  ><input type="text" name="_p"  /></td></tr>

</table>
                    </td>

                    <td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > t</td> <td class='text'  ><input type="text" name="_t"  /></td></tr>

</table>
                    </td>

		<td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > r</td> <td class='text'  ><input type="text" name="_r"  /></td></tr>

</table>
                  </td>

		<td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > ht</td> <td class='text'  ><input type="text" name="_ht"  /></td></tr>

</table>
                  </td>
                </tr>    

                

            </table>
        </td>
    </tr>

    

    

    

    

    <tr>

         <td class='text'   valign="top" style="border: 1px #000000 solid; height: 15px;">

            <table width="100%" cellpadding="0" cellspacing="0">

                <tr>

                    <td class='text'   colspan="5" align="center" style="border: 1px #000000 solid; height: 15px;">

                        <h3>

                            HPI

                        </h3>
                    </td>
                </tr>

                <tr>

                    

                    <td class='text'    style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > location</td> <td class='text'  ><input type="text" name="_location"  /></td></tr>

</table>
                    </td>

                    <td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > quality</td> <td class='text'  ><input type="text" name="_quality"  /></td></tr>

</table>
                    </td>

                     <td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > severity</td> <td class='text'  ><input type="text" name="_severity"  /></td></tr>

</table>
                    </td>

                    <td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > duration</td> <td class='text'  ><input type="text" name="_duration"  /></td></tr>

</table>
                    </td>	

                    <td class='text'   style="border: 1px #000000 solid;">&nbsp;

                       
                  </td>		
                </tr>

<tr>

<td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > timing</td> <td class='text'  ><input type="text" name="_timing"  /></td></tr>

</table>
            </td>

		<td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > context</td> <td class='text'  ><input type="text" name="_context"  /></td></tr>

</table>
            </td>

		<td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > modifying factors</td> <td class='text'  ><input type="text" name="_modifying_factors"  /></td></tr>

</table>
            </td>

		<td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > signs symptoms</td> <td class='text'  ><input type="text" name="_signs_symptoms"  /></td></tr>

</table>
            </td>

		<td class='text'   style="border: 1px #000000 solid;">

<table>

<tr><td class='text'  > status of chronic illness</td> <td class='text'  ><input type="text" name="_status_of_chronic_illness"  /></td></tr>

</table>
            </td></tr>

                

                

            </table>
        </td>
    </tr>

    

     <tr>

         <td class='text'   valign="top" style="border: 1px #000000 solid; height: 15px;">

            <table width="100%" cellpadding="0" cellspacing="0">

                <tr>

                    <td class='text'   align="center" style="border: 1px #000000 solid; height: 15px;">                       

                            ROS

                        
                    </td>

	<td class='text'   align="center" style="border: 1px #000000 solid; height: 15px;">

                        

                            +

                        
                  </td>

	<td class='text'   align="center" style="border: 1px #000000 solid; height: 15px;">

                        

                            -

                       
                  </td>
                </tr>

                <tr>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">
                        Systemic</td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > systemic positive</td> <td class='text'  ><input type="text" name="_systemic_positive"  /></td></tr>

</table>
                    </td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > systemic negative</td> <td class='text'  ><input type="text" name="_systemic_negative"  /></td></tr>

</table>
                    </td>
                </tr>

                <tr>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">
                        ENT</td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > ent positive</td> <td class='text'  ><input type="text" name="_ent_positive"  /></td></tr>

</table>
                    </td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > ent negative</td> <td class='text'  ><input type="text" name="_ent_negative"  /></td></tr>

</table>
                    </td>
                </tr>

                <tr>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">
                        Eyes</td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > eyes positive</td> <td class='text'  ><input type="text" name="_eyes_positive"  /></td></tr>

</table>
                    </td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > eyes negative</td> <td class='text'  ><input type="text" name="_eyes_negative"  /></td></tr>

</table>
                    </td>
                </tr>

                <tr>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">
                        Lymph</td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > lymph positive</td> <td class='text'  ><input type="text" name="_lymph_positive"  /></td></tr>

</table>
                    </td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > lymph negative</td> <td class='text'  ><input type="text" name="_lymph_negative"  /></td></tr>

</table>
                    </td>
                </tr>

                <tr>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">
                        Resp</td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > resp positive</td> <td class='text'  ><input type="text" name="_resp_positive"  /></td></tr>

</table>
                    </td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > resp negative</td> <td class='text'  ><input type="text" name="_resp_negative"  /></td></tr>

</table>
                    </td>
                </tr>

                <tr>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">
                        CV</td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > cv positive</td> <td class='text'  ><input type="text" name="_cv_positive"  /></td></tr>

</table>
                    </td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > cv negative</td> <td class='text'  ><input type="text" name="_cv_negative"  /></td></tr>

</table>
                    </td>
                </tr>

                <tr>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">
                        GI</td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > gi positive</td> <td class='text'  ><input type="text" name="_gi_positive"  /></td></tr>

</table>
                    </td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > gi negative</td> <td class='text'  ><input type="text" name="_gi_negative"  /></td></tr>

</table>
                    </td>
                </tr>

                <tr>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">
                        GU</td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > gu positive</td> <td class='text'  ><input type="text" name="_gu_positive"  /></td></tr>

</table>
                    </td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > gu negative</td> <td class='text'  ><input type="text" name="_gu_negative"  /></td></tr>

</table>
                    </td>
                </tr>

                <tr>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">
                        Skin</td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > skin positive</td> <td class='text'  ><input type="text" name="_skin_positive"  /></td></tr>

</table>
                    </td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > skin negative</td> <td class='text'  ><input type="text" name="_skin_negative"  /></td></tr>

</table>
                    </td>
                </tr>

                <tr>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">
                        MS</td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

                      
                      <table>

<tr><td class='text'  > ms positive</td> <td class='text'  ><input type="text" name="_ms_positive"  /></td></tr>
</table>
                  </td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > ms negative</td> <td class='text'  ><input type="text" name="_ms_negative"  /></td></tr>

</table>
                    </td>
                </tr>

                <tr>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">
                        Psych</td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > psych positive</td> <td class='text'  ><input type="text" name="_psych_positive"  /></td></tr>

</table>
                    </td>

                    <td class='text'   align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > psych negative</td> <td class='text'  ><input type="text" name="_psych_negative"  /></td></tr>

</table>
                    </td>
                </tr>

                <tr>

                    <td class='text'    align="left" colspan="3" style="border-right: #000000 1px solid; border-top: #000000 1px solid;

                        border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px">

<table>

<tr><td class='text'  > all other ros negative </td> <td class='text'  ><textarea name="_all_other_ros_negative_"  rows="4" cols="40"></textarea></td></tr>

</table>
                    </td>
                </tr>

                

                

                

            </table>
        </td>
    </tr>

    

    

      <tr>

         <td class='text'   valign="top" style="border: 1px #000000 solid; height: 15px;">

            <table width="100%" cellpadding="0" cellspacing="0">

                <tr>

                    <td class='text'   colspan="3" align="center" style="border: 1px #000000 solid; height: 15px;">                      

<table>

<tr><td class='text'  > past famiy social history</td> <td class='text'  ><textarea name="_past_famiy_social_history"  rows="4" cols="40"></textarea></td></tr>

</table>
                    </td>
                </tr>

                

                 <tr>

                    

                    <td class='text'   style="border: 1px #000000 solid; height: 27px;">

<table>

<tr><td class='text'  > ph no change since</td> <td class='text'  ><input type="text" name="_ph_no_change_since"  /></td></tr>

</table>
                    </td>

                    <td class='text'   style="border: 1px #000000 solid; height: 27px;">

<table>

<tr><td class='text'  > fh no change since</td> <td class='text'  ><input type="text" name="_fh_no_change_since"  /></td></tr>

</table>
                    </td>

                     <td class='text'   style="border: 1px #000000 solid; height: 27px;">

<table>

<tr><td class='text'  > sh no change since</td> <td class='text'  ><input type="text" name="_sh_no_change_since"  /></td></tr>

</table>
                    </td>
                </tr>  

<tr><td class='text'   colspan='3'>

<table>

<tr><td class='text'  ><?php xl("Examination",'e') ?></td> 
<td class='text'  ><textarea name="examination"  rows="4" cols="40"></textarea></td></tr>

</table>
</td></tr>            

                

            </table>
        </td>
    </tr>

</table>
<table></table><input type="submit" name="submit form" value="submit form" />
</form>
<a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <?php xl("[do not save]",'e') ?> </a>
<?php
formFooter();
?>
