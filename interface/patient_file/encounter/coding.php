<?php
include_once("../../globals.php");
include_once("../../../custom/code_types.inc.php");
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?php echo $bottom_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<dl>
<dt><span href="coding.php" class="title"><?php xl('Coding','e'); ?></span></dt>
<dd><a class="text" href="superbill_codes.php"
 target="<?php echo $GLOBALS['concurrent_layout'] ? '_parent' : 'Main'; ?>">
<?php xl('Superbill','e'); ?></a></dd>

<?php foreach ($code_types as $key => $value) { ?>
<dd><a class="text" href="search_code.php?type=<? echo $key ?>"
 target="Codes"><?php echo $key; ?> <?php xl('Search','e'); ?></a></dd>
<?php } ?>

<dd><a class="text" href="copay.php" target="Codes"><?php xl('Copay','e'); ?></a></dd>
<dd><a class="text" href="other.php" target="Codes"><?php xl('Other','e'); ?></a></dd><br />
<dt><span href="coding.php" class="title"><?php xl('Prescriptions','e'); ?></span></dt>
<dd><a class="text" href="<?=$GLOBALS['webroot']?>/controller.php?prescription&list&id=<?=$pid?>" target="Codes"><?php xl('List Prescriptions','e'); ?></a></dd>
<dd><a class="text" href="<?=$GLOBALS['webroot']?>/controller.php?prescription&edit&id=&pid=<?=$pid?>" target="Codes"><?php xl('Add Prescription','e'); ?></a></dd>
</dl>

</body>
</html>
