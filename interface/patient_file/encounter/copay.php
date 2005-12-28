<?
include_once("../../globals.php");
include_once("$srcdir/sql.inc");

//the number of rows to display before resetting and starting a new column:
$N=10

?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>


<table border=0 cellspacing=0 cellpadding=0 height=100%>
<tr>

<!--
<td background="<?echo $linepic;?>" width=7 height=100%>
&nbsp;
</td>
-->

<td valign=top>

<dl>

<form method=post name=copay_form action="diagnosis.php?mode=add&type=COPAY&text=copay" target=Diagnosis>

<dt><span class=title>Copay</span></dt>

<br>

<span class=text>$ </span><input type=entry name=code size=5>


<a class=text href="javascript:document.copay_form.submit();">Save</a>



</form>



</dl>


</td>
</tr>
</table>




</body>
</html>