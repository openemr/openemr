<?php

/**
 * _FormsHeader.tpl.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />

<title><?php $this->eprint($this->title); ?></title>

<meta name="description" content="OpenEMR Portal" />
<meta name="author" content="Form | sjpadgett@gmail.com" />

<!-- Styles -->

<?php
if ($_SESSION['patient_portal_onsite_two'] ?? 0) {
    Header::setupHeader(['no_main-theme', 'portal-theme', 'datetime-picker', 'moment']);
} else {
    Header::setupHeader(['datetime-picker', 'moment']);
}
?>

<script src="<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/libs/LAB.min.js"></script>
<script>
    $LAB.script("<?php echo $GLOBALS['assets_static_relative']; ?>/underscore/underscore-min.js").wait()
        .script("<?php echo $GLOBALS['assets_static_relative']; ?>/backbone/backbone-min.js")
        .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app.js?v=<?php echo $GLOBALS['v_js_includes']; ?>")
        .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/model.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()
        .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/view.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait();
</script>

</head>
<body>
