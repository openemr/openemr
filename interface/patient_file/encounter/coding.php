<?php

/**
 * coding.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("../../../custom/code_types.inc.php");

use OpenEMR\Core\Header;
?>
<html>
<head>
<?php Header::setupHeader(); ?>

<!-- DBC STUFF ================ -->

</head>
<body class="body_bottom">

<dl>
<dt><span href="coding.php" class="title"><?php echo xlt('Coding'); ?></span></dt>

<dd><a class="text" href="superbill_codes.php"
 target="_parent"
 onclick="top.restoreSession()">
<?php echo xlt('Superbill'); ?></a></dd>

<?php foreach ($code_types as $key => $value) { ?>
<dd><a class="text" href="search_code.php?type=<?php echo attr_url($key); ?>"
 target="Codes" onclick="top.restoreSession()">
    <?php echo text($key); ?> <?php echo xlt('Search'); ?></a></dd>
<?php } ?>

<dd><a class="text" href="copay.php" target="Codes" onclick="top.restoreSession()"><?php echo xlt('Copay'); ?></a></dd>
<dd><a class="text" href="other.php" target="Codes" onclick="top.restoreSession()"><?php echo xlt('Other'); ?></a></dd><br />

<?php if (!$GLOBALS['disable_prescriptions']) { ?>
<dt><span href="coding.php" class="title"><?php echo xlt('Prescriptions'); ?></span></dt>
<dd><a class="text" href="<?php echo $GLOBALS['webroot']?>/controller.php?prescription&list&id=<?php echo attr_url($pid); ?>"
 target="Codes" onclick="top.restoreSession()"><?php echo xlt('List Prescriptions'); ?></a></dd>
<dd><a class="text" href="<?php echo $GLOBALS['webroot']?>/controller.php?prescription&edit&id=&pid=<?php echo attr_url($pid); ?>"
 target="Codes" onclick="top.restoreSession()"><?php echo xlt('Add Prescription'); ?></a></dd>
<?php }; // if (!$GLOBALS['disable_prescriptions']) ?>
</dl>

</body>
</html>
