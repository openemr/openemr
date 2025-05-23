<?php

/**
 * Expand Contract State
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if ($current_state) {
    $container = 'container-fluid';
    $expand_title = xl('Click to Contract and set to henceforth open in Centered mode');
    $expand_icon_class = 'fa-compress';
} else {
    $container = 'container';
    $expand_title = xl('Click to Expand and set to henceforth open in Expanded mode');
    $expand_icon_class = 'fa-expand';
}
