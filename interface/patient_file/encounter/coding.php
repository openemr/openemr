<?php
include_once("../../globals.php");
include_once("../../../custom/code_types.inc.php");
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_bottom">

<dl>
<dt><span href="coding.php" class="title"><?php xl('Coding','e'); ?></span></dt>
<dd><a class="text" href="superbill_codes.php"
 target="<?php echo $GLOBALS['concurrent_layout'] ? '_parent' : 'Main'; ?>"
 onclick="top.restoreSession()">
<?php xl('Superbill','e'); ?></a></dd>

<?php foreach ($code_types as $key => $value) { ?>
<dd><a class="text" href="search_code.php?type=<?php echo $key ?>"
 target="Codes" onclick="top.restoreSession()">
<?php echo $key; ?> <?php xl('Search','e'); ?></a></dd>
<?php } ?>

<?php
if ( LANGUAGE == 5 ) $pres = "prescriptiondutch";
else $pres = "prescription";
?>

<dd><a class="text" href="copay.php" target="Codes" onclick="top.restoreSession()"><?php xl('Copay','e'); ?></a></dd>
<dd><a class="text" href="other.php" target="Codes" onclick="top.restoreSession()"><?php xl('Other','e'); ?></a></dd><br />
<dt><span href="coding.php" class="title"><?php xl('Prescriptions','e'); ?></span></dt>
<dd><a class="text" href="<?php echo $GLOBALS['webroot']?>/controller.php?<?php echo $pres?>&list&id=<?php echo $pid?>"
 target="Codes" onclick="top.restoreSession()"><?php xl('List Prescriptions','e'); ?></a></dd>
<dd><a class="text" href="<?php echo $GLOBALS['webroot']?>/controller.php?<?php echo $pres?>&edit&id=&pid=<?php echo $pid?>"
 target="Codes" onclick="top.restoreSession()"><?php xl('Add Prescription','e'); ?></a></dd>
</dl>

</body>
</html>
