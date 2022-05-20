(function (window, oeUI) {

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
    window.observationForm = form;
})(window, window.oeUI || {});

function clearReasonCode(newRow) {
    // make sure we clear everything out.
    let inputs = newRow.querySelectorAll(".reasonCodeContainer input");
    inputs.forEach(function (input) {
        input.value = "";
    });
    // make sure we are hiding the thing.
    let container = newRow.querySelector(".reasonCodeContainer");
    container.classList.add("d-none");
}

function duplicateRow(e) {
    var newRow = e.cloneNode(true);
    e.parentNode.insertBefore(newRow, e.nextSibling);
    changeIds('tb_row');
    changeIds('comments');
    changeIds('code');
    changeIds('description');
    changeIds('code_date');
    changeIds('displaytext');
    changeIds('code_type');
    changeIds('table_code');
    changeIds('ob_value');
    changeIds('ob_unit');
    changeIds('ob_value_phin');
    changeIds('ob_value_head');
    changeIds('ob_unit_head');
    changeIds('reason_code');
    changeIds('code_date_end');
    changeDatasetIds('toggle-container', 'toggleContainer', 'reason_code');
    clearReasonCode(newRow);
    removeVal(newRow.id);
    // reload our widget event listeners.
    window.oeUI.reasonCodeWidget.reload();
}

function removeVal(rowid) {
    rowid1 = rowid.split('tb_row_');
    document.getElementById("comments_" + rowid1[1]).value = '';
    document.getElementById("code_" + rowid1[1]).value = '';
    document.getElementById("description_" + rowid1[1]).value = '';
    document.getElementById("code_date_" + rowid1[1]).value = '';
    document.getElementById("displaytext_" + rowid1[1]).innerHTML = '';
    document.getElementById("code_type_" + rowid1[1]).value = '';
    document.getElementById("table_code_" + rowid1[1]).value = '';
    document.getElementById("ob_value_" + rowid1[1]).value = '';
    document.getElementById("ob_unit_" + rowid1[1]).value = '';
    document.getElementById("ob_value_phin_" + rowid1[1]).value = '';
    document.getElementById("code_date_end_" + rowid1[1]).value = '';
    //document.getElementById("ob_value_head_" + rowid1[1]).innerHTML = '';
    //document.getElementById("ob_unit_head_" + rowid1[1]).innerHTML = '';
}

function changeDatasetIds(propertySelector, dataSetProperty, keyPrefix) {
    var elements = document.querySelectorAll('[data-' + propertySelector + ']');
    if (elements) {
        elements.forEach(function (element, index) {
            element.dataset[dataSetProperty] = keyPrefix + "_" + (index + 1);
        });
    }
}

function changeIds(class_val) {
    var elem = document.getElementsByClassName(class_val);
    for (let i = 0; i < elem.length; i++) {
        if (elem[i].id) {
            index = i + 1;
            elem[i].id = class_val + "_" + index;
        }
    }
}

function deleteRow(event, rowId, rowCount) {
    if (rowCount > 1) {
        let elem = document.getElementById(rowId);
        elem.parentNode.removeChild(elem);
    }
    window.oeUI.reasonCodeWidget.reload();
}

function sel_code(webroot, id) {
    id = id.split('tb_row_');
    let checkId = '_' + id[1];
    document.getElementById('clickId').value = checkId;
    window.top.restoreSession();
    dlgopen(webroot + '/interface/patient_file/encounter/find_code_popup.php?default=' + encodeURIComponent('LOINC'), '_blank', 700, 400);
}

function set_related(codetype, code, selector, codedesc) {
    var checkId = document.getElementById('clickId').value;
    document.getElementById("code" + checkId).value = codetype + ':' + code;
    document.getElementById("description" + checkId).value = codedesc;
    document.getElementById("displaytext" + checkId).innerHTML = codedesc;
    document.getElementById("code_type" + checkId).value = codetype;
    if (codetype === 'LOINC') {
        document.getElementById("table_code" + checkId).value = 'LN';
        if (code === '21612-7') {
            document.getElementById('ob_value_head' + checkId).style.display = '';
            document.getElementById('ob_unit_head' + checkId).style.display = '';
            document.getElementById('ob_value' + checkId).style.display = '';
            var sel_unit_age = document.getElementById('ob_unit' + checkId);
            if (document.getElementById('ob_unit' + checkId).value == '') {
                var opt = document.createElement("option");
                opt.value = 'd';
                opt.text = 'Day';
                sel_unit_age.appendChild(opt);
                var opt1 = document.createElement("option");
                opt1.value = 'mo';
                opt1.text = 'Month';
                sel_unit_age.appendChild(opt1);
                var opt2 = document.createElement("option");
                opt2.value = 'UNK';
                opt2.text = 'Unknown';
                sel_unit_age.appendChild(opt2);
                var opt3 = document.createElement("option");
                opt3.value = 'wk';
                opt3.text = 'Week';
                sel_unit_age.appendChild(opt3);
                var opt4 = document.createElement("option");
                opt4.value = 'a';
                opt4.text = 'Year';
                sel_unit_age.appendChild(opt4);
            }
            document.getElementById('ob_unit' + checkId).style.display = 'block';
            document.getElementById('ob_value_phin' + checkId).style.display = 'none';
        } else if (code === '8661-1') {
            document.getElementById('ob_unit_head' + checkId).style.display = 'none';
            var select = document.getElementById('ob_unit' + checkId);
            select.innerHTML = "";
            document.getElementById('ob_unit' + checkId).style.display = 'none';
            document.getElementById('ob_value_phin' + checkId).style.display = 'none';
            document.getElementById('ob_value_head' + checkId).style.display = '';
            document.getElementById('ob_value' + checkId).style.display = '';
        }
    } else {
        document.getElementById("table_code" + checkId).value = 'PHINQUESTION';
        document.getElementById('ob_value_head' + checkId).style.display = '';
        document.getElementById('ob_unit_head' + checkId).style.display = '';
        var select_unit = document.getElementById('ob_unit' + checkId);
        select_unit.innerHTML = "";
        document.getElementById('ob_value' + checkId).value = '';
        document.getElementById('ob_value' + checkId).style.display = '';
        document.getElementById('ob_unit' + checkId).style.display = '';
        document.getElementById('ob_value_phin' + checkId).style.display = '';
    }
}
