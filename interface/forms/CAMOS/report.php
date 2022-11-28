<?php

/**
 * CAMOS report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Mark Leeds <drleeds@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2009 Mark Leeds <drleeds@gmail.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../../globals.php');
require_once("../../../library/api.inc.php");
require_once("content_parser.php");

function CAMOS_report($pid, $encounter, $cols, $id)
{
    $data = formFetch("form_CAMOS", $id);
    if ($data) {
        echo "<div class='navigateLink'><a href='" . $GLOBALS['webroot'] .
        "/interface/forms/CAMOS/rx_print.php?sigline=embossed' target=_new>" . xlt('Rx') . "</a>\n";
        echo " | ";
        echo "<a href='" . $GLOBALS['webroot'] .
        "/interface/forms/CAMOS/rx_print.php?sigline=signed' target=_new>" . xlt('Signed Rx') . "</a>\n";
        echo "<br />";
        echo "<a href='" . $GLOBALS['webroot'] .
        "/interface/forms/CAMOS/rx_print.php?letterhead=true&signer=patient' target=_new>" . xlt('Letterhead that patient signs') . "</a>\n";
        echo " | ";
        echo "<a href='" . $GLOBALS['webroot'] .
        "/interface/forms/CAMOS/rx_print.php?letterhead=true&signer=doctor' target=_new>" . xlt('Letterhead that doctor signs') . "</a>\n";
        echo "<br />";
        echo "<a href='" . $GLOBALS['webroot'] .
        "/interface/forms/CAMOS/notegen.php?pid=" . attr_url($pid) . "&encounter=" . attr_url($encounter) . "' target=_new>" . xlt('Print This Encounter') . "</a>\n";
        echo " | ";
        echo "<a href='" . $GLOBALS['webroot'] .
        "/interface/forms/CAMOS/notegen.php' target=_new>" . xlt('Print Any Encounter') . "</a></div>\n";
        echo "<pre>" . text(wordwrap(stripslashes(replace($pid, $encounter, $data['content'])))) . "</pre><hr>\n";
    }
}
