<?php
include_once("../../globals.php");
include_once("$srcdir/calendar.inc");
include_once("$srcdir/patient.inc");
?>
<html>
<head>
<? html_header_show();?>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

</head>
<body bgcolor="#FFFFFF" topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0 >
<form>

<input type=submit value="<?php xl('Find','e'); ?>" />

</form>

</body>
</html>
