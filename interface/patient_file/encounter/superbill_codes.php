<?php

/**
 * superbill_codes.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("../../../custom/code_types.inc.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

//the number of rows to display before resetting and starting a new column:
$N = 10;

$mode     = $_GET['mode'];
$type     = $_GET['type'];
$modifier = $_GET['modifier'];
$units    = $_GET['units'];
$fee      = $_GET['fee'];
$code     = $_GET['code'];
$text     = $_GET['text'];

if (isset($mode)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if ($mode == "add") {
        if (strtolower($type) == "copay") {
            BillingUtilities::addBilling($encounter, $type, sprintf("%01.2f", $code), $text, $pid, $userauthorized, $_SESSION['authUserID'], $modifier, $units, sprintf("%01.2f", 0 - $code));
        } elseif (strtolower($type) == "other") {
            BillingUtilities::addBilling($encounter, $type, $code, $text, $pid, $userauthorized, $_SESSION['authUserID'], $modifier, $units, sprintf("%01.2f", $fee));
        } else {
            BillingUtilities::addBilling($encounter, $type, $code, $text, $pid, $userauthorized, $_SESSION['authUserID'], $modifier, $units, $fee);
        }
    }
}
?>
<html>
<head>
<?php Header::setupHeader(); ?>
</head>
<body class="body_bottom">

<table class="table-borderless" cellspacing="0" cellpadding="0">
<tr>
<td class="align-top">

<dl>

<dt>

<a href="superbill_custom_full.php" onclick="top.restoreSession()">
<span class='title'><?php echo xlt('Superbill'); ?></span>
<span class='more'><?php echo text($tmore); ?></span></a>

<a href="encounter_bottom.php" onclick="top.restoreSession()"><span class='more'><?php echo text($tback); ?></span></a>

</dt>
</td></tr>
</table>

<table class='w-100 table-borderless' cellpadding='0' cellspacing='1'>
<?php
$res = sqlStatement("select * from codes where superbill = 1 order by code_type, code, code_text");

$codes = array();
echo " <tr>\n";
foreach ($code_types as $key => $value) {
    $codes[$key] = array();
    echo "  <th class='text-left'>" . text($key) . " Codes</th>\n";
}

echo " </tr>\n";

for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
    foreach ($code_types as $key => $value) {
        if ($value['id'] == $row['code_type']) {
            $codes[$key][] = $row;
            break;
        }
    }
}

$index = 0;

$numlines = 0;
foreach ($codes as $value) {
    $numlines = max($numlines, count($value));
}

while ($index < $numlines) {
    echo " <tr>\n";
    foreach ($codes as $key => $value) {
        echo "  <td class='align-top'>\n";
        if (!empty($value[$index])) {
            $code = $value[$index];
            echo "   <dd><a class='text' ";
            echo "href='superbill_codes.php?back=1&mode=add" .
                "&type="     . attr_url($key) .
                "&modifier=" . attr_url($code["modifier"]) .
                "&units="    . attr_url($code["units"]) .
                "&fee="      . attr_url($code["fee"]) .
                "&code="     . attr_url($code["code"]) .
                "&text="     . attr_url($code["code_text"]) .
                "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) .
            "' onclick='top.restoreSession()'>";
            echo "<b>" . text($code['code']) . "</b>" . "&nbsp;" . text($code['modifier']) . "&nbsp;" . text($code['code_text']);
            echo "</a></dd>\n";
        }

        echo "  </td>\n";
    }

    echo " </tr>\n";
    ++$index;
}

?>

</table>

</dl>

</body>
</html>
