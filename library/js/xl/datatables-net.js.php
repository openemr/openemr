<?php

/**
 *
 * This is to allow internationalization by OpenEMR of the datatables-net asset.
 *
 * Example code in script:
 *    $translationsDatatablesOverride = array('search'=>(xla('Search all columns') . ':')) (optional php command)
 *    require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); (php command)
 *
 * Note there is a optional mechanism to override translations via the
 *  $translationsDatatablesOverride array.
 *
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
"language": {
    "emptyTable":     <?php echo xlj('No data available in table'); ?>,
    "info":           <?php echo xlj('Showing'); ?> + " _START_ " + <?php echo xlj('to{{range}}'); ?> + " _END_ " + <?php echo xlj('of'); ?> + " _TOTAL_ " + <?php echo xlj('entries'); ?>,
    "infoEmpty":      <?php echo xlj('Showing 0 to 0 of 0 entries'); ?>,
    "infoFiltered":   "(" + <?php echo xlj('filtered from'); ?> + " _MAX_ " + <?php echo xlj('total entries'); ?> + ")",
    "lengthMenu":     <?php echo xlj('Show'); ?> + " _MENU_ " + <?php echo xlj('entries'); ?>,
    "loadingRecords": <?php echo xlj('Loading'); ?> + "...",
    "processing":     <?php echo xlj('Processing'); ?> + "...",
    "search":         <?php echo xlj('Search'); ?> + ":",
    "zeroRecords":    <?php echo xlj('No matching records found'); ?>,
    "paginate": {
        "first":      <?php echo xlj('First'); ?>,
        "last":       <?php echo xlj('Last'); ?>,
        "next":       <?php echo xlj('Next'); ?>,
        "previous":   <?php echo xlj('Previous'); ?>
    },
    "aria": {
        "sortAscending":  ": " + <?php echo xlj('activate to sort column ascending'); ?>,
        "sortDescending": ": " + <?php echo xlj('activate to sort column descending'); ?>
    }
    <?php
    if (!empty($translationsDatatablesOverride)) {
        foreach ($translationsDatatablesOverride as $key => $value) {
            echo ', ' . js_escape($key) . ': ' . js_escape($value);
        }
    }
    ?>
}
