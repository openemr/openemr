/**
 * interface/modules/zend_modules/public/js/application/common.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Function js_xl
 * Message Translation xl format
 *
 * @param {string} msg
 * @returns {undefined}
 */
function js_xl(msg) {
    let resultTranslated = '';
    const path = window.location;
    const arr = path.toString().split('public');
    const count = arr[1].split('/').length - 1;
    let newpath = './';
    for (let i = 0; i < count; i += 1) {
        newpath += '../';
    }
    $.ajax({
        type: 'POST',
        url: `${newpath}public/application/index/ajaxZxl`,
        async: false,
        data: {
            msg,
        },
        success(result) {
            resultTranslated = result;
        },
    });
    return resultTranslated;
}
