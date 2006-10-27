<?
include_once("../../globals.php");
include_once("$srcdir/transactions.inc");

$body_onload_code="";
if (isset($mode)) {
	if ($mode == "add") {
		newTransaction($pid, $body, $title, $userauthorized);
		$body_onload_code = "javascript:parent.Transactions.location.href='transactions.php';";
	}
}
?>
<html>
<head>

<link rel='stylesheet' href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> onload="<?echo $body_onload_code;?>"
 topmargin='0' rightmargin='0' leftmargin='2' bottommargin='0'
 marginwidth='2' marginheight='0'>

<form name='new_transaction'>
<input type='hidden' name='mode' value='add'>

<table border='0' cellpadding='0' cellspacing='0'>
 <tr>

  <td valign='top'>
   <span class='bold'><? xl('Transaction Type','e'); ?>:</span><br>
   <select name='title'>

    <option value="Referral"><? xl('Referral','e'); ?></option>
    <option value="Patient Request"><? xl('Patient Request','e'); ?></option>
    <option value="Physician Request"><? xl('Physician Request','e'); ?></option>
    <option value="Legal"><? xl('Legal','e'); ?></option>
    <option value="Billing"><? xl('Billing','e'); ?></option>

   </select>

   <br>

   <span class='bold'><? xl('Details','e'); ?>:</span><br>
   <textarea name='body' rows='6' cols='40' wrap='virtual'></textarea>

   <br>

   <a href="javascript:document.new_transaction.submit();" class='link_submit'>[<? xl('Add New Transaction','e'); ?>]</a>

  </td>
 </tr>
</table>

</form>

</body>
</html>