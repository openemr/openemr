/**
 * custom templates dynamic api
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2021 Jerry Padgett <sjpadgett@gmail.com>
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
    let title = '<i class="fa fa-th"></i><h4 class="ml-2">'+ xl("Text Templates") +'</h4>';
    let url = top.webroot_url + "/library/custom_template/custom_template.php?type=" + encodeURIComponent(id) + "&ccFlag=" + encodeURIComponent(ccFlag) + "&contextName=" + encodeURIComponent(oContext);
    dlgopen(url, '', 'modal-lg', 800, '', '', {
        buttons: [
            {text: xl('Do Nothing'), close: true, style: 'secondary'}
        ],
        type: 'iframe'
    });
    return false;
}

const bindTextArea = function () {
    const teventElement = document.querySelectorAll("textarea, input[type='text']:not(.skip-template-editor)");
    if (typeof teventElement === 'undefined' || teventElement === null) {
        return false;
    }
    teventElement.forEach(item => {
        item.addEventListener('dblclick', event => {
            if (event.target.nodeName === "TEXTAREA") {
                doTemplateEditor(this, event, event.target.dataset.textcontext);
            } else if (event.target.nodeName === "INPUT" && event.target.type === "text") {
                doTemplateEditor(this, event, 'Sentence');
            } else {
                return false;
            }
        })
    });

    console.log("Bound text events: ['" + location + "']");
};
