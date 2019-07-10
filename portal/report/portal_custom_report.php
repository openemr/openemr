<?php
/**
 * Patient custom report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady@sparmy.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Tony McCormick <tony@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

//landing page definition -- where to go if something goes wrong
$landingpage = "../index.php?site=" . urlencode($_SESSION['site_id']);
//

// kick out if patient not authenticated
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $user = $_SESSION['sessionUser'];
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: '.$landingpage.'&w');
    exit;
}

$ignoreAuth = true;
global $ignoreAuth;

require_once('../../interface/globals.php');
require_once("$srcdir/forms.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/report.inc");
require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/classes/Note.class.php");
require_once(dirname(__file__) . "/../../custom/code_types.inc.php");
require_once $GLOBALS['srcdir'].'/ESign/Api.php';
require_once($GLOBALS["include_root"] . "/orders/single_order_results.inc.php");
if ($GLOBALS['gbl_portal_cms_enable']) {
    require_once($GLOBALS["include_root"] . "/cmsportal/portal.inc.php");
}
require_once($GLOBALS['fileroot'] . "/controllers/C_Document.class.php");

use ESign\Api;
use Mpdf\Mpdf;

// For those who care that this is the patient report.
$GLOBALS['PATIENT_REPORT_ACTIVE'] = true;

$PDF_OUTPUT = empty($_POST['pdf']) ? 0 : intval($_POST['pdf']);

if ($PDF_OUTPUT) {
    $config_mpdf = array(
        'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
        'mode' => $GLOBALS['pdf_language'],
        'format' => $GLOBALS['pdf_size'],
        'default_font_size' => '9',
        'default_font' => 'dejavusans',
        'margin_left' => $GLOBALS['pdf_left_margin'],
        'margin_right' => $GLOBALS['pdf_right_margin'],
        'margin_top' => $GLOBALS['pdf_top_margin'],
        'margin_bottom' => $GLOBALS['pdf_bottom_margin'],
        'margin_header' => '',
        'margin_footer' => '',
        'orientation' => $GLOBALS['pdf_layout'],
        'shrink_tables_to_fit' => 1,
        'use_kwt' => true,
        'autoScriptToLang' => true,
        'keep_table_proportions' => true
    );
    $pdf = new mPDF($config_mpdf);
    if ($_SESSION['language_direction'] == 'rtl') {
        $pdf->SetDirectionality('rtl');
    }
    ob_start();
}

// get various authorization levels
$auth_notes_a  = true; //acl_check('encounters', 'notes_a');
$auth_notes    = true; //acl_check('encounters', 'notes');
$auth_coding_a = true; //acl_check('encounters', 'coding_a');
$auth_coding   = true; //acl_check('encounters', 'coding');
$auth_relaxed  = true; //acl_check('encounters', 'relaxed');
$auth_med      = true; //acl_check('patients'  , 'med');
$auth_demo     = true; //acl_check('patients'  , 'demo');

$esignApi = new Api();

$printable = empty($_GET['printable']) ? false : true;
if ($PDF_OUTPUT) {
    $printable = true;
}

unset($_GET['printable']);

// Number of columns in tables for insurance and encounter forms.
$N = $PDF_OUTPUT ? 4 : 6;

$first_issue = 1;

function getContent()
{
    global $web_root, $webserver_root;
    $content = ob_get_clean();
    // Fix a nasty html2pdf bug - it ignores document root!
    // TODO - now use mPDF, so should test if still need this fix
    $i = 0;
    $wrlen = strlen($web_root);
    $wsrlen = strlen($webserver_root);
    while (true) {
        $i = stripos($content, " src='/", $i + 1);
        if ($i === false) {
            break;
        }

        if (substr($content, $i+6, $wrlen) === $web_root &&
        substr($content, $i+6, $wsrlen) !== $webserver_root) {
            $content = substr($content, 0, $i + 6) . $webserver_root . substr($content, $i + 6 + $wrlen);
        }
    }

    return $content;
}

function postToGet($arin)
{
    $getstring="";
    foreach ($arin as $key => $val) {
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $getstring .= urlencode($key . "[]") . "=" . urlencode($v) . "&";
            }
        } else {
            $getstring .= urlencode($key) . "=" . urlencode($val) . "&";
        }
    }

    return $getstring;
}
?>

<?php if ($PDF_OUTPUT) { ?>
<link rel="stylesheet" href="<?php echo $webserver_root; ?>/interface/themes/style_pdf.css?v=<?php echo $v_js_includes; ?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $webserver_root; ?>/library/ESign/css/esign_report.css?v=<?php echo $v_js_includes; ?>" />
<?php } else {?>
<html>
<head>

<?php } ?>

<?php // do not show stuff from report.php in forms that is encaspulated
      // by div of navigateLink class. Specifically used for CAMOS, but
      // can also be used by other forms that require output in the
      // encounter listings output, but not in the custom report. ?>

<style>

.h3, h3 {
    font-size: 20px;
}
.report_search_div{
font-size: 20px !important;
font-style:bold;
}
.label {
color:black;
}/*
.groupname {
color:green;
}*/
input[type="checkbox"], input[type="radio"] {
    margin: 0 5px 5px;
    line-height: normal;
}
</style>

<?php if (!$PDF_OUTPUT) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/ESign/css/esign_report.css?v=<?php echo $v_js_includes; ?>" />
<script type="text/javascript" src="<?php echo $GLOBALS['web_root']?>/library/js/SearchHighlight.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript">var $j = jQuery.noConflict();</script>

    <?php // if the track_anything form exists, then include the styling
    if (file_exists(dirname(__FILE__) . "/../../forms/track_anything/style.css")) { ?>
 <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']?>/interface/forms/track_anything/style.css?v=<?php echo $v_js_includes; ?>" type="text/css">
    <?php  } ?>

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
if (sizeof($_GET) > 0) {
    $ar = $_GET;
} else {
    $ar = $_POST;
}

if ($printable) {
  /*******************************************************************
  $titleres = getPatientData($pid, "fname,lname,providerID");
  $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
  *******************************************************************/
    $titleres = getPatientData($pid, "fname,lname,providerID,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
    if ($_SESSION['pc_facility']) {
        $sql = "select * from facility where id=" . add_escape_custom($_SESSION['pc_facility']);
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

    // Setup Headers and Footers for mPDF only Download
    // in HTML view it's just one line at the top of page 1
    echo '<page_header style="text-align:right;"> ' . xlt("PATIENT") . ':' . text($titleres['lname']) . ', ' . text($titleres['fname']) . ' - ' . text($titleres['DOB_TS']) . '</page_header>    ';
    echo '<page_footer style="text-align:right;">' . xlt('Generated on') . ' ' . text(oeFormatShortDate()) . ' - ' . text($facility['name']) . ' ' . text($facility['phone']) . '</page_footer>';

    // Use logo if it exists as 'practice_logo.gif' in the site dir
    // old code used the global custom dir which is no longer a valid
    $practice_logo = "$OE_SITE_DIR/images/practice_logo.gif";
    if (file_exists($practice_logo)) {
        echo "<img src='$practice_logo' align='left'><br />\n";
    }
    ?>
    <h2><?php echo text($facility['name']); ?></h2>
    <?php echo text($facility['street']); ?><br>
    <?php echo text($facility['city']); ?>, <?php echo text($facility['state']); ?> <?php echo text($facility['postal_code']); ?><br clear='all'>
    <?php echo $facility['phone'] ?><br>

<a href="javascript:window.close();"><span class='title'><?php echo text($titleres['fname']) . " " . text($titleres['lname']); ?></span></a><br>
<span class='text'><?php echo xlt('Generated on'); ?>: <?php echo text(oeFormatShortDate()); ?></span>
<br><br>

    <?php
} else { // not printable
    ?>

    <!-- old href was here
    <br><br> -->

    <a href="./report/portal_custom_report.php?printable=1&<?php echo postToGet($ar); ?>" class='link_submit' target='new'>
<button><?php echo xlt('Printable Version'); ?></button>
</a><br>
<!--<div class="report_search_bar" style="width:100%;" id="search_options">
  <table style="width:100%;">
    <tr>
  <td>
    <input type="text" onKeyUp="clear_last_visit();remove_mark_all();find_all();" name="search_element" id="search_element" style="width:180px;"/>
  </td>
  <td>
     <a class="css_button" onClick="clear_last_visit();remove_mark_all();find_all();" ><span><?php //echo xlt('Find'); ?></span></a>
  </td>
  <td>
     <a class="css_button" onClick="next_prev('prev');" ><span><?php //echo xlt('Prev'); ?></span></a>
  </td>
  <td>
     <a class="css_button" onClick="next_prev('next');" ><span><?php //echo xlt('Next'); ?></span></a>
  </td>
  <td>
    <input type="checkbox" onClick="clear_last_visit();remove_mark_all();find_all();" name="search_case" id="search_case" />
  </td>
  <td>
    <span><?php //echo xlt('Match case'); ?></span>
  </td>
  <td style="padding-left:10px;">
    <span class="text"><b><?php //echo xlt('Search In'); ?>:</b></span>
    <br>
    <?php
    /*         $form_id_arr = array();
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
    } */
    ?>
      </td>
      <td style="padding-left:10px;;width:30%;">
    <span id ='alert_msg' style='color:red;'></span>
      </td>
    </tr>
  </table>
    </div>-->
    <?php
} // end not printable ?>

<?php

// include ALL form's report.php files
$inclookupres = sqlStatement("select distinct formdir from forms where pid = ? AND deleted=0", [$pid]);
while ($result = sqlFetchArray($inclookupres)) {
  // include_once("{$GLOBALS['incdir']}/forms/" . $result{"formdir"} . "/report.php");
    $formdir = $result['formdir'];
    if (substr($formdir, 0, 3) == 'LBF') {
        include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
    } else {
        include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
    }
}

if ($PDF_OUTPUT) {
    $tmp_files_remove = array();
}

// For each form field from patient_report.php...
//
foreach ($ar as $key => $val) {
    if ($key == 'pdf') {
        continue;
    }

    // These are the top checkboxes (demographics, allergies, etc.).
    //
    if (stristr($key, "include_")) {
        if ($val == "demographics") {
            echo "<hr />";
            echo "<div class='text demographics' id='DEM'>\n";
            print "<h1>".xlt('Patient Data').":</h1>";
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
            //if (acl_check('patients', 'med')) {
                print "<h1>".xlt('History Data').":</h1>";
                // printRecDataOne($history_data_array, getRecHistoryData ($pid), $N);
                $result1 = getHistoryData($pid);
                echo "   <table>\n";
                display_layout_rows('HIS', $result1);
                echo "   </table>\n";
            //}
            echo "</div>";

            // } elseif ($val == "employer") {
            //   print "<br><span class='bold'>".xl('Employer Data').":</span><br>";
            //   printRecDataOne($employer_data_array, getRecEmployerData ($pid), $N);
        } elseif ($val == "insurance") {
            echo "<hr />";
            echo "<div class='text insurance'>";
            echo "<h1>".xlt('Insurance Data').":</h1>";
            print "<br><span class=bold>".xlt('Primary Insurance Data').":</span><br>";
            printRecDataOne($insurance_data_array, getRecInsuranceData($pid, "primary"), $N);
            print "<span class=bold>".xlt('Secondary Insurance Data').":</span><br>";
            printRecDataOne($insurance_data_array, getRecInsuranceData($pid, "secondary"), $N);
            print "<span class=bold>".xlt('Tertiary Insurance Data').":</span><br>";
            printRecDataOne($insurance_data_array, getRecInsuranceData($pid, "tertiary"), $N);
            echo "</div>";
        } elseif ($val == "billing") {
            echo "<hr />";
            echo "<div class='text billing'>";
            print "<h1>".xlt('Billing Information').":</h1>";
            if ((!empty($ar['newpatient'])) && (count($ar['newpatient']) > 0)) {
                $billings = array();
                echo "<table>";
                echo "<tr><td width='400' class='bold'>" . xlt('Code') . "</td><td class='bold'>".xlt('Fee')."</td></tr>\n";
                $total = 0.00;
                $copays = 0.00;
                foreach ($ar['newpatient'] as $be) {
                    $ta = explode(":", $be);
                    $billing = getPatientBillingEncounter($pid, $ta[1]);
                    $billings[] = $billing;
                    foreach ($billing as $b) {
                        echo "<tr>\n";
                        echo "<td class=text>";
                        echo text($b['code_type']) . ":\t" . text($b['code']) . "&nbsp;". text($b['modifier']) . "&nbsp;&nbsp;&nbsp;" . text($b['code_text']) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                        echo "</td>\n";
                        echo "<td class=text>";
                        echo text(oeFormatMoney($b['fee']));
                        echo "</td>\n";
                        echo "</tr>\n";
                        $total += $b['fee'];
                        if ($b['code_type'] == "COPAY") {
                            $copays += $b['fee'];
                        }
                    }
                }

                echo "<tr><td>&nbsp;</td></tr>";
                echo "<tr><td class=bold>".xlt('Sub-Total')."</td><td class=text>" . text(oeFormatMoney($total + abs($copays))) . "</td></tr>";
                echo "<tr><td class=bold>".xlt('Paid')."</td><td class=text>" . text(oeFormatMoney(abs($copays))) . "</td></tr>";
                echo "<tr><td class=bold>".xlt('Total')."</td><td class=text>" . text(oeFormatMoney($total)) . "</td></tr>";
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
            //if (acl_check('patients', 'med')) {
                echo "<hr />";
                echo "<div class='text immunizations'>\n";
                print "<h1>".xlt('Patient Immunization').":</h1>";
                $sql = "select i1.immunization_id, i1.administered_date, substring(i1.note,1,20) as immunization_note, c.code_text_short ".
                   " from immunizations i1 ".
                   " left join code_types ct on ct.ct_key = 'CVX' ".
                   " left join codes c on c.code_type = ct.ct_id AND i1.cvx_code = c.code ".
                   " where i1.patient_id = ? and i1.added_erroneously = 0 ".
                   " order by administered_date desc";
                $result = sqlStatement($sql, [$pid]);
            while ($row=sqlFetchArray($result)) {
              // Figure out which name to use (ie. from cvx list or from the custom list)
                if ($GLOBALS['use_custom_immun_list']) {
                     $vaccine_display = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
                } else {
                    if (!empty($row['code_text_short'])) {
                        $vaccine_display = xlt($row['code_text_short']);
                    } else {
                         $vaccine_display = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
                    }
                }

                echo text($row['administered_date']) . " - " . $vaccine_display;
                if ($row['immunization_note']) {
                     echo " - " . text($row['immunization_note']);
                }

                echo "<br>\n";
            }

                echo "</div>\n";
            //}

        // communication report
        } elseif ($val == "batchcom") {
            echo "<hr />";
            echo "<div class='text transactions'>\n";
            print "<h1>".xlt('Patient Communication sent').":</h1>";
            $sql="SELECT concat( 'Messsage Type: ', batchcom.msg_type, ', Message Subject: ', batchcom.msg_subject, ', Sent on:', batchcom.msg_date_sent ) AS batchcom_data, batchcom.msg_text, concat( users.fname, users.lname ) AS user_name FROM `batchcom` JOIN `users` ON users.id = batchcom.sent_by WHERE batchcom.patient_id=?";
            // echo $sql;
            $result = sqlStatement($sql, [$pid]);
            while ($row=sqlFetchArray($result)) {
                echo text($row{'batchcom_data'}).", " . xlt('By') . ": ".text($row{'user_name'})."<br>" . xlt('Text') . ":<br> ".text($row{'msg_txt'})."<br>\n";
            }

            echo "</div>\n";
        } elseif ($val == "notes") {
            echo "<hr />";
            echo "<div class='text notes'>\n";
            print "<h1>".xlt('Patient Notes').":</h1>";
            printPatientNotes($pid);
            echo "</div>";
        } elseif ($val == "transactions") {
            echo "<hr />";
            echo "<div class='text transactions'>\n";
            print "<h1>".xlt('Patient Transactions').":</h1>";
            printPatientTransactions($pid);
            echo "</div>";
        }
    } else {
        // Documents is an array of checkboxes whose values are document IDs.
        //
        if ($key == "documents") {
            echo "<hr />";
            echo "<div class='text documents'>";
            foreach ($val as $valkey => $valvalue) {
                $document_id = $valvalue;
                if (!is_numeric($document_id)) {
                    continue;
                }

                $d = new Document($document_id);
                $fname = basename($d->get_url());
                $extension = substr($fname, strrpos($fname, "."));
                echo "<h1>" . xlt('Document') . " '" . text($fname) ."'</h1>";

                $notes = $d->get_notes();
                if (!empty($notes)) {
                    echo "<table>";
                }

                foreach ($notes as $note) {
                    echo '<tr>';
                    echo '<td>' . xlt('Note') . ' #' . text($note->get_id()) . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>' . xlt('Date') . ': ' . text(oeFormatShortDate($note->get_date())) . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>' . text($note->get_note()) . '<br><br></td>';
                    echo '</tr>';
                }

                if (!empty($notes)) {
                    echo "</table>";
                }

                if ($extension == ".png" || $extension == ".jpg" || $extension == ".jpeg" || $extension == ".gif") {
                    if ($PDF_OUTPUT) {
                        // OK to link to the image file because it will be accessed by the
                        // mPDF parser and not the browser.
                        $tempDocC = new C_Document;
                        $fileTemp = $tempDocC->retrieve_action($d->get_foreign_id(), $document_id, false, true, true, true);
                        // tmp file in ../documents/temp since need to be available via webroot
                        $from_file_tmp_web_name = tempnam($GLOBALS['OE_SITE_DIR'].'/documents/temp', "oer");
                        file_put_contents($from_file_tmp_web_name, $fileTemp);
                        echo "<img src='$from_file_tmp_web_name'";
                        // Flag images with excessive width for possible stylesheet action.
                        $asize = getimagesize($from_file_tmp_web_name);
                        if ($asize[0] > 750) {
                            echo " class='bigimage'";
                        }
                        $tmp_files_remove[] = $from_file_tmp_web_name;
                        echo " /><br><br>";
                    } else {
                        echo "<img src='" . $GLOBALS['webroot'] .
                            "/controller.php?document&retrieve&patient_id=&document_id=" .
                            attr_url($document_id) . "&as_file=false'><br><br>";
                    }
                } else {
                    // Most clinic documents are expected to be PDFs, and in that happy case
                    // we can avoid the lengthy image conversion process.
                    if ($PDF_OUTPUT && $extension == ".pdf") {
                        echo "</div></div>\n"; // HTML to PDF conversion will fail if there are open tags.
                        $content = getContent();
                        $pdf->writeHTML($content); // catch up with buffer.
                        $tempDocC = new C_Document;
                        $pdfTemp = $tempDocC->retrieve_action($d->get_foreign_id(), $document_id, false, true, true, true);
                        // tmp file in temporary_files_dir
                        $from_file_tmp_name = tempnam($GLOBALS['temporary_files_dir'], "oer");
                        file_put_contents($from_file_tmp_name, $pdfTemp);
                        $pagecount = $pdf->setSourceFile($from_file_tmp_name);
                        for ($i = 0; $i < $pagecount; ++$i) {
                            $pdf->AddPage();
                            $itpl = $pdf->importPage($i+1);
                            $pdf->useTemplate($itpl);
                        }
                        unlink($from_file_tmp_name);

                        // Make sure whatever follows is on a new page.
                        $pdf->AddPage();

                        // Resume output buffering and the above-closed tags.
                        ob_start();

                        echo "<div><div class='text documents'>\n";
                    } else {
                        if ($PDF_OUTPUT) {
                            // OK to link to the image file because it will be accessed by the mPDF parser and not the browser.
                            $tempDocC = new C_Document;
                            $fileTemp = $tempDocC->retrieve_action($d->get_foreign_id(), $document_id, false, false, true, true);
                            // tmp file in ../documents/temp since need to be available via webroot
                            $from_file_tmp_web_name = tempnam($GLOBALS['OE_SITE_DIR'].'/documents/temp', "oer");
                            file_put_contents($from_file_tmp_web_name, $fileTemp);
                            echo "<img src='$from_file_tmp_web_name'><br><br>";
                            $tmp_files_remove[] = $from_file_tmp_web_name;
                        } else {
                            echo "<img src='" . $GLOBALS['webroot'] . "/controller.php?document&retrieve&patient_id=&document_id=" . attr_url($document_id) . "&as_file=false&original_file=false'><br><br>";
                        }
                    }
                } // end if-else
            } // end Documents loop
            echo "</div>";
        } else if ($key == "procedures") { // Procedures is an array of checkboxes whose values are procedure order IDs.
            if ($auth_med) {
                echo "<hr />";
                echo "<div class='text documents'>";
                foreach ($val as $valkey => $poid) {
                    echo "<h1>" . xlt('Procedure Order') . ":</h1>";
                    echo "<br />\n";
                    // Need to move the inline styles from this function to the stylesheet, but until
                    // then we do it just for PDFs to avoid breaking anything.
                    generate_order_report($poid, false, !$PDF_OUTPUT);
                    echo "<br />\n";
                }

                echo "</div>";
            }
        } else if (strpos($key, "issue_") === 0) {
            // display patient Issues

            if ($first_issue) {
                $prevIssueType = 'asdf1234!@#$'; // random junk so as to not match anything
                $first_issue = 0;
                echo "<hr />";
                echo "<h1>".xlt("Issues")."</h1>";
            }

            preg_match('/^(.*)_(\d+)$/', $key, $res);
            $rowid = $res[2];
            $irow = sqlQuery("SELECT type, title, comments, diagnosis " .
                            "FROM lists WHERE id = ?", [$rowid]);
            $diagnosis = $irow['diagnosis'];
            if ($prevIssueType != $irow['type']) {
                // output a header for each Issue Type we encounter
                $disptype = $ISSUE_TYPES[$irow['type']][0];
                echo "<div class='issue_type'>" . attr($disptype) . ":</div>\n";
                $prevIssueType = $irow['type'];
            }

            echo "<div class='text issue'>";
            echo "<span class='issue_title'>" . text($irow['title']) . ":</span>";
            echo "<span class='issue_comments'> " . text($irow['comments']) . "</span>\n";
            // Show issue's chief diagnosis and its description:
            if ($diagnosis) {
                echo "<div class='text issue_diag'>";
                echo "<span class='bold'>[".xlt('Diagnosis')."]</span><br>";
                $dcodes = explode(";", $diagnosis);
                foreach ($dcodes as $dcode) {
                    echo "<span class='italic'>".text($dcode)."</span>: ";
                    echo lookup_code_descriptions($dcode)."<br>\n";
                }

                //echo $diagnosis." -- ".lookup_code_descriptions($diagnosis)."\n";
                echo "</div>";
            }

            // Supplemental data for GCAC or Contraception issues.
            if ($irow['type'] == 'ippf_gcac') {
                echo "   <table>\n";
                display_layout_rows('GCA', sqlQuery("SELECT * FROM lists_ippf_gcac WHERE id = ?", [$rowid]));
                echo "   </table>\n";
            } else if ($irow['type'] == 'contraceptive') {
                echo "   <table>\n";
                display_layout_rows('CON', sqlQuery("SELECT * FROM lists_ippf_con WHERE id = ?", [$rowid]));
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
                $formres = getFormNameByFormdirAndFormid($res[1], $form_id);
                $dateres = getEncounterDateByEncounter($form_encounter);
                $formId = getFormIdByFormdirAndFormid($res[1], $form_id);

                if ($res[1] == 'newpatient') {
                    echo "<div class='text encounter'>\n";
                    echo "<h1>" . xlt($formres["form_name"]) . "</h1>";
                } else {
                    echo "<div class='text encounter_form'>";
                    echo "<h1>" . text(xl_form_title($formres["form_name"])) . "</h1>";
                }

                // show the encounter's date
                echo "(" . text(oeFormatSDFT(strtotime($dateres["date"]))) . ") ";
                if ($res[1] == 'newpatient') {
                    // display the provider info
                    echo ' '. xlt('Provider') . ': ' . text(getProviderName(getProviderIdOfEncounter($form_encounter)));
                }

                echo "<br>\n";

                // call the report function for the form
                ?>
                <div name="search_div" id="search_div_<?php echo attr($form_id)?>_<?php echo attr($res[1])?>" class="report_search_div class_<?php echo attr($res[1]); ?>">
                <?php
                if (substr($res[1], 0, 3) == 'LBF') {
                    call_user_func("lbf_report", $pid, $form_encounter, $N, $form_id, $res[1]);
                } else {
                    call_user_func($res[1] . "_report", $pid, $form_encounter, $N, $form_id);
                }

                $esign = $esignApi->createFormESign($formId, $res[1], $form_encounter);
                if ($esign->isLogViewable("report")) {
                    $esign->renderLog();
                }
                ?>

                </div>
                <?php

                if ($res[1] == 'newpatient') {
                    // display billing info
                    $bres = sqlStatement(
                        "SELECT b.date, b.code, b.code_text " .
                        "FROM billing AS b, code_types AS ct WHERE " .
                        "b.pid = ? AND " .
                        "b.encounter = ? AND " .
                        "b.activity = 1 AND " .
                        "b.code_type = ct.ct_key AND " .
                        "ct.ct_diag = 0 " .
                        "ORDER BY b.date",
                        array($pid, $form_encounter)
                    );
                    while ($brow=sqlFetchArray($bres)) {
                        echo "<span class='bold'>&nbsp;".xlt('Procedure').": </span><span class='text'>" .
                            text($brow['code']) . " " . text($brow['code_text']) . "</span><br>\n";
                    }
                }

                print "</div>";
            } // end auth-check for encounter forms
        } // end if('issue_')... else...
    } // end if('include_')... else...
} // end $ar loop

if ($printable) {
    echo "<br /><br />" . xlt('Signature') . ": _______________________________<br />";
}
?>

</div> <!-- end of report_custom DIV -->

<?php
if ($PDF_OUTPUT) {
    $content = getContent();
    $pdf->writeHTML($content);
    if ($PDF_OUTPUT == 1) {
        $pdf->Output('report.pdf', $GLOBALS['pdf_output']); // D = Download, I = Inline
    } else {
        // This is the case of writing the PDF as a message to the CMS portal.
        $ptdata = getPatientData($pid, 'cmsportal_login');
        $contents = $pdf->Output('', true);
        echo "<html><head>\n";
        echo "</head><body class='body_top'>\n";
        $result = cms_portal_call(array(
        'action'   => 'putmessage',
        'user'     => $ptdata['cmsportal_login'],
        'title'    => xl('Your Clinical Report'),
        'message'  => xl('Please see the attached PDF.'),
        'filename' => 'report.pdf',
        'mimetype' => 'application/pdf',
        'contents' => base64_encode($contents),
        ));
        if ($result['errmsg']) {
            die(text($result['errmsg']));
        }

        echo "<p>" . xlt('Report has been sent to the patient.') . "</p>\n";
        echo "</body></html>\n";
    }
    foreach ($tmp_files_remove as $tmp_file) {
        // Remove the tmp files that were created
        unlink($tmp_file);
    }
} else {
    ?>
</body>
</html>
<?php } ?>
