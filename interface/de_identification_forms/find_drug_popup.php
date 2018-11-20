<?php
/********************************************************************************\
 * Copyright (C) ViCarePlus, Visolve (vicareplus_engg@visolve.com)              *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
 \********************************************************************************/

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
<title><?php echo xlt('Drug Finder'); ?></title>
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
    alert('<?php echo xls("Select Drug");?>');
  if (opener.closed || ! opener.set_related)
   alert("<?php echo xls('The destination form was closed')?>");
  else
   opener.set_related(str,"drugs");

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
  alert('<?php echo xls("Search string should have at least three characters");?>');
  return false;
 }
 top.restoreSession();
 return true;
}

</script>
</head>
<body class="body_top">
<form method='post' name='theform'  action='find_drug_popup.php' onsubmit="return check_search_str();">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(collectCsrfToken()); ?>" />
<center>
<input type="hidden" name="search_status" id="search_status" value=1;>
<table border='0' cellpadding='5' cellspacing='0'>
 <tr>
  <td height="1">
  </td>
 </tr>
 <tr>
  <td>
   <b>
    <?php echo xlt('Search for'); ?>
   <input type='text' name='search_term' id='search_term' size='12' value='<?php echo attr($_REQUEST['search_term']); ?>'
    title='<?php echo xla('Any part of the drug id or drug name'); ?>' />
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
<form method='post' name='select_drug'>
<table>
<tr>
<td colspan="4">
<?php if ($_REQUEST['bn_search']) {
    if (!verifyCsrfToken($_POST["csrf_token_form"])) {
        csrfNotVerified();
    }

    $search_term = $_REQUEST['search_term'];
    {
    $query = "SELECT count(*) as count FROM drugs " .
      "WHERE (drug_id LIKE ? OR " .
      "name LIKE ?) ";
    $res = sqlStatement($query, array('%'.$search_term.'%', '%'.$search_term.'%'));
    if ($row = sqlFetchArray($res)) {
        $no_of_items = $row['count'];
        if ($no_of_items < 1) {
            ?>
        <script language='JavaScript'>
            alert("<?php echo xls('Search string does not match with list in database');
            echo '\n';
            echo xls('Please enter new search string');?>");
        document.theform.search_term.value=" ";
        document.theform.search_term.focus();
        </script>
        <?php
        }

        $query = "SELECT drug_id, name FROM drugs " .
        "WHERE (drug_id LIKE ? OR " .
        "name LIKE ?) " .
        "ORDER BY drug_id";
        $res = sqlStatement($query, array('%'.$search_term.'%', '%'.$search_term.'%'));
        $row_count = 0;
        while ($row = sqlFetchArray($res)) {
              $row_count = $row_count + 1;
              $itercode = $row['drug_id'];
              $itertext = ucfirst(strtolower(trim($row['name'])));
                ?>
               <input type="checkbox" id="chkbox" name ="chkbox" value= "<?php echo attr($itercode) . "-" . attr($itertext); ?>" > <?php echo text($itercode) . "    " . text($itertext) . "</br>";
        }
    }

    }
?>
</td>
</tr>
 </table>
<center>
 <input type='button' name='select_all' value='<?php echo xla('Select All'); ?>' onclick="chkbox_select_all(document.select_drug.chkbox);"/>

 <input type='button' name='unselect_all' value='<?php echo xla('Unselect All'); ?>' onclick="chkbox_select_none(document.select_drug.chkbox);"/>

 <input type='button' name='submit' value='<?php echo xla('Submit'); ?>' onclick="window_submit(document.select_drug.chkbox);"/>

 <input type='button' name='cancel' value='<?php echo xla('Cancel'); ?>' onclick="window_close();"/>
</center>
<?php } ?>
</form>
</body>
</html>
