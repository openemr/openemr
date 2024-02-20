/**
 * Formatting library in javascript.  Attempts to mimic the PHP formatting in library/formatting.inc.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2010-2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function (oeFormatters) {

    function pad(n) {
        return n.toString().padStart(2, '0');
    }
    function I18NDateFormat(date, displayFormatSetting = undefined) {
        if (typeof date === 'undefined') {
            date = new Date();
        } else if (!(date instanceof Date)) {
            date = new Date(date);
        }
        let jsGlobals = window.top.jsGlobals || {};
        let date_display_format = displayFormatSetting !== undefined ? displayFormatSetting : jsGlobals['date_display_format'];
        let timezone = jsGlobals['timezone'] || undefined; // default to undefined to choose local timezone if its not set
        let defaultLocale = 'en-US';
        if (date_display_format == 0) {
            return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
        } else if (date_display_format == 1) {
            defaultLocale = 'en-US';
        } else {
            defaultLocale = 'en-GB'; // choose european time format
        }
        return new Intl.DateTimeFormat(defaultLocale, { month: '2-digit', day: '2-digit', year: 'numeric', timezone: timezone }).format(date);

    }
    function DateFormatRead(mode = 'legacy') {
        let jsGlobals = window.top.jsGlobals || {};
        let date_display_format = jsGlobals['date_display_format'];
        //For the 3 supported date format,the javascript code also should be twicked to display the date as per it.
        //Output of this function is given to 'ifFormat' parameter of the 'Calendar.setup'.
        //This will show the date as per the global settings.
        if (date_display_format == 0) {
            if (mode == 'legacy') {
                return "%Y-%m-%d";
            } else if (mode == 'validateJS') {
                return "YYYY-MM-DD";
            } else { //mode=='jquery-datetimepicker'
                return "Y-m-d";
            }
        } else if (date_display_format == 1) {
            if (mode == 'legacy') {
                return "%m/%d/%Y";
            } else if (mode == 'validateJS') {
                return "MM/DD/YYYY";
            } else { //$mode=='jquery-datetimepicker'
                return "m/d/Y";
            }
        } else if (date_display_format == 2) {
            if (mode == 'legacy') {
                return "%d/%m/%Y";
            } else if (mode == 'validateJS') {
                return "DD/MM/YYYY";
            } else { //$mode=='jquery-datetimepicker'
                return "d/m/Y";
            }
        }
    }
    oeFormatters.DateFormatRead = DateFormatRead;
    oeFormatters.I18NDateFormat = I18NDateFormat;
})(window.top.oeFormatters = window.top.oeFormatters || {});
