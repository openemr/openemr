/**
 * custom templates dynamic api
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function doTemplateEditor(_this, event, oContext = '') {
    // has to be one of two.
    let id = event.target.id;
    let ccFlag = 'id';
    if (!id) {
        id = event.target.name;
        ccFlag = 'name';
    }
    let url = top.webroot_url + "/library/custom_template/custom_template.php?type=" + encodeURIComponent(id) + "&ccFlag=" + encodeURIComponent(ccFlag);

    dlgopen(url, '', 'modal-mlg', 550, '', '<i class="fa fa-th"></i>', {
        buttons: [
            {text: '<i class="fa fa-thumbs-up"></i>', close: true, style: 'default'}
        ],
        type: 'iframe'
    });
    return false;
}

const bindTextArea = function () {
    const teventElement = document.querySelector("textarea");
    if (typeof teventElement === 'undefined' || teventElement === null) {
        return false;
    }
    document.body.addEventListener('dblclick', event => {
        if (event.target.nodeName === "TEXTAREA") {
            doTemplateEditor(this, event);
        } else if (event.target.nodeName === "INPUT" && event.target.type === "text") {
            doTemplateEditor(this, event, 'sentence');
        } else {
            return false;
        }
    });
    console.log("Bound text events: ['" + location + "']");
};
