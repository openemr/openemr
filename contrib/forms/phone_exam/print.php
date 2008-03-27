<?php

include_once("../../globals.php");

include_once("../../../library/api.inc");



formHeader("Phone Exam");

?>




<html>

<head>
<?php html_header_show();?>

<title>New Patient Encounter</title>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">



</head>

<body class="body_top">

<br><br>

<?php

$row = formFetch('form_phone_exam', $_GET['id']);

echo $row['notes'];

?>

<br><br>

<a href="<?php echo $GLOBALS['form_exit_url']; ?>" onclick="top.restoreSession()">Done</a>

</body>

<?php

formFooter();

?>

