<?php

/**
 * gad-7 form using form api     view.php
 * open a previously completed GAD-7 form for further editing
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ruth Moulton <moulton ruth@muswell.me.uk>
 * @copyright Copyright (c) 2021 ruth moulton <ruth@muswell.me.uk>
 *
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("gad7.inc.php");  // common strings, require_once(globals.php), other includes etc

use OpenEMR\Common\Csrf\CsrfUtils;    // security module
use OpenEMR\Core\Header;

$form_folder = "gad7";
?>
<html><head>
 <head>
    <title><?php echo text($str_form_title); ?> </title>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">
<?php // read in the values from the filled in form held in db
$obj = formFetch("form_gad7", $_GET["id"]); ?>
<script>
// get scores from previous saving of the form
var gad7_score = 0;

</script>
<SCRIPT
  src="<?php echo $rootdir;?>/forms/gad7/gad7_javasrc.js">
 </script>

<SCRIPT>
// stuff that uses embedded php must go here, not in the include javascript file - it must be executed on server side before page is sent to client. included javascript is only executed on the client
var changes_made = false;

function create_q8(question, menue){
 // create the question - the second part is italicised
       var text = document.createTextNode(jsAttr(<?php echo js_escape($str_q8); ?>));
       question.appendChild(text);
       var new_line = document.createElement("br"); // second part is in italics
       var ital = document.createElement("i"); // second part is in italics
       var question_2 = document.createTextNode(jsAttr(<?php echo js_escape($str_q8_2); ?>));
       ital.appendChild(question_2) ;
       question.name = "eighth";
       question.appendChild(new_line);
       question.appendChild(ital);

// populate the   the menue
         menue.options[0] = new Option ( <?php echo js_escape($str_default);  ?>, "undef");
         menue.options[1] = new Option ( <?php echo js_escape($str_not); ?>, "0");
         menue.options[2] = new Option ( <?php echo js_escape($str_somewhat); ?>, "1");
         menue.options[3] = new Option ( <?php echo js_escape($str_very); ?>, "2");
         menue.options[4] = new Option ( <?php echo js_escape($str_extremely);?>, "3");
        /*  menue.options[5] = new Option ( <?php echo js_escape($str_default);  ?>, "4"); */

}
// check user really wants to exit without saving new answers
function nosave_exit() {
var conf = true;

/* if there have been no changes, just exit other wise get user to confirm exit without saving changes */
if (changes_made) {
    conf = confirm ( <?php echo js_escape($str_nosave_confirm); ?> );
    }
if (conf) {
    window.location.href="<?php echo $GLOBALS['form_exit_url']; ?>";
    }
return ( conf );
}
</script>

<form method=post action="<?php echo $rootdir;?>/forms/gad7/save.php?mode=update&id=<?php echo attr_url($_GET["id"]); ?>" name="my_form" >
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<br></br>
<span   ><font size=4><?php echo text($str_form_name); ?></font></span>
<br></br>
<input type="Submit" value="<?php echo xla('Save Form'); ?>" style="color: #483D8B" >
&nbsp &nbsp
<input type="button" value="<?php echo attr($str_nosave_exit);?>" onclick="top.restoreSession();return( nosave_exit());" style="color: #483D8B">
 <br><br>
<span class="text"><h2><?php echo xlt('How often have you been bothered by the following over the past 2 weeks?'); ?></h2></span>
<br>
<table>
<tr>
<td>
<span class="text"><?php echo  text($str_nervous); ?></span>
<select name="nervous_score" onchange="update_score(0, my_form.nervous_score.value);changes_made=true">
     <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
    </select>
<script>
     // set the default to the previous value - so it is displayed in the menue box
    document.my_form.nervous_score.options[<?php echo text($obj['nervous_score']); ?>].defaultSelected=true;
    var i = <?php echo text($obj['nervous_score']); ?> ; //the value from last time
    gad7_score += i;
    all_scores[0] = i;
</script>
 <br>
</br>
</tr>
 </table>
  <table>
  <tr> <td>
<span class="text" ><?php echo text($str_control_worry); ?></span>
<select name="control_worry_score" onchange="update_score(1, my_form.control_worry_score.value);changes_made=true" >
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
 </select>
<script>
     // set the default to the previous value - so it is displayed in the menue box
     var i = <?php echo text($obj['control_worry_score']); ?>; //the value from last time
   document.my_form.control_worry_score.options[i].defaultSelected=true;
    gad7_score += i;
    all_scores[1] = i;
</script>
 <br></br>
</tr>
 </table>
  <table>
  <tr>
  <td>
<span class="text" ><?php echo text($str_worry); ?></span>
<select name="worry_score" onchange="update_score(2, my_form.worry_score.value);changes_made=true" >
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
    </select>
       <script>
     // set the previous value to the default - so it is displayed in the menue box
      var i = <?php echo text($obj['worry_score']); ?> ; //the value from last time
    document.my_form.worry_score.options[i].defaultSelected=true;
    gad7_score += i;
    all_scores[2] = i;
    </script>
     <br></br>
</tr>
 </table>
 <table>
 <tr><td>
<span class="text" ><?php echo text($str_relax); ?></span>
<select name="relax_score" onchange="update_score(3, my_form.relax_score.value);changes_made=true">
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
 </select>
<script>
     // set the previous value to the default - so it is displayed in the menue box
      var i = <?php echo text($obj['relax_score']); ?> ; //the value from last time
    document.my_form.relax_score.options[i].defaultSelected=true;
    gad7_score += i;
    all_scores[3] = i;
    </script>
    <br></br>
</tr>
 </table>
  <table>
  <tr><td>
<span class="text" ><?php echo text($str_restless); ?></span>
<select name="restless_score" onchange="update_score(4, my_form.restless_score.value);changes_made=true">
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
    </select>
<script>
     // set the previous value to the default - so it is displayed in the menue box
     var i = <?php echo text($obj['restless_score']); ?> ; //the value from last time
    document.my_form.restless_score.options[i].defaultSelected=true;
    gad7_score += i;
    all_scores[4] = i;
    </script>
    <br></br>
</tr>
 </table>
 <table>
 <tr><td>
<span class="text" ><?php echo text($str_annoyed); ?></span>
<select name="irritable_score" onchange="update_score(5, my_form.irritable_score.value);changes_made=true">
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
    </select>
<script>
     // set the previous value to the default - so it is displayed in the menue box
       var i = <?php echo text($obj['irritable_score']); ?> ; //the value from last time
    document.my_form.irritable_score.options[i].defaultSelected=true;
    gad7_score += i;
    all_scores[5] = i;
</script>
    <br></br>
    </tr>
 </table>
  <table>
  <tr><td>
<span class="text" ><?php echo text($str_afraid); ?></span>
<select name="fear_score" onchange="update_score(6, my_form.fear_score.value);changes_made=true">
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
    </select>
<script>
     // set the previous value to the default - so it is displayed in the menue box
     var i = <?php echo text($obj['fear_score']);?> ; //the value from last time
    document.my_form.fear_score.options[i].defaultSelected=true;
    gad7_score += i;
    all_scores[6] = i;
</script>
  <br></br>
</tr>
 </table>

 <!-- where the final question (8)  will go if the score > 0 -->
  </table>
  <table  frame = above>
  <tr><td>
<!-- optional - only asked if score so far >0 and not included in final score -->
<!-- where the final question will go if the score > 0 -->
  <span id="q8_place"></span>
  <br>
 </table>
 <table frame=hsides>
<tr><td>
 <span id="show_gad7_score"><b><?php echo xlt("Total GAD-7 score"); ?>:</b> </td>
<!-- use this to save the individual scores in the database -->
<!-- input type="hidden" name="scores_array" -->
  <br></br>
  </tr>
  </table>
  <SCRIPT>
// only display the final question if the score is > 0
// pass the function the answer previously entered onto the form
manage_question_8 ("<?php echo text($obj["difficulty"]); ?>"); //do we need q8
update_score ("undef",gad7_score); //display total from last time
 </script>
 <br>
<input type="Submit" value="<?php echo xla('Save Form'); ?>" style="color: #483D8B"   >
&nbsp &nbsp
<input type="button" value="<?php echo attr($str_nosave_exit);?>" onclick="top.restoreSession();return( nosave_exit());" style="color: #483D8B">
 <br><br><br>
</form>

<?php
formFooter();
?>
