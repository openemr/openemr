<?php

/**
 * Dash Board Header Simple.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>

<?php
if ($GLOBALS['enable_help'] == 1) {
    $help_icon = '<a class="oe-pull-away oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color: var(--gray)" title="' . xla("Click to view Help") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
} elseif ($GLOBALS['enable_help'] == 2) {
    $help_icon = '<a class="oe-pull-away oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color: var(--light) !important" title="' . xla("To enable help - Go to  Administration > Globals > Features > Enable Help Modal") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
} elseif ($GLOBALS['enable_help'] == 0) {
    $help_icon = '';
}
?>
<?php
if ($expandable == 1) {?>
<div class="clearfix">
    <h2 id="header_title" class="clearfix"><span id='header_text'><?php echo text($header_title)?><?php echo " " . text(getPatientNameFirstLast($pid));?></span>  <i id="exp_cont_icon" class="fa <?php echo attr($expand_icon_class);?> oe-superscript-small expand_contract" title="<?php echo attr($expand_title); ?>" aria-hidden="true"></i><a href='<?php echo attr($go_back_href)?>' onclick='top.restoreSession()'  title="<?php echo xla("Go back")?>" ><i id='advanced-tooltip' class='fa fa-undo fa-2x small'></i></a><?php echo $help_icon; ?>
    </h2>
</div>

    <?php
} else {?>
<div class="clearfix">
    <h2 id="header_title" class="clearfix"><span id='header_text'><?php echo text($header_title)?><?php echo " " . text(getPatientNameFirstLast($pid));?></span>&nbsp;&nbsp;  <a href='<?php echo attr($go_back_href)?>' onclick='top.restoreSession()'  title="<?php echo xla("Go back")?>" ><i id='advanced-tooltip' class='fa fa-undo fa-2x small'></i></a><?php echo $help_icon; ?></h2>
</div>
    <?php
}?>
