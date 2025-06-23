<?php

/**
 * Generated DocBlock
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  andres_paglayan <andres_paglayan>
 * @author  cornfeed <jdough823@gmail.com>
 * @author  fndtn357 <fndtn357@gmail.com>
 * @author  sunsetsystems <sunsetsystems>
 * @copyright Copyright (c) 2005 andres_paglayan <andres_paglayan>
 * @copyright Copyright (c) 2011 cornfeed <jdough823@gmail.com>
 * @copyright Copyright (c) 2012 fndtn357 <fndtn357@gmail.com>
 * @copyright Copyright (c) 2007 sunsetsystems <sunsetsystems>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<!-- Form generated from formsWiz  -->
<?php
require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Core\Header;

formHeader("Form: hearingtest");
?>
<html><head>
    <?php Header::setupHeader(); ?>
</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?php echo $rootdir;?>/forms/hearingtest/save.php?mode=new" name="my_form">
<span class="title">Hearing Test</span><br /><br />
<input type=checkbox name='with_hearing_aid'  ><span class=text>With hearing Aid?</span><br />
<table>
<tr>
<td>
<span class=text>Right Ear 250: </span><input type="text" name="right_ear_250" value="" ><br />
<span class=text>Right Ear 500: </span><input type="text" name="right_ear_500" value="" ><br />
<span class=text>Right Ear 1000: </span><input type="text" name="right_ear_1000" value="" ><br />
<span class=text>Right Ear 2000: </span><input type="text" name="right_ear_2000" value="" ><br />
<span class=text>Right Ear 3000: </span><input type="text" name="right_ear_3000" value="" ><br />
<span class=text>Right Ear 4000: </span><input type="text" name="right_ear_4000" value="" ><br />
<span class=text>Right Ear 5000: </span><input type="text" name="right_ear_5000" value="" ><br />
<span class=text>Right Ear 6000: </span><input type="text" name="right_ear_6000" value="" ><br />
</td>
<td>
<span class=text>Left Ear 250: </span><input type="text" name="left_ear_250" value="" ><br />
<span class=text>Left Ear 500: </span><input type="text" name="left_ear_500" value="" ><br />
<span class=text>Left Ear 1000: </span><input type="text" name="left_ear_1000" value="" ><br />
<span class=text>Left Ear 2000: </span><input type="text" name="left_ear_2000" value="" ><br />
<span class=text>Left Ear 3000: </span><input type="text" name="left_ear_3000" value="" ><br />
<span class=text>Left Ear 4000: </span><input type="text" name="left_ear_4000" value="" ><br />
<span class=text>Left Ear 5000: </span><input type="text" name="left_ear_5000" value="" ><br />
<span class=text>Left Ear 6000: </span><input type="text" name="left_ear_6000" value="" ><br />
</td>
</tr>
</table>
<br />
<table>
<tr>
<td valign=top>
<span class=text>Additional Notes: </span><br /><textarea cols=40 rows=8 name="additional_notes" ></textarea>
</td>
</tr>
</table>
<br />
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>
</form>
<?php
formFooter();
?>
