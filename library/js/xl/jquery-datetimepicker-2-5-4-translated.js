/**
 *
 * This is to allow internationalization by OpenEMR of the jquery-datetimepicker.
 * (with and without a time selector)
 *
 * Example code in script:
 * datetimepickerTranslated('.datetimepicker', {
 *   timepicker: true
 *   , showSeconds: false
 *   , formatInput: false
 *   , maxDate: '+1970/01/01' // `+1970/01/01` means today for tomorrow use `+1970/01/02`
 *   // can add any additional settings to datetimepicker here;
 * });
 * datetimepickerTranslated('.datepicker', {
 * timepicker: false
 * , showSeconds: false
 * , formatInput: false
 * , minDate: '-1970/01/01' // `-1970/01/01` means today for tomorrow use `-1970/01/02`
 * // can add any additional settings to datetimepicker here;
 * });
 *
 * // settings explanation {
 *     timepicker: true // this will set whether to use the timepicker
 *     , showSeconds: false // this will show seconds if using the timepicker
 *     , formatInput: false // this will set whether to format the input to the user selected date format within globals
 *     , minDate: '-1970/01/01' // this will set the minimum date that can be selected
 *     , maxDate: '+1970/01/01' // this will set the maximum date that can be selected
 *     // can add any additional settings to datetimepicker here;
 *     // see https://xdsoft.net/jqplugins/datetimepicker/ for more info
 * }
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
function datetimepickerTranslated(selector, params) {
    if (typeof selector === 'undefined') {
        selector = '.datetimepicker';
    }
    if (window.top.xl === 'undefined') {
        throw new Error("Missing xl function");
    }
    let jsGlobals = window.top.jsGlobals || {};
    let languageDirection = jsGlobals.languageDirection || 'ltr';
    let formatters = window.top.oeFormatters || {};
    let DateFormatRead = formatters.DateFormatRead || function (mode = 'legacy') { return "Y-m-d"; };
    if (typeof params === 'undefined') {
        params = {
            timepicker: false
            , showSeconds: false
            , formatInput: false
        };
    }
    let xl = window.top.xl;

    let defaults = {
        i18n: {
            en: {
                months: [
                    xl('January'), xl('February'), xl('March'), xl('April'), xl('May'), xl('June'), xl('July'), xl('August'), xl('September'), xl('October'), xl('November'), xl('December')
                ],
                dayOfWeekShort: [
                    xl('Sun'), xl('Mon'), xl('Tue'), xl('Wed'), xl('Thu'), xl('Fri'), xl('Sat')
                ],
                dayWeek: [
                    xl('Sunday'), xl('Monday'), xl('Tuesday'), xl('Wednesday'), xl('Thursday'), xl('Friday'), xl('Saturday')
                ]
            }
        }
        , yearStart: '1900'
        , scrollInput: false
        , scrollMonth: false
        , rtl: (languageDirection === 'rtl') ? true : false
    };
    defaults = Object.assign(defaults, params);
    // go through and setup more specific settings
    if (params.timepicker) {
        if (params.showSeconds) {
            if (params.formatInput) {
                defaults.format = DateFormatRead("jquery-datetimepicker") + ' H:i:s';
            } else {
                defaults.format = 'Y-m-d H:i:s';
            }
        } else {
            if (params.formatInput) {
                defaults.format = DateFormatRead("jquery-datetimepicker") + ' g:i a';
                defaults.formatTime = 'g:i a';
                defaults.validateOnBlur = false;
            } else {
                defaults.format = 'Y-m-d H:i';
            }
        }
    } else {
        if (params.formatInput) {
            defaults.format = DateFormatRead("jquery-datetimepicker");
        } else {
            defaults.format = 'Y-m-d';
        }
        defaults.timepicker = false;
    }
    if (languageDirection === 'rtl') {
        defaults.onGenerate = function (current_time, $input) {
            //position of input
            var position = $($input).offset()
            //width of date picke popup
            var datepickerPopupWidth = $('.xdsoft_datetimepicker').width();

            if (position.left < datepickerPopupWidth) {
                $('.xdsoft_datetimepicker').offset({left: position.left});
            } else {
                //put a popup in the regular position
                $('.xdsoft_datetimepicker').offset({left: position.left - datepickerPopupWidth + $($input).innerWidth()});
            }
        }
    }
    jQuery(selector).datetimepicker(defaults);
}
