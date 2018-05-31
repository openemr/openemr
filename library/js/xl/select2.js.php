<?php
/**
 *
 * This is to allow internationalization by OpenEMR of the select2 asset.
 *
 * Example code in script:
 *    $translationsSelect2Override = array('searching'=>(xla('Search') . ':')) (optional php command)
 *    require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); (php command)
 *
 * Note there is a optional mechanism to override translations via the
 *  $translationsSelect2Override array.
 *
 * Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
 * Copyright (C) 2018 Amiel Elboim <amielel@matrix.co.il>
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
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */
?>
"dir":'<?php echo addslashes($_SESSION['language_direction']);?>',
"language": {
    errorLoading: function () {
        return '<?php echo xls('The results could not be loaded'); ?>.';
    },
    inputTooLong: function (args) {

        return '<?php echo xls('Please delete characters')?>';
    },
    inputTooShort: function (args) {

        return '<?php echo xls('Please enter more characters')?>';

    },
    loadingMore: function () {
        return '<?php echo xls('Loading more results')?>…';
    },
    maximumSelected: function (args) {
        var message = '<?php echo xls('You can only select') ?> ' + args.maximum;

        if (args.maximum != 1) {
            message += ' <?php echo xls('items') ?>';
        } else {
            message += ' <?php echo xls('item') ?>';
        }

        return message;
    },
    noResults: function () {
        return '<?php echo xls('No results found')?>';
    },
    searching: function () {
        return '<?php echo xla('Searching')?>…';
    }
    <?php
    if (!empty($translationsSelect2Override)) {
        foreach ($translationsSelect2Override as $key => $value) {
            echo ', "' . $key . '": function () { return "' . $value . '"}';
        }
    }
    ?>
},
