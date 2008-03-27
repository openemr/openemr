<?php
include_once("globals.php");
?>

<html>
<head>
<?php html_header_show(); ?>

<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">

</head>
<body class="body_top">

<font class="title"><?php xl('Debug Information','e'); ?></font>

<br>
<?php echo xl('Pennington Firm OpenEMR  v') . $openemr_version; ?><br>

</body>
</html>
