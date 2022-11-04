<?php

/**
 * phq-9 form using forms api     new.php    create a new form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ruth Moulton <moulton ruth@muswell.me.uk>
 * @copyright Copyright (c) 2021 ruth moulton <ruth@muswell.me.uk>
 *
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("phq9.inc.php"); //common strings
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if ($viewmode == 'update') {
    $obj = formFetch("form_phq9", $_GET["id"]);
} else {
    $obj = null;
}
?>
<html>
<head>
    <title><?php echo text($str_form_title); ?> </title>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">
    <script>
        //var no_qs = 10; // number of questions in the form
        var phq9_score = 0; // total score

    </script>
    <?php $qno = 0; ?>

    <script src="<?php echo $rootdir; ?>/forms/phq9/phq9_javasrc.js"></script>

    <script>
        // stuff that uses embedded php must go here, not in the include javascript file -
        // it must be executed on server side before page is sent to client. included
        // javascript is only executed on the client
        function create_q10(question, menue ) {
            // create the 10th question - the second part is italicised. Only displayed if score > 0
            var text = document.createTextNode(jsAttr("10" + ". "+<?php echo js_escape($str_q10); ?>));
            question.appendChild(text);
            var new_line = document.createElement("br");
            var ital = document.createElement("i"); // second part is in italics
            var question_2 = document.createTextNode(jsAttr(<?php echo   js_escape($str_q10_2); ?>));
            ital.appendChild(question_2);
            question.name = "tenth";
            question.appendChild(new_line);
            question.appendChild(ital);
// populate the   the menue
            menue.options[0] = new Option( <?php echo js_escape($str_not); ?>, "0");
            menue.options[1] = new Option( <?php echo js_escape($str_somewhat); ?>, "1");
            menue.options[2] = new Option( <?php echo js_escape($str_very); ?>, "2");
            menue.options[3] = new Option( <?php echo js_escape($str_extremely);?>, "3");
            menue.options[4] = new Option( <?php echo js_escape($str_default);  ?>, "undef");
        }
    </script>
    <div class="col-12">
        <h3><?php echo text($str_form_name); ?></h3>
        <form method=post action="<?php echo $rootdir; ?>/forms/phq9/save.php?mode=<?php echo attr_url($viewmode); ?>&id=<?php echo attr_url($_GET['id'] ?? 0); ?>" name="my_form"<?php if (!$obj) {
            ?> onSubmit="return(check_all());"<?php }?>>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <div class="title"><?php echo xlt('How often have you been bothered by the following over the past 2 weeks?'); ?></div>
            <hr />
            <table>
                <tr>
                    <td>
                        <span class="label"><?php echo ++$qno . ". " . xlt('Little interest or pleasure in doing things'); ?></span>
                        <select class="form-input" name="interest_score" onchange="update_score(0, my_form.interest_score.value);">
                            <?php if (!$obj) { ?>
                                <option value="undef"><?php echo text($str_default); ?></option> <?php } ?>
                            <option value="0"><?php echo text($str_not); ?></option>
                            <option value="1"><?php echo text($str_several); ?></option>
                            <option value="2"><?php echo text($str_more); ?></option>
                            <option value="3"><?php echo text($str_nearly); ?></option>
                        </select>
                        <?php if ($obj) { ?>
                        <script>
                            // set the default to the previous value - so it is displayed in the menue box
                            document.my_form.interest_score.options[<?php echo attr($obj['interest_score']); ?>].defaultSelected=true;
                            var i = <?php echo js_escape($obj['interest_score']); ?>; //the value from last time
                            phq9_score += parseInt (i);
                            all_scores[0] = i;
                        </script><?php } ?>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>
                        <span class="label"><?php echo ++$qno . ". " . xlt('Feeling down, depressed, or hopeless'); ?></span>
                        <select class="input-sm my-1" name="hopeless_score" onchange="update_score(1, my_form.hopeless_score.value);">
                            <?php if (!$obj) {
                                ?><option value="undef"><?php echo text($str_default); ?></option><?php } ?>
                            <option value="0"><?php echo text($str_not); ?></option>
                            <option value="1"><?php echo text($str_several); ?></option>
                            <option value="2"><?php echo text($str_more); ?></option>
                            <option value="3"><?php echo text($str_nearly); ?></option>
                        </select>
                        <?php if ($obj) { ?>
                        <script>
                            // set the default to the previous value - so it is displayed in the menue box
                            var i = <?php echo js_escape($obj['hopeless_score']); ?>; //the value from last time
                            document.my_form.hopeless_score.options[i].defaultSelected=true;
                            phq9_score += parseInt (i);
                            all_scores[1] = i;
                        </script><?php } ?>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>
                        <span class="label"><?php echo ++$qno . ". " . xlt('Trouble falling or staying asleep, or sleeping too much'); ?></span>
                        <select class="input-sm my-1" name="sleep_score" onchange="update_score(2, my_form.sleep_score.value);">
                            <?php if (!$obj) {
                                ?><option value="undef"><?php echo text($str_default); ?></option><?php } ?>
                            <option value="0"><?php echo text($str_not); ?></option>
                            <option value="1"><?php echo text($str_several); ?></option>
                            <option value="2"><?php echo text($str_more); ?></option>
                            <option value="3"><?php echo text($str_nearly); ?></option>
                        </select>
                        <?php if ($obj) { ?>
                        <script>
                            // set the previous value to the default - so it is displayed in the menue box
                            var i = <?php echo js_escape($obj['sleep_score']); ?> ; //the value from last time
                            document.my_form.sleep_score.options[i].defaultSelected=true;
                            phq9_score += parseInt (i);
                            all_scores[2] = i;
                        </script><?php } ?>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>
                        <span class="label"><?php echo ++$qno . ". " . xlt('Feeling tired or having little energy'); ?></span>
                        <select class="input-sm my-1" name="fatigue_score" onchange="update_score(3, my_form.fatigue_score.value);">
                            <?php if (!$obj) {
                                ?><option value="undef"><?php echo text($str_default); ?></option><?php } ?>
                            <option value="0"><?php echo text($str_not); ?></option>
                            <option value="1"><?php echo text($str_several); ?></option>
                            <option value="2"><?php echo text($str_more); ?></option>
                            <option value="3"><?php echo text($str_nearly); ?></option>
                        </select>
                        <?php if ($obj) { ?>
                        <script>
                            // set the previous value to the default - so it is displayed in the menue box
                            var i = <?php echo js_escape($obj['fatigue_score']); ?> ; //the value from last time
                            document.my_form.fatigue_score.options[i].defaultSelected=true;
                            phq9_score += parseInt (i);
                            all_scores[3] = i;
                        </script><?php } ?>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>
                        <span class="label"><?php echo ++$qno . ". " . xlt('Poor appetite or overeating'); ?></span>
                        <select class="input-sm my-1" name="appetite_score" onchange="update_score(4, my_form.appetite_score.value);">
                            <?php if (!$obj) {
                                ?><option value="undef"><?php echo text($str_default); ?></option><?php } ?>
                            <option value="0"><?php echo text($str_not); ?></option>
                            <option value="1"><?php echo text($str_several); ?></option>
                            <option value="2"><?php echo text($str_more); ?></option>
                            <option value="3"><?php echo text($str_nearly); ?></option>
                        </select>
                        <?php if ($obj) { ?>
                        <script>
                            // set the previous value to the default - so it is displayed in the menue box
                            var i = <?php echo js_escape($obj['appetite_score']); ?> ; //the value from last time
                            document.my_form.appetite_score.options[i].defaultSelected=true;
                            phq9_score += parseInt (i);
                            all_scores[4] = i;
                        </script><?php } ?>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>
                        <span class="label"><?php echo ++$qno . ". " . xlt('Feeling bad about yourself - or that you are a failure or have let yourself or your family down'); ?></span>
                        <select class="input-sm my-1" name="failure_score" onchange="update_score(5, my_form.failure_score.value);">
                            <?php if (!$obj) {
                                ?><option value="undef"><?php echo text($str_default); ?></option><?php } ?>
                            <option value="0"><?php echo text($str_not); ?></option>
                            <option value="1"><?php echo text($str_several); ?></option>
                            <option value="2"><?php echo text($str_more); ?></option>
                            <option value="3"><?php echo text($str_nearly); ?></option>
                        </select>
                        <?php if ($obj) { ?>
                        <script>
                            // set the previous value to the default - so it is displayed in the menue box
                            var i = <?php echo js_escape($obj['failure_score']); ?> ; //the value from last time
                            document.my_form.failure_score.options[i].defaultSelected=true;
                            phq9_score += parseInt (i);
                            all_scores[5] = i;
                        </script><?php } ?>
                    </td>
            </table>
            </tr>
            <table>
                <tr>
                    <td>
                        <span class="label"><?php echo ++$qno . ". " . xlt('Trouble concentrating on things, such as reading an article or watching videos'); ?></span>
                        <select class="input-sm my-1" name="focus_score" onchange="update_score(6, my_form.focus_score.value);">
                            <?php if (!$obj) {
                                ?><option value="undef"><?php echo text($str_default); ?></option><?php } ?>
                            <option value="0"><?php echo text($str_not); ?></option>
                            <option value="1"><?php echo text($str_several); ?></option>
                            <option value="2"><?php echo text($str_more); ?></option>
                            <option value="3"><?php echo text($str_nearly); ?></option>
                        </select>
                        <?php if ($obj) { ?>
                        <script>
                            // set the previous value to the default - so it is displayed in the menue box
                            var i = <?php echo js_escape($obj['focus_score']);?> ; //the value from last time
                            document.my_form.focus_score.options[i].defaultSelected=true;
                            phq9_score += parseInt (i);
                            all_scores[6] = i;
                        </script><?php } ?>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>
                        <span class="label"><?php echo ++$qno . ". " . xlt('Moving or speaking slowly noted by others or fidgety or restless more than usual'); ?></span>
                        <select class="input-sm my-1" name="psychomotor_score" onchange="update_score(7, my_form.psychomotor_score.value);">
                            <?php if (!$obj) {
                                ?><option value="undef"><?php echo text($str_default); ?></option><?php } ?>
                            <option value="0"><?php echo text($str_not); ?></option>
                            <option value="1"><?php echo text($str_several); ?></option>
                            <option value="2"><?php echo text($str_more); ?></option>
                            <option value="3"><?php echo text($str_nearly); ?></option>
                        </select>
                        <?php if ($obj) { ?>
                        <script>
                            // set the previous value to the default - so it is displayed in the menue box
                            var i = <?php echo js_escape($obj['psychomotor_score']);?> ; //the value from last time
                            document.my_form.psychomotor_score.options[i].defaultSelected=true;
                            phq9_score += parseInt (i);
                            all_scores[7] = i;
                        </script><?php } ?>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>
                        <span class="label"><?php echo ++$qno . ". " . xlt('Thoughts that you would be better off dead, or of hurting yourself'); ?></span>
                        <select class="input-sm my-1" name="suicide_score" onchange="update_score(8, my_form.suicide_score.value);">
                            <?php if (!$obj) {
                                ?><option value="undef"><?php echo text($str_default); ?></option><?php } ?>
                            <option value="0"><?php echo text($str_not); ?></option>
                            <option value="1"><?php echo text($str_several); ?></option>
                            <option value="2"><?php echo text($str_more); ?></option>
                            <option value="3"><?php echo text($str_nearly); ?></option>
                        </select>
                        <?php if ($obj) { ?>
                        <script>
                            // set the previous value to the default - so it is displayed in the menue box
                            var i = <?php echo js_escape($obj['suicide_score']);?> ; //the value from last time
                            document.my_form.suicide_score.options[i].defaultSelected=true;
                            phq9_score += parseInt (i);
                            all_scores[8] = i;
                        </script><?php } ?>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>
                    <br>
                        <span id="q10_place" class="label"></span>
                    </td>
                </tr>
            </table>

            <script>
                function check_all() {
                    // has each question been answered and save scores
                    var flag = false;
                    var list = '';
                    for (i = 0; i < (no_qs - 1); i++) { // last questionis optional
                        if (!all_answered[i]) {
                            list = list + Number(i + 1) + ',';
                            flag = true;
                        }
                    }
                    if (flag) {
                        list[list.length - 1] = ' '; /* get rid of trailing comma */
                        alert(xl("Please answer all of the questions") + ": " + list + " " + xl("are unanswered"));
                        return false;
                    }
                    return true;
                }

                // warn if about to exit without saving answers - check that's what the user really wants
                function nosave_exit() {
                    var conf = confirm(<?php echo js_escape($str_nosave_confirm); ?>);

                    if (conf) {
                        window.location.href = "<?php echo $GLOBALS['form_exit_url']; ?>";
                    }
                    return (conf);
                }
            </script>
            <table>
                <tr>
                    <td>
                        <hr />
                        <span id="show_phq9_score"><b><?php echo xlt("Total PHQ-9 score"); ?>:</b>
                    </td>
                </tr>
            </table>
            <script>
                manage_question_10 (<?php echo js_escape($obj["difficulty"]); ?>);
                update_score("undef", phq9_score);
            </script>
            <table>
                <tr>
                    <td>
                        <button class="btn btn-primary btn-save my-2" type="submit" value="<?php echo xla('Save Form'); ?>"><?php echo xlt('Save Form'); ?></button>
                        <button class="btn btn-secondary btn-cancel" type="button" value="<?php echo xla('Cancel'); ?>" onclick="top.restoreSession();return( nosave_exit());"><?php echo xlt('Cancel'); ?></button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php
    formFooter();
    ?>
