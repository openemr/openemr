<?php
/* interface/forms/<folder_name>/report.php
 * This page shown when printing patient reports and on encounter summary page.
 * To adapt for other uses edit function name, $form_name and $folder_name.
 * !!! Requires appropriately referenced custom canvas.js file !!!
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  David Hantke
 * @link    http://www.open-emr.org
 */

require_once("../../globals.php");
require_once($GLOBALS['srcdir'].'/api.inc');

function sketchpad_report($pid, $encounter, $cols=2, $id) {
	$form_name = xlt('Sketchpad');
	$folder_name = 'sketchpad';
	$table_name = 'form_' . $folder_name;

	$data = formFetch($table_name, $id);
    if ($data) {

        // data retrieval and manipulation
	    $output = $data['output'];
	    $comments = $data['comments'];
	    $bg = $GLOBALS['webroot'] . '/interface/forms/' . $folder_name . '/images/' . $data['background'];
	    $ts = strtotime($data['date']); // for appending timestamp to create unique canvas id
        $dim = getimagesize($GLOBALS['fileroot'] . '/interface/forms/' . $folder_name . '/images/' . $data['background']);
	    $w = $dim['0'];
	    $h = $dim['1'];

        echo '<!DOCTYPE html>';
        echo '<html>';
        echo '<head>';
		echo '<link rel="stylesheet" href="../../forms/' . $folder_name . '/style.css" type="text/css"/>';
        echo '<style>'; // variables seemingly aren't carried over into external css files, so define the styles here
        echo '#canvas_' . $ts . ' {';
        echo    'background: url("' . $bg . '");';
        echo    'border: 1px solid black;';
        echo '}';
        echo '#comments {';
        echo    'width:' . $w . 'px;';
        echo '}';
        echo '</style>';
        echo '<script src="../../../public/assets/jquery-min-3-1-1/index.js" type="text/javascript" charset="utf-8"></script>';
        echo '<script src="./js/canvas.js" type="text/javascript" charset="utf-8"></script>';
        echo '</head>';

        echo '<body>';

        if ($output) {
            echo '<canvas id="canvas_' . $ts . '" width="' . $w . '" height="' . $h . '"></canvas><br/>';
        }

        if ($comments) {
            echo '<table width=' . $w . '>';
            echo '<td><span class="bold">' . xlt("Comments"). ':&nbsp</span><span class="text">' . $comments . '</span></td>';
            echo '</table>';
        }
        echo '<input type="hidden" id="output">';

        echo '<script>';
            echo '$(document).ready(function() {';
                echo 'var sketch = new Sketch("canvas_' . $ts . '");';
                echo 'var output = "' . $output . '";';
                echo 'sketch.loadJSON(output);';
            echo '});';
        echo '</script>';

        echo '</body>';
        echo '</html>';
    }
}
?>
