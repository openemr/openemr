<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */

foreach (glob($GLOBALS['OE_SITE_DIR'] . "/documents/onsite_portal_documents/templates/*.tpl") as $filename) {
    $basefile = basename($filename, ".tpl");
    $btnname = str_replace('_', ' ', $basefile);
    $btnfile = $basefile . '.tpl';

    echo '<li class="bg-success"><a id="' . $basefile . '"' . 'href="#" onclick="page.newDocument(' . "<%= cpid %>,'<%= cuser %>','$btnfile')".'"'.">$btnname</a></li>";
}

foreach (glob($GLOBALS['OE_SITE_DIR'] . "/documents/onsite_portal_documents/templates/" . $pid . "/*.tpl") as $filename) {
    $basefile = basename($filename, ".tpl");
    $btnname = str_replace('_', ' ', $basefile);
    $btnfile = $basefile . '.tpl';

    echo '<li class="bg-success"><a id="' . $basefile . '"' . 'href="#" onclick="page.newDocument(' . "<%= cpid %>,'<%= cuser %>','$btnfile')".'"'.">$btnname</a></li>";
}
