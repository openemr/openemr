<?php
//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
include_once("../globals.php");
include_once("$srcdir/registry.inc");
include_once("$srcdir/sql.inc");
include_once("../../library/acl.inc");
include_once("batchcom.inc.php");

// gacl control
$thisauth = acl_check('admin', 'batchcom');

if (!$thisauth) {
  echo "<html>\n<body>\n";
  echo "<p>".xl('You are not authorized for this.','','','</p>')."\n";
  echo "</body>\n</html>\n";
  exit();
 }

// menu arrays (done this way so it's easier to validate input on validate selections)
$choices=Array (xl('CSV File'),xl('Email'),xl('Phone call list'));
$gender=Array (xl('Any'),xl('Male'),xl('Female'));
$hipaa=Array (xl('NO'),xl('YES'));
$sort_by=Array (xl('Zip Code')=>'patient_data.postal_code',xl('Last Name')=>'patient_data.lname',xl('Appointment Date')=>'last_ap' );

// process form
if ($_POST['form_action']=='Process') {
    //validation uses the functions in batchcom.inc.php
    //validate dates
    if (!check_date_format($_POST['app_s'])) $form_err.=xl('Date format for "appointment start" is not valid','','<br>');
    if (!check_date_format($_POST['app_e'])) $form_err.=xl('Date format for "appointment end" is not valid','','<br>');
    if (!check_date_format($_POST['seen_since'])) $form_err.=xl('Date format for "seen since" is not valid','','<br>');
    if (!check_date_format($_POST['not_seen_since'])) $form_err.=xl('Date format for "not seen since" is not valid','','<br>');
    // validate numbers
    if (!check_age($_POST['age_from'])) $form_err.=xl('Age format for "age from" is not valid','','<br>');
    if (!check_age($_POST['age_upto'])) $form_err.=xl('Age format for "age up to" is not valid','','<br>');
    // validate selections
    if (!check_select($_POST['gender'],$gender)) $form_err.=xl('Error in "Gender" selection','','<br>');
    if (!check_select($_POST['process_type'],$choices)) $form_err.=xl('Error in "Process" selection','','<br>');
    if (!check_select($_POST['hipaa_choice'],$hipaa)) $form_err.=xl('Error in "HIPAA" selection','','<br>');
    if (!check_select($_POST['sort_by'],$sort_by)) $form_err.=xl('Error in "Sort By" selection','','<br>');
    // validates and or
    if (!check_yes_no ($_POST['and_or_gender'])) $form_err.=xl('Error in YES or NO option','','<br>');
    if (!check_yes_no ($_POST['and_or_app_within'])) $form_err.=xl('Error in YES or NO option','','<br>');
    if (!check_yes_no ($_POST['and_or_seen_since'])) $form_err.=xl('Error in YES or NO option','','<br>');
    if (!check_yes_no ($_POST['and_or_not_seen_since'])) $form_err.=xl('Error in YES or NO option','','<br>');

    //process sql
    if (!$form_err) {

           
         $sql="select patient_data.*, cal_events.pc_eventDate as next_appt,cal_events.pc_startTime as appt_start_time,cal_date.last_appt,forms.last_visit from patient_data left outer join openemr_postcalendar_events as cal_events on patient_data.pid=cal_events.pc_pid and curdate() < cal_events.pc_eventDate left outer join (select pc_pid,max(pc_eventDate) as last_appt from openemr_postcalendar_events where curdate() >= pc_eventDate group by pc_pid ) as cal_date on cal_date.pc_pid=patient_data.pid left outer join (select pid,max(date) as last_visit from forms where curdate() >= date group by pid) as forms on forms.pid=patient_data.pid";
        //appointment dates
        if ($_POST['app_s']!=0 AND $_POST['app_s']!='') {
            $and=where_or_and ($and);        
            $sql_where_a=" $and cal_events.pc_eventDate > '".$_POST['app_s']."'";
        } 
        if ($_POST['app_e']!=0 AND $_POST['app_e']!='') {
            $and=where_or_and ($and);
            $sql_where_a.=" $and cal_events.pc_endDate < '".$_POST['app_e']."'";
        } 
        $sql.=$sql_where_a;
        
        // encounter dates
        if ($_POST['seen_since']!=0 AND $_POST['seen_since']!='') {
            $and=where_or_and ($and);
            $sql.=" $and forms.date > '".$_POST['seen_since']."' " ;
        } 
        if ($_POST['seen_upto']!=0 AND $_POST['not_seen_since']!='') {
            $and=where_or_and ($and);
            $sql.=" $and forms.date > '".$_POST['seen_since']."' " ;
        }

        // age
        if ($_POST['age_from']!=0 AND $_POST['age_from']!='') {
            $and=where_or_and ($and);
            $sql.=" $and DATEDIFF( CURDATE( ), patient_data.DOB )/ 365.25 >= '".$_POST['age_from']."' ";
        } 
        if ($_POST['age_upto']!=0 AND $_POST['age_upto']!='') {
            $and=where_or_and ($and);
            $sql.=" $and DATEDIFF( CURDATE( ), patient_data.DOB )/ 365.25 <= '".$_POST['age_upto']."' ";
        }

        // gender
        if ($_POST['gender']!='Any') {
            $and=where_or_and ($and);
            $sql.=" $and patient_data.sex='".$_POST['gender']."' ";
        }

        // hipaa overwrite
        if ($_POST['hipaa_choice']!='NO') {
            $and=where_or_and ($and);
            $sql.=" $and patient_data.hipaa_mail='YES' ";
        }
        
        switch ($_POST['process_type']):
            case $choices[1]: // Email
                $and=where_or_and ($and);
                $sql.=" $and patient_data.email IS NOT NULL ";
            break;
        endswitch;

        // sort by
        $sql.=' ORDER BY '.$_POST['sort_by'];
        //echo $sql;
        // send query for results.
        $res = sqlStatement($sql);

        // if no results.
        if (mysql_num_rows($res)==0){
	?>
        <html>
	<head>
	<?php html_header_show();?>
	<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
	<link rel="stylesheet" href="batchcom.css" type="text/css">
	<script type="text/javascript" src="../../library/overlib_mini.js"></script>
	<script type="text/javascript" src="../../library/calendar.js"></script>
	</head>
	<body class="body_top">
	<!-- larry's sms/email notification -->
	<span class="title"><?php include_once("batch_navigation.php");?></span>
	<!--- end of larry's insert -->
	<span class="title"><?php xl('Batch Communication Tool','e')?></span>
	<br><br>
	<div class="text">
        <?php    
            echo (xl('No results found, please try again.','','<br>'));
        ?> </div></body></html> <?php
        //if results
        } else { 
            switch ($_POST['process_type']):
                case $choices[0]: // CSV File
                    require_once ('batchCSV.php');
                break;
                case $choices[1]: // Email
                    require_once ('batchEmail.php');
                break;
                case $choices[2]: // Phone list
                    require_once ('batchPhoneList.php');
                break;
            endswitch;
        }
        // end results

        exit ();
    } 
}

//START OUT OUR PAGE....
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="batchcom.css" type="text/css">
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>


</head>
<body class="body_top">
<!-- larry's sms/email notification -->
<span class="title"><?php include_once("batch_navigation.php");?></span>
<!--- end of larry's insert -->
<span class="title"><?php xl('Batch Communication Tool','e')?></span>
<br><br>

<!-- for the popup date selector -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<FORM name="select_form" METHOD=POST ACTION="">
<div class="text">
<div class="main_box">
    <table class="table" ><tr><td >
        <?php
        if ($form_err) {
            echo (xl('The following errors occurred')."<br>$form_err<br><br>");
        }

        xl('Process','e')?>:</td><td><SELECT NAME="process_type">
                <?php
                foreach ($choices as $value) {
                    echo ("<option>$value</option>");
                }
                ?>
                </SELECT></td>
            <td>&nbsp;</td><td>&nbsp;</td>
            </tr><tr><td >

        <?php xl('Overwrite HIPAA choice','e')?> :</td><td align='left'><SELECT NAME="hipaa_choice">
                                    <?php
                                    foreach ($hipaa as $value) {
                                        echo ("<option>$value</option>");
                                    }
                                    ?>
                                    </SELECT></td>
           <td>&nbsp;</td><td>&nbsp;</td>
           </tr><tr><td>
           <?php xl('Age From','e')?>:<INPUT TYPE="text" size="2" NAME="age_from"></td><td> <?php xl('Up to','e')?>:<INPUT TYPE="text" size="2" NAME="age_upto"></td><td>
        <?php xl('And','e')?>:<INPUT TYPE="radio" NAME="and_or_gender" value="AND" checked>, <?php xl('Or','e')?>:<INPUT TYPE="radio" NAME="and_or_gender" value="OR"></td><td>
        <?php xl('Gender','e')?> :<SELECT NAME="gender">
                <?php
                foreach ($gender as $value) {
                    echo ("<option>$value</option>");
                }
                ?>
                </SELECT></td>
           </tr><tr><td>
        <!-- later gator
        <br>Insurance: <SELECT multiple NAME="insurance" Rows="10" cols="20">

                        </SELECT>
        -->
      <?php xl('And','e')?>:<INPUT TYPE="radio" NAME="and_or_app_within" value="AND" checked>, <?php xl('Or','e')?>:<INPUT TYPE="radio" NAME="and_or_app_within" value="OR"></td><td> <?php xl('Appointment within','e')?>:</td><td><INPUT TYPE='text' size='12' NAME='app_s'> <a href="javascript:show_calendar('select_form.app_s')"
    title="<?php xl('Click here to choose a date','e')?>"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0' ></a></td><td>

        <?php xl('And','e')?> :  <INPUT TYPE='text' size='12' NAME='app_e'> <a href="javascript:show_calendar('select_form.app_e')"
    title="<?php xl('Click here to choose a date','e')?>"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0' ></a></td>
     </tr><tr><td>
   
     <?php xl('And','e')?>:<INPUT TYPE="radio" NAME="and_or_seen_since" value="AND" checked>, <?php xl('Or','e')?>:<INPUT TYPE="radio" NAME="and_or_seen_since" value="OR"></td><td> <?php xl('Seen since','e')?> :</td><td><INPUT TYPE='text' size='12' NAME='seen_since'> <a href="javascript:show_calendar('select_form.seen_since')"
    title="<?php xl('Click here to choose a date','e')?>"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a></td>
  <td>&nbsp;</td>
   </tr><tr><td>

        <?php xl('And','e')?>:<INPUT TYPE="radio" NAME="and_or_not_seen_since" value="AND" checked>, <?php xl('Or','e')?>:<INPUT TYPE="radio" NAME="and_or_not_seen_since" value="OR"></td><td> <?php xl('Not seen since','e')?> :</td><td><INPUT TYPE='text' size='12' NAME='not_seen_since'> <a href="javascript:show_calendar('select_form.not_seen_since')"
    title="<?php xl('Click here to choose a date','e')?>"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a></td>
 <td>&nbsp;</td>
   </tr><tr><td>
        <?php xl('Sort by','e')?> :</td><td><SELECT NAME="sort_by">
                <?php
                foreach ($sort_by as $key => $value) {
                    echo ("<option value=\"".$value."\">$key</option>");
                }
                ?>
                </SELECT></td>
     <td>&nbsp;</td><td>&nbsp;</td>
       </tr><tr><td colspan='3'>
    (<?php xl('Fill here only if sending email notification to patients','e')?>)</td>
   <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
   </tr><tr><td>
    <?php xl('Email Sender','e')?> :</td><td><INPUT TYPE="text" NAME="email_sender" value="your@example.com"></td>
  <td>&nbsp;</td><td>&nbsp;</td>
   </tr><tr><td>
    <?php xl('Email Subject','e')?>:</td><td><INPUT TYPE="text" NAME="email_subject" value="From your clinic"></td>
  <td>&nbsp;</td><td>&nbsp;</td>
  </tr><tr><td colspan='3'>
    <?php xl('Email Text, Usable Tag: ***NAME*** , i.e. Dear ***NAME***','e')?></td>
   <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
   <tr><td colspan='4'>
    <TEXTAREA NAME="email_body" ROWS="8" COLS="40"></TEXTAREA></td>
    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
     </tr><tr><td>

    <INPUT TYPE="submit" name="form_action" value=<?php xl('Process','e','\'','\''); ?>> </td><td><?php xl('Process takes some time','e')?></td> <td>&nbsp;</td><td>&nbsp;</td></tr>
</table>
</div>
</div>
</FORM>

