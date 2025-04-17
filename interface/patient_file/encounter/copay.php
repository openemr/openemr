<?php

/**
 * copay.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// TODO: Code cleanup

require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// This may be more appropriate to move to the library
// later
function getInsuranceCompanies($pid)
{
    $res = sqlStatement("SELECT * FROM insurance_data WHERE pid = ? " .
    "ORDER BY type ASC, date DESC", array($pid));
    $prevtype = '';
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        if (strcmp($row['type'], $prevtype) == 0) {
            continue;
        }

        $prevtype = $row['type'];
        $all[$iter] = $row;
    }

    return $all;
}

//the number of rows to display before resetting and starting a new column:
$N = 10
?>
<html>
<head>
<script>
function cleartext(atrib)
{
document.copay_form.code.value=document.copay_form.codeH.value;
document.copay_form.codeH.value="";
}
</script>


<?php Header::setupHeader(); ?>
</head>
<body class="body_bottom">

<table class="table-borderless h-100" cellspacing='0' cellpadding='0'>
<tr>

<td class="align-top">

<dl>

<form method='post' name='copay_form' action="diagnosis.php?mode=add&type=COPAY&text=copay&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>"
 target='Diagnosis' onsubmit='return top.restoreSession()'>

<dt><span class='title'><?php echo xlt('Copay'); ?></span></dt>

<br />
<input type='hidden' name='code' />
<span class='text'><?php echo xlt('$'); ?> </span><input type='entry' name='codeH' value='' size='5' />

<input type="SUBMIT" value="<?php echo xla('Save');?>" onclick="cleartext('clear')"><br /><br />


<div<?php if ($GLOBALS['simplified_copay']) {
    echo " class='d-none'";
    } ?>>
<input type="radio" name="payment_method" value="cash" checked><?php echo xlt('cash'); ?>
<input type="radio" name="payment_method" value="credit card"><?php echo xlt('credit'); ?>
<input type="radio" name="payment_method" value="check"><?php echo xlt('check'); ?>
<input type="radio" name="payment_method" value="other"><?php echo xlt('other'); ?><br /><br />
<input type="radio" name="payment_method" value="insurance"><?php echo xlt('insurance'); ?>
<?php
if ($ret = getInsuranceCompanies($pid)) {
    if (sizeof($ret) > 0) {
        echo "<select name='insurance_company'>\n";
        foreach ($ret as $iter) {
            $plan_name = trim($iter['plan_name']);
            if ($plan_name != '') {
                echo "<option value='"
                . attr($plan_name)
                . "'>" . text($plan_name) . "\n";
            }
        }

        echo "</select>\n";
    }
}
?>
<br /><br />
<input type="radio" name="payment_method" value="write off"><?php echo xlt('write off'); ?>

</div>

</form>

</dl>

</td>
</tr>
</table>

</body>
</html>
