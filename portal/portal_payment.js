function coloring() {
    for (var i = 1; ; ++i) {
        if (document.getElementById('paying_' + i)) {
            paying = document.getElementById('paying_' + i).value * 1;
            patient_balance = document.getElementById('duept_' + i).innerHTML * 1;
            if (patient_balance > 0 && paying > 0) {
                if (paying > patient_balance) {
                    document.getElementById('paying_' + i).style.background = '#FF0000';
                }
                else if (paying < patient_balance) {
                    document.getElementById('paying_' + i).style.background = '#99CC00';
                }
                else if (paying == patient_balance) {
                    document.getElementById('paying_' + i).style.background = '#ffffff';
                }
            }
            else {
                document.getElementById('paying_' + i).style.background = '#ffffff';
            }
        }
        else {
            break;
        }
    }
}

function CheckVisible(MakeBlank) {//Displays and hides the check number text box.
    if (document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value == 'check_payment' ||
        document.getElementById('form_method').options[document.getElementById('form_method').selectedIndex].value == 'bank_draft') {
        document.getElementById('check_number').disabled = false;
    }
    else {
        document.getElementById('check_number').disabled = true;
    }
}


function cursor_pointer() {//Point the cursor to the latest encounter(Today)
    var f = document.forms["invoiceForm"];
    var total = 0;
    for (var i = 0; i < f.elements.length; ++i) {
        var elem = f.elements[i];
        var ename = elem.name;
        if (ename.indexOf('form_upay[') == 0) {
            elem.focus();
            break;
        }
    }
}

function make_it_hide_enc_pay() {
    document.getElementById('td_head_insurance_payment').style.display = "none";
    document.getElementById('td_head_patient_co_pay').style.display = "none";
    document.getElementById('td_head_co_pay').style.display = "none";
    document.getElementById('td_head_insurance_balance').style.display = "none";
    for (var i = 1; ; ++i) {
        var td_inspaid_elem = document.getElementById('td_inspaid_' + i)
        var td_patient_copay_elem = document.getElementById('td_patient_copay_' + i)
        var td_copay_elem = document.getElementById('td_copay_' + i)
        var balance_elem = document.getElementById('balance_' + i)
        if (td_inspaid_elem) {
            td_inspaid_elem.style.display = "none";
            td_patient_copay_elem.style.display = "none";
            td_copay_elem.style.display = "none";
            balance_elem.style.display = "none";
        }
        else {
            break;
        }
    }
    document.getElementById('td_total_4').style.display = "none";
    document.getElementById('td_total_7').style.display = "none";
    document.getElementById('td_total_8').style.display = "none";
    document.getElementById('td_total_6').style.display = "none";

    document.getElementById('table_display').width = "420px";
}

function make_visible() {
    document.getElementById('td_head_rep_doc').style.display = "";
    document.getElementById('td_head_description').style.display = "";
    document.getElementById('td_head_total_charge').style.display = "none";
    document.getElementById('td_head_insurance_payment').style.display = "none";
    document.getElementById('td_head_patient_payment').style.display = "none";
    document.getElementById('td_head_patient_co_pay').style.display = "none";
    document.getElementById('td_head_co_pay').style.display = "none";
    document.getElementById('td_head_insurance_balance').style.display = "none";
    document.getElementById('td_head_patient_balance').style.display = "none";
    for (var i = 1; ; ++i) {
        var td_charges_elem = document.getElementById('td_charges_' + i)
        var td_inspaid_elem = document.getElementById('td_inspaid_' + i)
        var td_ptpaid_elem = document.getElementById('td_ptpaid_' + i)
        var td_patient_copay_elem = document.getElementById('td_patient_copay_' + i)
        var td_copay_elem = document.getElementById('td_copay_' + i)
        var balance_elem = document.getElementById('balance_' + i)
        var duept_elem = document.getElementById('duept_' + i)
        if (td_charges_elem) {
            td_charges_elem.style.display = "none";
            td_inspaid_elem.style.display = "none";
            td_ptpaid_elem.style.display = "none";
            td_patient_copay_elem.style.display = "none";
            td_copay_elem.style.display = "none";
            balance_elem.style.display = "none";
            duept_elem.style.display = "none";
        }
        else {
            break;
        }
    }
    document.getElementById('td_total_7').style.display = "";
    document.getElementById('td_total_8').style.display = "";
    document.getElementById('td_total_1').style.display = "none";
    document.getElementById('td_total_2').style.display = "none";
    document.getElementById('td_total_3').style.display = "none";
    document.getElementById('td_total_4').style.display = "none";
    document.getElementById('td_total_5').style.display = "none";
    document.getElementById('td_total_6').style.display = "none";

    document.getElementById('table_display').width = "505px";
}


