<?php
/**
 *
 * This is to allow internationalization by OpenEMR of the jquery-datetimepicker.
 * (with and without a time selector)
 *
 * Example code in script:
 *  $('.datetimepicker').datetimepicker({
 *    $datetimepicker_timepicker = true; (php variable)
 *    $datetimepicker_formatInput = false; (php variable)
 *    require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); (php command)
 *    can add any additional settings to datetimepicker here; need to prepend first setting with a comma
 *  });
 *
 * $datetimepicker_timepicker - this will set whether to use the timepicker
 * $datetimepicker_formatInput - this will set whether to format the input to
 *  the user selected date format within globals.(TODO: this setting has not
 *  yet been supported, but will be supported after more testing)
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
    i18n:{
        en: {
            months: [
                "<?php echo xla('January'); ?>", "<?php echo xla('February'); ?>", "<?php echo xla('March'); ?>", "<?php echo xla('April'); ?>", "<?php echo xla('May'); ?>", "<?php echo xla('June'); ?>", "<?php echo xla('July'); ?>", "<?php echo xla('August'); ?>", "<?php echo xla('September'); ?>", "<?php echo xla('October'); ?>", "<?php echo xla('November'); ?>", "<?php echo xla('December'); ?>"
            ],
            dayOfWeekShort: [
                "<?php echo xla('Sun'); ?>", "<?php echo xla('Mon'); ?>", "<?php echo xla('Tue'); ?>", "<?php echo xla('Wed'); ?>", "<?php echo xla('Thu'); ?>", "<?php echo xla('Fri'); ?>", "<?php echo xla('Sat'); ?>"
            ],
            dayOfWeek: ["<?php echo xla('Sunday'); ?>", "<?php echo xla('Monday'); ?>", "<?php echo xla('Tuesday'); ?>", "<?php echo xla('Wednesday'); ?>", "<?php echo xla('Thursday'); ?>", "<?php echo xla('Friday'); ?>", "<?php echo xla('Saturday'); ?>"
            ]
        },
    },
    rtl: <?php echo ($_SESSION['language_direction'] == 'rtl') ? "true" : "false"; ?>,
    <?php if ($datetimepicker_timepicker) { ?>
        format: 'Y-m-d H:i',
        timepicker:true
    <?php } else { ?>
        format: 'Y-m-d',
        timepicker:false
    <?php } ?>
