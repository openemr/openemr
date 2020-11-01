<?php

 /**
 * Help Icon
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if ($_SESSION ['language_direction'] == 'ltr') {
    $help_icon_title = "To enable help - Go to the User Name on top right > Settings > Features > Enable Help Modal";
} elseif ($_SESSION ['language_direction'] == 'rtl') {
    $help_icon_title = "To enable help - Go to the User Name on top left > Settings > Features > Enable Help Modal";
}

if ($GLOBALS['enable_help'] == 1) {
    $help_icon = '<a class="oe-pull-away oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#676666" title="' . xla("Click to view Help") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
} elseif ($GLOBALS['enable_help'] == 2) {
    $help_icon = '<a class="oe-pull-away oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#DCD6D0 !Important" title="' . xla($help_icon_title) . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
} elseif ($GLOBALS['enable_help'] == 0) {
    $help_icon = '';
}
