/**
* User Debugging Javascript Errors
*
* @package   OpenEMR
* @link      http://www.open-emr.org
* @author    Jerry Padgett <sjpadgett@gmail.com>
* @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
* @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

if (typeof top.userDebug !== 'undefined' && (top.userDebug === '1' || top.userDebug === '3')) {
    window.onerror = function (msg, url, lineNo, columnNo, error) {
        const is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
        const is_firefox = navigator.userAgent.indexOf('Firefox') > -1;
        const is_safari = navigator.userAgent.indexOf("Safari") > -1;

        var showDebugAlert = function (message) {
            let errorMsg = [
                'URL: ' + message.URL,
                'Line: ' + message.Line + ' Column: ' + message.Column,
                'Error object: ' + JSON.stringify(message.Error)
            ].join("\n");

            let msg = message.Message + "\n" + errorMsg;

            alert(msg);

            return false;

        };
        // Dialog Async Alert for future
        var displayLoggedErrors = function (log) {
            dlgopen('', '', 675, 250, '', '<i class="fa fa-warning"style="color:red"> Alert</i>', {
                buttons: [
                    {text: '<i class="fa fa-thumbs-up">&nbsp;OK</i>', close: true, style: 'default'}
                ],
                type: 'Alert',
                html: log
            });

            return false;
        };

        let string = msg.toLowerCase();
        let substring = "script error";
        if (string.indexOf(substring) > -1) {
            showDebugAlert('Script Error: See Browser Console for Detail');
        } else {
            let message = {
                Message: msg,
                URL: url,
                Line: lineNo,
                Column: columnNo,
                Error: JSON.stringify(error)
            };

            showDebugAlert(message);
        }

        return false;
    };
}
