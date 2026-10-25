
function dermCsrfToken() {
    var el = document.querySelector('input[name="csrf_token_form"]');
    return el ? el.value : '';
}

/**
 * Resolve the main-form procedure-details container for a finding index.
 * Dynamic cards use #render-restofplans-N; legacy first card may use unsuffixed
 * #render-restofplans. Never write into the hidden sample template.
 */
function dermProcedureDetailsTarget(planCount) {
    var count = String(planCount == null ? "" : planCount);
    var $sample = $("#custom-icd-form-sample");

    function notSample($nodes) {
        return $nodes.filter(function () {
            return $sample.length === 0 || !$.contains($sample[0], this);
        });
    }

    var $bySuffix = notSample($("#render-restofplans-" + count));
    if ($bySuffix.length) {
        return $bySuffix.first();
    }

    // Server-rendered index 0 historically used the unsuffixed id.
    if (count === "0" || count === "") {
        var $unsuffixed = notSample($("#plan-forms #render-restofplans"));
        if ($unsuffixed.length) {
            return $unsuffixed.first();
        }
    }

    // Fallback: container on the card whose Procedure Details button opened the modal.
    var $btn = $(".plan-modal").filter(function () {
        return String($(this).attr("count")) === count;
    }).first();
    if ($btn.length) {
        var $inCard = notSample($btn.closest(".custom-icd-form").find("[id^='render-restofplans']"));
        if ($inCard.length) {
            return $inCard.first();
        }
    }

    return $();
}

function getValuesFromModal(id) {
    var plan_count = $("#plan_count").val();
    var $restOfPlan = dermProcedureDetailsTarget(plan_count);

    if (!$restOfPlan.length) {
        console.error("getValuesFromModal: no procedure details target for plan_count=", plan_count);
        return;
    }

    // Keep legacy callers that still reference restOfPlan as a selector string working.
    restOfPlan = $restOfPlan;

    $restOfPlan.empty();
    var hiddenInputTitle = "";
    var inputTitle = "";
    var addTitle = false;

    $restOfPlan.append("<h4>Procedure Details:</h4>");
    $("#" + id + " input, #areas_" + id).each(function () {
        var input = $(this);
        var inputId = input.attr("id") || "";
        if (!inputId) {
            return;
        }

        var property = inputId.replace(id + "-", "").replace(/-/g, " ");
        var propertyValue = input.val();

        var prePendingComma = '<p style="display: inline-block; margin: 5px 0 5px 0;">';
        if (!addTitle) {
            prePendingComma = prePendingComma + " , ";
        }

        if (property.includes("title")) {
            addTitle = true;
            var inputName = "treatment[" + plan_count + "][" + inputId + "]";
            property = property.replace("title", "");

            var propertyData = window.dataInfo ? window.dataInfo[property] : null;
            var modifiedProperty = propertyData ? propertyData : modifyPropertyName(property);

            hiddenInputTitle = $("<input>").attr({
                type: "hidden",
                name: inputName,
                value: modifiedProperty,
            });

            inputTitle = '<h5 style="margin: 0;width:100%;">' + modifiedProperty + "</h5>";
        } else if (property == "hidden") {
            $restOfPlan.append('<p style="width:100%;margin:2px;"></p>');
        } else {
            if (propertyValue == "on") {
                var checkedName = "treatment[" + plan_count + "][" + inputId + "]";
                var checked = input.prop("checked") ? "" : ": []";

                if (input.prop("checked")) {
                    var tempValue = inputId.replace(id, "");
                    var checkedData = window.dataInfo ? window.dataInfo[tempValue] : null;
                    var checkedLabel = checkedData ? checkedData : modifyPropertyName(property);

                    var checkedHidden = $("<input>").attr({
                        type: "hidden",
                        name: checkedName,
                        value: 1,
                    });

                    if (addTitle) {
                        $restOfPlan.append(hiddenInputTitle);
                        $restOfPlan.append(inputTitle);
                        addTitle = false;
                    }

                    $restOfPlan.append(checkedHidden);
                    $restOfPlan.append(prePendingComma + checkedLabel + checked + "</p>");
                }
            } else {
                var isAreasField =
                    inputId == "areas_cantharidin" ||
                    inputId == "areas_cryotherapy" ||
                    inputId == "areas_incision" ||
                    inputId == "areas_electro" ||
                    inputId == "areas_debridement" ||
                    inputId == "areas_dermabrasion" ||
                    inputId == "areas_extraction" ||
                    inputId == "areas_hyfrecation" ||
                    inputId == "areas_intralesional" ||
                    inputId == "areas_laser" ||
                    inputId == "areas_nonlaser" ||
                    inputId == "areas_peel" ||
                    inputId == "areas_silver" ||
                    inputId == "areas_snip" ||
                    inputId == "areas_sclerotherapy" ||
                    inputId == "areas_biopsy" ||
                    inputId == "areas_photodynamic" ||
                    inputId == "areas_platelet" ||
                    inputId == "areas_microneedling";

                if (isAreasField) {
                    if (propertyValue !== "") {
                        var areasName = "treatment[" + plan_count + "][" + inputId + "]";
                        propertyValue = String(propertyValue).replace(/,/g, ", ");
                        var areasHidden = $("<input>").attr({
                            type: "hidden",
                            name: areasName,
                            value: propertyValue,
                        });

                        if (addTitle) {
                            $restOfPlan.append(hiddenInputTitle);
                            $restOfPlan.append(inputTitle);
                            addTitle = false;
                        }

                        $restOfPlan.append(areasHidden);
                        $restOfPlan.append(prePendingComma + propertyValue + "</p>");
                    }
                } else {
                    if (propertyValue !== "") {
                        if (addTitle) {
                            $restOfPlan.append(hiddenInputTitle);
                            $restOfPlan.append(inputTitle);
                            addTitle = false;
                        }
                        var freeTemp = inputId.replace(id, "");
                        var freeData = window.dataInfo ? window.dataInfo[freeTemp] : null;
                        var freeLabel = (freeData ? freeData : modifyPropertyName(property)) + ": ";

                        if (freeTemp.includes("free")) {
                            freeLabel = "";
                        }

                        if (freeTemp.includes("inner")) {
                            prePendingComma = '<p style="display: inline-block; margin: 5px 0 5px 5px;"> ';
                        }

                        $restOfPlan.append(
                            prePendingComma + freeLabel + propertyValue + "</p>"
                        );
                    }
                }

                var valueName = "treatment[" + plan_count + "][" + inputId + "]";
                var valueHidden = $("<input>").attr({
                    type: "hidden",
                    name: valueName,
                    value: propertyValue,
                });
                $restOfPlan.append(valueHidden);
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
