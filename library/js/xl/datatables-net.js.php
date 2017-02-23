<?php
/**
 *
 * This is to allow internationalization by OpenEMR of the datatables-net asset.
 *
 * Example code in script:
 *    require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); (php command)
 *
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
"oLanguage": {
    "sSearch"      : "<?php echo xla('Search all columns'); ?>:",
    "sLengthMenu"  : "<?php echo xla('Show') . ' _MENU_ ' . xla('entries'); ?>",
    "sZeroRecords" : "<?php echo xla('No matching records found'); ?>",
    "sInfo"        : "<?php echo xla('Showing') . ' _START_ ' . xla('to{{range}}') . ' _END_ ' . xla('of') . ' _TOTAL_ ' . xla('entries'); ?>",
    "sInfoEmpty"   : "<?php echo xla('Nothing to show'); ?>",
    "sInfoFiltered": "(<?php echo xla('filtered from') . ' _MAX_ ' . xla('total entries'); ?>)",
    "oPaginate": {
        "sFirst"   : "<?php echo xla('First'); ?>",
        "sPrevious": "<?php echo xla('Previous'); ?>",
        "sNext"    : "<?php echo xla('Next'); ?>",
        "sLast"    : "<?php echo xla('Last'); ?>"
    }
}