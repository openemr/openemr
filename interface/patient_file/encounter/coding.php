<?
include_once("../../globals.php");
include_once("../../../custom/code_types.inc.php");
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<dl>
<dt><span href="coding.php" class="title">Coding</span></dt>
<dd><a class="text" href="superbill_codes.php" target="Main">Superbill</a></dd>

<? foreach ($code_types as $key => $value) { ?>
<dd><a class="text" href="search_code.php?type=<? echo $key ?>" target="Codes"><? echo $key ?> Search</a></dd>
<? } ?>

<dd><a class="text" href="copay.php" target="Codes">Copay</a></dd>
<dd><a class="text" href="other.php" target="Codes">Other</a></dd><br />
<dt><span href="coding.php" class="title">Prescriptions</span></dt>
<dd><a class="text" href="<?=$GLOBALS['webroot']?>/controller.php?prescription&list&id=<?=$pid?>" target="Codes">List Prescriptions</a></dd>
<dd><a class="text" href="<?=$GLOBALS['webroot']?>/controller.php?prescription&edit&id=&pid=<?=$pid?>" target="Codes">Add Prescription</a></dd>
</dl>

</body>
</html>
