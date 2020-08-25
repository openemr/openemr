/**
 * dialog_utils.js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* eslint-disable no-var */
var opener_list = [];

function set_opener(window, opener) {
    window.top.opener_list[window] = opener;
}

function get_opener(window) {
    return window.top.opener_list[window];
}
