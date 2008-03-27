<?php 
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // The purpose of this module is to show a list of insurance
 // companies that match the passed-in search strings, and to allow
 // one of them to be selected.

 include_once("../globals.php");

 // Putting a message here will cause a popup window to display it.
 $info_msg = "";

 function addwhere($where, $colname, $value) {
  if ($value) {
   $where .= " AND ";
   $where .= "$colname LIKE '%$value%'";
  }
  return $where;
 }

 // The following code builds the appropriate SQL query from the
 // search parameters passed by our opener (ins_search.php).

 $where = '';
 $where = addwhere($where, 'i.name'  , $_REQUEST['form_name']  );
 $where = addwhere($where, 'i.attn'  , $_REQUEST['form_attn']  );
 $where = addwhere($where, 'i.cms_id', $_REQUEST['form_cms_id']);
 $where = addwhere($where, 'a.line1' , $_REQUEST['form_addr1'] );
 $where = addwhere($where, 'a.line2' , $_REQUEST['form_addr2'] );
 $where = addwhere($where, 'a.city'  , $_REQUEST['form_city']  );
 $where = addwhere($where, 'a.state' , $_REQUEST['form_state'] );
 $where = addwhere($where, 'a.zip'   , $_REQUEST['form_zip']   );

 $phone_parts = array();

 // Search by area code if there is one.
 if (preg_match("/(\d\d\d)/",
  $_REQUEST['form_phone'], $phone_parts))
  $where = addwhere($where, 'p.area_code', $phone_parts[1]);

 // If there is also an exchange, search for that too.
 if (preg_match("/\d\d\d\D*(\d\d\d)/",
  $_REQUEST['form_phone'], $phone_parts))
  $where = addwhere($where, 'p.prefix', $phone_parts[1]);

 // If the last 4 phone number digits are given, search for that too.
 if (preg_match("/\d\d\d\D*\d\d\d\D*(\d\d\d\d)/",
  $_REQUEST['form_phone'], $phone_parts))
  $where = addwhere($where, 'p.number', $phone_parts[1]);

 $query = "SELECT " .
  "i.id, i.name, i.attn, " .
  "a.line1, a.line2, a.city, a.state, a.zip, " .
  "p.area_code, p.prefix, p.number " .
  "FROM insurance_companies AS i, addresses AS a, phone_numbers AS p " .
  "WHERE a.foreign_id = i.id AND p.foreign_id = i.id$where " .
  "ORDER BY i.name, a.zip";
 $res = sqlStatement($query);
?>
<html>
<head>
<title><?php xl('List Insurance Companies','e');?></title>
<link rel="stylesheet" href='<?php  echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script language="JavaScript">

 // This is invoked when an insurance company name is clicked.
 function setins(ins_id, ins_name) {
  if (opener.closed || ! opener.set_insurance)
   alert('The parent window was closed; I cannot apply your selection.');
  else
   opener.set_insurance(ins_id, ins_name);
  window.close();
  return false;
 }

</script>

</head>

<body class="body_top">
<form method='post' name='theform'>
<center>

<table border='0' width='100%'>
 <tr>
  <td><b><?php xl('Name','e');?></b>&nbsp;</td>
  <td><b><?php xl('Attn','e');?></b>&nbsp;</td>
  <td><b><?php xl('Address','e');?></b>&nbsp;</td>
  <td><b>&nbsp;</b>&nbsp;</td>
  <td><b><?php xl('City','e');?></b>&nbsp;</td>
  <td><b><?php xl('State','e');?></b>&nbsp;</td>
  <td><b><?php xl('Zip','e');?></b>&nbsp;</td>
  <td><b><?php xl('Phone','e');?></b></td>
 </tr>

<?php 
  while ($row = sqlFetchArray($res)) {
   $anchor = "<a href=\"\" onclick=\"return setins(" .
    $row['id'] . ",'" . addslashes($row['name']) . "')\">";
   $phone = '&nbsp';
   if ($row['number']) {
    $phone = $row['area_code'] . '-' . $row['prefix'] . '-' . $row['number'];
   }
   echo " <tr>\n";
   echo "  <td valign='top'>$anchor" . $row['name'] . "</a>&nbsp;</td>\n";
   echo "  <td valign='top'>" . $row['attn'] . "&nbsp;</td>\n";
   echo "  <td valign='top'>" . $row['line1'] . "&nbsp;</td>\n";
   echo "  <td valign='top'>" . $row['line2'] . "&nbsp;</td>\n";
   echo "  <td valign='top'>" . $row['city'] . "&nbsp;</td>\n";
   echo "  <td valign='top'>" . $row['state'] . "&nbsp;</td>\n";
   echo "  <td valign='top'>" . $row['zip'] . "&nbsp;</td>\n";
   echo "  <td valign='top'>$phone</td>\n";
   echo " </tr>\n";
  }
?>
</table>

</center>
</form>
</body>
</html>
