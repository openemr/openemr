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
 * @copyright Copyright (c) 2017-2023 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2007 sunsetsystems <sunsetsystems>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<!-- Forms generated from formsWiz -->
<?php
require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Core\Header;

?>
<!-- TODO: Clean the code -->
<html><head>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">
<?php
require_once("$srcdir/api.inc.php");
$obj = formFetch("form_obstetrical", $_GET["id"]);
?>
<form method=post action="<?php echo $rootdir?>/forms/obstetrical/save.php?mode=update&id=<?php echo attr_url($_GET["id"]); ?>" name="my_form">
<span class="title">Obstetrical Form</span><br /><br />
<table>
<tr>
<td><span class=text>Name: </span></td><td><input type="text" name="name" value="<?php echo attr($obj["name"]); ?>" ></td>
<td><span class=text>Birthdate: </span></td><td><input type="text" size=10 name=birthdate value="<?php if ($obj["birthdate"] != "0000-00-00 00:00:00") {
    echo attr(date("Y-m-d", strtotime($obj["birthdate"])));
                                                                                                 } else {
                                                                                                     echo "YYYY-MM-DD";
                                                                                                 }?>"></td>
<td><span class=text>Birth Status: </span></td><td><input type="text" name="birth_status" value="<?php echo attr($obj["birth_status"]); ?>" ></td>
<td><span class=text>Gender: </span></td><td><input type="text" name="gender" value="<?php echo attr($obj["gender"]); ?>" ></td>
<td><input type=checkbox name="circumcised"  <?php if ($obj["circumcised"] == "on") {
    echo "checked";
                                             };?>></td><td><span class=text>Circumcised</span></td>
</tr>
</table>

<table>
<tr>
<td><span class=text>Pediatrician: </span></td><td><input type="text" name="pediatrician" value="<?php echo attr($obj["pediatrician"]); ?>" ></td>
<td><span class=text>Birth Weight: </span></td><td><input type="text" name="birth_weight" value="<?php echo attr($obj["birth_weight"]); ?>" ></td>
</tr><tr>
<td><span class=text>Length (Inches): </span></td><td><input type="text" name="length_inches" value="<?php echo attr($obj["length_inches"]); ?>" ></td>
<td><span class=text>Head Circumference (Inches): </span></td><td><input type="text" name="head_circumference_inches" value="<?php echo attr($obj["head_circumference_inches"]); ?>" ></td>
</tr><tr>
<td><span class=text>Feeding: </span></td><td><input type="text" name="feeding" value="<?php echo attr($obj["feeding"]); ?>" ></td>
<td><span class=text>Delivery Method: </span></td><td><input type="text" name="delivery_method" value="<?php echo attr($obj["delivery_method"]); ?>" ></td>
</tr><tr>
<td><span class=text>Labor Hours: </span></td><td><input type="text" name="labor_hours" value="<?php echo attr($obj["labor_hours"]); ?>" ></td>
<td><span class=text>Pregnancy (Weeks): </span></td><td><input type="text" name="pregnancy_weeks" value="<?php echo attr($obj["pregnancy_weeks"]); ?>" ></td>
</tr><tr>
<td><span class=text>Anesthesia: </span></td><td colspan=3><input type="text" name="anesthesia" value="<?php echo attr($obj["anesthesia"]); ?>" ></td>
</tr>
</table>

<table>
<tr>
<td><span class=text>Reactions to Medications and Immunizations: </span><br /><textarea cols=40 rows=4 wrap=virtual name="reactions_to_medications_and_immunizations" ><?php echo text($obj["reactions_to_medications_and_immunizations"]); ?></textarea></td>
<td><span class=text>Birth Complications: </span><br /><textarea cols=40 rows=4 wrap=virtual name="birth_complications" ><?php echo text($obj["birth_complications"]); ?></textarea></td>
</tr><tr>
<td><span class=text>Developmental Problems: </span><br /><textarea cols=40 rows=4 wrap=virtual name="developmental_problems" ><?php echo text($obj["developmental_problems"]); ?></textarea></td>
<td><span class=text>Chronic Illness: </span><br /><textarea cols=40 rows=4 wrap=virtual name="chronic_illness" ><?php echo text($obj["chronic_illness"]); ?></textarea></td>
</tr><tr>
<td><span class=text>Chronic Medication: </span><br /><textarea cols=40 rows=4 wrap=virtual name="chronic_medication" ><?php echo text($obj["chronic_medication"]); ?></textarea></td>
<td><span class=text>Hospitalization: </span><br /><textarea cols=40 rows=4 wrap=virtual name="hospitalization" ><?php echo text($obj["hospitalization"]); ?></textarea></td>
</tr><tr>
<td><span class=text>Surgery: </span><br /><textarea cols=40 rows=4 wrap=virtual name="surgery" ><?php echo text($obj["surgery"]); ?></textarea></td>
<td><span class=text>Injury: </span><br /><textarea cols=40 rows=4 wrap=virtual name="injury" ><?php echo text($obj["injury"]); ?></textarea></td>
</tr><tr>
<td><span class=text>Day Care: </span><br /><textarea cols=40 rows=4 wrap=virtual name="day_care" ><?php echo text($obj["day_care"]); ?></textarea></td>
<td><span class=text>Additional Notes: </span><br /><textarea cols=40 rows=4 wrap=virtual name="additional_notes" ><?php echo text($obj["additional_notes"]); ?></textarea></td>
</tr>
</table>
<br />
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
