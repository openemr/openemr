<?php

/**
 * /template_menu.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

foreach (glob($GLOBALS['OE_SITE_DIR'] . "/documents/onsite_portal_documents/templates/*.tpl") as $filename) {
    $basefile = basename($filename, ".tpl");
    $btnname = str_replace('_', ' ', $basefile);
    $btnfile = $basefile . '.tpl';

    echo '<li class="nav-item px-1 py-1 py-md-0"><a class="nav-link text-success btn btn-outline-success" id="' . $basefile . '"' . 'href="#" onclick="page.newDocument(' . "<%= cpid %>,'<%= cuser %>','$btnfile')" . ';"' . ">$btnname</a></li>";
}

foreach (glob($GLOBALS['OE_SITE_DIR'] . "/documents/onsite_portal_documents/templates/" . $pid . "/*.tpl") as $filename) {
    $basefile = basename($filename, ".tpl");
    $btnname = str_replace('_', ' ', $basefile);
    $btnfile = $basefile . '.tpl';

    echo '<li class="nav-item px-1 py-1 py-md-0"><a class="nav-link text-success btn btn-outline-success" id="' . $basefile . '"' . 'href="#" onclick="page.newDocument(' . "<%= cpid %>,'<%= cuser %>','$btnfile')" . '";' . ">$btnname</a></li>";
}
