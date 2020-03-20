<?php
/**
 * Panels
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;
?>
<html>
<head>
<?php
// TODO add code to allow add or remove a patient from a panel
// TODO fix the header

?>

<?php Header::setupHeader(['datetime-picker', 'select2']); ?>

<style>
.highlight {
  color: green;
}
tr.selected {
  background-color: white;
}
</style>

</head>

<body class="body_top">

    <span class="title"><?php echo xlt('Panels'); ?></span>

</body>

</html>
