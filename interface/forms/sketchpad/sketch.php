<?php
/* interface/forms/<folder_name>/sketch.php
 * This page shown when user requests a new sketchpad form and selects the desired background.
 * To adapt for other uses edit $form_name and $folder_name.
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

require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');

$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

$form_name = xlt('Sketchpad');
$folder_name = 'sketchpad';
//$bg = basename($_GET['background']);
//$dim = getimagesize($GLOBALS['fileroot'] . '/interface/forms/' . $folder_name . '/images/' . $bg);
$bg = $GLOBALS['fileroot'] . '/interface/forms/' . $folder_name . '/images/' . basename($_GET['background']);
$dim = getimagesize($bg);
$w = $dim['0'];
$h = $dim['1'];
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
  <script src="../../../library/js/canvas.js" type="text/javascript" charset="utf-8"></script>
</head>

<body class="body_top">
  <div id="title"><span class="title"><?php echo $form_name; ?></span></div><br />
  <form method="post" action="<?php echo $rootdir;?>/forms/<?php echo $folder_name; ?>/save.php?mode=new" name="sketchpad">
  <canvas id="canvas" name="canvas" width='<?php echo $w; ?>' height='<?php echo $h; ?>'></canvas><br/><br/>
  <input type="button" value="Erase" id="eraseBtn"><br/><br/>
  <input type="hidden" name="output" id="output">
  <input type="hidden" name="background" value="<?php echo attr(basename($bg)); ?>">
  <span class=text><?php echo xlt('Comments'); ?>: </span><br/>
  <textarea id="comments" name="comments" rows="6" wrap="virtual"></textarea><br/><br/>
  <a href="javascript:top.restoreSession();document.sketchpad.submit();" class="link_submit">[<?php echo xlt('Save'); ?>]</a>
  <a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link" onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save'); ?>]</a>
  </form>
  <script>
    $(document).ready(function(){
      var sketch = new Sketch("canvas");
      $("#eraseBtn").click(function(){
        sketch.clear();
      });
    });
  </script>
</body>
</html>
