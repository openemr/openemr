<?php

/**
 * contacts view.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    andres_paglayan <andres_paglayan>
 * @author    sunsetsystems <sunsetsystems>
 * @author    cornfeed <jdough823@gmail.com>
 * @author    fndtn357 <fndtn357@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @copyright Copyright (c) 2005 andres_paglayan <andres_paglayan>
 * @copyright Copyright (c) 2007 sunsetsystems <sunsetsystems>
 * @copyright Copyright (c) 2011 cornfeed <jdough823@gmail.com>
 * @copyright Copyright (c) 2012 fndtn357 <fndtn357@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<!-- Forms generated from formsWiz -->
<?php
require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

?>
<html><head>
    <?php Header::setupHeader(); ?>
</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
require_once("$srcdir/api.inc");
$obj = formFetch("form_contacts", $_GET["id"]);
?>
<form method=post action="<?php echo $rootdir?>/forms/contacts/save.php?mode=update&id=<?php echo attr_url($_GET["id"]); ?>" name="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<span class="title">Contacts</span><br /><br />
<table>
<tr>
<td valign=top>
<table>
<tr><td><span class=text>OD Base Curve: </span></td><td><input size=4 type="text" name="od_base_curve" value="<?php echo attr($obj["od_base_curve"]); ?>" ></td></tr>
<tr><td><span class=text>OD Sphere: </span></td><td><input size=4 type="text" name="od_sphere" value="<?php echo attr($obj["od_sphere"]); ?>" ></td></tr>
<tr><td><span class=text>OD Cylinder: </span></td><td><input size=4 type="text" name="od_cylinder" value="<?php echo attr($obj["od_cylinder"]); ?>" ></td></tr>
<tr><td><span class=text>OD Axis: </span></td><td><input size=4 type="text" name="od_axis" value="<?php echo attr($obj["od_axis"]); ?>" ></td></tr>
<tr><td><span class=text>OD Diameter: </span></td><td><input size=4 type="text" name="od_diameter" value="<?php echo attr($obj["od_diameter"]); ?>" ></td></tr>
</table>
</td>
<td valign=top>
<table>
<tr><td><span class=text>OS Base Curve: </span></td><td><input size=4 type="text" name="os_base_curve" value="<?php echo attr($obj["os_base_curve"]); ?>" ></td></tr>
<tr><td><span class=text>OS Sphere: </span></td><td><input size=4 type="text" name="os_sphere" value="<?php echo attr($obj["os_sphere"]); ?>" ></td></tr>
<tr><td><span class=text>OS Cylinder: </span></td><td><input size=4 type="text" name="os_cylinder" value="<?php echo attr($obj["os_cylinder"]); ?>" ></td></tr>
<tr><td><span class=text>OS Axis: </span></td><td><input size=4 type="text" name="os_axis" value="<?php echo attr($obj["os_axis"]); ?>" ></td></tr>
<tr><td><span class=text>OS Diameter: </span></td><td><input size=4 type="text" name="os_diameter" value="<?php echo attr($obj["os_diameter"]); ?>" ></td></tr>
</table>
</td>
</tr>
</table>

<table>
<tr>
<td><span class=text>Material: </span></td><td><input size=4 type="text" name="material" value="<?php echo attr($obj["material"]); ?>" ></td>
<td><span class=text>Color: </span></td><td><input size=4 type="text" name="color" value="<?php echo attr($obj["color"]); ?>" ></td>
<td><span class=text>Bifocal Type: </span></td><td><input size=4 type="text" name="bifocal_type" value="<?php echo attr($obj["bifocal_type"]); ?>" ></td>
<td><span class=text>Add: </span></td><td><input size=4 type="text" name="add_value" value="<?php echo attr($obj["add_value"]); ?>" ></td>
<td><span class=text>V/A Far: </span></td><td><input size=4 type="text" name="va_far" value="<?php echo attr($obj["va_far"]); ?>" ></td>
<td><span class=text>V/A Near: </span></td><td><input size=4 type="text" name="va_near" value="<?php echo attr($obj["va_near"]); ?>" ></td>
</tr>
</table>

<table>
<tr>
<td valign=top>
<span class=text>Additional Notes: </span><br /><textarea cols=40 rows=8 wrap=virtual name="additional_notes" ><?php echo text($obj["additional_notes"]);?></textarea><br />
</td>
</tr>
</table>

<br />
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
