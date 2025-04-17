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
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>

//datepicker elements
var datepicker_xlMonths = [<?php echo xlj('January'); ?>, <?php echo xlj('February'); ?>, <?php echo xlj('March'); ?>, <?php echo xlj('April'); ?>, <?php echo xlj('May'); ?>, <?php echo xlj('June'); ?>, <?php echo xlj('July'); ?>, <?php echo xlj('August'); ?>, <?php echo xlj('September'); ?>, <?php echo xlj('October'); ?>, <?php echo xlj('November'); ?>, <?php echo xlj('December'); ?>];
var datepicker_xlDayofwkshort= [<?php echo xlj('Sun'); ?>, <?php echo xlj('Mon'); ?>, <?php echo xlj('Tue'); ?>, <?php echo xlj('Wed'); ?>, <?php echo xlj('Thu'); ?>, <?php echo xlj('Fri'); ?>, <?php echo xlj('Sat'); ?>];
var datepicker_xlDayofwk= [<?php echo xlj('Sunday'); ?>, <?php echo xlj('Monday'); ?>, <?php echo xlj('Tuesday'); ?>, <?php echo xlj('Wednesday'); ?>, <?php echo xlj('Thursday'); ?>, <?php echo xlj('Friday'); ?>, <?php echo xlj('Saturday'); ?>];
var datepicker_rtl = <?php echo (($_SESSION['language_direction'] ?? '') == 'rtl') ? "true" : "false"; ?>;
var datepicker_yearStart = '1900';
var datepicker_format = 'Y-m-d';
var datepicker_scrollInput = false;
var datepicker_scrollMonth = false;

//datetimepicker elements
var datetimepicker_xlMonths = [<?php echo xlj('January'); ?>, <?php echo xlj('February'); ?>, <?php echo xlj('March'); ?>, <?php echo xlj('April'); ?>, <?php echo xlj('May'); ?>, <?php echo xlj('June'); ?>, <?php echo xlj('July'); ?>, <?php echo xlj('August'); ?>, <?php echo xlj('September'); ?>, <?php echo xlj('October'); ?>, <?php echo xlj('November'); ?>, <?php echo xlj('December'); ?>];
var datetimepicker_xlDayofwkshort= [<?php echo xlj('Sun'); ?>, <?php echo xlj('Mon'); ?>, <?php echo xlj('Tue'); ?>, <?php echo xlj('Wed'); ?>, <?php echo xlj('Thu'); ?>, <?php echo xlj('Fri'); ?>, <?php echo xlj('Sat'); ?>];
var datetimepicker_xlDayofwk= [<?php echo xlj('Sunday'); ?>, <?php echo xlj('Monday'); ?>, <?php echo xlj('Tuesday'); ?>, <?php echo xlj('Wednesday'); ?>, <?php echo xlj('Thursday'); ?>, <?php echo xlj('Friday'); ?>, <?php echo xlj('Saturday'); ?>];
var datetimepicker_rtl = <?php echo (($_SESSION['language_direction'] ?? '') == 'rtl') ? "true" : "false"; ?>;
var datetimepicker_yearStart = '1900';
var datetimepicker_format = 'Y-m-d H:i:s';
var datetimepicker_step = '30';
var datetimepicker_scrollInput = false;
var datetimepicker_scrollMonth = false;
