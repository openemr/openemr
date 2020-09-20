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
 *    $datetimepicker_maxDate = '+1970/01/01' (php variable) `+1970/01/01` means today for tomorrow use `+1970/01/02`
 *    require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); (php command)
 *    can add any additional settings to datetimepicker here; need to prepend first setting with a comma
 *  });
 *  $('.datepicker').datetimepicker({
 *    $datetimepicker_timepicker = false; (php variable)
 *    $datetimepicker_showseconds = false; (php variable)
 *    $datetimepicker_formatInput = false; (php variable)
 *    $datetimepicker_minDate = '-1970/01/01'; (php variable)
 *    require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); (php command)
 *    can add any additional settings to datetimepicker here; need to prepend first setting with a comma
 *  });
 *
 * $datetimepicker_timepicker - this will set whether to use the timepicker
 * $datetimepicker_showseconds - this will show seconds if using the timepicker
 * $datetimepicker_formatInput - this will set whether to format the input to
 * $datetimepicker_minDate - this will set the minimum date that can be selected
 * $datetimepicker_maxDate - this will set the minimum date that can be selected
 *  the user selected date format within globals. (This works with the following functions to fully
 *  support internationalization of dates; note this setting does not yet work with the timepicker yet)
 *   -oeFormatShortDate() function for when placing a default formatted date in the field
 *   -DateToYYYYMMDD() function when insert the formatted date into database or codebase works on it
 *
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
    i18n:{
        en: {
            months: [
                <?php echo xlj('January'); ?>, <?php echo xlj('February'); ?>, <?php echo xlj('March'); ?>, <?php echo xlj('April'); ?>, <?php echo xlj('May'); ?>, <?php echo xlj('June'); ?>, <?php echo xlj('July'); ?>, <?php echo xlj('August'); ?>, <?php echo xlj('September'); ?>, <?php echo xlj('October'); ?>, <?php echo xlj('November'); ?>, <?php echo xlj('December'); ?>
            ],
            dayOfWeekShort: [
                <?php echo xlj('Sun'); ?>, <?php echo xlj('Mon'); ?>, <?php echo xlj('Tue'); ?>, <?php echo xlj('Wed'); ?>, <?php echo xlj('Thu'); ?>, <?php echo xlj('Fri'); ?>, <?php echo xlj('Sat'); ?>
            ],
            dayOfWeek: [<?php echo xlj('Sunday'); ?>, <?php echo xlj('Monday'); ?>, <?php echo xlj('Tuesday'); ?>, <?php echo xlj('Wednesday'); ?>, <?php echo xlj('Thursday'); ?>, <?php echo xlj('Friday'); ?>, <?php echo xlj('Saturday'); ?>
            ]
        },
    },
    <?php if ($_SESSION['language_direction'] == 'rtl') { ?>
    /**
     * In RTL languages a datepicker popup is opened in left and it's cutted by the edge of the window
     * This patch resolves that and moves a datepicker popup to right side.
     */
    onGenerate:function(current_time,$input){
        //position of input
        var position = $($input).offset()
        //width of date picke popup
        var datepickerPopupWidth = $('.xdsoft_datetimepicker').width();

        if(position.left < datepickerPopupWidth){
            $('.xdsoft_datetimepicker').offset({left:position.left});
        } else {
            //put a popup in the regular position
            $('.xdsoft_datetimepicker').offset({left:position.left - datepickerPopupWidth + $($input).innerWidth()});
        }
    },
    <?php } ?>
    yearStart: '1900',
    scrollInput: false,
    scrollMonth: false,
    rtl: <?php echo ($_SESSION['language_direction'] == 'rtl') ? "true" : "false"; ?>,
    <?php if (!empty($datetimepicker_minDate)) { ?>
        minDate: '<?php echo $datetimepicker_minDate; ?>',
    <?php } ?>
    <?php if (!empty($datetimepicker_maxDate)) { ?>
        maxDate: '<?php echo $datetimepicker_maxDate; ?>',
    <?php } ?>
    <?php if ($datetimepicker_timepicker) { ?>
        <?php if ($datetimepicker_showseconds) { ?>
            <?php if ($datetimepicker_formatInput) { ?>
                format: '<?php echo DateFormatRead("jquery-datetimepicker"); ?> H:i:s',
            <?php } else { ?>
                format: 'Y-m-d H:i:s',
            <?php } ?>
        <?php } else { ?>
            <?php if ($datetimepicker_formatInput) { ?>
                format: '<?php echo DateFormatRead("jquery-datetimepicker"); ?> H:i',
            <?php } else { ?>
                format: 'Y-m-d H:i',
            <?php } ?>
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
