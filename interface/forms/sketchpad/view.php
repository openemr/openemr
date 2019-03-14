<?php
/* interface/forms/<folder_name>/view.php
 * Displays editable sketch accessed from encounter view.
 * To adapt for other uses edit $form_name and $folder_name.
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

$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

$form_name = xlt('Sketchpad');
$folder_name = 'sketchpad';
$table_name = 'form_' . $folder_name;

$data = formFetch($table_name, $_GET["id"]);
$output = $data['output'];
$comments = $data['comments'];
$bg = $GLOBALS['fileroot'] . '/interface/forms/' . $folder_name . '/images/' . $data['background'];
$dim = getimagesize($bg);
$w = $dim[0];
$h = $dim[1];
$bg = $GLOBALS['webroot'] . '/interface/forms/' . $folder_name . '/images/' . basename($bg);
?>

<!DOCTYPE html>
<html>
<head>
  <title><?php echo $form_name; ?></title>
  <style>
    #canvas {
      background: url("<?php echo $bg; ?>");
      /* Prevent nearby text being highlighted when accidentally dragging mouse outside confines of the canvas */
      -webkit-touch-callout: none;
      -webkit-user-select: none;
      -khtml-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
    }
    #comments {
      width:<?php echo $w; ?>px
    }
  </style>
  <link rel="stylesheet" href="../../forms/<?php echo $folder_name; ?>/style.css" type="text/css"/>
  <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
  <script src="../../../public/assets/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
  <script src="./js/canvas.js" type="text/javascript" charset="utf-8"></script>
</head>
<body>
  <div id="title">
  <a href="<?php echo $returnurl; ?>" onclick="top.restoreSession()">
    <span class="title"><?php echo $form_name; ?></span> <span class="back">(<?php echo xlt('Back'); ?>)</span>
  </a></div><br/>
  <form method=post action="<?php echo $rootdir?>/forms/<?php echo attr($folder_name); ?>/save.php?mode=update&id=<?php echo attr($_GET["id"]);?>"
	name="<?php echo $folder_name ?>">
    <canvas id="canvas" width="<?php echo $w; ?>" height="<?php echo $h; ?>"></canvas><br/>
    <input type="button" value="Erase" id="eraseBtn"><br/><br/>
    <span class=text><?php echo xlt('Comments'); ?>: </span><br/>
    <textarea id="comments" name="comments" rows="6"><?php echo $comments; ?></textarea><br/><br/>
    <input type="hidden" id="output" name="output">
    <input type="hidden" id="background" name="background" value="<?php echo $data['background']; ?>">
    <a href="javascript:top.restoreSession();document.<?php echo $folder_name ?>.submit();" class="link_submit">[<?php echo xlt('Save'); ?>]</a>
    <a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link" onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save'); ?>]</a>
  </form>
  <script>
    $(document).ready(function(){
      var sketch = new Sketch("canvas");
      var output = "<?php echo $output; ?>";
      sketch.loadJSON(output);
      $("#eraseBtn").click(function(){
        sketch.clear();
      });
    });
  </script>
</body>
</html>
