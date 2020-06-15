<?php

/**
 * Internationalization by OpenEMR of the dygraphs asset.
 *
 * Example of use. When using dygraph asset, place following line before inclusion of dygraphs.js:
 *   require $GLOBALS['srcdir'] . '/js/xl/dygraphs.js.php'; (php command)
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>

<script>
    // Support for translations of months in graphing dygraphs scripts
    var SHORT_MONTH_NAMES_CUSTOM = [<?php echo xlj('Jan'); ?>, <?php echo xlj('Feb'); ?>, <?php echo xlj('Mar'); ?>, <?php echo xlj('Apr'); ?>, <?php echo xlj('May'); ?>, <?php echo xlj('Jun'); ?>, <?php echo xlj('Jul'); ?>, <?php echo xlj('Aug'); ?>, <?php echo xlj('Sep'); ?>, <?php echo xlj('Oct'); ?>, <?php echo xlj('Nov'); ?>, <?php echo xlj('Dec'); ?>];
    // Dygraph xlabel translation
    var xlabel_translate = <?php echo xlj('Zoom: click-drag, Pan: shift-click-drag, Restore: double-click'); ?>;
</script>
