<?
include_once("../../globals.php");

?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<dl>
<dt><span href="coding.php" class="title">Coding/Billing</span></dt>
<dd><a class="text" href="superbill_codes.php" target="Main">Superbill</a></dd>
<!--<dd><a class="text" href="icd9cm_codes_custom.php" target="Codes">ICD-9-CM Custom</a></dd>-->
<dd><a class="text" href="icd9cm_codes.php" target="Codes">ICD-9-CM Search</a></dd>
<!--<dd><a class="text" href="cpt_codes_custom.php" target="Codes">CPT Custom</a></dd>-->
<dd><a class="text" href="cptcm_codes.php" target="Codes">CPT Search</a></dd>
<dd><a class="text" href="hcpcs_codes.php" target="Codes">HCPCS Search</a></dd>
<dd><a class="text" href="copay.php" target="Codes">Copay</a></dd>
<dd><a class="text" href="other.php" target="Codes">Other</a></dd><br />
<dt><span href="coding.php" class="title">Prescriptions</span></dt>
<dd><a class="text" href="<?=$GLOBALS['webroot']?>/controller.php?prescription&list&id=<?=$pid?>" target="Codes">List Prescriptions</a></dd>
<dd><a class="text" href="<?=$GLOBALS['webroot']?>/controller.php?prescription&edit&id=&pid=<?=$pid?>" target="Codes">Add Prescription</a></dd>
</dl>



</body>
</html>
