/**
 * Javascript library for the clinical note form.
 *
 * @package OpenEMR
 * @subpackage Forms
 * @link   http://www.open-emr.org
 * @author Jacob T Paul <jacob@zhservices.com>
 * @author Vinish K <vinish@zhservices.com>
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @author Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (C) 2025 Open Plan IT Ltd. <support@openplanit.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/
(function() {
    let codeArray = [];
    let defaultType = '';
    let defaultCategory = '';
    function duplicateRow(event) {
        event.preventDefault();
        let btn = event.currentTarget;
        let oldId = btn.id;
        if (!oldId) {
            console.error("No row id found for button ", btn);
            return;
        } else {
            oldId = 'tb_row_' + oldId.split('btn-add_')[1];
        }
        let dupRow = document.getElementById(oldId);
        let newRow = dupRow.cloneNode(true);
        let $newRow = $(newRow);
        $newRow.find(".btn-add").click(duplicateRow);
        $newRow.find(".btn-delete").click(deleteRow);
        $newRow.find(".clinical_notes_type").change(typeChange);
        dupRow.parentNode.insertBefore(newRow, dupRow.nextSibling);
        changeIds('tb_row');
        changeIds('description');
        changeIds('code');
        changeIds('codetext');
        changeIds('code_date');
        changeIds('clinical_notes_type');
        changeIds('clinical_notes_category');
        changeIds('count');
        changeIds('btn-add');
        changeIds('btn-delete');
        changeIds('id');
        removeVal(newRow.id);
        updateDefaults(newRow.id);
    }

    function updateDefaults(rowid) {
        let rowid1 = rowid.split('tb_row_');
        let typeEl = document.getElementById("clinical_notes_type_" + rowid1[1]);
        let categoryEl = document.getElementById("clinical_notes_category_" + rowid1[1]);
        let codeEl = document.getElementById("code_" + rowid1[1]);
        let codeTextEl = document.getElementById("codetext_" + rowid1[1]);
        let codeContext = document.getElementById("description_" + rowid1[1]);
        // note these two elements could be missing if there are no active list ids enabled for type and category
        if (typeEl) {
            typeEl.value = defaultType;
        }
        if (categoryEl) {
            categoryEl.value = defaultCategory;
        }
        codeEl.value = '';
        codeTextEl.value = '';
        codeContext.dataset.textcontext = '';
    }

    function removeVal(rowid) {
        rowid1 = rowid.split('tb_row_');
        let elements = ['description', 'code', 'codetext', 'code_date', 'clinical_notes_type', 'clinical_notes_category', 'id'];
        for (let i = 0; i < elements.length; i++) {
            let el = document.getElementById(elements[i] + "_" + rowid1[1]);
            if (el) {
                el.value = '';
            }
        }
        // this is an external function defined in CustomTemplateApi.js
        if (typeof doTemplateEditor !== 'undefined') {
            document.getElementById("description_" + rowid1[1]).addEventListener('dblclick', event => {
                doTemplateEditor(this, event, event.target.dataset.textcontext);
            })
        }
    }

    function changeIds(class_val) {
        var elem = document.getElementsByClassName(class_val);
        for (let i = 0; i < elem.length; i++) {
            if (elem[i].id) {
                index = i + 1;
                elem[i].id = class_val + "_" + index;
            }
            if (class_val == 'count') {
                elem[i].value = index;
            }
        }
    }

    function deleteRow(event) {
        event.preventDefault();
        let btn = event.currentTarget;
        let rowid = btn.id;
        if (!rowid) {
            console.error("No row id found for button ", btn);
            return;
        } else {
            rowid = 'tb_row_' + rowid.split('btn-delete_')[1];
        }

        // check to make sure there are other rows before deleting the last one
        if (document.getElementsByClassName('tb_row').length <= 1) {
            alert(window.top.xl('You must have at least one clinical note.'));
            return;
        }
        if (rowid) {
            let elem = document.getElementById(rowid);
            elem.parentNode.removeChild(elem);
        }
    }

    function typeChange(event) {
        try {
            let othis = event.currentTarget;
            let rowid = othis.id.split('clinical_notes_type_');
            let oId = rowid[1];
            let codeEl = document.getElementById("code_" + oId);
            let codeTextEl = document.getElementById("codetext_" + oId);
            let codeContext = document.getElementById("description_" + oId);
            let type = othis.options[othis.selectedIndex].value;
            let i = codeArray.findIndex((v, idx) => codeArray[idx].value === type);
            if (i >= 0)
            {
                codeEl.value = jsText(codeArray[i].code);
                codeTextEl.value = jsText(codeArray[i].title);
                codeContext.dataset.textcontext = jsText(codeArray[i].title);
            } else {
                console.error("Code not found in array for selected element ", codeEl);
                // they are clearing out the value so we are going to empty everything out.
                codeEl.value = "";
                codeTextEl.value = "";
                codeContext.vlaue = "";
            }

        } catch (e) {
            alert(jsText(e));
        }
    }
    function init(config) {
        codeArray = config.codeArray;
        defaultType = config.defaultType || '';
        defaultCategory = config.defaultCategory || '';
        // Initialize other components if needed
        $(function () {
            // special case to deal with static and dynamic datepicker items
            $(document).on('mouseover', '.datepicker', function () {
                datetimepickerTranslated('.datepicker', {
                    timepicker: false
                    , showSeconds: false
                    , formatInput: false
                });
            });

            // initialize
            $(".clinical_notes_type").change(typeChange);

            // init code values by triggering the change in case
            // there are any default values set in the template
            $(".clinical_notes_type").trigger("change");
            $(".btn-add").click(duplicateRow);
            $(".btn-delete").click(deleteRow);
            if (typeof config.alertMessage !== 'undefined' && config.alertMessage != '') {
                alert(config.alertMessage);
            }
        });
    }
    window.oeFormsClinicalNotes = {
        init: init
    };
})(window);
