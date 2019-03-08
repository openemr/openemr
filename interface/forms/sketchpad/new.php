<?php
/* interface/forms/<folder_name>/new.php
 * Displays selection menu for sketchpad canvas backgrounds based on contents of images folder.
 * To adapt for other uses edit $form_name and $folder_name.
 * !!! Sketchpad equires appropriately referenced custom canvas.js file !!!
 *
 * Sketchpad new script. Inital page shown when user requests a new sketchpad form.
 * Displays menu for selection of different backgrounds according to contents of images
 * subfolder. Images should be named without spaces (use underscore if necessary) and
 * without parentheses. At the very least, both jpeg and png files can be used for
 * backgrounds. Other formats have not been tested.
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

$backgrounds = glob($GLOBALS['fileroot'] . '/interface/forms/' . $folder_name . '/images/*');
?>

<!DOCTYPE html>
<html>
<head>
</head>
<body class="body_top">
	<div id="title">
		<a href="<?php echo $returnurl; ?>" onclick="top.restoreSession()">
			<span class="title"><?php echo $form_name; ?></span> <span class="back">(<?php echo xlt('Back'); ?>)</span>
		</a>
	</div><br />
	<form method="get" action="<?php echo $rootdir;?>/forms/<?php echo $folder_name; ?>/sketch.php?mode=new">
		<?php foreach ($backgrounds as $key => $value): ?>
			<input type="radio" name="background"  value="<?php echo $value; ?>"
				style="margin-left:3em"</input> <?php echo str_replace('_',' ',substr(basename($value),0,(strpos(basename($value),'.')))); ?><br />
		<?php endforeach; ?><br />
		<input type="submit" value="OK" style="margin-left:3em"></input>
	</form>
</body>
</html>
