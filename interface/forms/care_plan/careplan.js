(function(window, oeUI) {

    function init(webroot, reasonCodeTypes) {
        // we want to setup our reason code widgets
        if (oeUI.reasonCodeWidget) {
            oeUI.reasonCodeWidget.init(webroot, reasonCodeTypes);
        } else {
            console.error("Missing required dependency reasonCodeWidget");
            return;
        }
    }

    let form = {
        "init": init
    };
    window.careplanForm = form;
})(window, window.oeUI || {});

function duplicateRow(e) {
    var newRow = e.cloneNode(true);
    e.parentNode.insertBefore(newRow, e.nextSibling);
    changeIds('tb_row');
    changeIds('description');
    changeIds('code');
    changeIds('codetext');
    changeIds('code_date');
    changeIds('displaytext');
    changeIds('care_plan_type');
    changeIds('user');
    changeIds('count');
    changeIds('reason_code');
    changeDatasetIds('toggle-container', 'toggleContainer', 'reason_code');
    clearReasonCode(newRow);
    removeVal(newRow.id);
    // reload our widget event listeners.
    window.oeUI.reasonCodeWidget.reload();
}

function removeVal(rowid) {
    rowid1 = rowid.split('tb_row_');
    document.getElementById("description_" + rowid1[1]).value = '';
    document.getElementById("code_" + rowid1[1]).value = '';
    document.getElementById("codetext_" + rowid1[1]).value = '';
    document.getElementById("code_date_" + rowid1[1]).value = '';
    document.getElementById("displaytext_" + rowid1[1]).innerHTML = '';
    document.getElementById("care_plan_type_" + rowid1[1]).value = '';
    document.getElementById("user_" + rowid1[1]).value = '';
    if (typeof doTemplateEditor !== 'undefined') {
        document.getElementById("description_" + rowid1[1]).addEventListener('dblclick', event => {
            doTemplateEditor(this, event, event.target.dataset.textcontext);
        })
    }
}

function changeDatasetIds(propertySelector, dataSetProperty, keyPrefix) {
    var elements = document.querySelectorAll('[data-' + propertySelector + ']');
    if (elements) {
        elements.forEach(function(element, index) {
            element.dataset[dataSetProperty] = keyPrefix + "_" + (index + 1);
        });
    }
}

function clearReasonCode(newRow) {
    // make sure we clear everything out.
    let inputs = newRow.querySelectorAll(".reasonCodeContainer input");
    inputs.forEach(function(input) {
        input.value = "";
    });
    // make sure we are hiding the thing.
    let container = newRow.querySelector(".reasonCodeContainer");
    container.classList.add("d-none");
}

function changeIds(class_val) {
    var elem = document.getElementsByClassName(class_val);
    for (let i = 0; i < elem.length; i++) {
        if (elem[i].id) {
            index = i + 1;
            elem[i].id = class_val + "_" + index;
        }
        if(class_val == 'count') {
            elem[i].value = index;
        }
    }
}

function deleteRow(event, rowId, rowCount) {
    event.stopPropagation();
    event.preventDefault();
    if (rowCount > 1) {
        let elem = document.getElementById(rowId);
        elem.parentNode.removeChild(elem);
    }
    window.oeUI.reasonCodeWidget.reload();
}

function sel_code(webroot, id) {
    id = id.split('tb_row_');
    let checkId = '_' + id[1];
    if (typeof checkId === 'undefined') {
        checkId = 1;
    }
    document.getElementById('clickId').value = checkId;
    window.top.restoreSession();
    dlgopen(webroot + "/interface/patient_file/encounter/find_code_popup.php?default=SNOMED-CT", '_blank', 700, 400);
}

function set_related(codetype, code, selector, codedesc) {
    let checkId = document.getElementById('clickId').value;
    if (codetype !== "") {
        document.getElementById("code" + checkId).value = (codetype + ":" + code);
    } else {
        document.getElementById("code" + checkId).value = "";
    }
    document.getElementById("codetext" + checkId).value = codedesc;
    document.getElementById("displaytext" + checkId).innerHTML  = codedesc;
}
