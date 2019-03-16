<?php
/* interface/forms/<folder_name>/report.php
 * This page shown when printing patient reports and on encounter summary page.
 * To adapt for other uses edit function name, $form_name and $folder_name.
 * !!! Requires custom canvas.js file placed in openemr/library/js folder !!!
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

use OpenEMR\Core\Header;

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
?>
        <!DOCTYPE html>
        <html>
        <head>
        <?php Header::setupHeader(); ?>
		<link rel="stylesheet" href="../../forms/<?php echo $folder_name; ?>/style.css" type="text/css"/>
        <style>
            #canvas_<?php echo $ts; ?> {
                background: url("<?php echo $bg; ?>");
                border: 1px solid black;
            }
            #comments {
                width:<?php echo $w; ?>px;
            }
        </style>
        <script src="../../../library/js/canvas.js" type="text/javascript" charset="utf-8"></script>
        </head>

        <body>

        <?php if ($output) { ?>
            <canvas id="canvas_<?php echo $ts; ?>" width="<?php echo $w; ?>" height="<?php echo $h; ?>"></canvas><br/>
        <?php }

        if ($comments) { ?>
            <table width=<?php echo $w; ?>>
            <td><span class="bold"><?php echo xlt("Comments"); ?>:&nbsp</span><span class="text"><?php echo $comments; ?></span></td>
            </table>
        <?php } ?>
        <input type="hidden" id="output">

        <script>
            $(document).ready(function() {
                var sketch = new Sketch("canvas_<?php echo $ts; ?>");
                var output = "<?php echo $output; ?>";
                sketch.loadJSON(output);
            });
        </script>

        </body>
        </html>
<?php
    }
}
?>
