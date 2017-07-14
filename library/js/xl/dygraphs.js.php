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

<script type="text/javascript">
    // Support for translations of months in graphing dygraphs scripts
    var SHORT_MONTH_NAMES_CUSTOM = ['<?php echo xla('Jan'); ?>', '<?php echo xla('Feb'); ?>', '<?php echo xla('Mar'); ?>', '<?php echo xla('Apr'); ?>', '<?php echo xla('May'); ?>', '<?php echo xla('Jun'); ?>', '<?php echo xla('Jul'); ?>', '<?php echo xla('Aug'); ?>', '<?php echo xla('Sep'); ?>', '<?php echo xla('Oct'); ?>', '<?php echo xla('Nov'); ?>', '<?php echo xla('Dec'); ?>'];
    // Dygraph xlabel translation
    var xlabel_translate = '<?php echo xla('Zoom: click-drag, Pan: shift-click-drag, Restore: double-click'); ?>';
</script>
