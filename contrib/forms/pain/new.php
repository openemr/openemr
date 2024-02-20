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
 * @copyright Copyright (c) 2006 sunsetsystems <sunsetsystems>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<?php
require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Core\Header;

formHeader("Form: pain");
?>
<html><head>
<?php Header::setupHeader(); ?>
</head>
<body class="body_top">
<form method=post action="<?php echo $rootdir;?>/forms/pain/save.php?mode=new" name="my_form">
<span class="title">Pain Evaluation</span><br /><br />


<input type=checkbox name='dull'  ><span class=text>Dull</span>
<input type=checkbox name='colicky'  ><span class=text>Colicky</span>
<input type=checkbox name='sharp'  ><span class=text>Sharp</span>
<span class=text>Duration of Pain: </span><input type="text" name="duration_of_pain" value="" ><br />


<span class=text>History of Pain: </span><br /><textarea cols=40 rows=4 wrap=virtual name="history_of_pain" ></textarea><br />


<table><tr><td>
<table><tr>
<td><span class=text>Accompanying Symptoms Vomitting: </span></td><td><input type="text" name="accompanying_symptoms_vomitting" value="" ></td>
</tr><tr>
<td><span class=text>Accompanying Symptoms Nausea: </span></td><td><input type="text" name="accompanying_symptoms_nausea" value="" ></td>
</tr><tr>
<td><span class=text>Accompanying Symptoms Headache: </span></td><td><input type="text" name="accompanying_symptoms_headache" value="" ></td>
</tr></table>
</td><td>
<span class=text>Accompanying Symptoms Other: </span><br /><textarea cols=40 rows=8 wrap=virtual name="accompanying_symptoms_other" ></textarea><br />
</td></tr></table>

<table>
<tr><td>
<span class=text>Pain Referred to Other Sites?: </span><br /><textarea cols=40 rows=4 wrap=virtual name="pain_referred_to_other_sites" ></textarea>
</td><td>
<span class=text>What Relieves Pain?: </span><br /><textarea cols=40 rows=4 wrap=virtual name="what_relieves_pain" ></textarea>
</td></tr><tr><td>
<span class=text>What Makes Pain Worse (Movement/Positions/Activities)?: </span><br /><textarea cols=40 rows=4 wrap=virtual name="what_makes_pain_worse" ></textarea>
</td><td>
<span class=text>Additional Notes: </span><br /><textarea cols=40 rows=4 wrap=virtual name="additional_notes" ></textarea>
</td></tr></table>

<br />
<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link">[Don't Save]</a>
</form>
<?php
formFooter();
?>
