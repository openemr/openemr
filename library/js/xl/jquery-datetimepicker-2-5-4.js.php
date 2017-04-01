<?php
/**
 *
 * This is to allow internationalization by OpenEMR of the jquery-datetimepicker.
 * (with and without a time selector)
 *
 * Example code in script:
 *  $('.datetimepicker').datetimepicker({
 *    $datetimepicker_timepicker = true; (php variable)
 *    $datetimepicker_showseconds = false; (php variable)
 *    $datetimepicker_formatInput = false; (php variable)
 *    require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); (php command)
 *    can add any additional settings to datetimepicker here; need to prepend first setting with a comma
 *  });
 *  $('.datepicker').datetimepicker({
 *    $datetimepicker_timepicker = false; (php variable)
 *    $datetimepicker_showseconds = false; (php variable)
 *    $datetimepicker_formatInput = false; (php variable)
 *    require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); (php command)
 *    can add any additional settings to datetimepicker here; need to prepend first setting with a comma
 *  });
 *
 * $datetimepicker_timepicker - this will set whether to use the timepicker
 * $datetimepicker_showseconds - this will show seconds if using the timepicker
 * $datetimepicker_formatInput - this will set whether to format the input to
 *  the user selected date format within globals. (This works with the following functions to fully
 *  support internationalization of dates; note this setting does not yet work with the timepicker yet)
 *   -oeFormatShortDate() function for when placing a default formatted date in the field
 *   -DateToYYYYMMDD() function when insert the formatted date into database or codebase works on it
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
    yearStart: '1900',
    rtl: <?php echo ($_SESSION['language_direction'] == 'rtl') ? "true" : "false"; ?>,
    <?php if ($datetimepicker_timepicker) { ?>
        <?php if ($datetimepicker_showseconds) { ?>
            format: 'Y-m-d H:i:s',
        <?php } else { ?>
            format: 'Y-m-d H:i',
        <?php } ?>
        timepicker:true,
        step: '30'
    <?php } else { ?>
        <?php if ($datetimepicker_formatInput) { ?>
            format: '<?php echo DateFormatRead("jquery-datetimepicker"); ?>',
        <?php } else { ?>
            format: 'Y-m-d',
        <?php } ?>
        timepicker:false
    <?php } ?>
