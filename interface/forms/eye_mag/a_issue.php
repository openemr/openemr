<?php
/**
 * add or edit an issue.
 *  taken from /interface/patient_file/summary and adapted... 
 *
 * Copyright (C) 2005-2011 Rod Roark <rod@sunsetsystems.com>
 * Copyright (C) 2015 Ray Magauran <magauran@MedFetch.com> 
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author Ray Magauran <magauran@MedFetch.com> 
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//
$form_folder= "eye_mag";

require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/lists.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/sql.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
require_once($GLOBALS['srcdir'].'/csv_like_join.php');
require_once($GLOBALS['srcdir'].'/htmlspecialchars.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/log.inc');
require_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");
//specifically to build $PMSFH

$thispid = 0 + (empty($_REQUEST['thispid']) ? $pid : $_REQUEST['thispid']);
$info_msg = "";

// A nonempty thisenc means we are to link the issue to the encounter.
// ie. we are going to use this as a billing issue? What?
$thisenc = 0 + (empty($_REQUEST['thisenc']) ? 0 : $_REQUEST['thisenc']);

// A nonempty thistype is an issue type to be forced for a new issue.  What?
$thistype = empty($_REQUEST['thistype']) ? '' : $_REQUEST['thistype'];
$issue = $_REQUEST['issue'];
$thispid = $_REQUEST['thispid'];
$delete = $_REQUEST['delete'];
$form_save = $_REQUEST['form_save'];
$pid = $_SESSION['pid'];
$encounter = $_SESSION['encounter'];
$form_id = $_REQUEST['form_id'];
$form_type = $_REQUEST['form_type'];

if ($thispid=='') $thispid = $pid;
if ($issue && !acl_check('patients','med','','write') ) die(xlt("Edit is not authorized!"));
if ( !acl_check('patients','med','',array('write','addonly') )) die(xlt("Add is not authorized!"));

$patient = getPatientData($thispid, "*");
$PMSFH = build_PMSFH($thispid);

//add in our specific ISSUE_TYPES.  Will need to extract list_options and store these separately too
//transition to using PMSFH soley...  We may not need this at all...
//this is still used to display PMSFHROS radio selectors. 

$ISSUE_TYPES['POH'] = array("Past Ocular History","POH","O","1");
$ISSUE_TYPES['FH'] = array("Family History","FH","O","1");
$ISSUE_TYPES['SOCH'] = array("Social History","SocH","O","1");
$ISSUE_TYPES['ROS'] = array("Review of Systems","ROS","O","1");
 
function QuotedOrNull($fld) {
  if ($fld) return "'".add_escape_custom($fld)."'";
  return "NULL";
}
function row_delete($table, $where) {
    $query = "SELECT * FROM $table WHERE $where";
    $tres = sqlStatement($query);
    $count = 0;
    while ($trow = sqlFetchArray($tres)) {
     $logstring = "";
     foreach ($trow as $key => $value) {
      if (! $value || $value == '0000-00-00 00:00:00') continue;
      if ($logstring) $logstring .= " ";
      $logstring .= $key . "='" . addslashes($value) . "'";
    }
    newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$table: $logstring");
    ++$count;
    }
    if ($count) {
     $query = "DELETE FROM $table WHERE $where";
     sqlStatement($query);
     }
}
if ($delete ==1) {
  row_delete("issue_encounter", "list_id = '$issue'");
  row_delete("lists", "id = '$issue'");
  //need to return something to tell them we did it?
  //need to clear the fields too.  Do it w/ javascript in client
  exit;
 }

function cbvalue($cbname) {
  return $_REQUEST[$cbname] ? '1' : '0';
}

function invalue($inname) {
  return (int) trim($_REQUEST[$inname]);
}

// Do not use this function since quotes are added in query escaping mechanism
// Only keeping since used in the football injury code football_injury.inc.php that is included.
// If start using this function, then incorporate the add_escape_custom() function into it
function txvalue($txname) {
  return "'" . trim($_REQUEST[$txname]) . "'";
}

function rbinput($name, $value, $desc, $colname) {
  global $irow;
  $ret  = "<input type='radio' name='".attr($name)."' value='".attr($value)."'";
  if ($irow[$colname] == $value) $ret .= " checked";
  $ret .= " />".text($desc);
  return $ret;
}

function rbcell($name, $value, $desc, $colname) {
 return "<td width='25%' nowrap>" . rbinput($name, $value, $desc, $colname) . "</td>\n";
}

// Given an issue type as a string, compute its index.
//Not sure of the value of this sub given transition to array $PMSFH
function issueTypeIndex($tstr) {
  global $ISSUE_TYPES;
  $i = 0;
  foreach ($ISSUE_TYPES as $key => $value) {
    if ($key == $tstr) break;
    ++$i;
  }
  return $i;
}

if ($form_save) {
  if ($form_type=='8') { //ROS
    $query="UPDATE form_eye_mag set ROSGENERAL=?,ROSHEENT=?,ROSCV=?,ROSPULM=?,ROSGI=?,ROSGU=?,ROSDERM=?,ROSNEURO=?,ROSPSYCH=?,ROSMUSCULO=?,ROSIMMUNO=?,ROSENDOCRINE=? where id=? and pid=?";
    sqlStatement($query,array($_REQUEST['ROSGENERAL'],$_REQUEST['ROSHEENT'],$_REQUEST['ROSCV'],$_REQUEST['ROSPULM'],$_REQUEST['ROSGI'],$_REQUEST['ROSGU'],$_REQUEST['ROSDERM'],$_REQUEST['ROSNEURO'],$_REQUEST['ROSPSYCH'],$_REQUEST['ROSMUSCULO'],$_REQUEST['ROSIMMUNO'],$_REQUEST['ROSENDOCRINE'],$form_id,$pid));
    exit;
  }
  if ($form_type=='7') { //SocHx
    $newdata = array();
    $fres = sqlStatement("SELECT * FROM layout_options " .
      "WHERE form_id = 'HIS' AND uor > 0 AND field_id != '' " .
      "ORDER BY group_name, seq");
    while ($frow = sqlFetchArray($fres)) {
      $field_id  = $frow['field_id'];
      $newdata[$field_id] = get_layout_form_value($frow);
    }
    updateHistoryData($pid, $newdata);
    if ($_REQUEST['marital_status'] >'') {  
      // have to match input with list_option for marital to not break openEMR
      $query="select * from list_options where list_id='marital'";
      $fres = sqlStatement($query);
      while ($frow = sqlFetchArray($fres)) { 
        if (($_REQUEST['marital_status'] == $frow['option_id'])||($_REQUEST['marital_status'] == $frow['title'])) {
          $status = $frow['option_id'];
          $query = "UPDATE patient_data set status=? where pid=?";
          sqlStatement($query,array($status,$pid));
        }
      }
    }
    if ($_REQUEST['occupation'] > '') { 
      $query = "UPDATE patient_data set occupation=? where pid=?";
      sqlStatement($query,array($_REQUEST['occupation'],$pid));
    }
    exit;
  } //done if social history
  if ($form_type =='6') { //FH
    //we are doing a save for FH
    $query = "UPDATE history_data set 
            relatives_cancer=?,
            relatives_diabetes=?,
            relatives_high_blood_pressure=?,
            relatives_heart_problems=?, 
            relatives_stroke=?,
            relatives_epilepsy=?,
            relatives_mental_illness=?,
            relatives_suicide=?,
            usertext11=?,
            usertext12=?,
            usertext13=?,
            usertext14=?,
            usertext15=?,
            usertext16=?,
            usertext17=?,
            usertext18=? where pid=?";
            //echo $_REQUEST['relatives_cancer'],$_REQUEST['relatives_diabetes'],$_REQUEST['relatives_high_blood_pressure'],$_REQUEST['relatives_heart_problems'],$_REQUEST['relatives_stroke'],$_REQUEST['relatives_epilepsy'],$_REQUEST['relatives_mental_illness'],$_REQUEST['relatives_suicide'],$_REQUEST['usertext11'],$_REQUEST['usertext12'],$_REQUEST['usertext13'],$_REQUEST['usertext14'],$_REQUEST['usertext15'],$_REQUEST['usertext16'],$_REQUEST['usertext17'],$_REQUEST['usertext18'],$pid;
    $resFH = sqlStatement($query,array($_REQUEST['relatives_cancer'],$_REQUEST['relatives_diabetes'],$_REQUEST['relatives_high_blood_pressure'],$_REQUEST['relatives_heart_problems'],$_REQUEST['relatives_stroke'],$_REQUEST['relatives_epilepsy'],$_REQUEST['relatives_mental_illness'],$_REQUEST['relatives_suicide'],$_REQUEST['usertext11'],$_REQUEST['usertext12'],$_REQUEST['usertext13'],$_REQUEST['usertext14'],$_REQUEST['usertext15'],$_REQUEST['usertext16'],$_REQUEST['usertext17'],$_REQUEST['usertext18'],$pid));
    exit;
  } //done if FH

  /* FROM HERE ON OUT ie form_type < 6, we must use the openEMR convention ISSUE_TYPES
   * to keep from breaking reporting stuff - CQM, etc.  We will skip Dental (form_type==4)
   * as it has no place in this H&P.  Save this 
   */

  if ($form_type =='5') { // POH, is a subset of medical_problem, subtype eye
    //$_REQUEST['form_type'] ='0';
    $form_type='0';
    $subtype="eye";
  } else {
    $subtype ='';
  }

  $i = 0;
  $text_type = "unknown";
 
  foreach ($ISSUE_TYPES as $key => $value) {
   if ($i++ == $form_type) $text_type = $key;
  }

  $form_begin = fixDate($_REQUEST['form_begin'], '');
  $form_end   = fixDate($_REQUEST['form_end'], '');

  // if there is an issue with this title already for this patient, 
  // we want to update it, not add a new one.
  // better yet, if $issue is defined, we know we have to update it.
  // if there isn't then see if there was one already.
  if (!$issue) {
    if ($subtype == '') {
      $query = "SELECT id,pid from lists where title=? and type=? and pid=?";
      $issue2 = sqlQuery($query,array($_REQUEST['form_title'],$_REQUEST['form_type'],$pid));
      $issue = $issue2['id'];
    } else {
      $query = "SELECT id,pid from lists where title=? and type=? and pid=? and subtype=?";
      $issue2 = sqlQuery($query,array($_REQUEST['form_title'],$_REQUEST['form_type'],$pid,$subtype));
      $issue = $issue2['id'];
    }
  }

  if ($issue) { //if an issue with this title/subtype already exists we are updating it...
   $query = "UPDATE lists SET " .
    "type = '"        . add_escape_custom($text_type)                  . "', " .
    "title = '"       . add_escape_custom($_REQUEST['form_title'])        . "', " .
    "comments = '"    . add_escape_custom($_REQUEST['form_comments'])     . "', " .
    "begdate = "      . QuotedOrNull($form_begin)   . ", "  .
    "enddate = "      . QuotedOrNull($form_end)     . ", "  .
    "returndate = "   . QuotedOrNull($form_return)  . ", "  .
    "diagnosis = '"   . add_escape_custom($_REQUEST['form_diagnosis'])    . "', " .
    "occurrence = '"  . add_escape_custom($_REQUEST['form_occur'])        . "', " .
    "classification = '" . add_escape_custom($_REQUEST['form_classification']) . "', " .
    "reinjury_id = '" . add_escape_custom($_REQUEST['form_reinjury_id'])  . "', " .
    "referredby = '"  . add_escape_custom($_REQUEST['form_referredby'])   . "', " .
    "injury_grade = '" . add_escape_custom($_REQUEST['form_injury_grade']) . "', " .
    "injury_part = '" . add_escape_custom($form_injury_part)           . "', " .
    "injury_type = '" . add_escape_custom($form_injury_type)           . "', " .
    "outcome = '"     . add_escape_custom($_REQUEST['form_outcome'])      . "', " .
    "destination = '" . add_escape_custom($_REQUEST['form_destination'])   . "', " .
    "reaction ='"     . add_escape_custom($_REQUEST['form_reaction'])     . "', " .
    "erx_uploaded = '0', " .
    "modifydate = NOW(), " .
    "subtype = '"     . $subtype. "' " .
    "WHERE id = '" . add_escape_custom($issue) . "'";
    sqlStatement($query);
    if ($text_type == "medication" && enddate != '') {
      sqlStatement('UPDATE prescriptions SET '
        . 'medication = 0 where patient_id = ? '
        . " and upper(trim(drug)) = ? "
        . ' and medication = 1', array($thispid,strtoupper($_REQUEST['form_title'])) );
    }
  } else {
   $query =  "INSERT INTO lists ( " .
    "date, pid, type, title, activity, comments, begdate, enddate, returndate, " .
    "diagnosis, occurrence, classification, referredby, user, groupname, " .
    "outcome, destination, reinjury_id, injury_grade, injury_part, injury_type, " .
    "reaction,subtype " .
    ") VALUES ( " .
    "NOW(), " .
    "'" . add_escape_custom($thispid) . "', " .
    "'" . add_escape_custom($text_type)                 . "', " .
    "'" . add_escape_custom($_REQUEST['form_title'])       . "', " .
    "1, "                            .
    "'" . add_escape_custom($_REQUEST['form_comments'])    . "', " .
    QuotedOrNull($form_begin)        . ", "  .
    QuotedOrNull($form_end)        . ", "  .
    QuotedOrNull($form_return)       . ", "  .
    "'" . add_escape_custom($_REQUEST['form_diagnosis'])   . "', " .
    "'" . add_escape_custom($_REQUEST['form_occur'])       . "', " .
    "'" . add_escape_custom($_REQUEST['form_classification']) . "', " .
    "'" . add_escape_custom($_REQUEST['form_referredby'])  . "', " .
    "'" . add_escape_custom($$_SESSION['authUser'])     . "', " .
    "'" . add_escape_custom($$_SESSION['authProvider']) . "', " .
    "'" . add_escape_custom($_REQUEST['form_outcome'])     . "', " .
    "'" . add_escape_custom($_REQUEST['form_destination']) . "', " .
    "'" . add_escape_custom($_REQUEST['form_reinjury_id']) . "', " .
    "'" . add_escape_custom($_REQUEST['form_injury_grade']) . "', " .
    "'" . add_escape_custom($form_injury_part)          . "', " .
    "'" . add_escape_custom($form_injury_type)          . "', " .
    "'" . add_escape_custom($_REQUEST['form_reaction'])         . "', " .
    "'" . $subtype         . "' " .
   ")";
    $issue = sqlInsert($query);
  }

  // For record/reporting purposes, place entry in lists_touch table.
  //echo $pid." and ".$text_type;
  setListTouch($pid,$text_type);

  // If requested, link the issue to a specified encounter.
  if ($thisenc) {
    $query = "INSERT INTO issue_encounter ( " .
      "pid, list_id, encounter " .
      ") VALUES ( ?,?,? )";
    sqlStatement($query, array($thispid,$issue,$thisenc));
  }

  $tmp_title = addslashes($ISSUE_TYPES[$text_type][2] . ": $form_begin " .
    substr($_REQUEST['form_title'], 0, 40));
  $irow = '';
  //if it is a medication do we need to do something with dosage fields? 
  //leave all in title field form now.
}
//---- end save

$irow = array();
if ($issue) {
  $irow = sqlQuery("SELECT * FROM lists WHERE id = ?",array($issue));
} else if ($thistype) {
  $irow['type'] = $thistype;
  $irow['subtype'] = $subtype;
}
if ($thistype == "medical_problem" && $_REQUEST['subtype'] =="eye") {
  $irow['type'] = "POH";
  $thistype = "POH";
}

//$type_index = '5';

if (!empty($irow['type'])) {
  foreach ($ISSUE_TYPES as $key => $value) {
    if ($key == $irow['type']) break;
    ++$type_index;
  }
}
$given="ROSGENERAL,ROSHEENT,ROSCV,ROSPULM,ROSGI,ROSGU,ROSDERM,ROSNEURO,ROSPSYCH,ROSMUSCULO,ROSIMMUNO,ROSENDOCRINE";
    $query="SELECT $given from form_eye_mag where id=? and pid=?";
    $rres = sqlQuery($query,array($form_id,$pid));
    foreach (split(',',$given) as $item) {
        $$item = $rres[$item];
    }


?><html>
<head>
<title><?php echo $issue ? xlt('Edit') : xlt('Add New'); ?><?php echo " ".xlt('Issue'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/interface/forms/<?php echo $form_folder; ?>/style.css" type="text/css"> 
<!-- jQuery library -->
<script src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="<?php echo $GLOBALS['webroot'] ?>/library/js/bootstrap.min.js"></script>  
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
<script type="text/javascript" src="../../forms/<?php echo $form_folder; ?>/js/shortcut.js"></script>
<script type="text/javascript" src="../../forms/<?php echo $form_folder; ?>/js/my_js_base.js"></script>
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/css/font-awesome-4.2.0/css/font-awesome.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>

td, select, textarea {
 font-family: Fontawesome, Arial, Helvetica, sans-serif;
 font-size: 8pt;
 } 
 
 input[type="text"]{
 text-align:left;
 background-color: #FFF8DC;
 text-align: left;

}

div.section {
 border: solid;
 border-width: 1px;
 border-color: #0000ff;
 margin: 0 0 0 10pt;
 padding: 5pt;
}
.ROS_class input[type="text"] {
  width:120px;
}
</style>

<style type="text/css">@import url(<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.js"></script>
<?php require_once($GLOBALS['srcdir'].'/dynarch_calendar_en.inc.php'); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>



<script language="JavaScript">
 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
 var aitypes = new Array(); // issue type attributes
 var aopts   = new Array(); // Option objects
<?php
 
//This builds the litle quick pick list in this section.
 // Would be better as an autocomplete so lots of stuff can be seen.
 //or it is an ajax autocomplete live to the DB where a 
  // a cronjob or a mysql based view and trigger to update
 // the list_options for each $key based on the current provider
 // eg. one provider might have a lot of cataract surgery patients and list it as Phaco/PCIOL and another
 // might use a femto laser assisted Restore IOL procedure and he uses FT/Restore IOL
 // No matter the specialty, how the doctor documents can be analyzed and list_options created in the VIEW TABLE in the order
 // of their frequency.  Start at 10 should they want to always have something at the top.
 //I like the option of when updating the lists table, a trigger updates a VIEW and this autocomplete 
 //draws from the VIEW table.  Nice.  Real time update...  Need to consider top picks and that can be the role
 // of the current list_options table...  Ok.  1-10 from list_options, after that from VIEW via trigger that
 // ranks them by frequency over a limited time to keep DB humming...  Or maybe just a query with a subselect?
  $i='0';
  foreach ($ISSUE_TYPES as $key => $value) {
    echo " aitypes[$i] = " . attr($value[3]) . ";\n";
    echo " aopts[$i] = new Array();\n";

    if ($i < "4") { // "0" = medical_problem_issue_list leave out Dental "4"
      $qry = sqlStatement("SELECT title, title as option_id, count(title) AS freq  FROM `lists` WHERE `type` LIKE ? and 
        subtype = '' and pid in (select pid from form_encounter where provider_id =? 
        and date BETWEEN NOW() - INTERVAL 30 DAY AND NOW()) GROUP BY title order by freq desc limit 10", array($key,$_SESSION['authId'])); 
    //have to make sure a Tech sees what the provider prefers.  Is $_SESSION[authProvider] going to work here?
      if (sqlNumRows($qry) < ' 2') { //if they are just starting out, use the list_options for all
        $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = ? and subtype not like 'eye'",array($key."_issue_list"));
      }
    } elseif ($i == "5") { // POH medical group - leave POsurgicalH for now. Surgical will require a new issue type above too
      $query = "SELECT title, title as option_id, count(title) AS freq  FROM `lists` WHERE `type` LIKE 'medical_problem' and subtype = 'eye' and pid in (select pid from form_encounter where provider_id =? and date BETWEEN NOW() - INTERVAL 30 DAY AND NOW()) GROUP BY title order by freq desc limit 10";
      $qry = sqlStatement($query,array($_SESSION['authProvider'])); 
      if (sqlNumRows($qry) < ' 2') { //if they are just starting out, use the list_options for all
        $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = 'medical_problem_issue_list' and subtype = 'eye'");
      }
    } else if ($i == "6") { // FH group
      //need a way to pull FH out of patient_data and will display frame very differently
      $qry = "";
    } else if ($i == "7") { // SocHx group - leave blank for now?
      $qry = ""; 
    } 
    if ($i <"6") {
      while($res = sqlFetchArray($qry)){
        echo " aopts[$i][aopts[$i].length] = new Option('".attr(trim($res['option_id']))."', '".attr(xl_list_label(trim($res['title'])))."', false, false);\n";
      }
    }
  ++$i;
  }

?>

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 // React to selection of an issue type.  This loads the associated
 // shortcuts into the selection list of titles, and determines which
 // rows are displayed or hidden.
 // Need to work on this to display "non-openEMR" issue types like POH/FH/ROS
 function newtype(index) {
  var f = document.forms[0];
  var theopts = f.form_titles.options;
  theopts.length = 0;
  if (aopts[index]) {
  var i = 0;
  for (i = 0; i < aopts[index].length; ++i) {
   theopts[i] = aopts[index][i];
  }
  }
  document.getElementById('row_quick_picks'     ).style.display = i ? '' : 'none'; //select list of things
  document.getElementById('row_title'           ).style.display = '';
  document.getElementById('row_diagnosis'       ).style.display = 'none';
  document.getElementById('row_begindate'       ).style.display = 'none';
  document.getElementById('row_enddate'         ).style.display = 'none';
  document.getElementById('row_reaction'        ).style.display = 'none';
  document.getElementById('row_referredby'      ).style.display = 'none';
  document.getElementById('row_classification'  ).style.display = 'none';
  document.getElementById('row_occurrence'      ).style.display = 'none';
  document.getElementById('row_comments'        ).style.display = 'none';
  document.getElementById('row_outcome'         ).style.display = 'none';
  document.getElementById('row_destination'     ).style.display = 'none';
  document.getElementById('row_social'          ).style.display = 'none';
  document.getElementById('row_FH'              ).style.display = 'none';
  document.getElementById('row_ROS'             ).style.display = 'none';
  document.getElementById('row_PLACEHOLDER'     ).style.display = 'none';


  if (index == 0) { //PMH
    document.getElementById('row_diagnosis'     ).style.display = '';
    document.getElementById('row_begindate'     ).style.display = '';
    document.getElementById('row_enddate'       ).style.display = '';
    document.getElementById('row_occurrence'    ).style.display = '';
    document.getElementById('row_comments'      ).style.display = '';

  
  } else if (index == 1) { // Allergy
    document.getElementById('row_reaction'      ).style.display = '';
    document.getElementById('row_begindate'     ).style.display = '';
    document.getElementById('row_comments'      ).style.display = '';
    
  } else if (index == 2) { //meds
    document.getElementById('row_begindate'     ).style.display = '';
    document.getElementById('row_enddate'       ).style.display = '';
    document.getElementById('row_comments'      ).style.display = '';
    //change Onset to started
    //change resolved to COmpleted
    document.getElementById('onset'             ).textContent = "Start:";
    document.getElementById('resolved'          ).textContent ="Finish:";
    
  } else if (index == 3) { //surgery
    document.getElementById('row_begindate'     ).style.display = '';
    document.getElementById('row_referredby'    ).style.display = '';
    document.getElementById('by_whom'           ).textContent ="Surgeon:";
    document.getElementById('onset'             ).textContent = "Date:";
    
    document.getElementById('row_comments'      ).style.display = '';

   } else if (index == 4) { //Dental so skip it
  } else if (index == 5) { //POH
    document.getElementById('row_diagnosis'     ).style.display = '';
    document.getElementById('row_begindate'     ).style.display = '';
    document.getElementById('row_referredby'    ).style.display = '';
    document.getElementById('by_whom'           ).textContent ="Collaborator:";
    document.getElementById('onset'             ).textContent = "Date:";   
    document.getElementById('row_comments'      ).style.display = '';

  } else if (index == 6) { //FH
    document.getElementById('row_title'         ).style.display = 'none';
    document.getElementById('row_FH'            ).style.display = '';

  } else if (index == 7) { //SocH
    document.getElementById('row_title'         ).style.display = 'none';
    document.getElementById('row_social'        ).style.display = '';

  } else if (index == 8) { //ROS
    document.getElementById('row_title'         ).style.display = 'none';
    document.getElementById('row_ROS'           ).style.display = '';
  } else { //show nothing
    document.getElementById('row_title'         ).style.display = 'none';
    document.getElementById('row_PLACEHOLDER'   ).style.display = '';
  }
 }
 // If a clickoption title is selected, copy it to the title field.
 function set_text() {
  var f = document.forms[0];
  f.form_title.value = f.form_titles.options[f.form_titles.selectedIndex].text;
  f.form_titles.selectedIndex = -1;
 }
function refreshIssue() {
 parent.refreshIssues();
 parent.refresh_panel();
 //refreshIssues();
}
function submit_this_form() {
    var url = "../../forms/eye_mag/a_issue.php?form_save=1";
    var formData = $("form#theform").serialize();
    var f = document.forms[0];
    $.ajax({
           type   : 'POST',   // define the type of HTTP verb we want to use (POST for our form)
           url    : url,      // the url where we want to POST
           data   : formData  // our data object
        }).done(function(result){
          f.form_title.value = '';
          f.form_diagnosis.value = '';
          f.form_begin.value ='';
          f.form_end.value ='';
          f.form_referredby.value ='';
          f.form_reaction.value ='';
          f.form_classification.value ='';
          f.form_comments.value ='';
          f.form_outcome.value ='';
          f.form_destination.value ='';
          f.issue.value ='';
          //$("#page").html(result);
          refreshIssue();
        });
}
 // Process click on Delete link.
function deleteme() {
    var url = "../../forms/eye_mag/a_issue.php"; //?issue=<?php echo attr($issue); ?>&delete=1";
    var formData = $("form#theform").serialize();
    var f = document.forms[0];
    //alert(formData);
   $.ajax({
           type    : 'POST',   // define the type of HTTP verb we want to use (POST for our form)
           data    : { 
                        issue  : '<?php echo attr($issue) ?>',
                        delete : '1'
                      },
            url    : url,      // the url where we want to POST
           
           }).done(function (o){
            //CLEAR THE FORM TOO...
           // console.log(o);
            //refreshIssues();
            f.form_title.value = '';
            f.form_diagnosis.value = '';
            f.form_begin.value ='';
            f.form_end.value ='';
            f.form_referredby.value ='';
            f.form_reaction.value ='';
            f.form_classification.value ='';
            f.form_comments.value ='';
            f.form_outcome.value ='';
            f.form_destination.value ='';
            f.issue.value ='';
            
            refreshIssue();
           });
}
 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
  closeme();
 }

 function closeme() {
    if (parent.$) parent.$.fancybox.close();
    window.close();
 }

 // Called when the Active checkbox is clicked.  For consistency we
 // use the existence of an end date to indicate inactivity, even
 // though the simple verion of the form does not show an end date.
 function resolvedClicked(cb) {
  var f = document.forms[0];
  if (!cb.checked) {
   f.form_end.value = '';
  } else {
   var today = new Date();
   f.form_end.value = '' + (today.getYear() + 1900) + '-' +
    (today.getMonth() + 1) + '-' + today.getDate();
  }
 }

 // Called when resolved outcome is chosen and the end date is entered.
 function outcomeClicked(cb) {
  var f = document.forms[0];
  if (cb.value == '1'){
   var today = new Date();
   f.form_end.value = '' + (today.getYear() + 1900) + '-' +
    ("0" + (today.getMonth() + 1)).slice(-2) + '-' + ("0" + today.getDate()).slice(-2);
   f.form_end.focus();
  }
 }

// This is for callback by the find-code popup.
// Appends to or erases the current list of diagnoses.
function set_related(codetype, code, selector, codedesc) {
 var f = document.forms[0];
 var s = f.form_diagnosis.value;
 var title = f.form_title.value;
 if (code) {
  if (s.length > 0) s += ';';
  s += codetype + ':' + code;
 } else {
  s = '';
 }
 f.form_diagnosis.value = s;
 if(title == '') f.form_title.value = codedesc;
}

// This invokes the find-code popup.
function sel_diagnosis() {
  <?php
  if($irow['type'] == 'medical_problem')
  {
  ?>
 dlgopen('../../patient_file/encounter/find_code_popup.php?codetype=<?php echo attr(collect_codetypes("medical_problem","csv")) ?>', '_blank', 500, 400);
  <?php
  }
  else{
  ?>
  dlgopen('../../patient_file/encounter/find_code_popup.php?codetype=<?php echo attr(collect_codetypes("diagnosis","csv")) ?>', '_blank', 500, 400);
  <?php
  }
  ?>
}

// Check for errors when the form is submitted.
function validate() {
 var f = document.forms[0];
 if(f.form_begin.value > f.form_end.value && (f.form_end.value)) {
  alert("<?php echo addslashes(xl('Please Enter End Date greater than Begin Date!')); ?>");
  return false;
 }
 if (! f.form_title.value) {
  alert("<?php echo addslashes(xl('Please enter a title!')); ?>");
  return false;
 }
 top.restoreSession();
 return true;
}

// Supports customizable forms (currently just for IPPF).
function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}
//function for selecting the smoking status in drop down list based on the selection in radio button.
function smoking_statusClicked(cb) 
{    
     if (cb.value == 'currenttobacco')
     {
     document.getElementById('form_tobacco').selectedIndex = 1;
     }
     else if (cb.value == 'nevertobacco')
     {
     document.getElementById('form_tobacco').selectedIndex = 4;
     }
     else if (cb.value == 'quittobacco')
     {
     document.getElementById('form_tobacco').selectedIndex = 3;
     }
     else if (cb.value == 'not_applicabletobacco')
     {
     document.getElementById('form_tobacco').selectedIndex = 6;
     }
   radioChange(document.getElementById('form_tobacco').value);   
}
//function for selecting the smoking status in radio button based on the selection of drop down list.
function radioChange(rbutton)
{
    if (rbutton == 1 || rbutton == 2 || rbutton == 15 || rbutton == 16)
     {
     document.getElementById('radio_tobacco[current]').checked = true;
     }
     else if (rbutton == 3)
     {
     document.getElementById('radio_tobacco[quit]').checked = true;
     }
     else if (rbutton == 4)
     {
     document.getElementById('radio_tobacco[never]').checked = true;
     }
     else if (rbutton == 5 || rbutton == 9)
     {
     document.getElementById('radio_tobacco[not_applicable]').checked = true;
     }
     else if (rbutton == '')
     {
     var radList = document.getElementsByName('radio_tobacco');
     for (var i = 0; i < radList.length; i++) {
     if(radList[i].checked) radList[i].checked = false;
     }
     }
     //Added on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
     if(rbutton!=""){
         if(code_options_js[rbutton]!="")
            $("#smoke_code").html(" ( "+code_options_js[rbutton]+" )");
         else
             $("#smoke_code").html(""); 
     }
     else
        $("#smoke_code").html(""); 
}
function clear_option(section) {
  //click the field, erase the Negative radio and input Y
  var f = document.forms[0];
  var name = 'radio_'+section.name;
  var radio = document.getElementById(name);
    radio.checked = false;
  if (section.value==''){
    section.value="Y";
    section.select();
  }
}

function negate_radio(section) {
  if (section.checked ==true){
    var rfield = section.name.match(/radio_(.*)/);
    document.getElementById(rfield[1]).value='';
  } 
}
  //Added on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
 var code_options_js = Array();
 
 <?php
 $smoke_codes = getSmokeCodes();
  
 foreach ($smoke_codes as $val => $code) {
            echo "code_options_js"."['" . attr($val) . "']='" . attr($code) . "';\n";
      }
 ?>

</script>

</head>

<body  style="padding-right:0.3em;font-family: FontAwesome,serif,Arial;">

  <div id="page" name="page">
    <form method='POST' name='theform' id='theform'
     action='a_issue.php?thispid=<?php echo attr($thispid); ?>&thisenc=<?php echo attr($thisenc); ?>'
     onsubmit='return validate();'>
     <input type="hidden" name="form_id" id="form_id" value = "<?php echo $form_id; ?>">
     <input type="hidden" name="issue" id="issue" value = "<?php echo attr($issue); ?>">
        <?php
         $index = 0;
         $output ='';
        global $counter_header;
        $count_header='0';
        $output= array();
        foreach ($ISSUE_TYPES as $value => $focustitles) {
           $checked = '';
            if ($issue || $thistype) {
              if ($index == $type_index) { $checked .= " checked='checked' ";}
            } else if ($focustitles[1] == "Problem") {
              $checked .= " checked='checked' "; 
            }

            if ($focustitles[1] == "Medication") $focustitles[1] = "Meds";
            if ($focustitles[1] == "Problem") $focustitles[1] = "PMH";
            if ($focustitles[1] == "Surgery") $focustitles[1] = "Surg";
            if ($focustitles[1] == "SocH") $focustitles[1] = "Soc";
            $HELLO[$focustitles[1]] = "<input type='radio' name='form_type' id='".xla($index)."' value='".xla($index)."' ".$checked. " onclick='top.restoreSession();newtype($index);' />
            <span style='margin-top:2px;font-size:0.6em;font-weight:bold;'><label class='input-helper input-helper--checkbox' for='".xla($index)."'>" . xlt($focustitles[1]) . "</label></span>&nbsp;";
            ++$index;
        }
        $HELLO['ROS']="<input type='radio' name='form_type' id='8' value='8' ".$checked. " onclick='top.restoreSession();newtype(8);' />
            <span style='margin-top:2px;font-size:0.6em;font-weight:bold;'><label class='input-helper input-helper--checkbox' for='8'>ROS</label></span>";
  
        echo $HELLO['POH'].$HELLO['PMH'].$HELLO['Meds'].$HELLO['Surg'].$HELLO['Allergy'].$HELLO['FH'].$HELLO['Soc'].$HELLO['ROS'];

        ?>
      <div class="borderShadow" style="text-align:center;margin-top:7px;width;90%;">
      <table  border='0' width='90%'>
        <tr id='row_quick_picks'>
            <td valign='top' nowrap>&nbsp;</td>
            <td valign='top'  colspan="3">
              <select name='form_titles' size='<?php echo $GLOBALS['athletic_team'] ? 10 : 6; ?>' onchange='set_text()'>
              </select> 
            </td>
        </tr>
        <tr id="row_title">
          <td valign='top' class="right" id='title_diagnosis' nowrap><b><?php echo xlt('Title').$focustitle[1]; ?>:</b></td>
          <td  colspan="3">
            <input type='text' size='40' name='form_title' value='<?php echo xla($irow['title']) ?>' style='width:100%;text-align:left;' />
          </td>
        </tr>
        <tr id="row_diagnosis">
          <td valign='top' class="right" nowrap><b><?php echo xlt('Code'); ?>:</b></td>
          <td colspan="3">
            <input type='text' size='50' name='form_diagnosis'
              value='<?php echo attr($irow['diagnosis']) ?>' onclick='top.restoreSession();sel_diagnosis();'
              title='<?php echo xla('Click to select or change diagnoses'); ?>'
              style='width:100%' />
          </td>
        </tr>
        <tr id='row_begindate'>
          <td nowrap class="right"><b id="onset"><?php echo xlt('Onset'); ?>:</b></td>
          <td>

           <input type='text' size='10' name='form_begin' id='form_begin'
            value='<?php echo attr($irow['begdate']) ?>'¸ 
            style="width: 75px;font-size:0.8em;"
            onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
            title='<?php echo xla('yyyy-mm-dd date of onset, surgery or start of medication'); ?>' />
           <img src='../../pic/show_calendar.gif' align='absbottom' width='15' height='15'
            id='img_begin' border='0' alt='[?]' style='cursor:pointer'
            title='<?php echo xla('Click here to choose a date'); ?>' />
          </td>
          <td id='row_enddate' nowrap><input type='checkbox' name='form_active' value='1' <?php echo attr($irow['enddate']) ? "checked" : ""; ?>
            onclick='top.restoreSession();resolvedClicked(this);'
            title='<?php echo xla('Indicates if this issue is currently active'); ?>' />
            <b id="resolved"><?php echo xlt('Resolved'); ?>:</b>&nbsp;<input type='text' size='10' name='form_end' id='form_end'
            style="width: 75px;font-size:0.8em;"
            value='<?php echo attr($irow['enddate']) ?>'
            onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
            title='<?php echo xla('yyyy-mm-dd date of recovery or end of medication'); ?>' />
           <img src='../../pic/show_calendar.gif' align='absbottom' width='15' height='15'
            id='img_end' border='0' alt='[?]' style='cursor:pointer'
            title='<?php echo xla('Click here to choose a date'); ?>' />            
          </td>
         </tr>

         <tr id='row_occurrence'>
          <td valign='top'  class="right" nowrap><b><?php echo xlt('Course'); ?>:</b></td>
          <td colspan="3">
           <?php
            // Modified 6/2009 by BM to incorporate the occurrence items into the list_options listings
            generate_form_field(array('data_type'=>1,'field_id'=>'occur','list_id'=>'occurrence','empty_title'=>'SKIP'), $irow['occurrence']);
           ?>
          </td>
         </tr>

         <tr id='row_classification'>
          <td valign='top'  class="right" nowrap><b><?php echo xlt('Classification'); ?>:</b></td>
          <td colspan="3">
           <select name='form_classification'>
              <?php
             foreach ($ISSUE_CLASSIFICATIONS as $key => $value) {
              echo "   <option value='".attr($key)."'";
              if ($key == $irow['classification']) echo " selected";
              echo ">".text($value)."\n";
             }
              ?>
           </select>
          </td>
        </tr>
        <tr id='row_reaction'>
           <td valign='top'  class="right" nowrap><b><?php echo xlt('Reaction'); ?>:</b></td>
           <td  colspan="3">
            <input type='text' size='40' name='form_reaction' value='<?php echo attr($irow['reaction']) ?>'
             style='width:100%' title='<?php echo xla('Allergy Reaction'); ?>' />
           </td>
        </tr>
        <tr id='row_referredby'>
                    <td class="right" nowrap><b id="by_whom"><?php echo xlt('Referred by'); ?>:</b></td>
                    <td  colspan="3">
                     <input type='text' size='40' name='form_referredby' value='<?php echo attr($irow['referredby']) ?>'
                      style='width:100%' title='<?php echo xla('Referring physician and practice'); ?>' />
                    </td>
        </tr>
        <tr id='row_comments'>
          <td valign='top' class="right" nowrap><b><?php echo xlt('Comments'); ?>:</b></td>
          <td colspan="3">
           <textarea name='form_comments' rows='1' cols='40' wrap='virtual' style='width:100%'><?php echo text($irow['comments']) ?></textarea>
          </td>
        </tr>
        <tr id="row_outcome">
          <td valign='top'  class="right" nowrap><b><?php echo xlt('Outcome'); ?>:</b></td>
          <td>
           <?php
            echo generate_select_list('form_outcome', 'outcome', $irow['outcome'], '', '', '', 'outcomeClicked(this);');
           ?>
          </td>
        </tr>
        <tr id="row_destination">
          <td valign='top'  class="right" nowrap><b><?php echo xlt('Destination'); ?>:</b></td>
          <td colspan="3">
          <?php if (true) { ?>
           <input type='text' size='40' name='form_destination' value='<?php echo attr($irow['destination']) ?>'
            style='width:100%' title='GP, Secondary care specialist, etc.' />
          <?php } else { // leave this here for now, please -- Rod ?>
           <?php echo rbinput('form_destination', '1', 'GP'                 , 'destination') ?>&nbsp;
           <?php echo rbinput('form_destination', '2', 'Secondary care spec', 'destination') ?>&nbsp;
           <?php echo rbinput('form_destination', '3', 'GP via physio'      , 'destination') ?>&nbsp;
           <?php echo rbinput('form_destination', '4', 'GP via podiatry'    , 'destination') ?>
          <?php } ?>
          </td>
        </tr>
      </table>
      <table id="row_social" width="100%">      
            <?php 
              $given ="*";
              $dateStart=$_POST['dateState'];
              $dateEnd=$_POST['dateEnd'];
              if ($dateStart && $dateEnd) {
                    $result1 = sqlQuery("select $given from history_data where pid = ? and date >= ? and date <= ? order by date DESC limit 0,1", array($pid,$dateStart,$dateEnd) );
                }
                else if ($dateStart && !$dateEnd) {
                    $result1 = sqlQuery("select $given from history_data where pid = ? and date >= ? order by date DESC limit 0,1", array($pid,$dateStart) );
                }
                else if (!$dateStart && $dateEnd) {
                    $result1 = sqlQuery("select $given from history_data where pid = ? and date <= ? order by date DESC limit 0,1", array($pid,$dateEnd) );
                }
                else {
                    $result1 = sqlQuery("select $given from history_data where pid=? order by date DESC limit 0,1", array($pid) );
                }

                $group_fields_query = sqlStatement("SELECT * FROM layout_options " .
                "WHERE form_id = 'HIS' AND group_name = '4Lifestyle' AND uor > 0 " .
                "ORDER BY seq");
                
                     /* while ($frow = sqlFetchArray($fres)) {
                                  $this_group = isset($frow['group_name']) ? $frow['group_name'] : "" ;
                                  $titlecols  = isset($frow['titlecols']) ? $frow['titlecols'] : "";
                                  $datacols   = isset($frow['datacols']) ? $frow['datacols'] : "";
                                  $data_type  = isset($frow['data_type']) ? $frow['data_type'] : "";
                                  $field_id   = isset($frow['field_id']) ? $frow['field_id'] : "";
                                  $list_id    = isset($frow['list_id']) ? $frow['list_id'] : "";
                                  $currvalue  = '';
                  */
            
              while ($group_fields = sqlFetchArray($group_fields_query)) {
                  $titlecols  = $group_fields['titlecols'];
                  $datacols   = $group_fields['datacols'];
                  $data_type  = $group_fields['data_type'];
                  $field_id   = $group_fields['field_id'];
                  $list_id    = $group_fields['list_id'];
                  $currvalue  = '';
              if (isset($result1[$field_id])) $currvalue = $result1[$field_id];
              if ($data_type == 28 || $data_type == 32) {
                  $tmp = explode('|', $currvalue);
                  switch(count($tmp)) {
                    case "4": {
                      $result2[$field_id]['resnote'] = $tmp[0];
                      $result2[$field_id]['restype'] = $tmp[1];
                      $result2[$field_id]['resdate'] = $tmp[2];
                      $result2[$field_id]['reslist'] = $tmp[3];
                    } break;
                    case "3": {
                      $result2[$field_id]['resnote'] = $tmp[0];
                      $result2[$field_id]['restype'] = $tmp[1];
                      $result2[$field_id]['resdate'] = $tmp[2];
                    } break;
                    case "2": {
                      $result2[$field_id]['resnote'] = $tmp[0];
                      $result2[$field_id]['restype'] = $tmp[1];
                      $result2[$field_id]['resdate'] = "";
                    } break;
                    case "1": {
                      $result2[$field_id]['resnote'] = $tmp[0];
                      $result2[$field_id]['resdate'] = $result2[$field_id]['restype'] = "";
                    } break;
                    default: {
                      $result2[$field_id]['restype'] = $result2[$field_id]['resdate'] = $result2[$field_id]['resnote'] = "";
                    } break;
                  }
                  $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
                  $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
                  $result2[$field_id]['resnote'] = htmlspecialchars( $result2[$field_id]['resnote'], ENT_QUOTES);
                  $result2[$field_id]['resdate'] = htmlspecialchars( $result2[$field_id]['resdate'], ENT_QUOTES);
               
                        //  if ($group_fields['title']) echo htmlspecialchars(xl_layout_label($group_fields['title']).":",ENT_NOQUOTES)."</b>"; else echo "&nbsp;";

                    //      echo generate_display_field($group_fields, $currvalue);
                } else if ($data_type == 2) {
                   $result2[$field_id]['resnote'] = nl2br(htmlspecialchars($currvalue,ENT_NOQUOTES));
                }
              }
            ?>
            <style>
              .data td{
                font-size:0.7em;

              }
              .data input[type="text"] {
               width:69px;
              }
              #form_box {
                width:80px;
              }
            </style>
            
            <tbody>
                <tr>
                  <td class="right" nowrap>Marital:</td>
                  <td colspan="3"><input type="text" style="width:75px;" name="marital_status" id="marital_status" value="<?php echo $patient['status']; ?>">
                  &nbsp;Occupation:&nbsp;<input type="text" style="width:75px;" name="occupation" id="occupation" value="<?php echo $patient['occupation']; ?>"></td>
                </tr>
                <tr>
                  <td></td>
                  <td colspan="3">
                    <select name="form_tobacco" id="form_tobacco" onchange="radioChange(this.options[this.selectedIndex].value)" title="Tobacco use">
                      <option value="" <?php if ($result2['tobacco']['reslist'] =='') echo "selected"; ?>>Unassigned</option>
                      <option value="1" <?php if ($result2['tobacco']['reslist'] =='1') echo "selected"; ?>>Current every day smoker</option>
                      <option value="2" <?php if ($result2['tobacco']['reslist'] =='2') echo "selected"; ?>>Current some day smoker</option>
                      <option value="3" <?php if ($result2['tobacco']['reslist'] =='3') echo "selected"; ?>>Former smoker</option>
                      <option value="4" <?php if ($result2['tobacco']['reslist'] =='4') echo "selected"; ?>>Never smoker</option>
                      <option value="5" <?php if ($result2['tobacco']['reslist'] =='5') echo "selected"; ?>>Smoker, current status unknown</option>
                      <option value="9" <?php if ($result2['tobacco']['reslist'] =='9') echo "selected"; ?>>Unknown if ever smoked</option>
                      <option value="15" <?php if ($result2['tobacco']['reslist'] =='15') echo "selected"; ?>>Heavy tobacco smoker</option>
                      <option value="16" <?php if ($result2['tobacco']['reslist'] =='16') echo "selected"; ?>>Light tobacco smoker</option>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td class="label right"  nowrap>Tobacco:</td>
                  <td class="text data" colspan="3">
                    <table cellpadding="0" cellspacing="0">
                      <tr>
                        <td><input type="text" name="form_text_tobacco" id="form_box" size="20" value="<?php echo xla($PMSFH[0]['SOCH']['tobacco']['resnote']); ?>">&nbsp;</td>
                        <td class="bold">&nbsp;&nbsp;</td>
                        <td class="text">
                          <input type="radio" name="radio_tobacco" id="radio_tobacco[current]" value="currenttobacco" onclick="smoking_statusClicked(this)" <?php if ($result2['tobacco']['restype'] =='currenttobacco') echo "checked"; ?>>Current&nbsp;</td>
                        <td class="text"><input type="radio" name="radio_tobacco" id="radio_tobacco[quit]" value="quittobacco" onclick="smoking_statusClicked(this)" <?php if ($result2['tobacco']['restype'] =='quittobacco') echo "checked"; ?>>Quit&nbsp;</td>
                        <td class="text" onclick='top.restoreSession();resolvedClicked(this);'>
                          <input type="text" size="6" 
                          name="date_tobacco" id="date_tobacco" 
                          value="<?php echo $result2['tobacco']['resdate']; ?>" 
                          title="Tobacco use" 
                          onkeyup="datekeyup(this,mypcc)" 
                          onblur="dateblur(this,mypcc)"><img src="../../pic/show_calendar.gif" align="absbottom" 
                          width="15" height="15" 
                          id="img_tobacco" 
                          border="0" alt="[?]" style="cursor:pointer" 
                          title="Click here to choose a date">&nbsp;
                        </td>
                        <td class="text">
                          <input type="radio" name="radio_tobacco" id="radio_tobacco[never]" value="nevertobacco" onclick="smoking_statusClicked(this)" <?php if ($result2['tobacco']['restype'] =='nevertobacco') echo "checked"; ?>>Never&nbsp;
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <tr>
                  <td  class="label right" nowrap>Coffee:</td>
                  <td class="text data" colspan="3">
                    <table cellpadding="0" cellspacing="0">
                  <tbody>
                    <tr>
                      <td><input type="text" name="form_coffee" id="form_box" size="20" value="<?php echo $result2['coffee']['resnote']; ?>">&nbsp;</td>
                      <td class="bold">&nbsp;&nbsp;</td>
                      <td class="text"><input type="radio" name="radio_coffee" id="radio_coffee[current]" value="currentcoffee" <?php if ($PMSFH[0]['SOCH']['coffee']['restype'] =='currentcoffee') echo "checked"; ?>>Current&nbsp;</td>
                      <td class="text"><input type="radio" name="radio_coffee" id="radio_coffee[quit]" value="quitcoffee" <?php if ($PMSFH[0]['SOCH']['coffee']['restype'] =='quitcoffee') echo "checked"; ?>>Quit&nbsp;</td>
                      <td class="text"><input type="text" size="6" name="date_coffee" id="date_coffee" value="" title="Caffeine consumption" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)"><img src="/openemr/interface/pic/show_calendar.gif" align="absbottom" width="15" height="15" id="img_coffee" border="0" alt="[?]" style="cursor:pointer" title="Click here to choose a date">&nbsp;</td>
                      <td class="text"><input type="radio" name="radio_coffee" id="radio_coffee[never]" value="nevercoffee" <?php if ($PMSFH[0]['SOCH']['coffee']['restype'] =='nevercoffee') echo "checked"; ?>>Never&nbsp;</td>
                    </tr>
                  </tbody>
                    </table>
                  </td>
                </tr>

                <tr>
                  <td class="label right"  nowrap>Alcohol:</td>
                  <td class="text data" colspan="3">
                    <table cellpadding="0" cellspacing="0">
                      <tbody>
                        <tr><td><input type="text" name="form_alcohol" id="form_box" size="20" value="<?php echo $result2['alcohol']['resnote']; ?>">&nbsp;</td><td class="bold">&nbsp;&nbsp;</td><td class="text"><input type="radio" name="radio_alcohol" id="radio_alcohol[current]" value="currentalcohol" <?php if ($PMSFH[0]['SOCH']['alcohol']['restype'] =='currentalcohol') echo "checked"; ?>>Current&nbsp;</td>
                          <td class="text"><input type="radio" name="radio_alcohol" id="radio_alcohol[quit]" value="quitalcohol" <?php if ($PMSFH[0]['SOCH']['alcohol']['restype'] =='quitalcohol') echo "checked"; ?>>Quit&nbsp;</td>
                          <td class="text"><input type="text" size="6" name="date_alcohol" id="date_alcohol" value="" title="Alcohol consumption" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)"><img src="/openemr/interface/pic/show_calendar.gif" align="absbottom" width="15" height="15" id="img_alcohol" border="0" alt="[?]" style="cursor:pointer" title="Click here to choose a date">&nbsp;</td>
                          <td class="text"><input type="radio" name="radio_alcohol" id="radio_alcohol[never]" value="neveralcohol" <?php if ($PMSFH[0]['SOCH']['alcohol']['restype'] =='neveralcohol') echo "checked"; ?>>Never&nbsp;</td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>

                <tr>
                  <td class="label right"  nowrap>Drugs:</td>
                  <td class="text data" colspan="3">
                    <table cellpadding="0" cellspacing="0">
                      <tbody>
                        <tr>
                          <td><input type="text" name="form_recreational_drugs" id="form_box" size="20" value="<?php echo $result2['recreational_drugs']['resnote']; ?>">&nbsp;</td><td class="bold">&nbsp;&nbsp;</td>
                          <td class="text"><input type="radio" name="radio_recreational_drugs" id="radio_recreational_drugs[current]" value="currentrecreational_drugs" <?php if ($PMSFH[0]['SOCH']['recreational_drugs']['restype'] =='currentrecreational_drugs') echo "checked"; ?>>Current&nbsp;</td>
                          <td class="text"><input type="radio" name="radio_recreational_drugs" id="radio_recreational_drugs[quit]" value="quitrecreational_drugs" <?php if ($PMSFH[0]['SOCH']['recreational_drugs']['restype'] =='quitrecreational_drugs') echo "checked"; ?>>Quit&nbsp;</td>
                          <td class="text"><input type="text" size="6" name="date_recreational_drugs" id="date_recreational_drugs" value="" title="Recreational drug use" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)"><img src="/openemr/interface/pic/show_calendar.gif" align="absbottom" width="15" height="15" id="img_recreational_drugs" border="0" alt="[?]" style="cursor:pointer" title="Click here to choose a date">&nbsp;</td> 
                          <td class="text"><input type="radio" name="radio_recreational_drugs" id="radio_recreational_drugs[never]" value="neverrecreational_drugs" <?php if ($PMSFH[0]['SOCH']['recreational_drugs']['restype'] =='neverrecreational_drugs') echo "checked"; ?>>Never&nbsp;</td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
               
                <tr class="nodisplay" ><td class="label right"  nowrap>Counseling:</td><td class="text data" colspan="3"><table cellpadding="0" cellspacing="0"><tbody><tr><td><input type="text" name="form_counseling" id="form_box" size="20" value="<?php echo $result2['counseling']['resnote']; ?>">&nbsp;</td><td class="bold">&nbsp;&nbsp;</td><td class="text"><input type="radio" name="radio_counseling" id="radio_counseling[current]" value="currentcounseling" <?php if ($PMSFH[0]['SOCH']['counseling']['restype'] =='currentcounseling') echo "checked"; ?>>Current&nbsp;</td>
                  <td class="text"><input type="radio" name="radio_counseling" id="radio_counseling[quit]" value="quitcounseling" <?php if ($PMSFH[0]['SOCH']['counseling']['restype'] =='quitcounseling') echo "checked"; ?>>Quit&nbsp;</td>
                  <td class="text"><input type="text" size="6" name="date_counseling" id="date_counseling" value="" title="Counseling activities" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)"><img src="/openemr/interface/pic/show_calendar.gif" align="absbottom" width="15" height="15" id="img_counseling" border="0" alt="[?]" style="cursor:pointer" title="Click here to choose a date">&nbsp;</td>
                  <td class="text"><input type="radio" name="radio_counseling" id="radio_counseling[never]" value="nevercounseling" <?php if ($PMSFH[0]['SOCH']['counseling']['restype'] =='nevercounseling') echo "checked"; ?>>Never&nbsp;</td>
                </tr></tbody></table></td></tr>
            
                <tr><td class="label right" nowrap>Exercise:</td><td class="text data" colspan="3"><table cellpadding="0" cellspacing="0"><tbody><tr><td><input type="text" name="form_exercise_patterns" id="form_box" size="20" value="<?php echo $result2['exercise_patterns']['resnote']; ?>">&nbsp;</td><td class="bold">&nbsp;&nbsp;</td><td class="text"><input type="radio" name="radio_exercise_patterns" id="radio_exercise_patterns[current]" value="currentexercise_patterns" <?php if ($PMSFH[0]['SOCH']['exercise_patterns']['restype'] =='currentexercise_patterns') echo "checked"; ?>>Current&nbsp;</td>
                  <td class="text"><input type="radio" name="radio_exercise_patterns" id="radio_exercise_patterns[quit]" value="quitexercise_patterns" <?php if ($PMSFH[0]['SOCH']['exercise_patterns']['restype'] =='quitexercise_patterns') echo "checked"; ?>>Quit&nbsp;</td>
                  <td class="text"><input type="text" name="date_exercise_patterns" id="date_exercise_patterns" value="" title="Exercise patterns" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)"><img src="/openemr/interface/pic/show_calendar.gif" align="absbottom" width="15" height="15" id="img_exercise_patterns" border="0" alt="[?]" style="cursor:pointer" title="Click here to choose a date">&nbsp;</td>
                  <td class="text"><input type="radio" name="radio_exercise_patterns" id="radio_exercise_patterns[never]" value="neverexercise_patterns"<?php if ($PMSFH[0]['SOCH']['exercise_patterns']['restype'] =='neverexercise_patterns') echo "checked"; ?>>Never&nbsp;</td>
                </tr></tbody></table></td></tr>
                
                <tr class="nodisplay"><td class="label right"  nowrap>Hazardous Activities:</td><td class="text data" colspan="3"><table cellpadding="0" cellspacing="0"><tbody><tr><td><input type="text" name="form_hazardous_activities" id="form_box" size="20" value="<?php echo $result2['hazardous_activities']['resnote']; ?>">&nbsp;</td><td class="bold">&nbsp;&nbsp;</td><td class="text"><input type="radio" name="radio_hazardous_activities" id="radio_hazardous_activities[current]" value="currenthazardous_activities" <?php if ($PMSFH[0]['SOCH']['hazardous_activities']['restype'] =='currenthazardous_activities') echo "checked"; ?>>Current&nbsp;</td>
                  <td class="text"><input type="radio" name="radio_hazardous_activities" id="radio_hazardous_activities[quit]" value="quithazardous_activities" <?php if ($PMSFH[0]['SOCH']['hazardous_activities']['restype'] =='quithazardous_activities') echo "checked"; ?>>Quit&nbsp;</td>
                  <td class="text"><input type="text" name="date_hazardous_activities" id="date_hazardous_activities" value="" title="Hazardous activities" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)"><img src="/openemr/interface/pic/show_calendar.gif" align="absbottom" width="15" height="15" id="img_hazardous_activities" border="0" alt="[?]" style="cursor:pointer" title="Click here to choose a date">&nbsp;</td>
                  <td class="text"><input type="radio" name="radio_hazardous_activities" id="radio_hazardous_activities[never]" value="neverhazardous_activities" <?php if ($PMSFH[0]['SOCH']['hazardous_activities']['restype'] =='neverhazardous_activities') echo "checked"; ?>>Never&nbsp;</td>
                </tr></tbody></table></td></tr>
               
                <tr><td class="label right"  nowrap>Sleep:</td><td class="text data" colspan="3"><input type="text" name="form_sleep_patterns" id="form_box" size="20" title="Sleep patterns" value="<?php echo $result2['sleep_patterns']['resnote']; ?>"></td></tr>
                
                <tr class="nodisplay">
                  <td class="label right"  nowrap>Seatbelt:</td>
                  <td class="text data" colspan="3">
                    <input type="text" name="form_seatbelt_use" id="form_box" size="20" title="Seatbelt use" value="<?php echo $result2['seatbelt_use']['resnote']; ?>">
                  </td>
                </tr>
            </tbody>
      </table>
      <table id="row_FH" name="row_FH" width="100%">
        <tr>
          <td class="label right" nowrap>Glaucoma:</td>
          <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext11" name="radio_usertext11" <?php if (!$usertext11) echo "checked='checked'"; ?>>
            <input type="text" name="usertext11" id="usertext11" onclick='clear_option(this)' value="<?php echo $result1['usertext11']; ?>"></td>
          <td class="label right" nowrap>Cataract:</td>
          <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext12" name="radio_usertext12" <?php if (!$usertext12) echo "checked='checked'"; ?>>
            <input type="text" name="usertext12" id="usertext12" onclick='clear_option(this)' value="<?php echo $result1['usertext12']; ?>"></td>
        </tr>
        <tr>
          <td class="label right" nowrap>AMD:</td>
          <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext13" name="radio_usertext13" <?php if (!$usertext13) echo "checked='checked'"; ?>>
            <input type="text" name="usertext13" id="usertext13" onclick='clear_option(this)' value="<?php echo $result1['usertext13']; ?>"></td>
          <td class="label right" nowrap>RD:</td>
          <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext14" name="radio_usertext14" <?php if (!$usertext14) echo "checked='checked'"; ?>>
            <input type="text" name="usertext14" id="usertext14" onclick='clear_option(this)' value="<?php echo $result1['usertext14']; ?>"></td>
        </tr>
        <tr>
          <td class="label right" nowrap>Blindness:</td>
          <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext15" name="radio_usertext15" <?php if (!$usertext15) echo "checked='checked'"; ?>>
            <input type="text" name="usertext15" id="usertext15" onclick='clear_option(this)' value="<?php echo $result1['usertext15']; ?>"></td>
          <td class="label right" nowrap>Amblyopia:</td>
          <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext16" name="radio_usertext16" <?php if (!$usertext16) echo "checked='checked'"; ?>>
            <input type="text" name="usertext16" id="usertext16" onclick='clear_option(this)' value="<?php echo $result1['usertext16']; ?>"></td>
        </tr>
        <tr>
          <td class="label right" nowrap>Strabismus:</td>
          <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext17" name="radio_usertext17" <?php if (!$usertext17) echo "checked='checked'"; ?>>
            <input type="text" name="usertext17" id="usertext17" onclick='clear_option(this)' value="<?php echo $result1['usertext17']; ?>"></td>
          <td class="label right" nowrap>Other:</td>
          <td class="text data"><input type="radio" onclick='negate_radio(this);' id="radio_usertext18" name="radio_usertext18" <?php if (!$usertext18) echo "checked='checked'"; ?>>
            <input type="text" name="usertext18" id="usertext18" onclick='clear_option(this)' value="<?php echo $result1['usertext18']; ?>"></td>
        </tr>
        <tr>
          <td class="label right" nowrap>Cancer:</td>
          <td class="text data">
            <input type="radio" onclick='negate_radio(this);' id="radio_relatives_cancer" name="radio_relatives_cancer" <?php if (!$result1['relatives_cancer']) echo "checked='checked'"; ?>>
            <input type="text" name="relatives_cancer" id="relatives_cancer" onclick='clear_option(this)' value="<?php echo $result1['relatives_cancer']; ?>"></td>
          <td class="label right" nowrap>Diabetes:</td>
          <td class="text data">
            <input type="radio" onclick='negate_radio(this);' id="radio_relatives_diabetes" name="radio_relatives_diabetes" <?php if (!$result1['relatives_diabetes']) echo "checked='checked'"; ?>>
            <input type="text" name="relatives_diabetes" id="relatives_diabetes" onclick='clear_option(this)' value="<?php echo $result1['relatives_diabetes']; ?>"></td>
        </tr>
        <tr>
          <td class="label right" nowrap>HTN:</td>
          <td class="text data">
            <input type="radio" onclick='negate_radio(this);' id="radio_relatives_high_blood_pressure" name="radio_relatives_high_blood_pressure" <?php if (!$result1['relatives_high_blood_pressure']) echo "checked='checked'"; ?>>
            <input type="text" name="relatives_high_blood_pressure" id="relatives_high_blood_pressure" onclick='clear_option(this)' value="<?php echo $result1['relatives_high_blood_pressure']; ?>"></td>
          <td class="label right" nowrap>Heart Problems:</td>
          <td class="text data">
            <input type="radio" onclick='negate_radio(this);' id="radio_relatives_heart_problems" name="radio_relatives_heart_problems" <?php if (!$result1['relatives_heart_problems']) echo "checked='checked'"; ?>>
            <input type="text" name="relatives_heart_problems" id="relatives_heart_problems" onclick='clear_option(this)' value="<?php echo $result1['relatives_heart_problems']; ?>"></td>
        </tr>
        <tr>
          <td class="label right" nowrap>Stroke:</td>
          <td class="text data">
            <input type="radio" onclick='negate_radio(this);' id="radio_relatives_stroke" name="radio_relatives_stroke" <?php if (!$result1['relatives_heart_problems']) echo "checked='checked'"; ?>>
            <input type="text" name="relatives_stroke" id="relatives_heart_problems" onclick='clear_option(this)' value="<?php echo $result1['relatives_stroke']; ?>"></td>
          <td class="label right" nowrap>Epilepsy:</td>
          <td class="text data">
            <input type="radio" onclick='negate_radio(this);' id="radio_relatives_epilepsy" name="radio_relatives_epilepsy" <?php if (!$result1['relatives_epilepsy']) echo "checked='checked'"; ?>>
            <input type="text" name="relatives_epilepsy" id="relatives_epilepsy" onclick='clear_option(this)' value="<?php echo $result1['relatives_epilepsy']; ?>"></td>
        </tr>
      </table>
      <table id="row_ROS" name="row_ROS" width="100%" class="ROS_class">
        <tr>
          <td>
            <h2>ROS:</h2>
          </td>
        </tr>
        <tr>
          <td></td>
          <td>
            <span class="underline" style="padding:5px;">Neg</span><span class="underline" style="margin:30px;">Positive</span>
          </td>
          <td></td>
          <td><span class="underline" style="padding:5px;">Neg</span><span class="underline" style="margin:30px;">Positive</span>
          </td>
        </tr>
        <tr>
          <td class="label right" nowrap>General:</td>
          <td>
            <input type="radio" onclick='negate_radio(this);' id="radio_ROSGENERAL" name="radio_ROSGENERAL" <?php if (!$ROSGENERAL) echo "checked='checked'"; ?>>
            <input type="text" name="ROSGENERAL" id="ROSGENERAL" onclick='clear_option(this)' value="<?php echo $ROSGENERAL; ?>"></td>
          <td class="label right" nowrap>HEENT:</td>
          <td>
            <input type="radio" onclick='negate_radio(this);' id="radio_ROSHEENT" name="radio_ROSHEENT"<?php if (!$ROSHEENT) echo "checked='checked'"; ?>>
            <input type="text" name="ROSHEENT" id="ROSHEENT" onclick='clear_option(this)' value="<?php echo $ROSHEENT; ?>"></td>
        </tr>  
        <tr>
          <td class="label right" nowrap>CV:</td>
          <td>
            <input type="radio" onclick='negate_radio(this);' id="radio_ROSCV" name="radio_ROSCV"<?php if (!$ROSCV) echo "checked='checked'"; ?>>
            <input type="text" name="ROSCV" id="ROSCV" onclick='clear_option(this)' value="<?php echo $ROSCV; ?>"></td>
          <td class="label right" nowrap>Pulmonary:</td>
          <td>
            <input type="radio" onclick='negate_radio(this);' id="radio_ROSPULM" name="radio_ROSPULM"<?php if (!$ROSPULM) echo "checked='checked'"; ?>>
            <input type="text" name="ROSPULM" id="ROSPULM" onclick='clear_option(this)' value="<?php echo $ROSPULM; ?>"></td>
          </tr>  
        <tr>
          <td class="label right" nowrap>GI:</td>
          <td>
            <input type="radio" onclick='negate_radio(this);' id="radio_ROSGI" name="radio_ROSGI"<?php if (!$ROSGI) echo "checked='checked'"; ?>>
            <input type="text" name="ROSGI" id="ROSGI" onclick='clear_option(this)' value="<?php echo $ROSGI; ?>"></td>
          <td class="label right" nowrap>GU:</td>
          <td>
            <input type="radio" onclick='negate_radio(this);' id="radio_ROSGU" name="radio_ROSGU"<?php if (!$ROSGU) echo "checked='checked'"; ?>>
            <input type="text" name="ROSGU" id="ROSGU" onclick='clear_option(this)' value="<?php echo $ROSGU; ?>"></td>
        </tr>  
        <tr>
          <td class="label right" nowrap>Derm:</td>
          <td>
            <input type="radio" onclick='negate_radio(this);' id="radio_ROSDERM" name="radio_ROSDERM"<?php if (!$ROSDERM) echo "checked='checked'"; ?>>
            <input type="text" name="ROSDERM" id="ROSDERM" onclick='clear_option(this)' value="<?php echo $ROSDERM; ?>"></td>
          <td class="label right" nowrap>Neuro:</td>
          <td>
           <input type="radio" onclick='negate_radio(this);' id="radio_ROSNEURO" name="radio_ROSNEURO"<?php if (!$ROSNEURO) echo "checked='checked'"; ?>>
            <input type="text" name="ROSNEURO" id="ROSNEURO" onclick='clear_option(this)' value="<?php echo $ROSNEURO; ?>"></td>
        </tr> 
        <tr>
          <td class="label right" nowrap>Psych:</td>
          <td>
            <input type="radio" onclick='negate_radio(this);' id="radio_ROSPSYCH" name="radio_ROSPSYCH"<?php if (!$ROSPSYCH) echo "checked='checked'"; ?>>
            <input type="text" name="ROSPSYCH" id="ROSPSYCH" onclick='clear_option(this)' value="<?php echo $ROSPSYCH; ?>"></td>
          <td class="label right" nowrap>Musculo:</td>
          <td>
           <input type="radio" onclick='negate_radio(this);' id="radio_ROSMUSCULO" name="radio_ROSMUSCULO"<?php if (!$ROSMUSCULO) echo "checked='checked'"; ?>>
            <input type="text" name="ROSMUSCULO" id="ROSMUSCULO" onclick='clear_option(this)' value="<?php echo $ROSMUSCULO; ?>"></td>
          </tr>   
        <tr>
          <td class="label right" nowrap>Immuno:</td>
          <td>
            <input type="radio" onclick='negate_radio(this);' id="radio_ROSIMMUNO" name="radio_ROSIMMUNO"<?php if (!$ROSIMMUNO) echo "checked='checked'"; ?>>
            <input type="text" name="ROSIMMUNO" id="ROSIMMUNO" onclick='clear_option(this)' value="<?php echo $ROSIMMUNO; ?>"></td>
          <td class="label right" nowrap>Endocrine:</td>
          <td>


            <input type="radio" onclick='negate_radio(this);' id="radio_ROSENDOCRINE" name="radio_ROSENDOCRINE"<?php if (!$ROSENDOCRINE) echo "checked='checked'"; ?>>
            <input type="text" name="ROSENDOCRINE" id="ROSENDOCRINE" onclick='clear_option(this)' value="<?php echo $ROSENDOCRINE; ?>"></td>
          </tr>  
        <tr><td></td></tr>
      </table>
      <table id="row_PLACEHOLDER" name="row_PLACEHOLDER" width="100%">
        <tr>
          <td>
          </td>
        </tr>
      </table>
    </div>
    <center>
      <p style="margin-top:4px;">

        <input type='button' id='form_save' name='form_save' onclick='top.restoreSession();submit_this_form();' value='<?php echo xla('Save'); ?>' />

        <?php if ($issue && acl_check('admin', 'super')) { ?>
        &nbsp;
        <input type='button' name='delete' onclick='top.restoreSession();deleteme();' value='<?php echo xla('Delete'); ?>' />
        <?php } ?>
        <!--
            &nbsp;
            <input type='button' value='<?php echo xla('Cancel'); ?>' onclick='closeme();' />
          -->
      </p>
      </center>
    </form>
  </div>
  <script language='JavaScript'>
     newtype(<?php if (!$type_index) $type_index="0"; echo $type_index; ?>);
     Calendar.setup({inputField:"form_begin", ifFormat:"%Y-%m-%d", button:"img_begin"});
     Calendar.setup({inputField:"form_end", ifFormat:"%Y-%m-%d", button:"img_end"});
     <?php 
     $has_cal ="tobacco,coffee,alcohol,recreational_drugs,exercise_patterns";
     foreach (split(',',$has_cal) as $item) {
      echo 'Calendar.setup({inputField:"date_'.$item.'", ifFormat:"%Y-%m-%d", button:"img_'.$item.'"});
      ';
     } ?>

  </script>
</body>
</html>


