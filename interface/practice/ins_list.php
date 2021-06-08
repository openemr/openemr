<?php

/**
 * The purpose of this module is to show a list of insurance
 * companies that match the passed-in search strings, and to allow
 * one of them to be selected.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2005 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}


// Putting a message here will cause a popup window to display it.
$info_msg = "";

function addwhere($where, $colname, $value)
{
    if ($value) {
        $where .= " AND ";
        $where .= "$colname LIKE '%" . add_escape_custom($value) . "%'";
    }

    return $where;
}

// The following code builds the appropriate SQL query from the
// search parameters passed by our opener (ins_search.php).

$where = '';
$where = addwhere($where, 'i.name', $_GET['form_name']);
$where = addwhere($where, 'i.attn', $_GET['form_attn']);
$where = addwhere($where, 'i.cms_id', $_GET['form_cms_id']);
$where = addwhere($where, 'a.line1', $_GET['form_addr1']);
$where = addwhere($where, 'a.line2', $_GET['form_addr2']);
$where = addwhere($where, 'a.city', $_GET['form_city']);
$where = addwhere($where, 'a.state', $_GET['form_state']);
$where = addwhere($where, 'a.zip', $_GET['form_zip']);

$phone_parts = array();

// Search by area code if there is one.
if (
    preg_match(
        "/(\d\d\d)/",
        $_GET['form_phone'],
        $phone_parts
    )
) {
    $where = addwhere($where, 'p.area_code', $phone_parts[1]);
}

// If there is also an exchange, search for that too.
if (
    preg_match(
        "/\d\d\d\D*(\d\d\d)/",
        $_GET['form_phone'],
        $phone_parts
    )
) {
    $where = addwhere($where, 'p.prefix', $phone_parts[1]);
}

// If the last 4 phone number digits are given, search for that too.
if (
    preg_match(
        "/\d\d\d\D*\d\d\d\D*(\d\d\d\d)/",
        $_GET['form_phone'],
        $phone_parts
    )
) {
    $where = addwhere($where, 'p.number', $phone_parts[1]);
}

$query = "SELECT " .
    "i.id, i.name, i.attn, " .
    "a.line1, a.line2, a.city, a.state, a.zip, " .
    "p.area_code, p.prefix, p.number " .
    "FROM insurance_companies AS i, addresses AS a, phone_numbers AS p " .
    "WHERE a.foreign_id = i.id ";

if (!empty($phone_parts)) {
    $query .= "AND p.foreign_id = i.id ";
}

$query .= $where . " ORDER BY i.name, a.zip";
$res = sqlStatement($query);
?>
<html>
<head>
<title><?php echo xlt('List Insurance Companies');?></title>
<?php Header::setupHeader(); ?>

<style>
td {
    font-size: 0.8125rem;
}
</style>

<script>

 // This is invoked when an insurance company name is clicked.
 function setins(ins_id, ins_name) {
   opener.set_insurance(ins_id, ins_name);
   dlgclose();
   return false;
 }

</script>

</head>

<body class="body_top">
<form method='post' name='theform'>
<center>

<table class="table table-sm border-0 w-100">
 <tr>
  <td class='font-weight-bold'><?php echo xlt('Name');?>&nbsp;</td>
  <td class='font-weight-bold'><?php echo xlt('Attn');?>&nbsp;</td>
  <td class='font-weight-bold'><?php echo xlt('Address');?>&nbsp;</td>
  <td class='font-weight-bold'>&nbsp;&nbsp;</td>
  <td class='font-weight-bold'><?php echo xlt('City');?>&nbsp;</td>
  <td class='font-weight-bold'><?php echo xlt('State');?>&nbsp;</td>
  <td class='font-weight-bold'><?php echo xlt('Zip');?>&nbsp;</td>
  <td class='font-weight-bold'><?php echo xlt('Phone');?></td>
 </tr>

<?php
while ($row = sqlFetchArray($res)) {
    $anchor = "<a href=\"\" onclick=\"return setins(" .
    attr_js($row['id']) . "," . attr_js($row['name']) . ")\">";
    $phone = '&nbsp';
    if ($row['number']) {
        $phone = text($row['area_code']) . '-' . text($row['prefix']) . '-' . text($row['number']);
    }

    echo " <tr>\n";
    echo "  <td valign='top'>$anchor" . text($row['name']) . "</a>&nbsp;</td>\n";
    echo "  <td valign='top'>" . text($row['attn']) . "&nbsp;</td>\n";
    echo "  <td valign='top'>" . text($row['line1']) . "&nbsp;</td>\n";
    echo "  <td valign='top'>" . text($row['line2']) . "&nbsp;</td>\n";
    echo "  <td valign='top'>" . text($row['city']) . "&nbsp;</td>\n";
    echo "  <td valign='top'>" . text($row['state']) . "&nbsp;</td>\n";
    echo "  <td valign='top'>" . text($row['zip']) . "&nbsp;</td>\n";
    echo "  <td valign='top'>" . $phone . "</td>\n";
    echo " </tr>\n";
}
?>
</table>

</center>
</form>
</body>
</html>
