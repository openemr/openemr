
function dermCsrfToken() {
    var el = document.querySelector('input[name="csrf_token_form"]');
    return el ? el.value : '';
}

// js_block.js (No changes needed in this file)
// *** MOVED JUSTIFIED ARRAY DECLARATION ***
var justified = []; // Declare the justified array in the outer scope of $(document).ready()

$(document).ready(function () {
    $(".dx-s").select2({});
    $(".dx-desc").select2({});

    $(".dx-s").on("change", function () {
        var selectedValue = $(this).val();
        let currentDescriptionId = $(this).attr("desc-id");
        $.ajax({
            url: "../../forms/general_dermatology/resources/dx-desc.php",
            method: "POST",
            data: { selectedValue: selectedValue, csrf_token_form: dermCsrfToken() },
            dataType: "json",
            success: function (response) {
                $("#" + currentDescriptionId).empty();
                if (response.value && response.value.length > 0) {
                    $.each(response.value, function (index, value) {
                        $("#" + currentDescriptionId).append(
                            $("<option>", {
                                value: value,
                                text: value,
                            })
                        );
                    });
                }
                $("#" + currentDescriptionId).select2();
            },
            error: function (xhr, status, error) {
                console.error(error);
            },
            complete: function() {
                // Trigger a custom event when dx-s is changed
                $(document).trigger('dxsChanged');
            }
        });
    });

    $("#procedure-plans").on("change", function () {
        var selectedValue = $(this).val();
        $.ajax({
            url: "../../forms/general_dermatology/resources/procedure-desc.php",
            method: "POST",
            data: { selectedValue: selectedValue, csrf_token_form: dermCsrfToken() },
            dataType: "json",
            success: function (response) {
                $("#plan-modal").attr(
                    "data-target",
                    ""
                );
                $("#plan-modal").attr(
                    "data-plan",
                    ""
                );
                $("#plan-desc").empty();
                $("#render-restofplans").empty();
                if (response.value && response.value.length > 0) {
                    $("#plan-desc").val(response.value);
                    $("#plan-modal").attr(
                        "data-target",
                        "#" + selectedValue.toLowerCase()
                    );
                    $("#plan-modal").attr(
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
    // *** ADDED CODE START ***

    // Event listener for changes to any dx_s select element
    $(document).on('change', 'select[name^="treatment["][name$="[dx_s]"]', function() {
        justified = []; // Clear the array before repopulating
        console.log("DX code changed");

        // Iterate over each dx_s select element
        $('select[name^="treatment["][name$="[dx_s]"]').each(function() {
            let selectedValue = $(this).val();
            if (selectedValue) {
                // Extract the DX code (handling "5-C44.519" format)
                let parts = selectedValue.split(' ');
                let lastPart = parts.pop(); // Get the last part (potential code)
                let dxCode = lastPart.includes('-') ? lastPart.split('-').pop() : lastPart;
                justified.push(dxCode);
            }
        });

        // Log the array to the console
        console.log('Justified DX Codes:', justified);
    });
    // *** ADDED CODE END ***
});
