<?php

/**
 * Generated DocBlock
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  andres_paglayan <andres_paglayan>
 * @author  cfapress <cfapress>
 * @author  Robert Down <robertdown@live.com>
 * @author  sunsetsystems <sunsetsystems>
 * @copyright Copyright (c) 2005 andres_paglayan <andres_paglayan>
 * @copyright Copyright (c) 2008 cfapress <cfapress>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
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
include_once("$srcdir/api.inc");
$obj = formFetch("form_pain", $_GET["id"]);
?>
<form method=post action="<?php echo $rootdir?>/forms/pain/save.php?mode=update&id=<?php echo attr_url($_GET["id"]); ?>" name="my_form">
<span class="title">Pain Evaluation</span><br /><br />




<input type=checkbox name="dull"  <?php if ($obj["dull"] == "on") {
    echo "checked";
                                  };?>><span class=text>Dull</span>
<input type=checkbox name="colicky"  <?php if ($obj["colicky"] == "on") {
    echo "checked";
                                     };?>><span class=text>Colicky</span>
<input type=checkbox name="sharp"  <?php if ($obj["sharp"] == "on") {
    echo "checked";
                                   };?>><span class=text>Sharp</span>
<span class=text>Duration of Pain: </span><input type="text" name="duration_of_pain" value="<?php echo attr($obj["duration_of_pain"]);?>" ><br />


<span class=text>History of Pain: </span><br /><textarea cols=40 rows=8 wrap=virtual name="history_of_pain" ><?php echo text($obj["history_of_pain"]);?></textarea><br />


<table><tr><td>
<table><tr>
<td><span class=text>Accompanying Symptoms Vomitting: </span></td><td><input type="text" name="accompanying_symptoms_vomitting" value="<?php echo attr($obj["accompanying_symptoms_vomitting"]);?>" ></td>
</tr><tr>
<td><span class=text>Accompanying Symptoms Nausea: </span></td><td><input type="text" name="accompanying_symptoms_nausea" value="<?php echo attr($obj["accompanying_symptoms_nausea"]);?>" ></td>
</tr><tr>
<td><span class=text>Accompanying Symptoms Headache: </span></td><td><input type="text" name="accompanying_symptoms_headache" value="<?php echo attr($obj["accompanying_symptoms_headache"]);?>" ></td>
</tr></table>
</td><td>
<span class=text>Accompanying Symptoms Other: </span><br /><textarea cols=40 rows=8 wrap=virtual name="accompanying_symptoms_other" ><?php echo text($obj["accompanying_symptoms_other"]);?></textarea><br />
</td></tr></table>

<table>
<tr><td>
<span class=text>Pain Referred to Other Sites?: </span><br /><textarea cols=40 rows=4 wrap=virtual name="pain_referred_to_other_sites" ><?php echo text($obj["pain_referred_to_other_sites"]);?></textarea>
</td><td>
<span class=text>What Relieves Pain?: </span><br /><textarea cols=40 rows=4 wrap=virtual name="what_relieves_pain" ><?php echo text($obj["what_relieves_pain"]);?></textarea>
</td></tr><tr><td>
<span class=text>What Makes Pain Worse (Movement/Positions/Activities)?: </span><br /><textarea cols=40 rows=4 wrap=virtual name="what_makes_pain_worse" ><?php echo text($obj["what_makes_pain_worse"]);?></textarea>
</td><td>
<span class=text>Additional Notes: </span><br /><textarea cols=40 rows=4 wrap=virtual name="additional_notes" ><?php echo text($obj["additional_notes"]);?></textarea>
</td></tr></table>



<br />
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
