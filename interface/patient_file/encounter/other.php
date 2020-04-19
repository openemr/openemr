<?php

/**
 * other.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

//the number of rows to display before resetting and starting a new column:
$N = 10
?>

<html>
<head>
<?php Header::setupHeader(); ?>
</head>
<body class="body_bottom">

<table class="table-borderless h-100" cellspacing='0' cellpadding='0'>
<tr>

<td class="align-top">

<dl>

<form method='post' name='other_form' action="diagnosis.php?mode=add&type=OTHER&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>"
 target='Diagnosis' onsubmit='return top.restoreSession()'>
<script>
function clearform(atrib){
document.other_form.text.value=document.other_form.textH.value;
document.other_form.code.value=document.other_form.codeH.value;
document.other_form.fee.value=document.other_form.feeH.value*document.other_form.noofunits.value;
document.other_form.units.value=document.other_form.noofunits.value;
document.other_form.textH.value='';
document.other_form.codeH.value='';
document.other_form.feeH.value='';
document.other_form.noofunits.value='1';
}
function isNumberKey(evt)
      {var charCode = (evt.which) ? evt.which : event.keyCode
         if (charCode > 31 && (charCode < 48 || charCode > 57))
         {
         alert(<?php echo xlj('Units must be a number'); ?>);
         return false;
         }
return true;  }
</script>
<dt><span class='title'><?php echo xlt('Other'); ?></span></dt>

<br />
<table>
<tr>
<td class="text"><?php echo xlt('code'); ?></td>
<td class="text"><?php echo xlt('description'); ?></td>
<td class="text">&nbsp;&nbsp;&nbsp;<?php echo xlt('fee'); ?></td>
<td class="text"><?php echo xlt('units'); ?></td>
<td></td>
</tr>
<tr>
<input type='hidden' name='code' />
<input type='hidden' name='text' />
<input type='hidden' name='fee' />
<input type='hidden' name='units' />
  <td class="text"><input type='entry' name='codeH' size='4' />&nbsp;&nbsp;</td>
  <td class="text"><input type='entry' name='textH' size='13' value="" />&nbsp;&nbsp;</td>
  <td class="text"><?php echo xlt('$'); ?> </span><input type='entry' name='feeH' size='5' /></td>
  <td> <input type='text' name="noofunits" onkeypress="return isNumberKey(event)" size='3' value='1' /></td>
  <td>&nbsp;<a class='text' onclick="clearform('clear')" href="javascript:top.restoreSession();document.other_form.submit();">
    <?php echo xlt('Save'); ?> </a>
  </td>
</tr>
</table>
</form>

</dl>

</td>
</tr>
</table>

</body>
</html>
