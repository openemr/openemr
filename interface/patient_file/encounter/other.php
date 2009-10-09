<?php
include_once("../../globals.php");
include_once("$srcdir/sql.inc");

//the number of rows to display before resetting and starting a new column:
$N=10
?>

<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_bottom">

<table border=0 cellspacing=0 cellpadding=0 height=100%>
<tr>

<td valign=top>

<dl>

<form method='post' name='other_form' action="diagnosis.php?mode=add&type=OTHER"
 target='Diagnosis' onsubmit='return top.restoreSession()'>
<script type="text/javascript">
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
         alert("<?php xl('Units must be a number','e'); ?>");
         return false;
         }
return true;  }
</script>
<dt><span class=title><?php xl('Other','e'); ?></span></dt>

<br>
<table>
<tr>
<td class="text"><?php xl('code','e'); ?></td>
<td class="text"><?php xl('description','e'); ?></td>
<td class="text">&nbsp;&nbsp;&nbsp;<?php xl('fee','e'); ?></td>
<td class="text"><?php xl('units','e'); ?></td>
<td></td>
</tr>
<tr>
<input type=hidden name=code>
<input type=hidden name=text>
<input type=hidden name=fee>
<input type=hidden name=units>
  <td class="text"><input type=entry name=codeH size=4>&nbsp;&nbsp;</td>
  <td class="text"><input type=entry name=textH size=13 value="">&nbsp;&nbsp;</td>
  <td class="text"><?php xl('$','e'); ?> </span><input type=entry name=feeH size=5></td>
  <td> <input type=text name="noofunits" onkeypress="return isNumberKey(event)" size=3 value=1></td>
  <td>&nbsp;<a class='text' onclick="clearform('clear')" href="javascript:top.restoreSession();document.other_form.submit();">
   <?php xl('Save','e'); ?> </a>
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
