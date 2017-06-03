<?php
/** 
 *  Dictation store
 * 
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @copyright Copyright (c) 2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * 
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
$newid = formSubmit("form_dictation", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Speech Dictation", $newid, "dictation", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
 $id = filter_input(INPUT_GET, 'id');
 $dictation = filter_input(INPUT_POST, 'dictation');                               //Filter input before storing in database.
 $storeDictation = htmlspecialchars($dictation, ENT_QUOTES | ENT_HTML5);           //convert html to entities code
 $additional_notes = filter_input(INPUT_POST, 'additional_notes');                               //Filter input before storing in database.
 $storeAdditionalNotes = htmlspecialchars($additional_notes, ENT_QUOTES | ENT_HTML5);            //convert html to entities code  
sqlInsert("update form_dictation set pid = ?,groupname=?,user=?,authorized=?,activity=1, date = NOW(), dictation=?, additional_notes=? where id=?",array($_SESSION["pid"],$_SESSION["authProvider"],$_SESSION["authUser"],$userauthorized,$storeDictation,$storeAdditionalNotes,$id));
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
