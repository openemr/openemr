<?php

/**
 * History and Physical Note form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc.php");

function history_physical_report($pid, $encounter, $cols, $id)
{
    // Fetch the history and physical note content
    $form_data = formFetch("form_history_physical", $id);

    echo "<div class='form-report'>";
    echo "<h4>History and Physical Note</h4>";

    if ($form_data) {
        // Display the content of the history_physical column
        echo nl2br(htmlspecialchars($form_data['history_physical']));
    } else {
        // Display a message if the form data was not found
        echo "<p>Could not load History and Physical Note content.</p>";
    }

    echo "</div>";
}
