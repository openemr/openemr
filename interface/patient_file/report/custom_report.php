<?php
use ESign\Api;
/**
 *
 * Patient custom report.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/report.inc");
require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/classes/Note.class.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/htmlspecialchars.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once(dirname(__file__) . "/../../../custom/code_types.inc.php");
require_once $GLOBALS['srcdir'].'/ESign/Api.php';

// For those who care that this is the patient report.
$GLOBALS['PATIENT_REPORT_ACTIVE'] = true;

$PDF_OUTPUT = empty($_POST['pdf']) ? false : true;

if ($PDF_OUTPUT) {
  require_once("$srcdir/html2pdf/html2pdf.class.php");
  $pdf = new HTML2PDF('P', 'Letter', 'en');
  ob_start();
}

// get various authorization levels
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_coding_a = acl_check('encounters', 'coding_a');
$auth_coding   = acl_check('encounters', 'coding');
$auth_relaxed  = acl_check('encounters', 'relaxed');
$auth_med      = acl_check('patients'  , 'med');
$auth_demo     = acl_check('patients'  , 'demo');

$esignApi = new Api();

$printable = empty($_GET['printable']) ? false : true;
if ($PDF_OUTPUT) $printable = true;
unset($_GET['printable']);

// Number of columns in tables for insurance and encounter forms.
$N = $PDF_OUTPUT ? 4 : 6;

$first_issue = 1;

function getContent() {
  global $web_root, $webserver_root;
  $content = ob_get_clean();
  // Fix a nasty html2pdf bug - it ignores document root!
  $i = 0;
  $wrlen = strlen($web_root);
  $wsrlen = strlen($webserver_root);
  while (true) {
    $i = stripos($content, " src='/", $i + 1);
    if ($i === false) break;
    if (substr($content, $i+6, $wrlen) === $web_root &&
        substr($content, $i+6, $wsrlen) !== $webserver_root)
    {
      $content = substr($content, 0, $i + 6) . $webserver_root . substr($content, $i + 6 + $wrlen);
    }
  }
  return $content;
}

function postToGet($arin) {
  $getstring="";
  foreach ($arin as $key => $val) {
    if (is_array($val)) {
      foreach ($val as $k => $v) {
        $getstring .= urlencode($key . "[]") . "=" . urlencode($v) . "&";
      }
    }
    else {
      $getstring .= urlencode($key) . "=" . urlencode($val) . "&";
    }
  }
  return $getstring;
}
?>

<?php if ($PDF_OUTPUT) { ?>
<link rel="stylesheet" href="<?php echo $webserver_root; ?>/interface/themes/style_pdf.css" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $webserver_root; ?>/library/ESign/css/esign_report.css" />
<?php } else {?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/ESign/css/esign_report.css" />
<?php } ?>

<?php // do not show stuff from report.php in forms that is encaspulated
      // by div of navigateLink class. Specifically used for CAMOS, but
      // can also be used by other forms that require output in the 
      // encounter listings output, but not in the custom report. ?>
<style>
  div.navigateLink {display:none;}
  .hilite {background-color: #FFFF00;}
  .hilite2 {background-color: transparent;}
  mark {background-color: #FFFF00;}
  .css_button{cursor:pointer;}
  .next {background-color: #FFFF00;}
  #search_options{
    position:fixed;
    left:0px;
    top:0px;
    z-index:10;
    border-bottom: solid thin #6D6D6D;
    padding:0% 2% 0% 2.5%;
  }
</style>

<?php if (!$PDF_OUTPUT) { ?>

<script type="text/javascript" src="<?php echo $GLOBALS['web_root']?>/library/js/jquery-1.5.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['web_root']?>/library/js/SearchHighlight.js"></script>
<script type="text/javascript">var $j = jQuery.noConflict();</script>

<script type="text/javascript">

  // Code for search & Highlight
  function reset_highlight(form_id,form_dir,class_name) { // Removes <span class='hilite' id=''>VAL</span> with VAL
      $j("."+class_name).each(function(){
      val = document.getElementById(this.id).innerHTML;
      $j("#"+this.id).replaceWith(val);
      
    });
  }
  var res_id = 0;            
  function doSearch(form_id,form_dir,exact,class_name,keys,case_sensitive) { // Uses jquery SearchHighlight Plug in
    var options ={};
    var keys = keys.replace(/^\s+|\s+$/g, '') ;
    options = {
      exact     :exact,
      style_name :class_name,
      style_name_suffix:false,
      highlight:'#search_div_'+form_id+'_'+form_dir,
      keys      :keys,
      set_case_sensitive:case_sensitive
      }
      $j(document).SearchHighlight(options);
        $j('.'+class_name).each(function(){
        res_id = res_id+1;
        $j(this).attr("id",'result_'+res_id);
      });
  }
  
  function remove_mark(form_id,form_dir){ // Removes all <mark> and </mark> tags
    var match1 = null;
    var src_str = document.getElementById('search_div_'+form_id+'_'+form_dir).innerHTML;
    var re = new RegExp('<mark>',"gi");
    var match2 = src_str.match(re);
    if(match2){
      src_str = src_str.replace(re,'');
    }
    var match2 = null;
    re = new RegExp('</mark>',"gi");
    if(match2){
      src_str = src_str.replace(re,'');
    }
    document.getElementById('search_div_'+form_id+'_'+form_dir).innerHTML=src_str;
  }
  function mark_hilight(form_id,form_dir,keys,case_sensitive){ // Adds <mark>match_val</mark> tags    
    keys = keys.replace(/^\s+|\s+$/g, '') ;
    if(keys == '') return;
    var src_str = $j('#search_div_'+form_id+'_'+form_dir).html();
    var term = keys;
    if((/\s+/).test(term) == true || (/['""-]{1,}/).test(term) == true){
      term = term.replace(/(\s+)/g,"(<[^>]+>)*$1(<[^>]+>)*");
      if(case_sensitive == true){
        var pattern = new RegExp("("+term+")", "g");
      }
      else{
        var pattern = new RegExp("("+term+")", "ig");
      }
      src_str = src_str.replace(/[\s\r\n]{1,}/g, ' '); // Replace text area newline or multiple spaces with single space
      src_str = src_str.replace(pattern, "<mark class='hilite'>$1</mark>");
      src_str = src_str.replace(/(<mark class=\'hilite\'>[^<>]*)((<[^>]+>)+)([^<>]*<\/mark>)/g,"$1</mark>$2<mark class='hilite'>$4");
      $j('#search_div_'+form_id+'_'+form_dir).html(src_str);
        $j('.hilite').each(function(){
        res_id = res_id+1;
        $j(this).attr("id",'result_'+res_id);
      });
    }else{
      if(case_sensitive == true)
      doSearch(form_id,form_dir,'partial','hilite',keys,'true');
      else
      doSearch(form_id,form_dir,'partial','hilite',keys,'false');
    }
  }
  
  var forms_array;
  var res_array   = Array();
  function find_all(){ // for each report the function mark_hilight() is called
    case_sensitive = false;
    if ($j('#search_case').attr('checked')) {
        case_sensitive = true;
    }
    var keys = document.getElementById('search_element').value;
    var match = null;
    match = keys.match(/[\^\$\.\|\?\+\(\)\\~`\!@#%&\+={}<>]{1,}/);
    if(match){
      document.getElementById('alert_msg').innerHTML='<?php echo xla('Special characters are not allowed');?>..!';
      return;
    }
    else{
      document.getElementById('alert_msg').innerHTML='';
    }
    
    forms_arr = document.getElementById('forms_to_search');
    for (var i = 0; i < forms_arr.options.length; i++) {
     if(forms_arr.options[i].selected ==true){
          $j('.class_'+forms_arr.options[i].value).each(function(){
          id_arr = this.id.split('search_div_');  
          var re = new RegExp('_','i');
          new_id = id_arr[1].replace(re, "|");
          new_id_arr = new_id.split('|');
          form_id = new_id_arr[0];
          form_dir = new_id_arr[1];
          mark_hilight(form_id,form_dir,keys,case_sensitive);
        });
          
      }
    }
    if($j('.hilite').length <1){
      if(keys != '')
      document.getElementById('alert_msg').innerHTML='<?php echo xla('No results found');?>..!';
    }
    else{
      document.getElementById('alert_msg').innerHTML='';
      f_id = $j('.hilite:first').attr('id');
      element = document.getElementById(f_id);
      element.scrollIntoView(false);
    }

  }
  
  function remove_mark_all(){ // clears previous search results if exists
    $j('.report_search_div').each(function(){
      var id_arr = this.id.split('search_div_');  
      var re = new RegExp('_','i');
      var new_id = id_arr[1].replace(re, "|");
      var new_id_arr = new_id.split('|');
      var form_id = new_id_arr[0];
      var form_dir = new_id_arr[1];
      reset_highlight(form_id,form_dir,'hilite');
      reset_highlight(form_id,form_dir,'hilite2');
      remove_mark(form_id,form_dir);
      res_id = 0;
      res_array =[];
    });
  }
  //
  var last_visited = -1;
  var last_clicked = "";
  var cur_res =0;
  function next(w_count){
    cur_res++;
    remove_mark_all();
    find_all();
    var index = -1;
    if(!($j(".hilite")[0])) {
      return;
    }
    $j('.hilite').each(function(){
      if($j(this).is(":visible")){
        index = index+1;
        res_array[index] = this.id;
      }
    });
    $j('.hilite').addClass("hilite2");
    $j('.hilite').removeClass("hilite");
    var array_count = res_array.length;
    if(last_clicked == "prev"){
      last_visited = last_visited + (w_count-1);
     }
     last_clicked = "next";
    for(k=0;k<w_count;k++){
      last_visited ++;
        if(last_visited == array_count){
          cur_res = 0;
          last_visited = -1;
          next(w_count);
          return;
        }
        $j("#"+res_array[last_visited]).addClass("next");
    }
    element = document.getElementById(res_array[last_visited]);
    element.scrollIntoView(false);
    
  }

  function prev(w_count){
    cur_res--;
    remove_mark_all();
    find_all();
    var index = -1;
    if(!($j(".hilite")[0])) {
      return;
    }
    $j('.hilite').each(function(){
      if($j(this).is(":visible")){
        index = index+1;
        res_array[index] = this.id;
      }
    });
     $j('.hilite').addClass("hilite2");
     $j('.hilite').removeClass("hilite");
     var array_count = res_array.length;
     if(last_clicked == "next"){
      last_visited = last_visited - (w_count-1);
     }
     last_clicked = "prev";
    for(k=0;k<w_count;k++){
      last_visited --;
      if(last_visited < 0){
        cur_res = (array_count/w_count) + 1;
        last_visited = array_count;
        prev(w_count);
        return;
      }
    $j("#"+res_array[last_visited]).addClass("next");
    
    }
    
    element = document.getElementById(res_array[last_visited]);
    element.scrollIntoView(false);
  }
  function clear_last_visit(){
    last_visited = -1;
    cur_res = 0;
    res_array = [];
    last_clicked = "";
  }

  function get_word_count(form_id,form_dir,keys,case_sensitive){
    keys = keys.replace(/^\s+|\s+$/g, '') ;
    if(keys == '') return;
    var src_str = $j('#search_div_'+form_id+'_'+form_dir).html();
    var term = keys;
    if((/\s+/).test(term) == true){
      term = term.replace(/(\s+)/g,"(<[^>]+>)*$1(<[^>]+>)*");
      if(case_sensitive == true){
        var pattern = new RegExp("("+term+")", "");
      }
      else{
        var pattern = new RegExp("("+term+")", "i");
      }
      src_str = src_str.replace(/[\s\r\n]{1,}/g, ' '); // Replace text area newline or multiple spaces with single space
      src_str = src_str.replace(pattern, "<mark class='hilite'>$1</mark>");
      src_str = src_str.replace(/(<mark class=\'hilite\'>[^<>]*)((<[^>]+>)+)([^<>]*<\/mark>)/,"$1</mark>$2<mark class='hilite'>$4");
      var res =[];
      res = src_str.match(/<mark class=\'hilite\'>/g);
      if(res != null){
        return res.length;
      }
    }else{
      return 1;
    } 
  }
  
  function next_prev(action){
    var w_count =0;
    case_sensitive = false;
    if ($j('#search_case').attr('checked')) {
        case_sensitive = true;
    }
    var keys = document.getElementById('search_element').value;
    var match = null;
    match = keys.match(/[\^\$\.\|\?\+\(\)\\~`\!@#%&\+={}<>]{1,}/);
    if(match){
      document.getElementById('alert_msg').innerHTML='<?php echo xla('Special characters are not allowed');?>..!';
      return;
    }
    else{
      document.getElementById('alert_msg').innerHTML='';
    }
    forms_arr = document.getElementById('forms_to_search');
    for (var i = 0; i < forms_arr.options.length; i++) {
     if(forms_arr.options[i].selected ==true){
          $j('.class_'+forms_arr.options[i].value).each(function(){
          id_arr = this.id.split('search_div_');  
          var re = new RegExp('_','i');
          new_id = id_arr[1].replace(re, "|");
          new_id_arr = new_id.split('|');
          form_id = new_id_arr[0];
          form_dir = new_id_arr[1];
          w_count = get_word_count(form_id,form_dir,keys,case_sensitive);
        });
        if(!isNaN(w_count)){
          break;
        }
      }
    }
    if(w_count <1){
      if(keys != '')
      document.getElementById('alert_msg').innerHTML='<?php echo xla('No results found');?>..!';
    }
    else{
      document.getElementById('alert_msg').innerHTML='';
      if(action == 'next'){
       next(w_count);
      }
      else if (action == 'prev'){
       prev(w_count);
      }
      var tot_res = res_array.length/w_count;
	  if(tot_res > 0){
		document.getElementById('alert_msg').innerHTML='<?php echo xla('Showing result');?> '+cur_res+' <?php echo xla('of');?> '+tot_res;
	  }
    }
    
  }
</script>
</head>
<body class="body_top" style="padding-top:95px;">
<?php } ?>
<div id="report_custom" style="width:100%;">  <!-- large outer DIV -->

<?php
if (sizeof($_GET) > 0) { $ar = $_GET; }
else { $ar = $_POST; }

if ($printable) {
  /*******************************************************************
  $titleres = getPatientData($pid, "fname,lname,providerID");
  $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
  *******************************************************************/
  $titleres = getPatientData($pid, "fname,lname,providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
  if ($_SESSION['pc_facility']) {
    $sql = "select * from facility where id=" . $_SESSION['pc_facility'];
  } else {
    $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
  }
  /******************************************************************/
  $db = $GLOBALS['adodb']['db'];
  $results = $db->Execute($sql);
  $facility = array();
  if (!$results->EOF) {
    $facility = $results->fields;
  }
  $practice_logo = "../../../custom/practice_logo.gif";
  if (file_exists($practice_logo)) {
    echo "<img src='$practice_logo' align='left'>\n";
  }
?>
<h2><?php echo $facility['name'] ?></h2>
<?php echo $facility['street'] ?><br>
<?php echo $facility['city'] ?>, <?php echo $facility['state'] ?> <?php echo $facility['postal_code'] ?><br clear='all'>
<?php echo $facility['phone'] ?><br>

<a href="javascript:window.close();"><span class='title'><?php echo $titleres['fname'] . " " . $titleres['lname']; ?></span></a><br>
<span class='text'><?php xl('Generated on','e'); ?>: <?php echo oeFormatShortDate(); ?></span>
<br><br>

<?php

} 
else { // not printable
?>

<a href="patient_report.php" onclick='top.restoreSession()'>
 <span class='title'><?php xl('Patient Report','e'); ?></span>
 <span class='back'><?php echo $tback;?></span>
</a><br><br>
<a href="custom_report.php?printable=1&<?php print postToGet($ar); ?>" class='link_submit' target='new' onclick='top.restoreSession()'>
 [<?php xl('Printable Version','e'); ?>]
</a><br>
<div class="report_search_bar" style="width:100%;" id="search_options">
  <table style="width:100%;">
    <tr>
      <td>
        <input type="text" onKeyUp="clear_last_visit();remove_mark_all();find_all();" name="search_element" id="search_element" style="width:180px;"/>
      </td>
      <td>
         <a class="css_button" onClick="clear_last_visit();remove_mark_all();find_all();" ><span><?php echo xlt('Find'); ?></span></a>
      </td>
      <td>
         <a class="css_button" onClick="next_prev('prev');" ><span><?php echo xlt('Prev'); ?></span></a>
      </td>
      <td>
         <a class="css_button" onClick="next_prev('next');" ><span><?php echo xlt('Next'); ?></span></a>
      </td>
      <td>
        <input type="checkbox" onClick="clear_last_visit();remove_mark_all();find_all();" name="search_case" id="search_case" />
      </td>
      <td>
        <span><?php echo xlt('Match case'); ?></span>
      </td>
      <td style="padding-left:10px;">
        <span class="text"><b><?php echo xlt('Search In'); ?>:</b></span>
        <br>
        <?php
        $form_id_arr = array();
        $form_dir_arr = array();
        $last_key ='';
        //ksort($ar);
        foreach ($ar as $key_search => $val_search) {
            if ($key_search == 'pdf' || $key_search == '' ) continue;
            if (($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
                        preg_match('/^(.*)_(\d+)$/', $key_search, $res_search);
                        $form_id_arr[] = add_escape_custom($res_search[2]);
                         $form_dir_arr[] = add_escape_custom($res_search[1]);
            }
        }
        //echo json_encode(json_encode($array_key_id));
        if(sizeof($form_id_arr)>0){
          $query = "SELECT DISTINCT(form_name),formdir FROM forms WHERE form_id IN ( '".implode("','",$form_id_arr)."') AND formdir IN ( '".implode("','",$form_dir_arr)."')";
          $arr = sqlStatement($query);
          echo "<select multiple size='4' style='width:300px;' id='forms_to_search' onchange='clear_last_visit();remove_mark_all();find_all();' >";
          while($res_forms_ids = sqlFetchArray($arr)){
            echo "<option value='".attr($res_forms_ids['formdir'])."' selected>".text($res_forms_ids['form_name'])."</option>";
          }
          echo "</select>";
        }
        ?>
      </td>
      <td style="padding-left:10px;;width:30%;">
        <span id ='alert_msg' style='color:red;'></span>
      </td>
    </tr>
  </table>
</div>
<?php
} // end not printable ?>

<?php

// include ALL form's report.php files
$inclookupres = sqlStatement("select distinct formdir from forms where pid = '$pid' AND deleted=0");
while($result = sqlFetchArray($inclookupres)) {
  // include_once("{$GLOBALS['incdir']}/forms/" . $result{"formdir"} . "/report.php");
  $formdir = $result['formdir'];
  if (substr($formdir,0,3) == 'LBF')
    include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
  else
    include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
}

// For each form field from patient_report.php...
//
foreach ($ar as $key => $val) {
    if ($key == 'pdf') continue;

    // These are the top checkboxes (demographics, allergies, etc.).
    //
    if (stristr($key,"include_")) {

        if ($val == "demographics") {
            
            echo "<hr />";
            echo "<div class='text demographics' id='DEM'>\n";
            print "<h1>".xl('Patient Data').":</h1>";
            // printRecDataOne($patient_data_array, getRecPatientData ($pid), $N);
            $result1 = getPatientData($pid);
            $result2 = getEmployerData($pid);
            echo "   <table>\n";
            display_layout_rows('DEM', $result1, $result2);
            echo "   </table>\n";
            echo "</div>\n";

        } elseif ($val == "history") {

            echo "<hr />";
            echo "<div class='text history' id='HIS'>\n";
            if (acl_check('patients', 'med')) {
                print "<h1>".xl('History Data').":</h1>";
                // printRecDataOne($history_data_array, getRecHistoryData ($pid), $N);
                $result1 = getHistoryData($pid);
                echo "   <table>\n";
                display_layout_rows('HIS', $result1);
                echo "   </table>\n";
            }
            echo "</div>";

            // } elseif ($val == "employer") {
            //   print "<br><span class='bold'>".xl('Employer Data').":</span><br>";
            //   printRecDataOne($employer_data_array, getRecEmployerData ($pid), $N);

        } elseif ($val == "insurance") {

            echo "<hr />";
            echo "<div class='text insurance'>";
            echo "<h1>".xl('Insurance Data').":</h1>";
            print "<br><span class=bold>".xl('Primary Insurance Data').":</span><br>";
            printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"primary"), $N);		
            print "<span class=bold>".xl('Secondary Insurance Data').":</span><br>";	
            printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"secondary"), $N);
            print "<span class=bold>".xl('Tertiary Insurance Data').":</span><br>";
            printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"tertiary"), $N);
            echo "</div>";

        } elseif ($val == "billing") {

            echo "<hr />";
            echo "<div class='text billing'>";
            print "<h1>".xl('Billing Information').":</h1>";
            if (count($ar['newpatient']) > 0) {
                $billings = array();
                echo "<table>";
                echo "<tr><td width='400' class='bold'>Code</td><td class='bold'>".xl('Fee')."</td></tr>\n";
                $total = 0.00;
                $copays = 0.00;
                foreach ($ar['newpatient'] as $be) {
                    $ta = split(":",$be);
                    $billing = getPatientBillingEncounter($pid,$ta[1]);
                    $billings[] = $billing;
                    foreach ($billing as $b) {
                        echo "<tr>\n";
                        echo "<td class=text>";
                        echo $b['code_type'] . ":\t" . $b['code'] . "&nbsp;". $b['modifier'] . "&nbsp;&nbsp;&nbsp;" . $b['code_text'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                        echo "</td>\n";
                        echo "<td class=text>";
                        echo oeFormatMoney($b['fee']);
                        echo "</td>\n";
                        echo "</tr>\n";
                        $total += $b['fee'];
                        if ($b['code_type'] == "COPAY") {
                            $copays += $b['fee'];
                        }
                    }
                }
                echo "<tr><td>&nbsp;</td></tr>";
                echo "<tr><td class=bold>".xl('Sub-Total')."</td><td class=text>" . oeFormatMoney($total + abs($copays)) . "</td></tr>";
                echo "<tr><td class=bold>".xl('Paid')."</td><td class=text>" . oeFormatMoney(abs($copays)) . "</td></tr>";
                echo "<tr><td class=bold>".xl('Total')."</td><td class=text>" . oeFormatMoney($total) . "</td></tr>";
                echo "</table>";
                echo "<pre>";
                //print_r($billings);
                echo "</pre>";
            } else {
                printPatientBilling($pid);
            }
            echo "</div>\n"; // end of billing DIV

    /****

        } elseif ($val == "allergies") {

            print "<span class=bold>Patient Allergies:</span><br>";
            printListData($pid, "allergy", "1");

        } elseif ($val == "medications") {

            print "<span class=bold>Patient Medications:</span><br>";
            printListData($pid, "medication", "1");

        } elseif ($val == "medical_problems") {

            print "<span class=bold>Patient Medical Problems:</span><br>";
            printListData($pid, "medical_problem", "1");

    ****/

        } elseif ($val == "immunizations") {

            if (acl_check('patients', 'med')) {
                echo "<hr />";
                echo "<div class='text immunizations'>\n";
                print "<h1>".xl('Patient Immunization').":</h1>";
                $sql = "select i1.immunization_id, i1.administered_date, substring(i1.note,1,20) as immunization_note, c.code_text_short ".
                   " from immunizations i1 ".
                   " left join code_types ct on ct.ct_key = 'CVX' ".
                   " left join codes c on c.code_type = ct.ct_id AND i1.cvx_code = c.code ".
                   " where i1.patient_id = '$pid' and i1.added_erroneously = 0 ".
                   " order by administered_date desc";
                $result = sqlStatement($sql);
                while ($row=sqlFetchArray($result)) {
                  // Figure out which name to use (ie. from cvx list or from the custom list)
                  if ($GLOBALS['use_custom_immun_list']) {
                     $vaccine_display = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
                  }
                  else {
                     if (!empty($row['code_text_short'])) {
                        $vaccine_display = htmlspecialchars( xl($row['code_text_short']), ENT_NOQUOTES);
                     }
                     else {
                        $vaccine_display = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
                     }
                  }
                  echo $row['administered_date'] . " - " . $vaccine_display;
                  if ($row['immunization_note']) {
                     echo " - " . $row['immunization_note'];
                  }
                  echo "<br>\n";
                }
                echo "</div>\n";
            }

        // communication report
        } elseif ($val == "batchcom") {

            echo "<hr />";
            echo "<div class='text transactions'>\n";
            print "<h1>".xl('Patient Communication sent').":</h1>";
            $sql="SELECT concat( 'Messsage Type: ', batchcom.msg_type, ', Message Subject: ', batchcom.msg_subject, ', Sent on:', batchcom.msg_date_sent ) AS batchcom_data, batchcom.msg_text, concat( users.fname, users.lname ) AS user_name FROM `batchcom` JOIN `users` ON users.id = batchcom.sent_by WHERE batchcom.patient_id='$pid'";
            // echo $sql;
            $result = sqlStatement($sql);
            while ($row=sqlFetchArray($result)) {
                echo $row{'batchcom_data'}.", By: ".$row{'user_name'}."<br>Text:<br> ".$row{'msg_txt'}."<br>\n";
            }
            echo "</div>\n";

        } elseif ($val == "notes") {

            echo "<hr />";
            echo "<div class='text notes'>\n";
            print "<h1>".xl('Patient Notes').":</h1>";
            printPatientNotes($pid);
            echo "</div>";

        } elseif ($val == "transactions") {

            echo "<hr />";
            echo "<div class='text transactions'>\n";
            print "<h1>".xl('Patient Transactions').":</h1>";
            printPatientTransactions($pid);
            echo "</div>";

        }

    } else {

        // Documents is an array of checkboxes whose values are document IDs.
        //
        if ($key == "documents") {

            echo "<hr />";
            echo "<div class='text documents'>";
            foreach($val as $valkey => $valvalue) {
                $document_id = $valvalue;
                if (!is_numeric($document_id)) continue;
                $d = new Document($document_id);
                $fname = basename($d->get_url());
				$couch_docid = $d->get_couch_docid();
				$couch_revid = $d->get_couch_revid();
                $extension = substr($fname, strrpos($fname,"."));
                echo "<h1>" . xl('Document') . " '" . $fname ."'</h1>";
                $notes = Note::notes_factory($d->get_id());
                if (!empty($notes)) echo "<table>";
                foreach ($notes as $note) {
                    echo '<tr>';
                    echo '<td>' . xl('Note') . ' #' . $note->get_id() . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>' . xl('Date') . ': ' . oeFormatShortDate($note->get_date()) . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>'.$note->get_note().'<br><br></td>';
                    echo '</tr>';
                }
                if (!empty($notes)) echo "</table>";

                $url_file = $d->get_url_filepath();
                if($couch_docid && $couch_revid){
                  $url_file = $d->get_couch_url($pid,$encounter);
                }
                // Collect filename and path
                $from_all = explode("/",$url_file);
                $from_filename = array_pop($from_all);
                $from_pathname_array = array();
                for ($i=0;$i<$d->get_path_depth();$i++) {
                  $from_pathname_array[] = array_pop($from_all);
                }
                $from_pathname_array = array_reverse($from_pathname_array);
                $from_pathname = implode("/",$from_pathname_array);

                if($couch_docid && $couch_revid) {
                  $from_file = $GLOBALS['OE_SITE_DIR'] . '/documents/temp/' . $from_filename;
                  $to_file = substr($from_file, 0, strrpos($from_file, '.')) . '_converted.jpg';
                }
                else {
                  $from_file = $GLOBALS["fileroot"] . "/sites/" . $_SESSION['site_id'] .
                    '/documents/' . $from_pathname . '/' . $from_filename;
                  $to_file = substr($from_file, 0, strrpos($from_file, '.')) . '_converted.jpg';
                }

                if ($extension == ".png" || $extension == ".jpg" || $extension == ".jpeg" || $extension == ".gif") {
                  if ($PDF_OUTPUT) {
                    // OK to link to the image file because it will be accessed by the
                    // HTML2PDF parser and not the browser.
                    $from_rel = $web_root . substr($from_file, strlen($webserver_root));
                    echo "<img src='$from_rel'";
                    // Flag images with excessive width for possible stylesheet action.
                    $asize = getimagesize($from_file);
                    if ($asize[0] > 750) echo " class='bigimage'";
                    echo " /><br><br>";
                  }
                  else {
                    echo "<img src='" . $GLOBALS['webroot'] .
                      "/controller.php?document&retrieve&patient_id=&document_id=" .
                      $document_id . "&as_file=false'><br><br>";
                  }
                }
                else {

          // Most clinic documents are expected to be PDFs, and in that happy case
          // we can avoid the lengthy image conversion process.
          if ($PDF_OUTPUT && $extension == ".pdf") {
            // HTML to PDF conversion will fail if there are open tags.
            echo "</div></div>\n";
            $content = getContent();
            // $pdf->setDefaultFont('Arial');
            $pdf->writeHTML($content, false);
            $pagecount = $pdf->pdf->setSourceFile($from_file);
            for($i = 0; $i < $pagecount; ++$i){
              $pdf->pdf->AddPage();  
              $itpl = $pdf->pdf->importPage($i + 1, '/MediaBox');
              $pdf->pdf->useTemplate($itpl);
            }
            // Make sure whatever follows is on a new page.
            $pdf->pdf->AddPage();
            // Resume output buffering and the above-closed tags.
            ob_start();
            echo "<div><div class='text documents'>\n";
          }
          else {
            if (! is_file($to_file)) exec("convert -density 200 \"$from_file\" -append -resize 850 \"$to_file\"");
            if (is_file($to_file)) {
              if ($PDF_OUTPUT) {
                // OK to link to the image file because it will be accessed by the
                // HTML2PDF parser and not the browser.
                echo "<img src='$to_file'><br><br>";
              }
              else {
                echo "<img src='" . $GLOBALS['webroot'] .
                  "/controller.php?document&retrieve&patient_id=&document_id=" .
                  $document_id . "&as_file=false&original_file=false'><br><br>";
              }
            } else {
              echo "<b>NOTE</b>: " . xl('Document') . "'" . $fname . "' " .
                xl('cannot be converted to JPEG. Perhaps ImageMagick is not installed?') . "<br><br>";
              if($couch_docid && $couch_revid) {
                unlink($from_file);
              }
            }
          }

                } // end if-else
            } // end Documents loop
            echo "</div>";

        } else if (strpos($key, "issue_") === 0) {
            // display patient Issues

            if ($first_issue) {
                $prevIssueType = 'asdf1234!@#$'; // random junk so as to not match anything
                $first_issue = 0;
                echo "<hr />";
                echo "<h1>".xl("Issues")."</h1>";
            }
            preg_match('/^(.*)_(\d+)$/', $key, $res);
            $rowid = $res[2];
            $irow = sqlQuery("SELECT type, title, comments, diagnosis " .
                            "FROM lists WHERE id = '$rowid'");
            $diagnosis = $irow['diagnosis'];
            if ($prevIssueType != $irow['type']) {
                // output a header for each Issue Type we encounter
                $disptype = $ISSUE_TYPES[$irow['type']][0];
                echo "<div class='issue_type'>" . $disptype . ":</div>\n";
                $prevIssueType = $irow['type'];
            }
            echo "<div class='text issue'>";
            echo "<span class='issue_title'>" . $irow['title'] . ":</span>";
            echo "<span class='issue_comments'> " . $irow['comments'] . "</span>\n";
            // Show issue's chief diagnosis and its description:
            if ($diagnosis) {
                echo "<div class='text issue_diag'>";
                echo "<span class='bold'>[".xl('Diagnosis')."]</span><br>";
                $dcodes = explode(";", $diagnosis);
                foreach ($dcodes as $dcode) {
                    echo "<span class='italic'>".$dcode."</span>: ";
                    echo lookup_code_descriptions($dcode)."<br>\n";
                }
                //echo $diagnosis." -- ".lookup_code_descriptions($diagnosis)."\n";
                echo "</div>";
            }

            // Supplemental data for GCAC or Contraception issues.
            if ($irow['type'] == 'ippf_gcac') {
                echo "   <table>\n";
                display_layout_rows('GCA', sqlQuery("SELECT * FROM lists_ippf_gcac WHERE id = '$rowid'"));
                echo "   </table>\n";
            }
            else if ($irow['type'] == 'contraceptive') {
                echo "   <table>\n";
                display_layout_rows('CON', sqlQuery("SELECT * FROM lists_ippf_con WHERE id = '$rowid'"));
                echo "   </table>\n";
            }

            echo "</div>\n"; //end the issue DIV

        } else {
            // we have an "encounter form" form field whose name is like
            // dirname_formid, with a value which is the encounter ID.
            //
            // display encounter forms, encoded as a POST variable
            // in the format: <formdirname_formid>=<encounterID>

            if (($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
                $form_encounter = $val;
                preg_match('/^(.*)_(\d+)$/', $key, $res);
                $form_id = $res[2];
                $formres = getFormNameByFormdirAndFormid($res[1],$form_id);
                $dateres = getEncounterDateByEncounter($form_encounter);
                $formId = getFormIdByFormdirAndFormid($res[1], $form_id);

                if ($res[1] == 'newpatient') {
                    echo "<div class='text encounter'>\n";
                    echo "<h1>" . xl($formres["form_name"]) . "</h1>";
                }
                else {
                    echo "<div class='text encounter_form'>";
                    echo "<h1>" . xl_form_title($formres["form_name"]) . "</h1>";
                }

                // show the encounter's date
                echo "(" . oeFormatSDFT(strtotime($dateres["date"])) . ") ";
                if ($res[1] == 'newpatient') {
                    // display the provider info
                    echo ' '. xl('Provider') . ': ' . text(getProviderName(getProviderIdOfEncounter($form_encounter)));
                }
                echo "<br>\n";
   
                // call the report function for the form
                ?>                
                <div name="search_div" id="search_div_<?php echo attr($form_id)?>_<?php echo attr($res[1])?>" class="report_search_div class_<?php echo attr($res[1]); ?>">
                <?php
                if (substr($res[1],0,3) == 'LBF')
                  call_user_func("lbf_report", $pid, $form_encounter, $N, $form_id, $res[1]);
                else
                  call_user_func($res[1] . "_report", $pid, $form_encounter, $N, $form_id);
                
                $esign = $esignApi->createFormESign( $formId, $res[1], $form_encounter );
                if ( $esign->isLogViewable() ) {
                    $esign->renderLog();
                }
                ?>
                
                </div>
                <?php

                if ($res[1] == 'newpatient') {
                    // display billing info
                    $bres = sqlStatement("SELECT b.date, b.code, b.code_text " .
                      "FROM billing AS b, code_types AS ct WHERE " .
                      "b.pid = ? AND " .
                      "b.encounter = ? AND " .
                      "b.activity = 1 AND " .
                      "b.code_type = ct.ct_key AND " .
                      "ct.ct_diag = 0 " .
                      "ORDER BY b.date",
                      array($pid, $form_encounter));
                    while ($brow=sqlFetchArray($bres)) {
                        echo "<span class='bold'>&nbsp;".xl('Procedure').": </span><span class='text'>" .
                            $brow['code'] . " " . $brow['code_text'] . "</span><br>\n";
                    }
                }

                print "</div>";
            
            } // end auth-check for encounter forms

        } // end if('issue_')... else...

    } // end if('include_')... else...

} // end $ar loop

if ($printable)
  echo "<br /><br />" . xl('Signature') . ": _______________________________<br />";
?>

</div> <!-- end of report_custom DIV -->

<?php
if ($PDF_OUTPUT) {
  $content = getContent();
  // $pdf->setDefaultFont('Arial');
  $pdf->writeHTML($content, false);
  $pdf->Output('report.pdf', 'D'); // D = Download, I = Inline
}
else {
?>
</body>
</html>
<?php } ?>
