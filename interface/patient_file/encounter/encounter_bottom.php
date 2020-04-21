<?php

/**
 * encounter_bottom.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/encounter.inc");
?>
<html>
<head>
</head>
<frameset rows="*" cols="200,400,*">
    <?php
    echo '<frame src="coding.php" name="Codesets" scrolling="auto">';
    echo '<frame src="blank.php" name="Codes" scrolling="auto">';
    echo '<frame src="diagnosis.php" name="Diagnosis" scrolling="auto"> ';
    ?>
</frameset>
</html>
