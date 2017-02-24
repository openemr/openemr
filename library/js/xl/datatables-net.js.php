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
 * Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */
?>
"language": {
    "emptyTable":     "<?php echo xla('No data available in table'); ?>",
    "info":           "<?php echo xla('Showing') . ' _START_ ' . xla('to{{range}}') . ' _END_ ' . xla('of') . ' _TOTAL_ ' . xla('entries'); ?>",
    "infoEmpty":      "<?php echo xla('Showing 0 to 0 of 0 entries'); ?>",
    "infoFiltered":   "(<?php echo xla('filtered from') . ' _MAX_ ' . xla('total entries'); ?>)",
    "lengthMenu":     "<?php echo xla('Show') . ' _MENU_ ' . xla('entries'); ?>",
    "loadingRecords": "<?php echo xla('Loading'); ?>...",
    "processing":     "<?php echo xla('Processing'); ?>...",
    "search":         "<?php echo xla('Search'); ?>:",
    "zeroRecords":    "<?php echo xla('No matching records found'); ?>",
    "paginate": {
        "first":      "<?php echo xla('First'); ?>",
        "last":       "<?php echo xla('Last'); ?>",
        "next":       "<?php echo xla('Next'); ?>",
        "previous":   "<?php echo xla('Previous'); ?>"
    },
    "aria": {
        "sortAscending":  ": <?php echo xla('activate to sort column ascending'); ?>",
        "sortDescending": ": <?php echo xla('activate to sort column descending'); ?>"
    }
    <?php
    if (!empty($translationsDatatablesOverride)) {
        foreach ($translationsDatatablesOverride as $key => $value) {
            echo ', "' . $key . '": "' . $value . '"';
        }
    }
    ?>
}