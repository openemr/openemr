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
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Amiel Elboim <amielel@matrix.co.il>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
"dir":<?php echo js_escape($_SESSION['language_direction']); ?>,
"language": {
    errorLoading: function () {
        return <?php echo xlj('The results could not be loaded'); ?> + '.';
    },
    inputTooLong: function (args) {

        return <?php echo xlj('Please delete characters')?>;
    },
    inputTooShort: function (args) {

        return <?php echo xlj('Please enter more characters')?>;

    },
    loadingMore: function () {
        return <?php echo xlj('Loading more results'); ?> + '...';
    },
    maximumSelected: function (args) {
        var message = <?php echo xlj('You can only select') ?> + ' ' + args.maximum;

        if (args.maximum != 1) {
            message += ' ' + <?php echo xlj('items') ?>;
        } else {
            message += ' ' + <?php echo xlj('item') ?>;
        }

        return message;
    },
    noResults: function () {
        return <?php echo xlj('No results found')?>;
    },
    searching: function () {
        return <?php echo xlj('Searching')?> + '...';
    }
    <?php
    if (!empty($translationsSelect2Override)) {
        foreach ($translationsSelect2Override as $key => $value) {
            echo ', ' . js_escape($key) . ': function () { return ' . js_escape($value) . '}';
        }
    }
    ?>
},
