<?php

/**
 * gad-7 form using forms api     new.php    create a new form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ruth Moulton <moulton ruth@muswell.me.uk>
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2021 ruth moulton <ruth@muswell.me.uk>
 * @copyright Copyright (c) 2022 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("gad7.inc.php"); //common strings, require_once(globals.php), other includes  etc

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
?>
<html>
<head>
    <title><?php echo text($string_form_title); ?></title>
    <?php Header::setupHeader(); ?>
    <script>
    var no_qs = 8; // number of questions in the form
    var gad7_score = 0; // total score
    </script>

    <script src="<?php echo $rootdir;?>/forms/gad7/gad7_javasrc.js?v=<?php echo $GLOBALS['v_js_includes'];?>"></script>
</head>
<body class="body_top">

<div class="container">

    <form method="post" action="<?php echo $rootdir;?>/forms/gad7/save.php?mode=new" name="my_form" onSubmit="return(check_all());" >
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

        <h1><?php echo text($str_form_name);?></h1>

        <div class="row my-2">
            <div class="col-12">
                <div class="alert alert-info alert-dismissible show">
                    <i class="fa fa-info-circle"></i>&nbsp;<?php echo text($disclaimer); ?>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-12">
                <h2 class="lead"><?php echo xlt('Over the last 2 weeks, how often have you been bothered by the following problems'); ?></h2>
            </div>
        </div>
        <div class="form-group row">
            <label for="nervous_score" class="col-sm-12 col-md-6 col-form-label"><?php echo xlt('Feeling nervous, anxious, or on edge'); ?></label>
            <div class="col-sm-12 col-md-6 col-form-control">
                <select name="nervous_score" id="nervous_score" class="select2 form-control" onchange="update_score(0, my_form.nervous_score.value);">
                    <option selected value="undef"><?php echo text($str_default); ?></option>
                    <option value="0"><?php echo text($str_not); ?></option>
                    <option value="1"><?php echo text($str_several); ?></option>
                    <option value="2"><?php echo text($str_more); ?></option>
                    <option value="3"><?php echo text($str_nearly); ?></option>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="control_worry_score" class="col-sm-12 col-md-6 col-form-label"><?php echo xlt('Not being able to stop or control worrying'); ?></label>
            <div class="col-sm-12 col-md-6 col-form-control">
                <select name="control_worry_score" class="select2 form-control" onchange="update_score(1, my_form.control_worry_score.value);" >
                    <option selected value="undef"><?php echo text($str_default); ?></option>
                    <option value="0"><?php echo text($str_not); ?></option>
                    <option value="1"><?php echo text($str_several); ?></option>
                    <option value="2"><?php echo text($str_more); ?></option>
                    <option value="3"><?php echo text($str_nearly); ?></option>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="worry_score" class="col-sm-12 col-md-6 col-form-label"><?php echo xlt('Worrying too much about different things'); ?></label>
            <div class="col-sm-12 col-md-6 col-form-control">
                <select name="worry_score" id="worry_score" class="select2 form-control" onchange="update_score(2, my_form.worry_score.value);" >
                    <option selected value="undef" ><?php echo text($str_default); ?></option>
                    <option value="0"><?php echo text($str_not); ?></option>
                    <option value="1"><?php echo text($str_several); ?></option>
                    <option value="2"><?php echo text($str_more); ?></option>
                    <option value="3"><?php echo text($str_nearly); ?></option>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="" class="col-sm-12 col-md-6 col-form-label"><?php echo xlt('Trouble relaxing'); ?></label>
            <div class="col-sm-12 col-md-6 col-form-control">
                <select name="relax_score" class="select2 form-control" onchange="update_score(3, my_form.relax_score.value);">
                    <option selected value="undef" ><?php echo text($str_default); ?></option>
                    <option value="0"><?php echo text($str_not); ?></option>
                    <option value="1"><?php echo text($str_several); ?></option>
                    <option value="2"><?php echo text($str_more); ?></option>
                    <option value="3"><?php echo text($str_nearly); ?></option>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="" class="col-sm-12 col-md-6 col-form-label"><?php echo xlt('Being so restless that it\'s hard to sit still'); ?></label>
            <div class="col-sm-12 col-md-6 col-form-control">
                <select name="restless_score" class="selec2 form-control" onchange="update_score(4, my_form.restless_score.value);">
                    <option selected value="undef" ><?php echo text($str_default); ?></option>
                    <option value="0"><?php echo text($str_not); ?></option>
                    <option value="1"><?php echo text($str_several); ?></option>
                    <option value="2"><?php echo text($str_more); ?></option>
                    <option value="3"><?php echo text($str_nearly); ?></option>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="" class="col-sm-12 col-md-6 col-form-label"><?php echo xlt('Becoming easily annoyed or irritable'); ?></label>
            <div class="col-sm-12 col-md-6 col-form-control">
                <select name="irritable_score" class="selec2 form-control" onchange="update_score(5, my_form.irritable_score.value);">
                    <option selected value="undef" ><?php echo text($str_default); ?></option>
                    <option value="0"><?php echo text($str_not); ?></option>
                    <option value="1"><?php echo text($str_several); ?></option>
                    <option value="2"><?php echo text($str_more); ?></option>
                    <option value="3"><?php echo text($str_nearly); ?></option>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="" class="col-sm-12 col-md-6 col-form-label"><?php echo xlt('Feeling afraid as if something awful might happen'); ?></label>
            <div class="col-sm-12 col-md-6 col-form-control">
                <select name="fear_score" class="selec2 form-control" onchange="update_score(6, my_form.fear_score.value);">
                    <option selected value="undef" ><?php echo text($str_default); ?></option>
                    <option value="0"><?php echo text($str_not); ?></option>
                    <option value="1"><?php echo text($str_several); ?></option>
                    <option value="2"><?php echo text($str_more); ?></option>
                    <option value="3"><?php echo text($str_nearly); ?></option>
                </select>
            </div>
        </div>
        <div class="form-group row d-none" id="q8_container">
            <label for="" class="col-sm-12 col-md-6 col-form-label"><?php echo $str_q8; ?>.<br><span class="small italic"><?php echo $str_q8_2;?></span></label>
            <div class="col-sm-12 col-md-6 col-form-control" id="q8_place">
                <select name="difficulty" id="difficulty" class="select2 form-control" onchange="record_score_q8(my_form.difficulty.value);">
                    <option value="undef"><?php echo text($str_default);  ?></option>
                    <option value="0"><?php echo text($str_not); ?></option>
                    <option value="1"><?php echo text($str_somewhat); ?></option>
                    <option value="2"><?php echo text($str_very); ?></option>
                    <option value="3"><?php echo text($str_extremely);?></option>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-12 col-md-6 offset-md-6 my-3">
                <span id="show_gad7_score"><?php echo xlt("Total GAD-7 score"); ?>:</span>&nbsp;<span id="score" class="font-weight-bold"></span>
            </div>
        </div>
        <div class="form-group row mt-3">
            <div class="col-sm-12 col-md-6 offset-md-6">
                <button type="submit" class="btn btn-primary btn-save"><?php echo xl("Save Form");?></button>
                <button type="button" class="btn btn-link btn-cancel" onclick="top.restoreSession();return( nosave_exit());"><?php echo $str_nosave_exit;?></button>
            </div>
        </div>
    </form>
</div>

<script>
update_score("undef", gad7_score);

function check_all() {
    // has each question been answered and save scores
    var  flag=false;
    var list='';
    for (i=0; i<(no_qs-1); i++) { // last questionis optional
        if (!all_answered[i] ) {
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
    return (conf);
}
</script>

<?php formFooter(); ?>
