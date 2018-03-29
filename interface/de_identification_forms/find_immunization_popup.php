<?php
/**
 * find_immunization_popup.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Visolve <vicareplus_engg@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) ViCarePlus, Visolve <vicareplus_engg@visolve.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("../../custom/code_types.inc.php");

$info_msg = "";
$codetype = $_REQUEST['codetype'];
$form_code_type = $_POST['form_code_type'];
?>
<html>
<head>
<?php html_header_show(); ?>
<title><?php xl('Immunization', 'e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<style>
td { font-size:10pt; }
</style>
<script language="JavaScript">
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
    alert('<?php echo xl("Select Immunizations");?>');
  if (opener.closed || ! opener.set_related)
   alert("<?php echo xl('The destination form was closed');?>");
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
  alert('<?php echo xl("Search string should have at least three characters");?>');
  return false;
 }
 top.restoreSession();
 return true;
}
</script>
</head>
<body class="body_top">
<form method='post' name='theform'  action='find_immunization_popup.php' onsubmit="return check_search_str();">
<center>
<table border='0' cellpadding='5' cellspacing='0'>
 <tr>
  <td height="1">
  </td>
 </tr>
 <tr>
  <td>
   <b>
    <?php xl('Search for', 'e'); ?>
   <input type='text' name='search_term' id='search_term' size='12' value='<?php echo attr($_REQUEST['search_term']); ?>'
    title='<?php xl('Any part of the immunization id or immunization name', 'e'); ?>' />
   &nbsp;
   <input type='submit' name='bn_search' value='<?php xl('Search', 'e'); ?>' />
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
<table border='0'>
 <tr>
  <td colspan="4">
<?php
  $search_term = $_REQUEST['search_term'];
  {
    $query = "SELECT count(*) as count FROM list_options " .
      "WHERE (list_id = 'immunizations' and title LIKE '%$search_term%' AND activity = 1) " ;
    $res = sqlStatement($query);
if ($row = sqlFetchArray($res)) {
    $no_of_items = addslashes($row['count']);
    if ($no_of_items < 1) {
        ?>
     <script language='JavaScript'>
        alert("<?php echo xl('Search string does not match with list in database');
        echo '\n';
        echo xl('Please enter new search string');?>");
     document.theform.search_term.value=" ";
     document.theform.search_term.focus();
     </script>
        <?php
    }

    $query = "SELECT option_id,title FROM list_options " .
    "WHERE (list_id = 'immunizations' and title LIKE '%$search_term%' AND activity = 1) " .
    "ORDER BY title";
    $res = sqlStatement($query);
    $row_count = 0;
    while ($row = sqlFetchArray($res)) {
        $row_count = $row_count + 1;
        $itercode = addslashes($row['option_id']);
        $itertext = addslashes(ucfirst(strtolower(trim($row['title']))));
        ?>
       <input type="checkbox" id="chkbox" value= "<?php echo $itercode."-".$itertext; ?>" > <?php echo $itercode."    ".$itertext."</br>";
    }
}

  }
?>
</td>
</tr>
 </table>
<center>
 <input type='button' name='select_all' value='<?php xl('Select All', 'e'); ?>' onclick="chkbox_select_all(document.select_immunization.chkbox);"/>

 <input type='button' name='select_none' value='<?php xl('Unselect All', 'e'); ?>' onclick="chkbox_select_none(document.select_immunization.chkbox);"/>

 <input type='button' name='submit' value='<?php xl('Submit', 'e'); ?>' onclick="window_submit(document.select_immunization.chkbox);"/>

 <input type='button' name='cancel' value='<?php xl('Cancel', 'e'); ?>' onclick="window_close();"/>

 </center>
<?php } ?>
</form>
</body>
</html>
