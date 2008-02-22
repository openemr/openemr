<?php 
include_once("../globals.php");
?>

<html>
<head>
<? html_header_show();?>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

</head>
<body <?php echo $title_bg_line;?> topmargin=0 rightmargin=4 leftmargin=2 bottommargin=0 marginheight=0>

<?php 
$res = sqlQuery("select * from users where username='".$_SESSION{"authUser"}."'");
?>

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td valign="middle" nowrap>
<span class="title_bar_top"><?php  xl('Logged in','e');?>: <?php echo $res{"fname"}." ".$res{"lname"};?></span><span style="font-size:9pt;"> (<?php echo $_SESSION['authGroup']; ?>)</span>
</td>

<td align="right" valign="middle" nowrap>
<span class="title_bar_top"><?php echo dateformat();?></span>
</td>

</tr>
</table>

</body>
</html>
