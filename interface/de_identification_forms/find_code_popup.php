<?php

/**
 * find_code_popup.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Visolve <vicareplus_engg@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 ViCarePlus, Visolve <vicareplus_engg@visolve.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("../../custom/code_types.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$info_msg = "";
$codetype = $_REQUEST['codetype'];
$form_code_type = $_POST['form_code_type'];
?>
<html>
<head>
<title><?php echo xlt('Code Finder'); ?></title>

<?php Header::setupHeader(); ?>

<style>
td {
    font-size: 0.8125rem;
}
</style>

<script>
//pass value selected to the parent window
 function window_submit(chk)
 {
  var str;
  var len=chk.length;
  if (len==undefined && chk.checked==1)
  {
    if(!str)
      str = chk.value;
    else
    str = "#"+chk.value;
  }
  else
  {
  for (pr = 0; pr < chk.length; pr++)
   {
    if(chk[pr].checked == 1)
    {
     if(!str)
      str = chk[pr].value;
     else
      str = str+"#"+chk[pr].value;
    }
   }
  }
  if(!str)
    alert(<?php echo xlj("Select Diagnosis"); ?>);
  if (opener.closed || ! opener.set_related)
   alert(<?php echo xlj('The destination form was closed'); ?>);
  else
   opener.set_related(str,"diagnosis");

  window.close();

 }

function window_close(chk)
{
 window.close();
}

function chkbox_select_none(chk)
{
 var len=chk.length;
 if (len==undefined) {chk.checked=false;}
 else
 {
  for (pr = 0; pr < chk.length; pr++)
  {
   chk[pr].checked=false;
  }
 }
}

function chkbox_select_all(chk)
{
 var len=chk.length;
 if (len==undefined) {chk.checked=true;}
 else
 {
  for (pr = 0; pr < chk.length; pr++)
  {
   chk[pr].checked=true;
  }
 }
}

function check_search_str()
{
 var search_str = document.getElementById('search_term').value;
 if(search_str.length < 3)
 {
  alert(<?php echo xlj("Search string should have at least three characters");?>);
  return false;
 }
 top.restoreSession();
 return true;
}

</script>
</head>
<body class="body_top">
<form method='post' name='theform' action='find_code_popup.php' onsubmit="return check_search_str();">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<center>
 <input type="hidden" name="search_status" id="search_status" value=1;>
<table class="border-0" cellpadding='5' cellspacing='0'>
 <tr>
  <td height="1">
  </td>
 </tr>
 <tr>
  <td>
   <b>
<?php
if ($codetype) {
    echo "<input type='text' name='form_code_type' value='" . attr($codetype) . "' size='5' readonly>\n";
} else {
    echo "   <select name='form_code_type'";
    echo ">\n";
    foreach ($code_types as $key => $value) {
        echo "    <option value='" . attr($key) . "'";
        if ($codetype == $key || $form_code_type == $key) {
            echo " selected";
        }

        echo ">" . text($key) . "</option>\n";
    }

    echo "    <option value='PROD'";
    if ($codetype == 'PROD' || $form_code_type == 'PROD') {
        echo " selected";
    }

    echo ">Product</option>\n";
    echo "   </select>&nbsp;&nbsp;\n";
}
?>
    <?php echo xlt('Search for'); ?>
   <input type='text' name='search_term' id='search_term' size='12' value='<?php echo attr($_REQUEST['search_term']); ?>'
    title='<?php xla('Any part of the desired code or its description'); ?>' />
   &nbsp;
   <input type='submit' name='bn_search' id='bn_search' value='<?php echo xla('Search'); ?>' />
   </b>
  </td>
 </tr>
 <tr>
  <td height="1">
  </td>
 </tr>
</table>
</center>
</form>
<form method='post' name='select_diagonsis'>
<table class='border-0'>
 <tr>
 <td colspan="4">
<?php if ($_REQUEST['bn_search']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $search_term = $_REQUEST['search_term'];
    if ($form_code_type == 'PROD') {
        $query = "SELECT dt.drug_id, dt.selector, d.name " .
        "FROM drug_templates AS dt, drugs AS d WHERE " .
        "( d.name LIKE ? OR " .
        "dt.selector LIKE ? ) " .
        "AND d.drug_id = dt.drug_id " .
        "ORDER BY d.name, dt.selector, dt.drug_id";
        $res = sqlStatement($query, array('%' . $search_term . '%', '%' . $search_term . '%'));
        $row_count = 0;
        while ($row = sqlFetchArray($res)) {
            $row_count = $row_count + 1;
            $drug_id = $row['drug_id'];
            $selector = $row['selector'];
            $desc = $row['name'];
            ?>
             <input type="checkbox" name="diagnosis[row_count]" value="<?php echo attr($desc); ?>" > <?php echo text($drug_id) . "    " . text($selector) . "     " . text($desc) . "<br />";
        }
    } else {
        $query = "SELECT count(*) as count FROM codes " .
        "WHERE (code_text LIKE ? OR " .
        "code LIKE ?) " ;
        $res = sqlStatement($query, array('%' . $search_term . '%', '%' . $search_term . '%'));
        if ($row = sqlFetchArray($res)) {
            $no_of_items = $row['count'];
            if ($no_of_items < 1) {
                ?>
             <script>
            alert(<?php echo xlj('Search string does not match with list in database'); ?> + '\n' + <?php echo xlj('Please enter new search string');?>);
          document.theform.search_term.value=" ";
             document.theform.search_term.focus();
             </script>
                <?php
            }

            $query = "SELECT code_type, code, modifier, code_text FROM codes " .
            "WHERE (code_text LIKE ? OR " .
            "code LIKE ?) " .
            "ORDER BY code";
          // echo "\n<!-- $query -->\n"; // debugging
            $res = sqlStatement($query, array('%' . $search_term . '%', '%' . $search_term . '%'));
            $row_count = 0;
            while ($row = sqlFetchArray($res)) {
                $row_count = $row_count + 1;
                $itercode = $row['code'];
                $itertext = ucfirst(strtolower(trim($row['code_text'])));
                ?>
                 <input type="checkbox" id="chkbox" value= "<?php echo attr($form_code_type) . ":" . attr($itercode) . "-" . attr($itertext); ?>" > <?php echo text($itercode) . "    " . text($itertext) . "<br />";
            }
        }
    }
    ?>
  </td>
 </tr>
 </table>
<center>
<br />
<div class="btn-group">
     <input type='button' class="btn btn-primary" id='select_all' value='<?php echo xla('Select All'); ?>' onclick="chkbox_select_all(document.select_diagonsis.chkbox);"/>

     <input type='button' class="btn btn-primary" id='unselect_all' value='<?php echo xla('Unselect All'); ?>' onclick="chkbox_select_none(document.select_diagonsis.chkbox);"/>

     <input type='button' class="btn btn-primary" id='submit' value='<?php echo xla('Submit'); ?>' onclick="window_submit(document.select_diagonsis.chkbox);"/>

     <input type='button' class="btn btn-primary" id='cancel' value='<?php echo xla('Cancel'); ?>' onclick="window_close();"/>
</div>
</center>
<?php } ?>
</form>
</body>
</html>
