
function dermCsrfToken() {
    var el = document.querySelector('input[name="csrf_token_form"]');
    return el ? el.value : '';
}
var selectedItems = [];
var formCount = 1;
// cratedForm = [];

/**
 * Phase 4 helpers: map region key -> catalog location + normalized markers.
 */
function dermMapBootstrap() {
    return window.GeneralDermatologyMap || { byId: {}, byLabel: {}, savedFindings: [] };
}

function dermNormalizeMapKey(mapKey) {
    return String(mapKey || '').replace(/_/g, ' ').replace(/\s+/g, ' ').trim();
}

function dermInferLaterality(label) {
    var l = String(label || '').toLowerCase();
    if (l.indexOf('left') !== -1) {
        return 'L';
    }
    if (l.indexOf('right') !== -1) {
        return 'R';
    }
    if (l.indexOf('mid ') !== -1 || l.indexOf('midline') !== -1 || l.indexOf('midline') !== -1) {
        return 'M';
    }
    return '';
}

function dermInferView(label, fallback) {
    var l = String(label || '').toLowerCase();
    var faceHints = ['face', 'forehead', 'cheek', 'nose', 'nasal', 'ala', 'lip', 'chin', 'ear', 'eyelid', 'temple', 'scalp', 'brow', 'infraorbital', 'orbital'];
    for (var i = 0; i < faceHints.length; i++) {
        if (l.indexOf(faceHints[i]) !== -1) {
            return 'face';
        }
    }
    if (l.indexOf('posterior') !== -1 || l.indexOf('back ') !== -1) {
        return 'body_back';
    }
    return fallback || 'body_front';
}

function dermSlugFromLabel(label) {
    var slug = String(label || '').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    return slug || 'location';
}

function dermResolveLocationFromMapKey(mapKey, fallbackView) {
    var label = dermNormalizeMapKey(mapKey);
    var boot = dermMapBootstrap();
    var known = (boot.byLabel && boot.byLabel[label.toLowerCase()]) || null;
    if (known) {
        return {
            id: known.id,
            label: known.label,
            laterality: known.laterality || dermInferLaterality(known.label),
            view: known.view || fallbackView || dermInferView(known.label, fallbackView),
            markers: []
        };
    }
    return {
        id: dermSlugFromLabel(label),
        label: label,
        laterality: dermInferLaterality(label),
        view: fallbackView || dermInferView(label, fallbackView),
        markers: []
    };
}

function dermViewFromImage($img) {
    var usemap = String(($img && $img.attr && $img.attr('usemap')) || '').replace('#', '').toLowerCase();
    if (usemap.indexOf('face') !== -1) {
        return 'face';
    }
    return 'body_front';
}

function dermNormalizedMarkerFromEvent(evt, $img) {
    if (!evt || typeof evt.pageX !== 'number' || !$img || !$img.length) {
        return null;
    }
    var offset = $img.offset();
    var w = $img.width();
    var h = $img.height();
    if (!offset || !w || !h) {
        return null;
    }
    var x = (evt.pageX - offset.left) / w;
    var y = (evt.pageY - offset.top) / h;
    x = Math.max(0, Math.min(1, x));
    y = Math.max(0, Math.min(1, y));
    return { x: Math.round(x * 1e6) / 1e6, y: Math.round(y * 1e6) / 1e6 };
}

function dermFindMapImageForEvent(evt) {
    var $match = $(".body-map").filter(function () {
        var $el = $(this);
        var o = $el.offset();
        var w = $el.width();
        var h = $el.height();
        if (!o || !w || !h || !evt || typeof evt.pageX !== 'number') {
            return false;
        }
        return evt.pageX >= o.left && evt.pageX <= o.left + w &&
            evt.pageY >= o.top && evt.pageY <= o.top + h;
    }).first();
    if ($match.length) {
        return $match;
    }
    return $(".body-map").first();
}

function dermWriteLocationToFindingCard($card, location, index) {
    if (!$card || !$card.length || !location) {
        return;
    }
    var $loc = $card.find('.finding-location').first();
    var $json = $card.find('.finding-location-json').first();
    if ($loc.length) {
        $loc.val(location.label || '');
        if (typeof index !== 'undefined' && index !== null && index !== '') {
            $loc.attr('name', 'treatment[' + index + '][finding_location]');
            $loc.attr('data-finding-index', index);
        }
    }
    if ($json.length) {
        $json.val(JSON.stringify(location));
        if (typeof index !== 'undefined' && index !== null && index !== '') {
            $json.attr('name', 'treatment[' + index + '][location_json]');
        }
    }
}

function dermMapKeyFromLocation(location, findingLocation) {
    if (location && location.label) {
        return dermNormalizeMapKey(location.label);
    }
    return dermNormalizeMapKey(findingLocation || '');
}

function dermRestoreMapSelectionsFromFindings() {
    var findings = (dermMapBootstrap().savedFindings || []);
    var keys = [];
    findings.forEach(function (finding) {
        var key = dermMapKeyFromLocation(finding.location, finding.finding_location);
        if (key && keys.indexOf(key) === -1) {
            keys.push(key);
            if ($.inArray(key, selectedItems) < 0) {
                selectedItems.push(key);
            }
        }
    });
    if (!keys.length) {
        return;
    }
    var keyList = keys.join(',');
    $(".body-map").each(function () {
        try {
            $(this).mapster('set', true, keyList);
        } catch (err) {
            // Mapster may not be ready on a given image (e.g. hidden swiper slide).
        }
    });
}

$(document).ready(function () {
    var image = $(".body-map");
    var procedureBodyMap = $(".procedure-bodymap");

    var defaultDipTooltip = "<b><u>Spine</u></b>";
    setTimeout(function(){
        CallOnLoad();
        dermRestoreMapSelectionsFromFindings();
    }, 1500)


    function CallOnLoad()  {
        var severityCount = $("#fetch_severity").text();

        if(severityCount.length > 0){

            $("#total_severity").val(severityCount);
        }

    }


    image.mapster({
        fill: true,
        fillOpacity: 0.5,
        fillColor: "d42e16",
        strokeColor: "3320FF",
        strokeOpacity: 1,
        strokeWidth: 1,
        isSelectable: true,
        singleSelect: false,
        mapKey: "name",
        listKey: "key",
        render_highlight: {
            fillColor: "000000",
        },
        fadeInterval: 50,
        onClick: function (e) {
            var newToolTip = defaultDipTooltip;
            if ($.inArray(e.key, selectedItems) >= 0) {
                selectedItems.splice($.inArray(e.key, selectedItems), 1);
            } else {
                selectedItems.push(e.key);
            }

            var evt = e && e.e ? e.e : null;
            var $img = dermFindMapImageForEvent(evt);
            var view = dermViewFromImage($img);
            var location = dermResolveLocationFromMapKey(e.key, view);
            var marker = dermNormalizedMarkerFromEvent(evt, $img);
            if (marker) {
                location.markers = [marker];
            }
            createNewFormWhenClickTheBodyLocation(e.key, location);

        },
    });

    procedureBodyMap.mapster({
        fill: true,
        fillOpacity: 0.5,
        fillColor: "d42e16",
        strokeColor: "3320FF",
        strokeOpacity: 1,
        strokeWidth: 1,
        isSelectable: false,
        singleSelect: false,
        mapKey: "name",
        listKey: "key",
        render_highlight: {
            fillColor: "000000",
        },
        fadeInterval: 50,
        onClick: function (e) {
            var newToolTip = defaultDipTooltip;
            var treated = document.getElementById("p-treated");
            var numOfTreated = document.getElementById("p-lession");
            item = e.key.replace(/_/g, " ");
            // if ($.inArray(item, selectedItems) >= 0) {
            //     selectedItems.splice($.inArray(item, selectedItems), 1);
            // } else {
            selectedItems.push(item);
            // }
            treated.innerHTML = selectedItems;
            numOfTreated.innerHTML = selectedItems.length;
        },
    });


    $(document).on('click', '.model_value', function(){
        $("#p-treated").text('');
        $("#p-lession").text('');
        var menu = $(this).attr('model-value');
        var text = $("#areas_" + menu).val();
        if (text.trim() !== "") {
            var areasArray = text.split(',');

            var trimmedAreasArray = areasArray.map(function(area) {
                return area.trim();
            });

            selectedItems = trimmedAreasArray;

            $("#p-treated").text(text);
            var numOfTreated = document.getElementById("p-lession");
            numOfTreated.innerHTML = selectedItems.length;
        }
    });

    $(document).on('click', '#addNewPlan', function() {
        createNewFormWhenClickTheBodyLocation('', null);
    });

    // Keep location_json in sync when the clinician edits the free-text label.
    $(document).on('change blur', '.finding-location', function () {
        var $input = $(this);
        var $card = $input.closest('.custom-icd-form');
        var label = dermNormalizeMapKey($input.val());
        if (!label) {
            return;
        }
        var index = $input.attr('data-finding-index');
        if (typeof index === 'undefined' || index === null || index === '') {
            var nameAttr = $input.attr('name') || '';
            var m = nameAttr.match(/treatment\[(\d+)\]/);
            index = m ? m[1] : null;
        }
        var existing = null;
        try {
            var raw = $card.find('.finding-location-json').val();
            existing = raw ? JSON.parse(raw) : null;
        } catch (err) {
            existing = null;
        }
        var resolved = dermResolveLocationFromMapKey(label, (existing && existing.view) || '');
        if (existing && existing.markers && existing.markers.length && dermNormalizeMapKey(existing.label) === label) {
            resolved.markers = existing.markers;
        }
        dermWriteLocationToFindingCard($card, resolved, index);
    });

    function createNewFormWhenClickTheBodyLocation(name, locationObj) {

        var countvalue = $("#treatment_count").val();
        if(countvalue > 0){
            formCount = parseInt(countvalue)+1;
            $("#treatment_count").val(formCount);
        }
        name = dermNormalizeMapKey(name);
        if (!locationObj && name) {
            locationObj = dermResolveLocationFromMapKey(name, '');
        }
        if (locationObj && !locationObj.label && name) {
            locationObj.label = name;
        }
        // Prefer structured label for the visible field.
        var displayName = (locationObj && locationObj.label) ? locationObj.label : name;

        // Prefer the last real finding card (not the hidden sample) with an empty location.
        var $lastCard = $("#plan-forms .custom-icd-form").not("#custom-icd-form-sample").last();
        if (!$lastCard.length) {
            $lastCard = $(".custom-icd-form").not("#custom-icd-form-sample").last();
        }
        var lastLocVal = $lastCard.find(".finding-location").first().val();
        if ($lastCard.length && (lastLocVal === undefined || lastLocVal === null || String(lastLocVal).trim() === "")) {
            var lastIndex = $lastCard.find(".finding-location").attr("data-finding-index");
            if (typeof lastIndex === 'undefined' || lastIndex === null || lastIndex === '') {
                var lastName = $lastCard.find(".finding-location").attr("name") || '';
                var lastMatch = lastName.match(/treatment\[(\d+)\]/);
                lastIndex = lastMatch ? lastMatch[1] : formCount;
            }
            dermWriteLocationToFindingCard($lastCard, locationObj || { id: '', label: displayName, laterality: '', view: '', markers: [] }, lastIndex);
        } else {
            var clonedForm = $("#custom-icd-form-sample").clone();

            // create id
            var icdForm = "custom-icd-form-" + formCount;
            var locationFieldId = "finding-location-" + formCount;
            var locationJsonId = "finding-location-json-" + formCount;
            var description = "finding-description-" + formCount;
            var minute = "minute-" + formCount;
            var icd = "finding-icd-" + formCount;
            var dxSId = "dx-s-" + formCount;
            var dxAlt = "dx-s-" + formCount;
            var idcAlt = "icd-alt-"+formCount;
            var dxSName = "dx-s-" + formCount + "[]";
            var dxDescId = "dx-desc-" + formCount;
            var dxDescName = "dx-desc-" + formCount + "[]";
            var drop = "finding-drop-" + formCount;
            var plan = "finding-plan-" + formCount;
            var procedure = "procedure-plans-" + formCount;
            var planDesc = "plan-desc-" + formCount;
            var planModal = "plan-modal-" + formCount;
            var render = "render-restofplans-" + formCount;

            // unselecting the options in mutltiselect
            clonedForm.find("option:selected").prop("selected", false);

            // set id
            clonedForm.attr("id", icdForm);
            if (formCount % 2 == 1) {
                clonedForm
                .prevObject.css("background-color", "#f1f1f1");
            }
            clonedForm
            .find("#new-plan")
            .attr("id", (displayName || ('plan' + formCount)).replace(/\s+/g, ''))
            .val(formCount);
            clonedForm
            .find("#delete_button")
            .attr("data-name", `treatment[${formCount}][severity_drop]`)
            .attr("class", 'btn btn-danger remove_plan mb-3')
            .attr("data-value", formCount);
            clonedForm
            .find("#finding-location")
            .attr("id", locationFieldId)
            .attr("name", 'treatment['+formCount+'][finding_location]')
            .attr("data-finding-index", formCount)
            .val(displayName);
            clonedForm
            .find("#finding-location-json, .finding-location-json")
            .attr("id", locationJsonId)
            .attr("name", 'treatment['+formCount+'][location_json]')
            .val(locationObj ? JSON.stringify(locationObj) : '');
            clonedForm
            .find("#finding-description")
            .attr("id", description)
            .attr("name", 'treatment['+formCount+'][finding_description]');

            clonedForm.find("#finding-icd").attr("id", icd).attr("name", icd);
            clonedForm.find("#dx-s").attr("id", dxSId).attr("name", 'treatment['+formCount+'][dx_s]');
            clonedForm
            .find("#icd-alt")
            .attr("id", idcAlt)
            .attr("name", 'treatment['+formCount+'][icd_alt]')
            .val("");
            clonedForm
            .find("#dx-desc")
            .attr("id", dxDescId)
            .attr("name", 'treatment['+formCount+'][dx_desc]');
            clonedForm.find(".select2").remove();
            clonedForm
            .find("#dx-alt")
            .attr("id", dxAlt)
            .attr("name", 'treatment['+formCount+'][dx_alt]')
            .val("");
            clonedForm
            .find("#severity-drop")
            .attr("id", drop)
            .attr("class", 'severity-pick form-control')
            .attr("name", 'treatment['+formCount+'][severity_drop]');
            clonedForm
            .find("#plan")
            .attr("id", plan)
            .attr("name", 'treatment['+formCount+'][plan]')
            .val("");
            clonedForm
            .find("#procedure-plans")
            .attr("id", procedure)
            .attr("name", 'treatment['+formCount+'][procedure_plans]')
            .val("");
            clonedForm
            .find("#plan-desc")
            .attr("id", planDesc)
            .attr("name", 'treatment['+formCount+'][plan_desc]')
            .val("");
            clonedForm
            .find("#plan-modal")
            .attr("id", planModal)
            .attr("name", planModal)
            .attr("count", formCount)
            .val("");
            clonedForm
            .find("#render-restofplans")
            .attr("id", render)
            .attr("name", render)
            .val("")
            .empty();

            
            // text
            clonedForm.find("#icd-text-num").text(`ICD Dx #${formCount}`);
            clonedForm.find("#plan-text-num").text(`Plan #${formCount}`);
            clonedForm
            .find("#procedure-plan-num")
            .text(`Procedure Plan #${formCount}`);
            clonedForm.appendTo("#plan-forms");
            
            clonedForm.find(".select2").css({ width: "100%" });
            clonedForm.find(".select2").css({ width: "100%" });

            formCount++;
            setTimeout(() => {
            $(".dx-s").select2();
            $(".dx-desc").select2();
            }, 200);
            $(".dx-s").select2();
            $(".dx-desc").select2();

            $("#" + dxSId).on("change", function () {
                var selectedValue = $(this).val();
                $.ajax({
                    url: "../../forms/general_dermatology/resources/dx-desc.php",
                    method: "POST",
                    data: { selectedValue: selectedValue, csrf_token_form: dermCsrfToken() },
                    dataType: "json",
                    success: function (response) {
                        $("#" + dxDescId).empty();
                        if (response.value && response.value.length > 0) {
                            $.each(response.value, function (index, value) {
                                console.log(value);
                                $("#" + dxDescId).append(
                                    $("<option>", {
                                        value: value,
                                        text: value,
                                    })
                                );
                            });
                        }
                        $("#" + dxDescId).select2();
                        // console.log(response);
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                    },
                });
            });
            $("#" + procedure).on("change", function () {
                var selectedValue = $(this).val();
                procedurePlanId = selectedValue.toLowerCase();
                $.ajax({
                    url: "../../forms/general_dermatology/resources/procedure-desc.php",
                    method: "POST",
                    data: { selectedValue: selectedValue, csrf_token_form: dermCsrfToken() },
                    dataType: "json",
                    success: function (response) {
                        $("#" + planDesc).empty();
                        $("#" + render).empty();
                        if (response.value && response.value.length > 0) {
                            $("#" + planDesc).val(response.value);
                            $("#" + planModal).attr(
                                "data-target",
                                "#" + selectedValue.toLowerCase()
                            );
                            $("#" + planModal).attr(
                                "data-plan",
                                selectedValue.toLowerCase()
                            );
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                    },
                });
            });
            bindTextArea();
        }
    }


    function removeExistingFormWhenClickTheBodyLocation(keyWithoutSpaces){


        var dynamicFormId = $('#' + keyWithoutSpaces);

        var planId = dynamicFormId.val();

        var sev_name = $(`#finding-drop-${planId}`).attr('name');
        var sev_val = $(`#finding-drop-${planId}`).val();


        // Find index of the matching item in the array
        var indexToRemove = selections.findIndex(item => item.name === sev_name && item.value === sev_val);

        // Remove the item if found
        if (indexToRemove !== -1) {
            selections.splice(indexToRemove, 1);

            updateCounts();
        }


        // if(cratedForm.length == 0){
        //     let treatmentsCounts = $("#total_treatment_count").val();
        //     if(treatmentsCounts > 0){
        //         $("#treatment_count").val(parseInt(treatmentsCounts));
        //     }else{
        //         formCount = 1;
        //     }


        // }
        dynamicFormId.parent('#custom-icd-form').remove();

    }

    let selections = []; // Initialize an array to store the selections

    function updateCounts() {
        let valueCounts = {};

        selections.forEach(entry => {
            if (!valueCounts[entry.value]) {
                valueCounts[entry.value] = 1;
            } else {
                valueCounts[entry.value]++;
            }
        });

        let nameList = document.getElementById('nameList');

        // Clear the existing list
        nameList.innerHTML = '';
        let jsonString = JSON.stringify(valueCounts);
        $("#total_severity").val(jsonString);
        $("#fetch_severity").text(jsonString);
        // Update the list with names and their counts
        for (let key in valueCounts) {
            let listItem = document.createElement('li');
            listItem.textContent = `${key}(${valueCounts[key]})`;
            nameList.appendChild(listItem);
        }
    }

    $(document).on('change', '.severity-pick', function() {

        var severityList = $("#fetch_severity").text();
        if(severityList.length > 0){

            selections = [];

            $('.severity-pick').each(function(index, element) {
                let name = $(element).attr('name');
                let value = $(element).val(); // Use .val() to get the selected value

                selections.push({
                    name: name,
                    value: value
                });
            });


        }

        let name = $(this).attr('name');
        let value = $(this).val();

        let existingEntryIndex = selections.findIndex(entry => entry.name === name);

        if (existingEntryIndex !== -1) {

            selections[existingEntryIndex] = { name, value };
        } else {

            selections.push({ name, value });
        }

        updateCounts();

    });



    $(document).on('click', '.plan-modal', function(){

        let planCount = $(this).attr('count');
        $("#plan_count").val(planCount);
        procedurePlanId = $(this).attr('data-plan');

        if(procedurePlanId == 'cantharidin'){
            
            var cantharidin_methods = [
                "cantharidin_none","cantharidin_paper","cantharidin_4h","cantharidin_6h"
            ];

             // Set the values into the visible checkboxes for each cantharidin method
             for (var i = 0; i < cantharidin_methods.length; i++) {
                var cantharidin_method = cantharidin_methods[i];
                var cantharidin_value = $('input[name="treatment[' + planCount + '][' + cantharidin_method + ']"]').val();
                $('#' + cantharidin_method).prop('checked', cantharidin_value == "1");
            }
            
            // Additional cantharidin fields
            var cantharidin_inputs = [
                "areas_cantharidin","areas-count-cantharidin","cantharidin-dressing-free-text","cantharidin_time_free_text"
            ];
            for (var i = 0; i < cantharidin_inputs.length; i++) {
                var cantharidin_input_key = cantharidin_inputs[i];
                var cantharidin_input_value = $('input[name="treatment[' + planCount + ']['+cantharidin_input_key+']"]').val();
                $('#'+cantharidin_input_key).val(cantharidin_input_value);
            }
        }

        if(procedurePlanId == 'electro'){

            // Additional electro fields
            var electro_methods = [
                'electro-alcohol', 'electro-hibi', 'electro-iodine', 'electro-lasercyn', 'electro-1p-lidocaine','electro-hibiclens',
                'electro-1p-lidocaine-with-epinephrine', 'electro-2p-lidocaine', 'electro-2p-lidocaine-wtih-epinephrine',
                'electro-2mm', 'electro-3mm', 'electro-4mm', 'electro-5mm', 'electro-6mm', 'electro-7mm', 'electro-8mm',
                'electro-1', 'electro-2', 'electro-3', 'electro-4', 'electro-mupirocin', 'electro-polysporin', 'electro-bacitracin',
                'electro-petrolatum', 'electro-pressure-dressing-with-telfa-gauze-and-hypafix',
                'electro-pressure-dressing-with-telfa-gauze-and-papertape', 'electro-regular-bandage', 'electro-telfa',
                'electro-paper-tape', 'electro-hypafix', 'electro-hydrocolloid','electro-84p-sodium-bicarbonate','electro-05p-ropivacaine','electro-1p-ropivacaine',
                'electro-sterile-water','electro-09p-bacteriostatic-sodium-chloride','electro-025p-marcaine-wtih-epinephrine','electro-025p-bupivicaine-wtih-epinephrine',
                'electro-025p-bupivicaine','electro-05p-bupivicaine-wtih-epinephrine','electro-05p-bupivicaine','electro-05p-lidocaine-with-epinephrine',
                'electro-anesthetic-volume-1cc','electro-anesthetic-volume-1-2cc','electro-anesthetic-volume-2-3cc',
                'electro-anesthetic-volume-3-4cc','electro-anesthetic-volume-4-5cc'
            ];

            // Set the values into the visible checkboxes for each electro method
            for (var i = 0; i < electro_methods.length; i++) {
                var electro_method = electro_methods[i];
                var electro_value = $('input[name="treatment[' + planCount + '][' + electro_method + ']"]').val();
                $('#' + electro_method).prop('checked', electro_value == "1");
            }

            
            var electro_inputs = [
                "electro-freeS","electro-anesthetic-free-text","areas_electro","areas-count-electro","electro-curette-size-free-text","electro-pre-op-size-free-text","electro-margins-free-text",
                "electro-post-op-size-free-text","electro-aftercare-tropical-free-text","electro-bandage-free-text","electro-anesthetic-volume-free-text","electro-aftercare_free_text"
            ];
            for (var i = 0; i < electro_inputs.length; i++) {
                var electro_input_key = electro_inputs[i];
                var electro_input_value = $('input[name="treatment[' + planCount + ']['+electro_input_key+']"]').val();
                $('#'+electro_input_key).val(electro_input_value);
            }
        }

        if (procedurePlanId == 'cryotherapy') {

            var cryotherapy_methods = [
                "cryotherapy_high_pressure_canister","cryotherapy_cotton_swab","cryotherapy_spf","cryotherapy_petrolatum","cryotherapy_triamcinolone-0-1p-ointment","cryotherapy_hydrocortisone-2-5p-ointment"
            ];

             // Set the values into the visible checkboxes for each cryotherapy method
             for (var i = 0; i < cryotherapy_methods.length; i++) {
                var cryotherapy_method = cryotherapy_methods[i];
                var cryotherapy_value = $('input[name="treatment[' + planCount + '][' + cryotherapy_method + ']"]').val();
                $('#' + cryotherapy_method).prop('checked', cryotherapy_value == "1");
            }
            
            // Additional cryotherapy fields
            var cryotherapy_inputs = [
                "cryotherapy_cryotherapy_free_text","areas_cryotherapy","areas-count-cryotherapy","cryotherapy_aftercare_free_text"
            ];
            for (var i = 0; i < cryotherapy_inputs.length; i++) {
                var cryotherapy_input_key = cryotherapy_inputs[i];
                var cryotherapy_input_value = $('input[name="treatment[' + planCount + ']['+cryotherapy_input_key+']"]').val();
                $('#'+cryotherapy_input_key).val(cryotherapy_input_value);
            }
        }


        if(procedurePlanId == 'debridement'){

            // Additional debridement fields
            var debridement_methods = [
                'debridement-alcohol', 'debridement-hibiclens', 'debridement-iodine', 'debridement-lasercyn',
                'debridement-1p-lidocaine', 'debridement-1p-lidocaine-with-epinephrine', 'debridement-2p-lidocaine',
                'debridement-2p-lidocaine-wtih-epinephrine', 'debridement-mechanical', 'debridement-surgical',
                'debridement-autolytic', 'debridement-enzymatic', 'debridement-mupirocin', 'debridement-polysporin',
                'debridement-bacitracin', 'debridement-petrolatum', 'debridement-pressure-dressing-with-telfa-gauze-and-hypafix',
                'debridement-pressure-dressing-with-telfa-gauze-and-papertape', 'debridement-regular-bandage', 'debridement-telfa',
                'debridement-paper-tape', 'debridement-hypafix', 'debridement-hydrocolloid','debridement-05p-lidocaine-with-epinephrine','debridement-05p-bupivicaine',
                'debridement-05p-bupivicaine-wtih-epinephrine','debridement-025p-bupivicaine','debridement-025p-bupivicaine-wtih-epinephrine',
                'debridement-025p-marcaine-wtih-epinephrine','debridement-09p-bacteriostatic-sodium-chloride','debridement-sterile-water',
                'debridement-1p-ropivacaine','debridement-05p-ropivacaine','debridement-84p-sodium-bicarbonate',
                'debridement-anesthetic-volume-1cc','debridement-anesthetic-volume-1-2cc','debridement-anesthetic-volume-2-3cc',
                'debridement-anesthetic-volume-3-4cc','debridement-anesthetic-volume-4-5cc'
            ];

            // Set the values into the visible checkboxes for each debridement method
            for (var i = 0; i < debridement_methods.length; i++) {
                var debridement_method = debridement_methods[i];
                var debridement_value = $('input[name="treatment[' + planCount + '][' + debridement_method + ']"]').val();
                $('#' + debridement_method).prop('checked', debridement_value == "1");
            }

            var debridement_inputs = [
                "debridement-skin-prep-free-text","debridement-anesthetic-free-text","areas_debridement","areas-count-debridement","debridement-method-free-text",
                "debridement-aftercare-tropical-free-text","debridement-bandage-free-text","debridement-anesthetic-volume-free-text","debridement-aftercare_free_text"
            ];
            for (var i = 0; i < debridement_inputs.length; i++) {
                var debridement_input_key = debridement_inputs[i];
                var debridement_input_value = $('input[name="treatment[' + planCount + ']['+debridement_input_key+']"]').val();
                $('#'+debridement_input_key).val(debridement_input_value);
            }
        }

        if(procedurePlanId == 'dermabrasion'){

            // Additional dermabrasion fields
            var dermabrasion_methods = [
                'dermabrasion-alcohol', 'dermabrasion-hibiclens', 'dermabrasion-iodine', 'dermabrasion-lasercyn',
                'dermabrasion-1p-lidocaine', 'dermabrasion-1p-lidocaine-with-epinephrine', 'dermabrasion-2p-lidocaine',
                'dermabrasion-2p-lidocaine-wtih-epinephrine', 'dermabrasion-rotating-motor-device', 'dermabrasion-pear',
                'dermabrasion-cylindrical', 'dermabrasion-disc', 'dermabrasion-mupirocin', 'dermabrasion-polysporin',
                'dermabrasion-bacitracin', 'dermabrasion-petrolatum', 'dermabrasion-spf', 'dermabrasion-triamcinolone-0-01p-ointment',
                'dermabrasion-hydrocortisone-2-5p-ointment', 'dermabrasion-pressure-dressing-with-telfa-gauze-and-hypafix',
                'dermabrasion-pressure-dressing-with-telfa-gauze-and-papertape', 'dermabrasion-regular-bandage', 'dermabrasion-telfa',
                'dermabrasion-paper-tape', 'dermabrasion-hypafix', 'dermabrasion-hydrocolloid','dermabrasion-05p-lidocaine-with-epinephrine',
                'dermabrasion-05p-bupivicaine','dermabrasion-05p-bupivicaine-wtih-epinephrine','dermabrasion-025p-bupivicaine',
                'dermabrasion-025p-bupivicaine-wtih-epinephrine','dermabrasion-025p-marcaine-wtih-epinephrine','dermabrasion-09p-bacteriostatic-sodium-chloride',
                'dermabrasion-sterile-water','dermabrasion-1p-ropivacaine','dermabrasion-05p-ropivacaine','dermabrasion-84p-sodium-bicarbonate',
                'dermabrasion-anesthetic-volume-1cc','dermabrasion-anesthetic-volume-1-2cc','dermabrasion-anesthetic-volume-2-3cc',
                'dermabrasion-anesthetic-volume-3-4cc','dermabrasion-anesthetic-volume-4-5cc','dermabrasion-triamcinolone-0-1p-ointment'
            ];

            // Set the values into the visible checkboxes for each dermabrasion method
            for (var i = 0; i < dermabrasion_methods.length; i++) {
                var dermabrasion_method = dermabrasion_methods[i];
                var dermabrasion_value = $('input[name="treatment[' + planCount + '][' + dermabrasion_method + ']"]').val();
                $('#' + dermabrasion_method).prop('checked', dermabrasion_value == "1");
            }

            var dermabrasion_inputs = [
                "dermabrasion-skin-prep-free-text","dermabrasion-anesthetic-free-text","areas_dermabrasion","areas-count-dermabrasion","dermabrasion-name-of-device-free-text",
                "dermabrasion-fraises-free-text","dermabrasion-aftercare-tropical-free-text","dermabrasion-bandage-free-text","dermabrasion-anesthetic-volume-free-text",
                "dermabrasion-Name-of-device-free-text","dermabrasion-Fraises-free-text","dermabrasion-aftercare_free_text"
            ];
            for (var i = 0; i < dermabrasion_inputs.length; i++) {
                var dermabrasion_input_key = dermabrasion_inputs[i];
                var dermabrasion_input_value = $('input[name="treatment[' + planCount + ']['+dermabrasion_input_key+']"]').val();
                $('#'+dermabrasion_input_key).val(dermabrasion_input_value);
            }
        }

        if(procedurePlanId == 'hyfrecation'){


            // Additional hyfrecation fields
            var hyfrecation_methods = [
                'hyfrecation-alcohol', 'hyfrecation-hibiclens', 'hyfrecation-iodine', 'hyfrecation-lasercyn',
                'hyfrecation-low-setting', 'hyfrecation-high-setting', 'hyfrecation-dermatosis-papulosa-nigra',
                'hyfrecation-skin-tags', 'hyfrecation-cherry-angiomas', 'hyfrecation-seborrheic-keratoses',
                'hyfrecation-syringomas', 'hyfrecation-sebaceous-hyperplasia', 'hyfrecation-milia', 'hyfrecation-hidrocystoma',
                'hyfrecation-none','hyfrecation-topical-5p-lidocaine','hyfrecation-topical-23p-lidocaine-7p-tetracaine',
                'hyfrecation-topical-20p-benzocaine-8p-lidocaine-4p-tetracaine','hyfrecation-topical-30p-lidocaine',
                'hyfrecation-topical-30-lidocaine-7-tetracaine',
                'hyfrecation-ice','hyfrecation-fan','hyfrecation-vibration','hyfrecation-1p-lidocaine','hyfrecation-1p-lidocaine-with-epinephrine',
                'hyfrecation-2p-lidocaine','hyfrecation-2p-lidocaine-wtih-epinephrine','hyfrecation-05p-lidocaine-with-epinephrine','hyfrecation-05p-bupivicaine',
                'hyfrecation-05p-bupivicaine-wtih-epinephrine','hyfrecation-025p-bupivicaine','hyfrecation-025p-bupivicaine-wtih-epinephrine',
                'hyfrecation-025p-marcaine-wtih-epinephrine','hyfrecation-09p-bacteriostatic-sodium-chloride','hyfrecation-sterile-water',
                'hyfrecation-1p-ropivacaine','hyfrecation-05p-ropivacaine','hyfrecation-84p-sodium-bicarbonate',
                'hyfrecation-anesthetic-volume-1cc','hyfrecation-anesthetic-volume-1-2cc','hyfrecation-anesthetic-volume-2-3cc',
                'hyfrecation-anesthetic-volume-3-4cc','hyfrecation-anesthetic-volume-4-5cc','hyfrecation-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine',
                'hyfrecation-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine','hyfrecation-spf','hyfrecation-petrolatum','hyfrecation-triamcinolone-0-1p-ointment',
                'hyfrecation-hydrocortisone-2-5p-ointment'
            ];

            // Set the values into the visible checkboxes for each hyfrecation method
            for (var i = 0; i < hyfrecation_methods.length; i++) {
                var hyfrecation_method = hyfrecation_methods[i];
                var hyfrecation_value = $('input[name="treatment[' + planCount + '][' + hyfrecation_method + ']"]').val();
                $('#' + hyfrecation_method).prop('checked', hyfrecation_value == "1");
            }

            var hyfrecation_inputs = [
                "hyfrecation-skin-prep-free-text","hyfrecation-anesthetic-free-text","areas_hyfrecation","areas-count-hyfrecation","hyfrecation-setting-free-text",
                "hyfrecation-lession-free-text","hyfrecation-aftercare-free-text","hyfrecation-anesthetic-volume-free-text","hyfrecation-aftercare_free_text"
            ];
            for (var i = 0; i < hyfrecation_inputs.length; i++) {
                var hyfrecation_input_key = hyfrecation_inputs[i];
                var hyfrecation_input_value = $('input[name="treatment[' + planCount + ']['+hyfrecation_input_key+']"]').val();
                $('#'+hyfrecation_input_key).val(hyfrecation_input_value);
            }
        }

        if(procedurePlanId == 'incision'){


            // Additional incision fields
            var incision_methods = [
                'incision-alcohol', 'incision-hibiclens', 'incision-iodine', 'incision-lasercyn', 'incision-1p-lidocaine',
                'incision-1p-lidocaine-with-epinephrine', 'incision-2p-lidocaine', 'incision-2p-lidocaine-wtih-epinephrine',
                'incision-p-none', 'incision-plain-packing-strip-gauze', 'incision-iodine-packing-strip-guaze',
                'incision-remove-packing-in-24-hrs', 'incision-removal-packing-in-48-hours', 'incision-culture-none',
                'incision-bacterial-culture', 'incision-mupirocin', 'incision-polysporin', 'incision-bacitracin',
                'incision-petrolatum', 'incision-pressure-dressing-with-telfa-gauze-and-hypafix',
                'incision-pressure-dressing-with-telfa-gauze-and-papertape', 'incision-regular-bandage', 'incision-telfa',
                'incision-paper-tape', 'incision-hypafix', 'incision-hydrocolloid','incision-1p-lidocaine',
                'incision-05p-lidocaine-with-epinephrine','incision-05p-bupivicaine','incision-05p-bupivicaine-wtih-epinephrine',
                'incision-025p-bupivicaine','incision-025p-bupivicaine-wtih-epinephrine','incision-025p-marcaine-wtih-epinephrine',
                'incision-09p-bacteriostatic-sodium-chloride','incision-sterile-water','incision-1p-ropivacaine','incision-05p-ropivacaine',
                'incision-84p-sodium-bicarbonate','incision-anesthetic-volume-1cc','incision-anesthetic-volume-1-2cc','incision-anesthetic-volume-2-3cc',
                'incision-anesthetic-volume-3-4cc','incision-anesthetic-volume-4-5cc','incision-aftercare-none','incision-dressing-none'
            ];

            // Set the values into the visible checkboxes for each incision method
            for (var i = 0; i < incision_methods.length; i++) {
                var incision_method = incision_methods[i];
                var incision_value = $('input[name="treatment[' + planCount + '][' + incision_method + ']"]').val();
                $('#' + incision_method).prop('checked', incision_value == "1");
            }

            var incision_inputs = [
                "incision-skin-prep-free-text","incision-anesthetic-free-text","areas_incision","areas-count-incision","incision-packing-free-text","incision-culture-free-text",
                "incision-aftercare-tropical-free-text","incision-bandage-free-text","incision-anesthetic-volume-free-text","incision-aftercare_free_text"
            ];
            for (var i = 0; i < incision_inputs.length; i++) {
                var incision_input_key = incision_inputs[i];
                var incision_input_value = $('input[name="treatment[' + planCount + ']['+incision_input_key+']"]').val();
                $('#'+incision_input_key).val(incision_input_value);
            }
        }

        if(procedurePlanId == 'filler'){


            // Additional filler and hyfrecation fields
            var filler_methods = [
                "filler-restylane", "filler-restylane-silk", "filler-restylane-eyelight", "filler-restylane-refyne", "filler-restylane-defyne", "filler-restylane-contour", "filler-restylane-lyft", 
                "filler-restylane-kysse", "filler-juvederm-ultra-xc", "filler-juvederm-ultra-plus-xc", "filler-juvederm-voluma-xc", "filler-juvederm-volbella-xc", "filler-juvederm-vollure-xc", 
                "filler-juvederm-volux-xc", "filler-sculptra","filler-none", "filler-topical-5-lidocaine", "filler-topical-23-lidocaine-7-tetracaine", "filler-topical-23-lidocaine-7-tetracaine-1-phenylephrine", 
                "filler-topical-20-benzocaine-10-lidocaine-4-tetracaine", "filler-topical-30-lidocaine", "filler-topical-30-lidocaine-7-tetracaine", "filler-ice", "filler-fan", "filler-vibration",
                'filler-cheek','filler-lips', 'filler-jawline', 'filler-nasolabial-folds', 'filler-temples', 'filler-chin', 'filler-tear-troughs',
                'filler-nose', 'filler-marionette-lines', 'filler-earlobes', 'filler-hands', 'filler-chest', 'filler-brows',
                'filler-buttocks', 'filler-thighs', 'filler-scars', 'filler-05cc', 'filler-1cc', 'filler-2cc', 'filler-3cc',
                'filler-4cc', 'filler-5cc','filler-9cc', 'filler-other', 'filler-cannula-18', 'filler-cannula-20', 'filler-cannula-22',
                'filler-cannula-25', 'filler-cannula-27', 'filler-none', 'filler-topical-5p-lidocaine',
                'filler-topical-23p-lidocaine-7p-tetracaine', 'filler-topical-20p-benzocaine-8p-lidocaine-4p-tetracaine',
                'filler-topical-30p-lidocaine', 'filler-05cc', 'filler-1cc', 'filler-2cc', 'filler-3cc', 'filler-4cc',
                'filler-5cc', 'filler-other', 'filler-spf', 'filler-petrolatum', 'filler-triamcinolone-0-1p-ointment',"filler-1p-lidocaine-with-epinephrine",
                'filler-hydrocortisone-2-5p-ointment',"filler-09p-bacteriostatic-sodium-chloride","filler-09p-sodium-chloride","filler-sterile-water","filler-1p-lidocaine",
                "filler-dilution-1-1","filler-dilution-1-2","filler-dilution-1-3","filler-dilution-1-4","filler-2p-lidocaine","filler-2p-lidocaine-with-epinephrine-1-100-000",
                "filler-1-percent-lidocaine", "filler-1-percent-lidocaine-with-epinephrine-1-100-000", "filler-2-percent-lidocaine", "filler-2-percent-lidocaine-with-epinephrine-1-100-000",
                "filler-8-cc-sterile-water-1-cc-2-percent-lidocaine", "filler-8-cc-sterile-water-1-cc-1-percent-lidocaine", "filler-7-cc-sterile-water-2-cc-2-percent-lidocaine", "filler-7-cc-sterile-water-2-cc-2-percent-lidocaine-duplicate"
            ];

            // Set the values into the visible checkboxes for each filler and hyfrecation method
            for (var i = 0; i < filler_methods.length; i++) {
                var method = filler_methods[i];
                var value = $('input[name="treatment[' + planCount + '][' + method + ']"]').val();
                $('#' + method).prop('checked', value == "1");
            }

            var filler_inputs = [
                "filler-injectable-free-text","filler-treatment-free-text","filler-anesthetic-free-text","filler-aftercare_free_text","filler-lot-free-text",
                "filler-dilution-free-text","filler-diluent-free-text","filler-cc-free-text","filler-expiration-free-text","filler-Sculptra-Dilution-free-text"
            ];
            for (var i = 0; i < filler_inputs.length; i++) {
                var filler_input_key = filler_inputs[i];
                var filler_input_value = $('input[name="treatment[' + planCount + ']['+filler_input_key+']"]').val();
                $('#'+filler_input_key).val(filler_input_value);
            }
        }

        if(procedurePlanId == 'extraction'){
            var extraction_alcohol = $('input[name="treatment[' + planCount + '][extraction-alcohol]"]').val();
            $('#extraction-alcohol').prop('checked', extraction_alcohol == "1");

            // List of extraction methods
            var extraction_methods = [
                'extraction-hibiclens', 'extraction-iodine', 'extraction-lasercyn', 'extraction-none',
                'extraction-topical-5p-lidocaine', 'extraction-topical-23p-lidocaine-7p-tetracaine',
                'extraction-topical-20p-benzocaine-8p-lidocaine-4p-tetracaine', 'extraction-topical-30p-lidocaine',
                'extraction-comedone-extractor', 'extraction-q-tips', 'extraction-power-extraction',
                'extraction-spf', 'extraction-petrolatum', 'extraction-triamcinolone-0-1p-ointment',
                'extraction-hydrocortisone-2-5p-ointment','extraction-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine',
                'extraction-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine','extraction-topical-30-lidocaine-7-tetracaine',
                'extraction-ice','extraction-fan','extraction-vibration','extraction-1p-lidocaine','extraction-1p-lidocaine-with-epinephrine',
                'extraction-2p-lidocaine','extraction-2p-lidocaine-wtih-epinephrine','extraction-05p-lidocaine-with-epinephrine','extraction-05p-bupivicaine',
                'extraction-05p-bupivicaine-wtih-epinephrine','extraction-025p-bupivicaine','extraction-025p-bupivicaine-wtih-epinephrine',
                'extraction-025p-marcaine-wtih-epinephrine','extraction-09p-bacteriostatic-sodium-chloride','extraction-sterile-water','extraction-1p-ropivacaine',
                'extraction-05p-ropivacaine','extraction-84p-sodium-bicarbonate','extraction-anesthetic-volume-1cc','extraction-anesthetic-volume-1-2cc',
                'extraction-anesthetic-volume-2-3cc','extraction-anesthetic-volume-3-4cc','extraction-anesthetic-volume-4-5cc',
            ];

            // Set the values into the visible checkboxes for each extraction method
            for (var i = 0; i < extraction_methods.length; i++) {
                var extraction_method = extraction_methods[i];
                var extraction_value = $('input[name="treatment[' + planCount + '][' + extraction_method + ']"]').val();
                $('#' + extraction_method).prop('checked', extraction_value == "1");
            }

            var extraction_inputs = [
                "extraction-skin-prep-free-text","extraction-anesthetic-free-text","areas_extraction","areas-count-extraction","extraction-extraction-method-free-text",
                "extraction-aftercare-free-text","extraction-aftercare_free_text","extraction-anesthetic-volume-free-text"
            ];
            for (var i = 0; i < extraction_inputs.length; i++) {
                var extraction_input_key = extraction_inputs[i];
                var extraction_input_value = $('input[name="treatment[' + planCount + ']['+extraction_input_key+']"]').val();
                $('#'+extraction_input_key).val(extraction_input_value);
            }
        }
        
        if(procedurePlanId == 'intralesional'){

            var intralesional_selectable = [
                'intralesional-kenalog','intralesional-candida-albicans','intralesional-bleomycin','intralesional-5-fluorouracil','intralesional-kenalog-fluorouracil',
                'intralesional-topical-23-lidocaine-7-tetracaine-1-phenylephrine','intralesional-none','intralesional-topical-5-lidocaine','intralesional-topical-23-lidocaine-7-tetracaine','intralesional-2p-lidocaine-wtih-epinephrine',
                'intralesional-topical-20-benzocaine-10-lidocaine-4-tetracaine','intralesional-topical-30-lidocaine','intralesional-topical-30-lidocaine-7-tetracaine',
                'intralesional-ice','intralesional-fan','intralesional-vibration','intralesional-2mg','intralesional-3mg','intralesional-5mg','intralesional-10mg',
                'intralesional-20mg','intralesional-40mg'
            ];

            for (var i = 0; i < intralesional_selectable.length; i++) {
                var intralesional_selectable_method = intralesional_selectable[i];
                var intralesional_selectable_value = $('input[name="treatment[' + planCount + '][' + intralesional_selectable_method + ']"]').val();
                $('#' + intralesional_selectable_method).prop('checked', intralesional_selectable_value == "1");
            }

            var intralesional_inputs = [
                "intralesional-Injectable-free-text","areas_intralesional","areas-count-intralesional","intralesional-anesthetic-free-text","intralesional-concentration-free-text",
                "intralesional-injection-total-volume-free-text","intralesional-total-number-of-lesions-injected-free-text","intralesional-lot-free-text","intralesional-expiration-free-text"
            ];
            for (var i = 0; i < intralesional_inputs.length; i++) {
                var intralesional_input_key = intralesional_inputs[i];
                var intralesional_input_value = $('input[name="treatment[' + planCount + ']['+intralesional_input_key+']"]').val();
                $('#'+intralesional_input_key).val(intralesional_input_value);
            }
        }

        if(procedurePlanId == 'laser'){

            var laser_selectable = [
                "laser-none","laser-topical-5-lidocaine","laser-topical-23-lidocaine-7-tetracaine","laser-2p-lidocaine-wtih-epinephrine","laser-topical-20-benzocaine-10-lidocaine-4-tetracaine","laser-topical-30-lidocaine",
                "laser-topical-23-lidocaine-7-tetracaine-1-phenylephrine","laser-topical-30-lidocaine-7-tetracaine","laser-ice","laser-fan","laser-vibration","laser-none-meds","laser-metal-shields","laser-disposable-stickers",
                "laser-gauze","laser-ice-cool","laser-fan-cool","laser-none-cool","laser-cryogen-spray","laser-dcd","laser-contact","laser-chilled-air","laser-gel-based","laser-purpura","laser-whitening","laser-erythema","laser-spf","laser-petrolatum",
                "laser-triamcinolone-0-1p-ointment","laser-hydrocortisone-2-5p-ointment",'laser-anesthetic-volume-1cc','laser-anesthetic-volume-1-2cc',
                'laser-anesthetic-volume-2-3cc','laser-anesthetic-volume-3-4cc','laser-anesthetic-volume-4-5cc',"laser-topical-5p-lidocaine","laser-topical-23p-lidocaine-7p-tetracaine","laser-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine",
                "laser-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine","laser-topical-30p-lidocaine","laser-1p-lidocaine","laser-1p-lidocaine-with-epinephrine","laser-2p-lidocaine","laser-05p-lidocaine-with-epinephrine","laser-05p-bupivicaine",
                "laser-05p-bupivicaine-wtih-epinephrine","laser-025p-bupivicaine","laser-025p-bupivicaine-wtih-epinephrine","laser-025p-marcaine-wtih-epinephrine","laser-09p-bacteriostatic-sodium-chloride","laser-sterile-water","laser-1p-ropivacaine",
                "laser-05p-ropivacaine","laser-84p-sodium-bicarbonate"
            ];

            for (var i = 0; i < laser_selectable.length; i++) {
                var laser_selectable_method = laser_selectable[i];
                var laser_selectable_value = $('input[name="treatment[' + planCount + '][' + laser_selectable_method + ']"]').val();
                $('#' + laser_selectable_method).prop('checked', laser_selectable_value == "1");
            }

            var laser_inputs = [
                "laser-Laser-free-text","areas_laser","areas-count-laser","laser-Treatment-free-text","laser-anesthetic-free-text","laser-pre-op-meds-free-text",
                "laser-patient-protective-eyewear-free-text","laser-Spot-Size-free-text","laser-Pulse-Duration-free-text","laser-Fluence-free-text","laser-Pulses-free-text",
                "laser-Passes-free-text","laser-cooling-free-text","laser-end-point-free-text","laser-aftercare_free_text","laser-Notes-free-text","laser-anesthetic-volume-free-text"
            ];
            for (var i = 0; i < laser_inputs.length; i++) {
                var laser_input_key = laser_inputs[i];
                var laser_input_value = $('input[name="treatment[' + planCount + ']['+laser_input_key+']"]').val();
                $('#laser #'+laser_input_key).val(laser_input_value);
            }
        }

        if(procedurePlanId == 'nonlaser'){

            var nonlaser_selectable = [
                "nonlaser-none","nonlaser-topical-5-lidocaine","nonlaser-topical-23-lidocaine-7-tetracaine","nonlaser-2p-lidocaine-wtih-epinephrine","nonlaser-topical-20-benzocaine-10-lidocaine-4-tetracaine",
                "nonlaser-topical-23-lidocaine-7-tetracaine-1-phenylephrine","nonlaser-topical-30-lidocaine",
                "nonlaser-topical-30-lidocaine-7-tetracaine","nonlaser-ice","nonlaser-fan","nonlaser-vibration","nonlaser-none-meds","nonlaser-metal-shields","nonlaser-disposable-stickers","nonlaser-gauze","nonlaser-ice-cool","nonlaser-fan-cool",
                "nonlaser-none-cool","nonlaser-cryogen-spray","nonlaser-dcd","nonlaser-contact","nonlaser-chilled-air","nonlaser-gel-based","nonlaser-purpura","nonlaser-whitening","nonlaser-erythema","nonlaser-spf","nonlaser-petrolatum",
                "nonlaser-triamcinolone-0-1p-ointment","nonlaser-hydrocortisone-2-5p-ointment","nonlaser-none-needed","nonlaser-none-meds-post",'nonlaser-anesthetic-volume-1cc','nonlaser-anesthetic-volume-1-2cc',
                'nonlaser-anesthetic-volume-2-3cc','nonlaser-anesthetic-volume-3-4cc','nonlaser-anesthetic-volume-4-5cc',"nonlaser-topical-5p-lidocaine","nonlaser-topical-23p-lidocaine-7p-tetracaine","nonlaser-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine",
                "nonlaser-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine","nonlaser-topical-30p-lidocaine","nonlaser-1p-lidocaine","nonlaser-1p-lidocaine-with-epinephrine","nonlaser-2p-lidocaine","nonlaser-05p-lidocaine-with-epinephrine","nonlaser-05p-bupivicaine",
                "nonlaser-05p-bupivicaine-wtih-epinephrine","nonlaser-025p-bupivicaine","nonlaser-025p-bupivicaine-wtih-epinephrine","nonlaser-025p-marcaine-wtih-epinephrine","nonlaser-09p-bacteriostatic-sodium-chloride","nonlaser-sterile-water","nonlaser-1p-ropivacaine",
                "nonlaser-05p-ropivacaine","nonlaser-84p-sodium-bicarbonate"
            ];

            for (var i = 0; i < nonlaser_selectable.length; i++) {
                var nonlaser_selectable_method = nonlaser_selectable[i];
                var nonlaser_selectable_value = $('input[name="treatment[' + planCount + '][' + nonlaser_selectable_method + ']"]').val();
                $('#nonlaser #' + nonlaser_selectable_method).prop('checked', nonlaser_selectable_value == "1");
            }

            var nonlaser_inputs = [
                "nonlaser-Device-free-text","areas_nonlaser","areas-count-nonlaser","nonlaser-Treatment-free-text","nonlaser-anesthetic-free-text","nonlaser-pre-op-meds-free-text",
                "nonlaser-patient-protective-eyewear-free-text","nonlaser-Depth-free-text","nonlaser-Spot-Size-free-text","nonlaser-Pulse-Duration-free-text",
                "nonlaser-Fluence-free-text","nonlaser-Pulses-free-text","nonlaser-Passes-free-text","nonlaser-cooling-free-text","nonlaser-end-point-free-text",
                "nonlaser-post-op-meds-free-text","nonlaser-aftercare_free_text","nonlaser-Notes-free-text","nonlaser-anesthetic-volume-free-text"
            ];
            for (var i = 0; i < nonlaser_inputs.length; i++) {
                var nonlaser_input_key = nonlaser_inputs[i];
                var nonlaser_input_value = $('input[name="treatment[' + planCount + ']['+nonlaser_input_key+']"]').val();
                $('#nonlaser #'+nonlaser_input_key).val(nonlaser_input_value);
            }
        }

        if(procedurePlanId == 'peel'){

            var peel_selectable = [
                "peel-none","peel-topical-5-lidocaine","peel-topical-23-lidocaine-7-tetracaine","peel-topical-23-lidocaine-7-tetracaine-1-phenylephrine",
                "peel-topical-20-benzocaine-10-lidocaine-4-tetracaine","peel-topical-30-lidocaine","peel-topical-30-lidocaine-7-tetracaine","peel-ice","peel-fan",
                "peel-vibration","peel-none-meds","peel-acetone","peel-petrolatum-for-protection-of-mucous-membranes","peel-none-ep","peel-frosting","peel-erythema",
                "peel-spf","peel-petrolatum","peel-triamcinolone-0-1p-ointment","peel-hydrocortisone-2-5p-ointment"
            ];

            for (var i = 0; i < peel_selectable.length; i++) {
                var peel_selectable_method = peel_selectable[i];
                var peel_selectable_value = $('input[name="treatment[' + planCount + '][' + peel_selectable_method + ']"]').val();
                $('#peel #' + peel_selectable_method).prop('checked', peel_selectable_value == "1");
            }
            
            var peel_inputs = [
                "peel-Peel-free-text","areas_peel","areas-count-peel","peel-Treatment-free-text","peel-anesthetic-free-text","peel-pre-op-meds-free-text",
                "peel-Prep-free-text","peel-Passes-free-text","peel-Treatment-time-free-text","peel-end-point-free-text","peel-aftercare_free_text","peel-Notes-free-text"
            ];
            for (var i = 0; i < peel_inputs.length; i++) {
                var peel_input_key = peel_inputs[i];
                var peel_input_value = $('input[name="treatment[' + planCount + ']['+peel_input_key+']"]').val();
                $('#peel #'+peel_input_key).val(peel_input_value);
            }
        }

        if(procedurePlanId == 'silver'){

            var silver_selectable = [
                "silver-alcohol","silver-hibiclens","silver-iodine","silver-lasercyn","silver-no-change","silver-erythema","silver-bleeding","silver-epidermal-damage",
                "silver-swelling","silver-blisters","silver-pain","silver-mupirocin","silver-polysporin","silver-bacitracin","silver-petrolatum","silver-pressure-dressing-with-telfa-gauze-and-hypafix",
                "silver-pressure-dressing-with-telfa-gauze-and-papertape","silver-regular-bandage","silver-telfa","silver-paper-tape","silver-hypafix","silver-hydrocolloid"
            ];

            for (var i = 0; i < silver_selectable.length; i++) {
                var silver_selectable_method = silver_selectable[i];
                var silver_selectable_value = $('input[name="treatment[' + planCount + '][' + silver_selectable_method + ']"]').val();
                $('#silver #' + silver_selectable_method).prop('checked', silver_selectable_value == "1");
            }
            
            var silver_inputs = [
                "silver-skin-prep-free-text","areas_silver","areas-count-silver","silver-post-op-effects-free-text","silver-aftercare_free_text","silver-bandage-free-text"
            ];
            for (var i = 0; i < silver_inputs.length; i++) {
                var silver_input_key = silver_inputs[i];
                var silver_input_value = $('input[name="treatment[' + planCount + ']['+silver_input_key+']"]').val();
                $('#silver #'+silver_input_key).val(silver_input_value);
            }
        }

        if(procedurePlanId == 'snip'){

            var snip_selectable = [
                "snip-alcohol","snip-hibiclens","snip-iodine","snip-lasercyn","snip-none","snip-topical-5p-lidocaine","snip-topical-23p-lidocaine-7p-tetracaine",
                "snip-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine","snip-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine","snip-topical-30p-lidocaine",
                "snip-topical-30-lidocaine-7-tetracaine","snip-ice","snip-fan","snip-vibration","snip-1p-lidocaine","snip-1p-lidocaine-with-epinephrine","snip-2p-lidocaine",
                "snip-2p-lidocaine-wtih-epinephrine","snip-05p-lidocaine-with-epinephrine","snip-05p-bupivicaine","snip-05p-bupivicaine-wtih-epinephrine","snip-025p-bupivicaine",
                "snip-025p-bupivicaine-wtih-epinephrine","snip-025p-marcaine-wtih-epinephrine","snip-09p-bacteriostatic-sodium-chloride","snip-sterile-water","snip-1p-ropivacaine",
                "snip-05p-ropivacaine","snip-84p-sodium-bicarbonate","snip-anesthetic-volume-1cc","snip-anesthetic-volume-1-2cc","snip-anesthetic-volume-2-3cc","snip-anesthetic-volume-3-4cc","snip-anesthetic-volume-4-5cc",
                "snip-pickups-and-tissue-scissors","snip-skin-tags","snip-dermatosis-papulosa-nigra","snip-cherry-angiomas","snip-spf","snip-petrolatum","snip-triamcinolone-0-1p-ointment",
                "snip-hydrocortisone-2-5p-ointment"
            ];

            for (var i = 0; i < snip_selectable.length; i++) {
                var snip_selectable_method = snip_selectable[i];
                var snip_selectable_value = $('input[name="treatment[' + planCount + '][' + snip_selectable_method + ']"]').val();
                $('#snip #' + snip_selectable_method).prop('checked', snip_selectable_value == "1");
            }
            
            var snip_inputs = [
                "snip-skin-prep-free-text","snip-anesthetic-free-text","snip-anesthetic-volume-free-text","snip-method-free-text","areas_snip","areas-count-snip","snip-Treatment-free-text",
                "snip-type-of-lesion-treated-free-text","snip-aftercare_free_text"];
            for (var i = 0; i < snip_inputs.length; i++) {
                var snip_input_key = snip_inputs[i];
                var snip_input_value = $('input[name="treatment[' + planCount + ']['+snip_input_key+']"]').val();
                $('#snip #'+snip_input_key).val(snip_input_value);
            }
        }

        if(procedurePlanId == 'sclerotherapy'){

            var selectable = [
                "sclerotherapy-alcohol","sclerotherapy-hibiclens","sclerotherapy-iodine","sclerotherapy-lasercyn","sclerotherapy-0-25-polidocanol","sclerotherapy-0-5-polidocanol",
                "sclerotherapy-1-polidocanol","sclerotherapy-2-polidocanol","sclerotherapy-3-polidocanol","sclerotherapy-4-polidocanol","sclerotherapy-0-2-sts",
                "sclerotherapy-0-5-sts","sclerotherapy-1-sts","sclerotherapy-3-sts","sclerotherapy-hypertonic-saline","sclerotherapy-1-2","sclerotherapy-1-3","sclerotherapy-1-4","sclerotherapy-1-5",
                "sclerotherapy-paper-tape-and-cotton-balls","sclerotherapy-compression-stockings"
            ];

            for (var i = 0; i < selectable.length; i++) {
                var selectable_method = selectable[i];
                var selectable_value = $('input[name="treatment[' + planCount + '][' + selectable_method + ']"]').val();
                $('#'+procedurePlanId+' #' + selectable_method).prop('checked', selectable_value == "1");
            }
            
            var inputs = [
                "areas_sclerotherapy","areas-count-sclerotherapy","sclerotherapy-skin-prep-free-text","sclerotherapy-Solution-Used-free-text","sclerotherapy-Foam-Ratio-free-text",
                "sclerotherapy-cc-used-free-text","sclerotherapy-Lot-number-free-text","sclerotherapy-Expiration-free-text","sclerotherapy-Bandage-free-text"
            ];
            for (var i = 0; i < inputs.length; i++) {
                var input_key = inputs[i];
                var input_value = $('input[name="treatment[' + planCount + ']['+input_key+']"]').val();
                $('#'+procedurePlanId+' #'+input_key).val(input_value);
            }
        }

        if(procedurePlanId == 'biopsy'){

            var selectable = [
                "biopsy-h-and-e","biopsy-dif","biopsy-frozen-section","biopsy-pas","biopsy-flow-cytometry","biopsy-t-cell-receptor-gene-rearrangement","biopsy-no-sutures-were-needed","biopsy-3-0-nylon",
                "biopsy-4-0-nylon","biopsy-5-0-nylon","biopsy-6-0-nylon","biopsy-3-0-prolene","biopsy-4-0-prolene","biopsy-5-0-prolene","biopsy-6-0-prolene","biopsy-aluminum-chloride","biopsy-electrodessication",
                "biopsy-electrofulguration","biopsy-electrocoagulation","biopsy-electrocautery","biopsy-anesthetic-volume-1cc","biopsy-anesthetic-volume-1-2cc",
                "biopsy-anesthetic-volume-2-3cc","biopsy-anesthetic-volume-3-4cc","biopsy-anesthetic-volume-4-5cc","biopsy-topical-5p-lidocaine","biopsy-topical-23p-lidocaine-7p-tetracaine","biopsy-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine",
                "biopsy-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine","biopsy-topical-30p-lidocaine","biopsy-1p-lidocaine","biopsy-1p-lidocaine-with-epinephrine","biopsy-2p-lidocaine","biopsy-05p-lidocaine-with-epinephrine","biopsy-05p-bupivicaine",
                "biopsy-05p-bupivicaine-wtih-epinephrine","biopsy-025p-bupivicaine","biopsy-025p-bupivicaine-wtih-epinephrine","biopsy-025p-marcaine-wtih-epinephrine","biopsy-09p-bacteriostatic-sodium-chloride","biopsy-sterile-water","biopsy-1p-ropivacaine",
                "biopsy-05p-ropivacaine","biopsy-84p-sodium-bicarbonate","biopsy-alcohol","biopsy-hibiclens","biopsy-iodine","biopsy-lasercyn",
                "biopsy-none","biopsy-topical-30-lidocaine-7-tetracaine","biopsy-ice","biopsy-fan","biopsy-vibration","biopsy-2p-lidocaine-wtih-epinephrine","biopsy-hemostasis-none",
                "biopsy-mupirocin", "biopsy-polysporin", "biopsy-bacitracin","biopsy-petrolatum", "biopsy-pressure-dressing-with-telfa-gauze-and-hypafix",
                "biopsy-pressure-dressing-with-telfa-gauze-and-papertape", "biopsy-regular-bandage", "biopsy-telfa","biopsy-paper-tape", "biopsy-hypafix", "biopsy-hydrocolloid",
                "biopsy-shave", "biopsy-punch", "biopsy-incisional","biopsy-excisional","biopsy-nail-bed","biopsy-nail-matrix","biopsy-nail","biopsy-ear","biopsy-penis", "biopsy-vulva", "biopsy-anus","biopsy-eyelid","biopsy-lip",
            ];

            for (var i = 0; i < selectable.length; i++) {
                var selectable_method = selectable[i];
                var selectable_value = $('input[name="treatment[' + planCount + '][' + selectable_method + ']"]').val();
                if(selectable_value == "1"){
                    $('#'+procedurePlanId+' #' + selectable_method).prop('checked', selectable_value == "1");
                }
            }
            
            var inputs = [
                "areas_biopsy","biopsy-bio-method-free-text","biopsy-bio-type-free-text","biopsy-Differential-diagnosis-free-text","biopsy-skin-prep-free-text","biopsy-anesthetic-free-text","biopsy-hemostasis-free-text",
                "biopsy-Sutures-free-text","biopsy-aftercare_free_text","biopsy-bandage-free-text","biopsy-anesthetic-volume-free-text","biopsy-Rule-out-free-text"
            ];
            for (var i = 0; i < inputs.length; i++) {
                var input_key = inputs[i];
                var input_value = $('input[name="treatment[' + planCount + ']['+input_key+']"]').val();
                $('#'+procedurePlanId+' #'+input_key).val(input_value);
            }
        }

        if(procedurePlanId == 'photodynamic'){

            var selectable = [
                "photodynamic-goggles-with-guaze", "photodynamic-metal-shields", "photodynamic-none", "photodynamic-30-min", "photodynamic-60-min", "photodynamic-90-min", "photodynamic-120-min", "photodynamic-16-min-40-sec", "photodynamic-levulan", "photodynamic-ameluz", "photodynamic-1", "photodynamic-2"
            ];

            for (var i = 0; i < selectable.length; i++) {
                var selectable_method = selectable[i];
                var selectable_value = $('input[name="treatment[' + planCount + '][' + selectable_method + ']"]').val();
                $('#'+procedurePlanId+' #' + selectable_method).prop('checked', selectable_value == "1");
            }
            
            var inputs = [
                "areas_photodynamic","areas-count-photodynamic","photodynamic-eye-protectoin-free-text","photodynamic-Incubation-Time-free-text","photodynamic-Illumination-Time-free-text",
                "photodynamic-PDT-agent-free-text","photodynamic-Number-Used-free-text","photodynamic-Lot-free-text","photodynamic-Expiration-Date-free-text","photodynamic-Comments-free-text"
            ];
            for (var i = 0; i < inputs.length; i++) {
                var input_key = inputs[i];
                var input_value = $('input[name="treatment[' + planCount + ']['+input_key+']"]').val();
                $('#'+procedurePlanId+' #'+input_key).val(input_value);
            }
        }

        if(procedurePlanId == 'platelet'){

            var selectable = [
                "platelet-hair-loss", "platelet-skin-rejuvenation", "platelet-rhytides","platelet-anesthetic-volume-1cc","platelet-anesthetic-volume-1-2cc",
                "platelet-anesthetic-volume-2-3cc","platelet-anesthetic-volume-3-4cc","platelet-anesthetic-volume-4-5cc","platelet-anesthetic-volume-5-6cc","platelet-anesthetic-volume-6-7cc",
                "platelet-anesthetic-volume-7-8cc","platelet-anesthetic-volume-8-9cc","platelet-topical-5p-lidocaine","platelet-topical-23p-lidocaine-7p-tetracaine",
                "platelet-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine","platelet-topical-30p-lidocaine","platelet-1p-lidocaine","platelet-1p-lidocaine-with-epinephrine",
                "platelet-2p-lidocaine","platelet-05p-lidocaine-with-epinephrine","platelet-05p-bupivicaine","platelet-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine",
                "platelet-05p-bupivicaine-wtih-epinephrine","platelet-025p-bupivicaine","platelet-025p-bupivicaine-wtih-epinephrine","platelet-025p-marcaine-wtih-epinephrine",
                "platelet-09p-bacteriostatic-sodium-chloride","platelet-sterile-water","platelet-1p-ropivacaine","platelet-1", "platelet-2","platelet-3", "platelet-4",
                "platelet-05p-ropivacaine","platelet-84p-sodium-bicarbonate","platelet-alcohol","platelet-hibiclens","platelet-iodine","platelet-lasercyn",
                "platelet-none","platelet-topical-30-lidocaine-7-tetracaine","platelet-ice","platelet-fan","platelet-vibration","platelet-2p-lidocaine-wtih-epinephrine",  
            ];

            for (var i = 0; i < selectable.length; i++) {
                var selectable_method = selectable[i];
                var selectable_value = $('input[name="treatment[' + planCount + '][' + selectable_method + ']"]').val();
                $('#'+procedurePlanId+' #' + selectable_method).prop('checked', selectable_value == "1");
            }
            
            var inputs = [
                "platelet-Indication-free-text","areas_platelet","areas-count-platelet","platelet-anesthetic-free-text","platelet-anesthetic-volume-free-text",
                "platelet-Number-of-Vials-Injected-free-text","platelet-Lot-free-text","platelet-Expiration-Date-free-text"
            ];
            for (var i = 0; i < inputs.length; i++) {
                var input_key = inputs[i];
                var input_value = $('input[name="treatment[' + planCount + ']['+input_key+']"]').val();
                $('#'+procedurePlanId+' #'+input_key).val(input_value);
            }
        }

        if(procedurePlanId == 'microneedling'){

            var selectable = [
                "microneedling-alcohol","microneedling-alcohol","microneedling-hibiclens","microneedling-iodine","microneedling-lasercyn","microneedling-none",
                "microneedling-topical-5-lidocaine","microneedling-topical-23-lidocaine-7-tetracaine","microneedling-topical-23-lidocaine-7-tetracaine-1-phenylephrine",
                "microneedling-topical-20-benzocaine-10-lidocaine-4-tetracaine","microneedling-topical-30-lidocaine","microneedling-topical-30-lidocaine-7-tetracaine","microneedling-ice","microneedling-fan",
                "microneedling-vibration","microneedling-prp","microneedling-spf","microneedling-petrolatum","microneedling-triamcinolone-0-1p-ointment","microneedling-hydrocortisone-2-5p-ointment"
            ];

            for (var i = 0; i < selectable.length; i++) {
                var selectable_method = selectable[i];
                var selectable_value = $('input[name="treatment[' + planCount + '][' + selectable_method + ']"]').val();
                $('#'+procedurePlanId+' #' + selectable_method).prop('checked', selectable_value == "1");
            }
            
            var inputs = [
                "microneedling-freeS","microneedling-anesthetic-free-text","areas_microneedling","microneedling-Infusions-free-text","microneedling-Location-1-free-text","microneedling-Depth-1-free-text",
                "microneedling-Location-2-free-text","microneedling-Depth-2-free-text","microneedling-Location-3-free-text","microneedling-Depth-3-free-text","microneedling-Location-4-free-text","microneedling-Depth-4-free-text",
                "microneedling-Location-5-free-text","microneedling-Depth-5-free-text","microneedling-Location-6-free-text","microneedling-Depth-6-free-text",
                "microneedling-additional-info-free-text","microneedling_aftercare_free_text"
            ];
            for (var i = 0; i < inputs.length; i++) {
                var input_key = inputs[i];
                var input_value = $('input[name="treatment[' + planCount + ']['+input_key+']"]').val();
                $('#'+procedurePlanId+' #'+input_key).val(input_value);
            }
        }

        if(procedurePlanId == 'unna'){

            var selectable = [
                "unna-alcohol", "unna-hibiclens", "unna-iodine", "unna-lasercyn", "unna-both-legs", "unna-left-leg", "unna-right-leg", "unna-both-arms", "unna-left-arm", "unna-right-arm", 
                "unna-lymphedema", "unna-chronic-ulcer", "unna-venous-insufficiency", "unna-venous-ulcer", "unna-stasis-ulcer", "unna-post-op-immobilization", "unna-thrombophlebitis", "unna-graft-dressing", 
                "unna-extensive-actinic-keratoses", "unna-numerous-sccis", "unna-5-fluorouracil-cream", "unna-aquaphor", "unna-petrolatum", "unna-bactroban", "unna-triamcinolone-0-1-ointment", "unna-gentamicin",
                "unna-mupirocin", "unna-polysporin", "unna-yes", "unna-no", "unna-coban", "unna-tubular-compression-stockings", "unna-compression-stockings"
            ];

            for (var i = 0; i < selectable.length; i++) {
                var selectable_method = selectable[i];
                var selectable_value = $('input[name="treatment[' + planCount + '][' + selectable_method + ']"]').val();
                $('#'+procedurePlanId+' #' + selectable_method).prop('checked', selectable_value == "1");
            }
            
            var inputs = [
                "unna-Wrap-of-Unna-boot-free-text","unna-Prior-Unna-boot-removed-free-text","unna-Medication-occluded-free-text","unna-Indication-free-text","unna-location-applied-free-text",
                "unna-freeS"
            ];
            for (var i = 0; i < inputs.length; i++) {
                var input_key = inputs[i];
                var input_value = $('input[name="treatment[' + planCount + ']['+input_key+']"]').val();
                $('#'+procedurePlanId+' #'+input_key).val(input_value);
            }
        }

        if(procedurePlanId == 'neuromodulator'){

            var selectable = [
                "neuromodulator-botox", "neuromodulator-dysport", "neuromodulator-xeomin", "neuromodulator-jeuveau", "neuromodulator-daxxify",
                "neuromodulator-none", "neuromodulator-topical-5-lidocaine", "neuromodulator-topical-23-lidocaine-7-tetracaine", "neuromodulator-topical-23-lidocaine-7-tetracaine-1-phenylephrine", 
                "neuromodulator-topical-20-benzocaine-10-lidocaine-4-tetracaine", "neuromodulator-topical-30-lidocaine", "neuromodulator-topical-30-lidocaine-7-tetracaine", 
                "neuromodulator-ice", "neuromodulator-fan", "neuromodulator-vibration", "neuromodulator-1cc", "neuromodulator-2cc", "neuromodulator-2-5cc",
                "neuromodulator-09p-bacteriostatic-sodium-chloride","neuromodulator-09p-sodium-chloride","neuromodulator-sterile-water","neuromodulator-1p-lidocaine", "neuromodulator-1p-lidocaine-with-epinephrine",
                "neuromodulator-spf", "neuromodulator-petrolatum", "neuromodulator-triamcinolone-0-1p-ointment", "neuromodulator-hydrocortisone-2-5p-ointment",
                "neuromodulator-glabella", "neuromodulator-frontalis", "neuromodulator-crows-feet-orbicularis-oculi", "neuromodulator-bunny-lines-nasalis", "neuromodulator-jelly-roll-pretarsal-orbicularis", 
                "neuromodulator-chin-mentalis", "neuromodulator-masseters", "neuromodulator-lip-flip-orbicularis-oris", "neuromodulator-smokers-lines-orbicularis-oris", "neuromodulator-gummy-smile-llsan", 
                "neuromodulator-depressor-anguli-oris", "neuromodulator-depressor-septi-nasi", "neuromodulator-nefirtiti-lift-platysma", "neuromodulator-neck-bands-platysma", "neuromodulator-hyperhidrosis-axillae", 
                "neuromodulator-hyperhidrosis-palms", "neuromodulator-hyperhidrosis-soles", "neuromodulator-hyperhidrosis-scalp", "neuromodulator-temporalis", "neuromodulator-occipitalis", "neuromodulator-cervical-paraspinal"

            ]

            for (var i = 0; i < selectable.length; i++) {
                var selectable_method = selectable[i];
                var selectable_value = $('input[name="treatment[' + planCount + '][' + selectable_method + ']"]').val();
                $('#'+procedurePlanId+' #' + selectable_method).prop('checked', selectable_value == "1");
            }
            
            var inputs = [
                "neuromodulator-injectable-free-text","neuromodulator-anesthetic-free-text","neuromodulator-aftercare_free_text","neuromodulator-lot-free-text",
                "neuromodulator-dilution-free-text","neuromodulator-diluent-free-text","neuromodulator-cc-free-text","neuromodulator-expiration-free-text","neuromodulator-units-free-text",
                "neuromodulator-inner-glabella-free-text","neuromodulator-inner-frontalis-free-text","neuromodulator-inner-crows-feet-orbicularis-oculi-free-text","neuromodulator-inner-bunny-lines-nasalis-free-text",
                "neuromodulator-inner-jelly-roll-pretarsal-orbicularis-free-text","neuromodulator-inner-chin-mentalis-free-text","neuromodulator-inner-masseters-free-text","neuromodulator-inner-lip-flip-orbicularis-oris-free-text",
                "neuromodulator-inner-smokers-lines-orbicularis-oris-free-text","neuromodulator-inner-gummy-smile-llsan-free-text","neuromodulator-inner-depressor-anguli-oris-free-text","neuromodulator-inner-depressor-septi-nasi-free-text",
                "neuromodulator-inner-nefirtiti-lift-platysma-free-text","neuromodulator-inner-neck-bands-platysma-free-text","neuromodulator-inner-hyperhidrosis-axillae-free-text","neuromodulator-inner-hyperhidrosis-palms-free-text",
                "neuromodulator-inner-hyperhidrosis-soles-free-text","neuromodulator-inner-hyperhidrosis-scalp-free-text","neuromodulator-inner-temporalis-free-text","neuromodulator-inner-occipitalis-free-text","neuromodulator-inner-cervical-paraspinal-free-text",
                "neuromodulator-treatment-1-free-text","neuromodulator-treatment-2-free-text","neuromodulator-treatment-3-free-text","neuromodulator-inner-treatment-1-free-text",
                "neuromodulator-inner-treatment-2-free-text","neuromodulator-inner-treatment-3-free-text"
            ];
            for (var i = 0; i < inputs.length; i++) {
                var input_key = inputs[i];
                var input_value = $('input[name="treatment[' + planCount + ']['+input_key+']"]').val();
                $('#'+procedurePlanId+' #'+input_key).val(input_value);
            }

        }


    })




    $(document).ready(function() {
        $(document).on('click', '.remove_plan', function() {
            var confirmation = confirm("Are you sure you want to delete?");

            if (confirmation) {

                var severityList = $("#fetch_severity").text();

                if(severityList.length > 0){

                    selections = [];

                    $('.severity-pick').each(function(index, element) {
                        let name = $(element).attr('name');
                        let value = $(element).val();

                        selections.push({
                            name: name,
                            value: value
                        });
                    });

                }

                let name = $(this).attr('data-name');
                let value = $(this).attr('selected-value');

                let existingEntryIndex = selections.findIndex(entry => entry.name === name);

                if (parseInt(existingEntryIndex) !== -1) {
                    selections.splice(existingEntryIndex, 1);
                }

                updateCounts();

                $(this).parent('.custom-icd-form').remove();
            }
        });
    });



});
