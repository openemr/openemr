<?php
/**
 *
 * This is to allow internationalization by OpenEMR of the jquery-datetimepicker.
 * (with and without a time selector)
 * This is the alternate template when do not have access to the javascript and
 * need to instead send variables to the javascript script.
 *
 * Example code in the script that will use the picker:
 *    require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-alternate-2-5-4.js.php'); (php command)
 *
 * Example code in the javascript for datepicker:
 *    $('.jquery-date-picker').datetimepicker({
 *        i18n:{
 *            en: {
 *                months: datepicker_xlMonths,
 *                dayOfWeekShort: datepicker_xlDayofwkshort,
 *                dayOfWeek: datepicker_xlDayofwk
 *            },
 *        },
 *        yearStart: datepicker_yearStart,
 *        rtl: datepicker_rtl,
 *        format: datepicker_format,
 *        timepicker:false
 *    });
 *
 * Example code in the javascript for datetimepicker:
 *    $('.jquery-date-time-picker').datetimepicker({
 *        i18n:{
 *            en: {
 *                months: datetimepicker_xlMonths,
 *                dayOfWeekShort: datetimepicker_xlDayofwkshort,
 *                dayOfWeek: datetimepicker_xlDayofwk
 *            },
 *        },
 *        yearStart: datetimepicker_yearStart,
 *        rtl: datetimepicker_rtl,
 *        format: datetimepicker_format,
 *        step: datetimepicker_step,
 *        timepicker:true
 *    });
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

//datepicker elements
var datepicker_xlMonths = ["<?php echo xla('January'); ?>","<?php echo xla('February'); ?>", "<?php echo xla('March'); ?>", "<?php echo xla('April'); ?>", "<?php echo xla('May'); ?>", "<?php echo xla('June'); ?>", "<?php echo xla('July'); ?>", "<?php echo xla('August'); ?>", "<?php echo xla('September'); ?>", "<?php echo xla('October'); ?>", "<?php echo xla('November'); ?>", "<?php echo xla('December'); ?>"];
var datepicker_xlDayofwkshort= ["<?php echo xla('Sun'); ?>", "<?php echo xla('Mon'); ?>", "<?php echo xla('Tue'); ?>", "<?php echo xla('Wed'); ?>", "<?php echo xla('Thu'); ?>", "<?php echo xla('Fri'); ?>", "<?php echo xla('Sat'); ?>"];
var datepicker_xlDayofwk= ["<?php echo xla('Sunday'); ?>", "<?php echo xla('Monday'); ?>", "<?php echo xla('Tuesday'); ?>", "<?php echo xla('Wednesday'); ?>", "<?php echo xla('Thursday'); ?>", "<?php echo xla('Friday'); ?>", "<?php echo xla('Saturday'); ?>"];
var datepicker_rtl = <?php echo ($_SESSION['language_direction'] == 'rtl') ? "true" : "false"; ?>;
var datepicker_yearStart = '1900';
var datepicker_format = 'Y-m-d';
var datepicker_scrollInput = false;
var datepicker_scrollMonth = false;

//datetimepicker elements
var datetimepicker_xlMonths = ["<?php echo xla('January'); ?>","<?php echo xla('February'); ?>", "<?php echo xla('March'); ?>", "<?php echo xla('April'); ?>", "<?php echo xla('May'); ?>", "<?php echo xla('June'); ?>", "<?php echo xla('July'); ?>", "<?php echo xla('August'); ?>", "<?php echo xla('September'); ?>", "<?php echo xla('October'); ?>", "<?php echo xla('November'); ?>", "<?php echo xla('December'); ?>"];
var datetimepicker_xlDayofwkshort= ["<?php echo xla('Sun'); ?>", "<?php echo xla('Mon'); ?>", "<?php echo xla('Tue'); ?>", "<?php echo xla('Wed'); ?>", "<?php echo xla('Thu'); ?>", "<?php echo xla('Fri'); ?>", "<?php echo xla('Sat'); ?>"];
var datetimepicker_xlDayofwk= ["<?php echo xla('Sunday'); ?>", "<?php echo xla('Monday'); ?>", "<?php echo xla('Tuesday'); ?>", "<?php echo xla('Wednesday'); ?>", "<?php echo xla('Thursday'); ?>", "<?php echo xla('Friday'); ?>", "<?php echo xla('Saturday'); ?>"];
var datetimepicker_rtl = <?php echo ($_SESSION['language_direction'] == 'rtl') ? "true" : "false"; ?>;
var datetimepicker_yearStart = '1900';
var datetimepicker_format = 'Y-m-d H:i:s';
var datetimepicker_step = '30';
var datetimepicker_scrollInput = false;
var datetimepicker_scrollMonth = false;
