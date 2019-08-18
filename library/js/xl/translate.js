/**
 * Javascript translate function.
 *  This calls the i18next.t function that has been set up in main.php
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function xl(string) {
    if (typeof top.i18next.t == 'function') {
        // top
        return top.i18next.t(string);
    } else {
        // opener (if called from a modal/popup)
        return opener.i18next.t(string);
    }
}
