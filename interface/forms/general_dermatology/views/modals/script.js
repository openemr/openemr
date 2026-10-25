
function dermCsrfToken() {
    var el = document.querySelector('input[name="csrf_token_form"]');
    return el ? el.value : '';
}
function getValuesFromModal(id) {

    var plan_count = $("#plan_count").val();

    restOfPlan =
        plan_count == 0
            ? "#render-restofplans"
            : "#render-restofplans-" +  plan_count;

    $(restOfPlan).empty();
    var hiddenInputTitle= "";
    var inputTitle= "";
    var addTitle = false;

    $(restOfPlan).append("<h4>Procedure Details:</h4>")
    $("#" + id + " input, #areas_"+id).each(function (index) {
        var input = $(this);

        var property = input.attr("id");
        property = property.replace(id + "-", "");
        property = property.replace(/-/g, " ");
        
        var propertyValue = input.val();

        
        var prePendingComma = '<p style="display: inline-block; margin: 5px 0 5px 0;">';
        if(!addTitle){
            prePendingComma = prePendingComma + " , ";
        }
        
        if (property.includes("title")) {
            addTitle = true;
            var inputName = 'treatment[' + plan_count + '][' + input.attr("id") + ']';
            property = property.replace("title", "");

            var propertyData = window.dataInfo[property]; 
                    var modifiedProperty = propertyData ? propertyData : modifyPropertyName(property);
            
            hiddenInputTitle = $('<input>')
                    .attr({
                        type: 'hidden',
                        name: inputName,
                        value: modifiedProperty
                    });
                
            inputTitle = `<h5 style="margin: 0;width:100%;">${modifiedProperty}</h5>`;
            
        } else if (property == "hidden") {
            $(restOfPlan).append(`<p style="width:100%;margin:2px;"></p>`);
        } else {
            if (propertyValue == "on") {
                var inputName = 'treatment[' + plan_count + '][' + input.attr("id") + ']';
                var checked = input.prop("checked") ? "" : ": []";

                if (input.prop("checked")) {
                    var tempValue = input.attr("id").replace(id, "");
                    var propertyData = window.dataInfo[tempValue]; 
                    var modifiedProperty = propertyData ? propertyData : modifyPropertyName(property);

                    var hiddenInput = $('<input>')
                    .attr({
                        type: 'hidden',
                        name: inputName,
                        value: 1
                    });

                    if(addTitle){
                        $(restOfPlan).append(hiddenInputTitle);
                        $(restOfPlan).append(inputTitle);
                        addTitle = false;
                    }

                $(restOfPlan).append(hiddenInput);

                    $(restOfPlan).append(
                        prePendingComma + modifiedProperty + checked +  "</p>"
                    );
                }


            } else {

                if(
                    input.attr("id") == 'areas_cantharidin' ||
                    input.attr("id") == 'areas_cryotherapy' ||
                    input.attr("id") == 'areas_incision' ||
                    input.attr("id") == 'areas_electro' ||
                    input.attr("id") == 'areas_debridement' ||
                    input.attr("id") == 'areas_dermabrasion' ||
                    input.attr("id") == 'areas_extraction' ||
                    input.attr("id") == 'areas_hyfrecation' ||
                    input.attr("id") == 'areas_intralesional' ||
                    input.attr("id") == 'areas_laser' ||
                    input.attr("id") == 'areas_nonlaser' ||
                    input.attr("id") == 'areas_peel' ||
                    input.attr("id") == 'areas_silver' ||
                    input.attr("id") == 'areas_snip' ||
                    input.attr("id") == 'areas_sclerotherapy' ||
                    input.attr("id") == 'areas_biopsy' || 
                    input.attr("id") == 'areas_photodynamic' || 
                    input.attr("id") == 'areas_platelet' || 
                    input.attr("id") == 'areas_microneedling'
                ){

                    if (propertyValue !== '') {
                        var inputName = 'treatment[' + plan_count + '][' + input.attr("id") + ']';
                        propertyValue = propertyValue.replace(/,/g, ", ");
                        var hiddenInput = $('<input>')
                        .attr({
                            type: 'hidden',
                            name: inputName,
                            value: propertyValue
                        });

                        if(addTitle){
                            $(restOfPlan).append(hiddenInputTitle);
                            $(restOfPlan).append(inputTitle);
                            addTitle = false;
                        }
                        
                        $(restOfPlan).append(hiddenInput);

                        $(restOfPlan).append(
                            prePendingComma +
                            propertyValue +
                            `</p>`  
                        );
                    }

                }else{
                    if (propertyValue !== '') {

                        if(addTitle){
                            $(restOfPlan).append(hiddenInputTitle);
                            $(restOfPlan).append(inputTitle);
                            addTitle = false;
                        }
                        // Remove the prefix and replace underscores with spaces
                        var tempValue = input.attr("id").replace(id, "");
                        var propertyData = window.dataInfo[tempValue]; 
                        var modifiedProperty = (propertyData ? propertyData : modifyPropertyName(property)) + `: `;
                        
                        if(tempValue.includes("free")){
                            modifiedProperty = "";
                        }

                        if(tempValue.includes("inner")){
                            prePendingComma = '<p style="display: inline-block; margin: 5px 0 5px 5px;"> ';
                        }

                        $(restOfPlan).append(
                            prePendingComma +
                            modifiedProperty +
                            propertyValue +
                            "</p>"
                        );
                    }

                }
                
                inputName = 'treatment[' + plan_count + '][' + input.attr("id") + ']';
                hiddenInput = $('<input>')
                .attr({
                    type: 'hidden',
                    name: inputName,
                    value: propertyValue
                });
                
                $(restOfPlan).append(hiddenInput);

            }
        }
    });
}


function modifyPropertyName(property) {
    // Remove the prefix and replace underscores with spaces
    return property.replace(/^[^_]+_/, '').replace(/_/g, ' ');
}

function getLocationAndCount() {
    var planId = (typeof procedurePlanId !== "undefined" && procedurePlanId)
        ? String(procedurePlanId)
        : "";
    if (!planId) {
        return;
    }

    var areas = "";
    if (typeof procedureSelectedAreas !== "undefined" && Array.isArray(procedureSelectedAreas) && procedureSelectedAreas.length) {
        areas = procedureSelectedAreas.join(", ");
    } else {
        areas = String($("#p-treated").text() || "").trim();
    }
    var areaCount = areas
        ? String((typeof procedureSelectedAreas !== "undefined" && Array.isArray(procedureSelectedAreas))
            ? procedureSelectedAreas.length
            : areas.split(",").filter(function (p) { return p.trim() !== ""; }).length)
        : "";

    $("#p-treated").text(areas);
    $("#p-lession").text(areaCount);
    $("#areas_" + planId).val(areas);
    $("#areas-count-" + planId).val(areaCount);
}

function removePrevClickedValue() {
    if (typeof procedureSelectedAreas !== "undefined" && Array.isArray(procedureSelectedAreas)) {
        procedureSelectedAreas.pop();
        var updated = procedureSelectedAreas.join(", ");
        var count = procedureSelectedAreas.length;
        $("#p-treated").text(updated);
        $("#p-lession").text(count ? String(count) : "");
        if (typeof procedurePlanId !== "undefined" && procedurePlanId) {
            $("#areas_" + procedurePlanId).val(updated);
            $("#areas-count-" + procedurePlanId).val(count ? String(count) : "");
        }
        try {
            $(".procedure-bodymap").mapster("set", false);
            if (procedureSelectedAreas.length) {
                $(".procedure-bodymap").mapster("set", true, procedureSelectedAreas.join(","));
            }
        } catch (err) {
            // ignore mapster errors if modal closed
        }
        return;
    }

    var areasArray = $("#p-treated").text().split(',');
    var areaCount = parseInt($("#p-lession").text(), 10);

    if (areasArray.length > 0 && areaCount > 0) {
        areasArray.pop();
        var updatedAreasString = areasArray.map(function (a) { return a.trim(); }).filter(Boolean).join(', ');

        $("#p-treated").text(updatedAreasString);
        $("#p-lession").text(--areaCount > 0 ? String(areaCount) : '');
        if (typeof procedurePlanId !== "undefined" && procedurePlanId) {
            $("#areas_" + procedurePlanId).val(updatedAreasString);
        }
    }
}

$(document).on('change', '.procedure_plan_select', function(){

    let selecteValue = $(this).val();

    let selectCount = $(this).attr('count');
    $("#procedure-plans-"+selectCount).attr(
        "data-plan",
        selecteValue.toLowerCase()
    );

    $("#plan-modal-"+selectCount).attr(
        "data-target",
        "#" + selecteValue.toLowerCase()
    );

    $("#plan-modal-"+selectCount).attr(
        "data-plan",
        selecteValue.toLowerCase()
    );

    $("#render-restofplans-"+selectCount).empty();

    $.ajax({
        url: "../../forms/general_dermatology/resources/procedure-desc.php",
        method: "POST",
        data: { selectedValue: selecteValue, csrf_token_form: dermCsrfToken() },
        dataType: "json",
        success: function (response) {
            $("#plan-desc-"+selectCount).empty();
            $("#plan-desc-"+selectCount).val(response.value);

        },
        error: function (xhr, status, error) {
            console.error(error);
        },
    });

    procedurePlanId = selecteValue.toLowerCase();

});

// write a function edit property id for present text


$(document).on('change', '#checkAll_Sites_Examined', function () {
    const autoSelectElementIds = ['scalp','hair','face','externalears','eyelids','lips','nose','neck','chest','shoulders','back','arms','forearms','hands','fingernails','legs','feet','toenails','groin','abdomen','ptdeclinedtoremoveunderwear'];
    if (this.checked) {
        autoSelectElementIds.forEach(function (elementId) {
            $("#" + elementId).prop('checked', true);
        });
    } else {
        const extraElements = ['externalgenitalia','genitalmucosa','conjunctivalmucosa','oralmucosa','ptdeclinedfullbodyskinexam','ptdeclinedtoremoveunderwear','ptdeclinedtoremovebra','ptwearingfingernailpolish','ptwearingtoenailpolish'];
        autoSelectElementIds.forEach(function (elementId) {
            $("#" + elementId).prop('checked', false);
        });
        extraElements.forEach(function (elementId) {
            $("#" + elementId).prop('checked', false);
        });
    }
});



$(document).on('change', '#checkAll_Lymph_Nodes', function () {
    const autoSelectElementIds = ['cervical','postauricular','occipital','submandibular','supraclavicular','axillary','inguinal','popliteal'];
    if (this.checked) {
        autoSelectElementIds.forEach(function (elementId) {
            $("#" + elementId).prop('checked', true);
        });
    } else {
        autoSelectElementIds.forEach(function (elementId) {
            $("#" + elementId).prop('checked', false);
        });
    }
});

$(document).on('change', '.single-select', function () {
    // Uncheck all checkboxes except the one that was clicked
    $('.single-select').not(this).prop('checked', false);
    var selectedId = $(this).attr('id');
        var timeSpent = '';
        switch(selectedId) {
            case '99202':
                timeSpent = '20';
                break;
            case '99203':
                timeSpent = '40';
                break;
            case '99204':
                timeSpent = '55';
                break;
            case '99205':
                timeSpent = '70';
                break;
            case '99212':
                timeSpent = '15';
                break;
            case '99213':
                timeSpent = '25';
                break;
            case '99214':
                timeSpent = '35';
                break;
            case '99215':
                timeSpent = '50';
                break;
            default:
                timeSpent = ''; // Default value if none selected
        }

        // Set the hidden input's value
        $('#total_spent').val(timeSpent);
        $('#billing_code').val(selectedId);
});
