<?php

/**
 * javascripts function to allow date internationalization
 * and converts date back to YYYY-MM-DD and YYYY-MM-DD HH:MM:SS (SS is optional)
 * formats
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>

function DateToYYYYMMDD_js(value){
    var value = value.replace(/\//g,'-');
    var parts = value.split('-');
    var date_display_format = <?php echo js_escape((empty($GLOBALS['date_display_format']) ? 0 : $GLOBALS['date_display_format'])) ?>;

    if (date_display_format == 1)      // mm/dd/yyyy, note year is added below
        value = parts[2] + '-' + parts[0]  + '-' + parts[1];
    else if (date_display_format == 2) // dd/mm/yyyy, note year is added below
        value = parts[2] + '-' + parts[1]  + '-' + parts[0];

    return value;
}

function TimeToHHMMSS_js(value){
    //For now, just return the Value, since input fields are not formatting time.
    // This can be upgraded if decided to format input time fields.
    return value.trim();
}

function DateToYYYYMMDDHHMMSS_js(value){
    if (typeof value === 'undefined') {
        return undefined;
    }
    var parts = value.split(' ');

    var datePart = DateToYYYYMMDD_js(parts[0]);
    var timePart = TimeToHHMMSS_js(parts[1]);

    var value = datePart + ' ' + timePart;

    return value.trim();
}
