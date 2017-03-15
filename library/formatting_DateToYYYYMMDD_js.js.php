<?php
/**
 * this is the javascript DateToYYYYMMDD_js to allow date internationalization
 * converts date back to YYYY-MM-DD format
 *
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
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
 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */
?>
function DateToYYYYMMDD_js(value){
    var value = value.replace(/\//g,'-');
    var parts = value.split('-');
    var date_display_format = <?php echo (empty($GLOBALS['date_display_format']) ? 0 : $GLOBALS['date_display_format']) ?>;

    if (date_display_format == 1)      // mm/dd/yyyy, note year is added below
        value = parts[2] + '-' + parts[0]  + '-' + parts[1];
    else if (date_display_format == 2) // dd/mm/yyyy, note year is added below
        value = parts[2] + '-' + parts[1]  + '-' + parts[0];

    return value;
}