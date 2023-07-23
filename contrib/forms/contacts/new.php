<?php

/**
 * contacts new.php
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
<!-- Form generated from formsWiz -->
<?php
require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form: contacts");
?>
<html><head>
    <?php Header::setupHeader(); ?>
</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?php echo $rootdir;?>/forms/contacts/save.php?mode=new" name="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<span class="title">Contacts</span><br /><br />
<table>
<tr>
<td valign=top>
<table>
<tr><td><span class=text>OD Base Curve: </span></td><td><input size=4 type="text" name="od_base_curve" value="" ></td></tr>
<tr><td><span class=text>OD Sphere: </span></td><td><input size=4 type="text" name="od_sphere" value="" ></td></tr>
<tr><td><span class=text>OD Cylinder: </span></td><td><input size=4 type="text" name="od_cylinder" value="" ></td></tr>
<tr><td><span class=text>OD Axis: </span></td><td><input size=4 type="text" name="od_axis" value="" ></td></tr>
<tr><td><span class=text>OD Diameter: </span></td><td><input size=4 type="text" name="od_diameter" value="" ></td></tr>
</table>
</td>
<td valign=top>
<table>
<tr><td><span class=text>OS Base Curve: </span></td><td><input size=4 type="text" name="os_base_curve" value="" ></td></tr>
<tr><td><span class=text>OS Sphere: </span></td><td><input size=4 type="text" name="os_sphere" value="" ></td></tr>
<tr><td><span class=text>OS Cylinder: </span></td><td><input size=4 type="text" name="os_cylinder" value="" ></td></tr>
<tr><td><span class=text>OS Axis: </span></td><td><input size=4 type="text" name="os_axis" value="" ></td></tr>
<tr><td><span class=text>OS Diameter: </span></td><td><input size=4 type="text" name="os_diameter" value="" ></td></tr>
</table>
</td>
</tr>
</table>

<table>
<tr><td><span class=text>Material: </span></td><td><input size=4 type="text" name="material" value="" ></td>
<td><span class=text>Color: </span></td><td><input size=4 type="text" name="color" value="" ></td>
<td><span class=text>Bifocal Type: </span></td><td><input size=4 type="text" name="bifocal_type" value="" ></td>
<td><span class=text>Add: </span></td><td><input size=4 type="text" name="add_value" value="" ></td>
<td><span class=text>V/A Far: </span></td><td><input size=4 type="text" name="va_far" value="" ></td>
<td><span class=text>V/A Near: </span></td><td><input size=4 type="text" name="va_near" value="" ></td>
</tr>
</table>

<table>
<tr>
<td valign=top>
<span class=text>Additional Notes: </span><br /><textarea cols=40 rows=8 wrap=virtual name="additional_notes" ></textarea><br />
</td>
</tr>
</table>
<br />
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[Don't Save]</a>
</form>
<?php
formFooter();
?>
