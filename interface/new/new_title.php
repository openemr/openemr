<?
include_once("../globals.php");
?>

<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $title_bg_line;?> topmargin=0 rightmargin=4 leftmargin=2 bottommargin=0 marginheight=0>

<?
$res = sqlQuery("select * from users where username='".$_SESSION{"authUser"}."'");
?>

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td valign="middle" nowrap>
<span class="title_bar_top">Logged in: <?echo $res{"fname"}." ".$res{"lname"};?></span><span style="font-size:9pt;"> (<?=$_SESSION['authGroup']?>)</span>
</td>

<td align="right" valign="middle" nowrap>
<span class="title_bar_top"><? echo date( "D F jS Y" );?></span>
</td>

</tr>
</table>

</body>
</html>
