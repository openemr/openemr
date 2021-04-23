<?php

/**
 * find_immunization_popup.php
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
<title><?php echo xlt('Immunization'); ?></title>
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
    alert(<?php echo xlj("Select Immunizations"); ?>);
  if (opener.closed || ! opener.set_related)
   alert(<?php echo xlj('The destination form was closed'); ?>);
  else
   opener.set_related(str,"immunizations");

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
  alert(<?php echo xlj("Search string should have at least three characters"); ?>);
  return false;
 }
 top.restoreSession();
 return true;
}
</script>
</head>
<body class="body_top">
<form method='post' name='theform'  action='find_immunization_popup.php' onsubmit="return check_search_str();">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>
<table class="border-0" cellpadding='5' cellspacing='0'>
 <tr>
  <td height="1">
  </td>
 </tr>
 <tr>
  <td>
   <b>
    <?php echo xlt('Search for'); ?>
   <input type='text' name='search_term' id='search_term' size='12' value='<?php echo attr($_REQUEST['search_term']); ?>'
    title='<?php echo xla('Any part of the immunization id or immunization name'); ?>' />
   &nbsp;
   <input type='submit' class="btn btn-primary btn-sm" name='bn_search' value='<?php echo xla('Search'); ?>' />
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
<form method='post' name='select_immunization'>
<?php if ($_REQUEST['bn_search']) { ?>
    <?php
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    ?>

<table class="border-0">
 <tr>
  <td colspan="4">
    <?php
    $search_term = $_REQUEST['search_term'];
    {
    $query = "SELECT count(*) as count FROM list_options " .
      "WHERE (list_id = 'immunizations' and title LIKE ? AND activity = 1) " ;
    $res = sqlStatement($query, array('%' . $search_term . '%'));
    if ($row = sqlFetchArray($res)) {
        $no_of_items = $row['count'];
        if ($no_of_items < 1) {
            ?>
     <script>
        alert(<?php echo xlj('Search string does not match with list in database'); ?> + '\n' + <?php echo xlj('Please enter new search string'); ?>);
     document.theform.search_term.value=" ";
     document.theform.search_term.focus();
     </script>
            <?php
        }

        $query = "SELECT option_id,title FROM list_options " .
        "WHERE (list_id = 'immunizations' and title LIKE ? AND activity = 1) " .
        "ORDER BY title";
        $res = sqlStatement($query, array('%' . $search_term . '%'));
        $row_count = 0;
        while ($row = sqlFetchArray($res)) {
            $row_count = $row_count + 1;
            $itercode = $row['option_id'];
            $itertext = ucfirst(strtolower(trim($row['title'])));
            ?>
       <input type="checkbox" id="chkbox" value= "<?php echo attr($itercode) . "-" . attr($itertext); ?>" > <?php echo text($itercode) . "    " . text($itertext) . "<br />";
        }
    }

    }
    ?>
</td>
</tr>
 </table>
<center>
<div class="btn-group">
 <input type='button' class="btn btn-primary" name='select_all' value='<?php echo xla('Select All'); ?>' onclick="chkbox_select_all(document.select_immunization.chkbox);"/>

 <input type='button' class="btn btn-primary" name='select_none' value='<?php echo xla('Unselect All'); ?>' onclick="chkbox_select_none(document.select_immunization.chkbox);"/>

 <input type='button' class="btn btn-primary" name='submit' value='<?php echo xla('Submit'); ?>' onclick="window_submit(document.select_immunization.chkbox);"/>

 <input type='button' class="btn btn-primary" name='cancel' value='<?php echo xla('Cancel'); ?>' onclick="window_close();"/>
</div>
 </center>
<?php } ?>
</form>
</body>
</html>
