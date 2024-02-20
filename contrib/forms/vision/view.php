<?php

/**
 * Generated DocBlock
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  andres_paglayan <andres_paglayan>
 * @author  cfapress <cfapress>
 * @author  sunsetsystems <sunsetsystems>
 * @copyright Copyright (c) 2005 andres_paglayan <andres_paglayan>
 * @copyright Copyright (c) 2008 cfapress <cfapress>
 * @copyright Copyright (c) 2007 sunsetsystems <sunsetsystems>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<?php
require_once("../../globals.php");

use OpenEMR\Core\Header;

?>
<html><head>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">
<?php
require_once("$srcdir/api.inc.php");
$obj = formFetch("form_vision", $_GET["id"]);
?>
<form method=post action="<?php echo $rootdir?>/forms/vision/save.php?mode=update&id=<?php echo attr_url($_GET["id"]); ?>" name="my_form">
<span class="title">Vision</span><br /><br />
<span class=bold>Keratometry</span><br />

<table>
<tr>
<td><span class=text>OD K1: </span></td><td><input size=3 type="text" name="od_k1" value="<?php echo attr($obj["od_k1"]);?>" ></td>
<td><span class=text>OD K1 Axis: </span></td><td><input size=3 type="text" name="od_k1_axis" value="<?php echo attr($obj["od_k1_axis"]);?>" ></td>
<td><span class=text>OD K2: </span></td><td><input size=3 type="text" name="od_k2" value="<?php echo attr($obj["od_k2"]);?>" ></td>
<td><span class=text>OD K2 Axis: </span></td><td><input size=3 type="text" name="od_k2_axis" value="<?php echo attr($obj["od_k2_axis"]);?>" ></td>
</tr>
<tr>
<td colspan=8>
<span class=text>OD Testing Status: </span><input type="text" name="od_testing_status" value="<?php echo attr($obj["od_testing_status"]);?>" >
</td>
</tr>
</table>


<table>
<tr>
<td><span class=text>OS K1: </span></td><td><input size=3 type="text" name="os_k1" value="<?php echo attr($obj["os_k1"]);?>" ></td>
<td><span class=text>OS K1 Axis: </span></td><td><input size=3 type="text" name="os_k1_axis" value="<?php echo attr($obj["os_k1_axis"]);?>" ></td>
<td><span class=text>OS K2: </span></td><td><input size=3 type="text" name="os_k2" value="<?php echo attr($obj["os_k2"]);?>" ></td>
<td><span class=text>OS K2 Axis: </span></td><td><input size=3 type="text" name="os_k2_axis" value="<?php echo attr($obj["os_k2_axis"]);?>" ></td>
</tr>
<tr>
<td colspan=8>
<span class=text>OS Testing Status: </span><input type="text" name="os_testing_status" value="<?php echo attr($obj["os_testing_status"]);?>" >
</td>
</tr>
</table>


<span class=text>Additional Notes: </span><br /><textarea cols=40 rows=8 wrap=virtual name="additional_notes" ><?php echo text($obj["additional_notes"]);?></textarea>
<br />
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
