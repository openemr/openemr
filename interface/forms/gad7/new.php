<?php

/**
 * gad-7 form using forms api     new.php    create a new form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ruth Moulton <moulton ruth@muswell.me.uk>
 * @copyright Copyright (c) 2021 ruth moulton <ruth@muswell.me.uk>
 *
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("gad7.inc.php"); //common strings, require_once(globals.php), other includes  etc

use OpenEMR\Common\Csrf\CsrfUtils;    // security module
use OpenEMR\Core\Header;
?>
<html>
<head>
    <title><?php echo text($string_form_title); ?> </title>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">

<script>
var no_qs = 8; // number of questions in the form
var gad7_score = 0; // total score
</script>

<SCRIPT
  src="<?php echo $rootdir;?>/forms/gad7/gad7_javasrc.js">
 </script>

 <script>
// stuff that uses embedded php must go here, not in the include javascript file -
// it must be executed on server side before page is sent to client. included
// javascript is only executed on the client
function create_q8(question, menue){
 // create the 8th question - the second part is italicised. Only displayed if score > 0
    var text = document.createTextNode(jsAttr(<?php echo js_escape($str_q8); ?>));
    question.appendChild(text);
    var new_line = document.createElement("br"); // second part is in italics
    var ital = document.createElement("i"); // second part is in italics
    var question_2 = document.createTextNode(jsAttr(<?php echo js_escape($str_q8_2); ?>));
    ital.appendChild(question_2);
    question.name = "eighth";
    question.appendChild(new_line);
    question.appendChild(ital);
// populate the   the menue
     menue.options[0] = new Option ( <?php echo js_escape($str_not); ?>, "0");
     menue.options[1] = new Option ( <?php echo js_escape($str_somewhat); ?>, "1");
     menue.options[2] = new Option ( <?php echo js_escape($str_very); ?>, "2");
     menue.options[3] = new Option ( <?php echo js_escape($str_extremely);?>, "3");
     menue.options[4] = new Option ( <?php echo js_escape($str_default);  ?>, "undef");
}
</script>
<form method=post action="<?php echo $rootdir;?>/forms/gad7/save.php?mode=new" name="my_form" onSubmit="return(check_all());" >
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<br></br>
<span><font size=4><?php echo text($str_form_name); ?></font></span>
<br></br>
<input type="Submit" value="<?php echo xla('Save Form'); ?>" style="color: #483D8B" >
 &nbsp &nbsp
 <input type="button" value="<?php echo attr($str_nosave_exit);?>" onclick="top.restoreSession();return( nosave_exit());" style="color: #483D8B">
<br></br>
<span class="text"> <h2><?php echo xlt('How often have you been bothered by the following over the past 2 weeks?'); ?></h2> </span>
<table><tr>
<td>
<span class="text" ><?php echo xlt('Feeling nervous, anxious, or on edge'); ?></span>
<select name="nervous_score" onchange="update_score(0, my_form.nervous_score.value);">
    <option selected value="undef"><?php echo text($str_default); ?></option>
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
    </select>
<br>
</br>
</tr>
 </table>
<table>
<tr>
<td>
<span class="text" ><?php echo xlt('Not being able to stop or control worrying'); ?></span>
<select name="control_worry_score" onchange="update_score(1, my_form.control_worry_score.value);" >
    <option selected value="undef"><?php echo text($str_default); ?></option>
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
    </select>
<br></br>
</tr>
 </table>
 <tr>
<td>
 <table>
<span class=text ><?php echo xlt('Worrying too much about different things'); ?></span>
<select name="worry_score" onchange="update_score(2, my_form.worry_score.value);" >
    <option selected value="undef" ><?php echo text($str_default); ?></option>
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
    </select>
 <br></br>
</tr>
 </table>
<table>
<tr>
<td>
<span class="text" ><?php echo xlt('Trouble relaxing'); ?></span>
 <select name="relax_score" onchange="update_score(3, my_form.relax_score.value);">
 <option selected value="undef" ><?php echo text($str_default); ?></option>
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
    </select>
    <br></br>
</tr>
 </table>
<table>
  <tr>
<td>
<span class="text"><?php echo xlt("Being so restless that it's hard to sit still"); ?></span>
<select name="restless_score" onchange="update_score(4, my_form.restless_score.value);">
 <option selected value="undef" ><?php echo text($str_default); ?></option>
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
    </select>
    <br></br>
</tr>
 </table>
<table>
 <tr>
<td>
<span class="text"><?php echo xlt('Becoming easily annoyed or irritable'); ?></span>
<select name="irritable_score" onchange="update_score(5, my_form.irritable_score.value);">
 <option selected value="undef" ><?php echo text($str_default); ?></option>
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
    </select>
 <br></br>
 </table>
 </tr>
<table>
  <tr>
<td>
<span class="text"><?php echo xlt('Feeling afraid as if something awful might happen'); ?></span>
<select name="fear_score" onchange="update_score(6, my_form.fear_score.value);">
 <option selected value="undef" ><?php echo text($str_default); ?></option>
    <option value="0"><?php echo text($str_not); ?></option>
    <option value="1"><?php echo text($str_several); ?></option>
    <option value="2"><?php echo text($str_more); ?></option>
    <option value="3"><?php echo text($str_nearly); ?></option>
    </select>
 <br><br>
</tr>
 </table>
<table  frame = above>
<tr><td>
 <span id="q8_place" class="text"><br></span>
 </table>
 <br></br>
 <SCRIPT>
function  check_all() {
   // has each question been answered and save scores
    var  flag=false;
    var list='';
    for (i=0; i<(no_qs-1); i++) { // last questionis optional
          if ( !all_answered[i] ){
          list = list+Number(i+1) + ',';
          flag=true;
          }
    }
    if (flag) {
          list[list.length-1] = ' '; /* get rid of trailing comma */
          alert(xl("Please answer all of the questions") + ": " + list + " " + xl("are unanswered"));
           return false;
     }
    return true;
  }
  // warn if about to exit without saving answers - check that's what the user really wants
function nosave_exit() {
    var conf = confirm (<?php echo js_escape($str_nosave_confirm) ; ?>);

    if (conf) {
        window.location.href="<?php echo $GLOBALS['form_exit_url']; ?>";
    }
    return ( conf );
}
</script>
<table frame=hsides><tr><td>
 <span id="show_gad7_score"><b><?php echo xlt("Total GAD-7 score"); ?>:</b> </td>
 </table>
 <script>
update_score("undef",gad7_score);
 </script>
 <br></br>
 <table>
 <tr><td>
 <input type="Submit" value="<?php echo xla('Save Form'); ?>" style="color: #483D8B">
 &nbsp &nbsp
 <input type="button" value="<?php echo attr($str_nosave_exit); ?>" onclick="top.restoreSession();return(nosave_exit());" style="color: #483D8B">
 <br><br>
 </table>
</form>
<?php
formFooter();
?>
