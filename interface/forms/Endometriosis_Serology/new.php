<!--
This form was genreated using formscript.pl

The javascript at the end allows the asessment of Endometriosis from the levels of various interleukins and cytokines.
This is based on several publications:

Serum anti-inflammatory cytokines for the evaluation of inflammatory status in endometriosis. doi:  10.4103/1735-1995.166215
Pro-inflammatory cytokines for evaluation of inflammatory status in endometriosis doi:  10.5114/ceji.2015.50840

-->
<script type="text/javascript" src="interleukins.js"></script>

<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: Endometriosis_Serology");
$returnurl = 'encounter_top.php';
?>
<html><head>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
DATE_HEADER
<a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'>[do not save]</a>
<form method=post action="<?php echo $rootdir;?>/forms/Endometriosis_Serology/save.php?mode=new" name="Endometriosis_Serology" onsubmit="return top.restoreSession()">
<hr>
<h1>Endometriosis Serology</h1>
<hr>
<input type="submit" name="submit form" value="submit form" /><br>
<br>
<h3>Lab Analysis</h3>
<br>
<h4>Serology</h4>

<table>

<tr><td>Serum il 1beta</td> <td><input type="text" name="serum_il_1beta" id="ilb" onchange="calculateEndo();" /></td></tr>

</table>

<table>

<tr><td>Serum il 6</td> <td><input type="text" name="serum_il_6" id="il6" onchange="calculateEndo();" /></td></tr>

</table>

<table>

<tr><td>Serum tnf alpha</td> <td><input type="text" name="serum_tnf_alpha" id="tnf" onchange="calculateEndo();" /></td></tr>

</table>
<br>
<h4>Risk of Endometriosis</h4>

<table>

<tr><td>Probability of endometriosis</td> <td><input type="text" name="probability_of_endometriosis" id="ast" /></td></tr>

</table>
<table></table><input type="submit" name="submit form" value="submit form" />
</form>
<a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'>[do not save]</a>
<?php
formFooter();
?>
