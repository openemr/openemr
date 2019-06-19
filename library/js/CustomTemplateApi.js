/**
 * custom templates dynamic api
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
const bindTextArea = function () {
    const hookLoaded = 1;
    const teventElement = document.querySelector("textarea");
    if(typeof teventElement === 'undefined' || teventElement === null) {
        return false;
    }
    document.body.addEventListener('dblclick', event =>  {
        if (event.target.nodeName === "TEXTAREA") {
            doTemplateEditor(this);
        } else if (event.target.nodeName === "INPUT" && event.target.type === "text") {
            // @TODO maybe send a sentence context for this input.
            doTemplateEditor(this, 'sentence');
        } else {
            return false;
        }
    });
};

function doTemplateEditor(_this, oContext = '') {
    let url = top.webroot_url + "/library/custom_template/custom_template.php";
    dlgopen(url, '', 'modal-mlg', 550, '', '<i class="fa fa-th"></i>', {
        buttons: [
            {text: '<i class="fa fa-thumbs-up"></i>', close: true, style: 'default'}
        ],
        type: 'iframe'
    });
    return false;
}
