/**
 * Handles the select2 plugin using OpenEMR translations.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
/**
 * Generates a jQuery select2 object with translated messages
 * @param selector
 * @param languageDirection
 * @param params
 */
function select2Translated(selector, languageDirection, params) {
    params = params || {};
    if (typeof languageDirection === 'undefined') {
        languageDirection = 'ltr';
    }
    if (typeof selector === 'undefined') {
        selector = '.sel2';
    }
    if (window.top.xl === 'undefined') {
        throw new Error("Missing xl function");
    }

    let defaults = {
        dir: languageDirection,
        language: {
            errorLoading: function () {
                window.top.xl('The results could not be loaded') + '.';
            }
            , inputTooLong: function (args) {
                return window.top.xl('Please delete characters');
            }
            , inputTooShort: function (args) {
                return window.top.xl('Please enter more characters');
            }
            , loadingMore: function () {
                return window.top.xl('Loading more results') + '...';
            }
            , maximumSelected: function (args) {
                var message = window.top.xl('You can only select') + ' ' + args.maximum;

                if (args.maximum != 1) {
                    message += ' ' + window.top.xl('items');
                } else {
                    message += ' ' + window.top.xl('item');
                }

                return message;
            }
            , noResults: function () {
                return window.top.xl('No results found');
            }
            , searching: function () {
                return window.top.xl('Searching') + '...';
            }
        }
    };
    let select2Params = Object.assign(defaults, params);
    $(selector).select2(select2Params);
}
