/**
 * interface/modules/zend_modules/public/js/application/sendTo.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    BASIL PT <basil@zhservices.com>
 * @author    FASALU RAHMAN K.M <fasalu@zhservices.com>
 * @author    Riju K P <rijukp@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Hide Menu if clicked outside
$(document).mouseup(function (e) {
    var container = $(".ap-st-st-12");
    var buttons = $(".send-to");
    if (!container.is(e.target) && container.has(e.target).length === 0 && !buttons.is(e.target)) {
        $(".ap-st-st-12").css("display", "none");
    }
})
//

var check_count = 0;
(function ($) {

    window.addRule = function (selector, styles, sheet) {

        styles = (function (styles) {
            if (typeof styles === "string") return styles;
            var clone = "";
            for (var prop in styles) {
                if (Object.prototype.hasOwnProperty.call(styles, prop)) {
                    var val = styles[prop];
                    prop = prop.replace(/([A-Z])/g, "-$1").toLowerCase(); // convert to dash-case
                    clone +=
                        prop +
                        ":" +
                        (prop === "content" ? '"' + val + '"' : val) +
                        "; ";
                }
            }
            return clone;
        }(styles));
        sheet = sheet || document.styleSheets[document.styleSheets.length - 1];

        if (sheet.insertRule) sheet.insertRule(selector + " {" + styles + "}", sheet.cssRules.length);
        else if (sheet.addRule) sheet.addRule(selector, styles);

        return this;

    };

    if ($) $.fn.addRule = function (styles, sheet) {
        addRule(this.selector, styles, sheet);
        return this;
    };

}(this.jQuery || this.Zepto));

$(function () {

    /* show hide on click  */
    $('.send-to').click(function () {
        //var pos  = $(this).position();
        //$('.ap-st-st-12').fadeToggle().css({
        //	"left" : (pos.left)+"px",
        //	"top"  : (pos.top+35)+"px"
        //});
        $('.se_in_15').fadeOut();
        var cleintwidth = $(window).width();
        var pos = $(this).position();
        var sendLeft = $('.ap-st-st-12').width() + pos.left;

        if (sendLeft > cleintwidth) {
            var sendCss = {
                right: ((cleintwidth - pos.left) - 74) + "px",
                top: (pos.top + 35) + "px"

            }
            $('.ap-st-st-12:after').addRule({left: "98%"});
            $('.ap-st-st-12:before').addRule({left: "98%"});
        } else {
            var sendCss = {left: (pos.left + 5) + "px", top: (pos.top + 35) + "px"}
        }
        $('.ap-st-st-12').fadeToggle().css(sendCss);
    });

    $(".ap-st-st-4").click(function () {
        if ($(this).is(":checked")) {
            $(".check_component").attr("checked", true);
            $(".selected_check").removeClass("selected_check");
        } else {
            $(".check_component").removeAttr("checked");
            $(".option_wrap").addClass("selected_check");
        }

    });

    $(".ap-st-st-5").on("click", ".check_component", function () {
        checkedCount = $('[name="componentcheckbox[]"]:checked').length;
        if (check_count == checkedCount) $(".ap-st-st-4").attr("checked", true);
        else $(".ap-st-st-4").attr("checked", false);
        $(this).parent().toggleClass("selected_check");
    });

    // Toggle Fax Section
    $('[name="send_to"]').click(function () {
        if ($(this).attr("id") == "send_to_fax") {
            $(".ap-st-st-8").show();
            $(".display_block").removeClass("display_block");
            $("#combination_form_div").addClass("display_block");
        } else if ($(this).attr("id") == "send_to_printer") {
            $(".ap-st-st-8").hide();
            $(".display_block").removeClass("display_block");
            $("#combination_form_div").addClass("display_block");
        } else if ($(this).attr("id") == "send_to_hie") {
            $(".display_block").removeClass("display_block");
            $("#hie_div").addClass("display_block");
        } else if ($(this).attr("id") == "send_to_emrdirect") {
            $(".display_block").removeClass("display_block");
            $("#emrDirect_div").addClass("display_block");
        } else if ($(this).attr("id") == "download_all") {
            $(".display_block").removeClass("display_block");
            $("#download_all_div").addClass("display_block");
        }
    });

    //
    $("#fax_reciever").change(function () {
        if ($("#fax_reciever").val() != '') {
            if ($(this).val() == 'other') {
                $("#fax_no").attr('style', '');
                $("#fax_no").attr('readonly', false);
                $("#fax_no").val('');
                $("#facility_tr").hide();
                $("#fax_no_tr").show();
            } else {
                $("#fax_no_tr").hide();
                $("#facility_tr").show();
                $.ajax({
                    type: "POST",
                    url: APP_URL + "/application/sendto/ajax",
                    dataType: "html",
                    data: {
                        ajax_mode: 'fax_details',
                        req_list: $("#fax_reciever").val(),
                    },
                    success: function (thedata) {
                        $("#facility_fax_no").html(thedata);

                    },
                    error: function () {
                        alert("ajax error");
                    }
                });
            }
        }
    });


    //
    $("#facility_fax_no").change(function () {
        if ($("#facility_fax_no").val()) {
            $("#fax_no_tr").show();
            $("#fax_no").attr('style', 'background:#ccc;');
            $("#fax_no").attr('readonly', 'readonly');
            $("#fax_no").val($("#facility_fax_no").val());
        } else {
            var resultTranslated = js_xl("No Fax Number Saved For The Selected Organization");
            alert(resultTranslated.msg);
        }
        return false;
    });

    $(".export-document").click(function (event) {
        let qrda = $("input[name='downloadformat']:checked").val();
        if (qrda == 'qrda') {
            $("#componentsForCCDA").hide('slow');
            $("#componentsForQRDA3").hide('slow');
            $("#componentsForQRDA").show('slow');
        } else if (qrda == 'qrda3') {
            $("#componentsForCCDA").hide('slow');
            $("#componentsForQRDA").hide('slow');
            $("#componentsForQRDA3").show('slow');
        } else {
            $("#componentsForQRDA").hide('slow');
            $("#componentsForQRDA3").hide('slow');
            $("#componentsForCCDA").show('slow');
        }
    });

    $(".showcomponentsForCCDA-div").click(function () {
        let qrda = $("input[name='downloadformat']:checked").val();
        if (qrda === 'qrda') {
            $("#componentsForCCDA").hide('slow');
            $("#componentsForQRDA3").hide('slow');
            $("#componentsForQRDA").toggle('slow');
        } else if (qrda === 'qrda3') {
            $("#componentsForCCDA").hide('slow');
            $("#componentsForQRDA").hide('slow');
            $("#componentsForQRDA3").toggle('slow');
        } else {
            $("#componentsForQRDA").hide('slow');
            $("#componentsForQRDA3").hide('slow');
            $("#componentsForCCDA").toggle('slow');
        }
    });

    //check all for component
    $('#chkall_cmp1').click(function (event) {
        if (this.checked) {
            $("#chkall_cmp_div1").removeClass("selected_check");
            $('.chkbxcmp_click1').each(function () {
                this.checked = true;
                $(".selected_check").removeClass("selected_check");
            });
        } else {
            $("#chkall_cmp_div1").addClass("selected_check");
            $('.chkbxcmp_click1').each(function () {
                this.checked = false;
                $(".chkdivcmp1").addClass("selected_check");
            });
        }
    });
    $(".chkbxcmp_click1").click(function () {
        chk_cmp_id = $(this).attr('id');
        if ($(this).is(":checked")) {
            $("#" + chk_cmp_id).removeClass("selected_check");
        } else {
            $("#" + chk_cmp_id).addClass("selected_check");
        }
        chkbx_click_len = $(".chkbxcmp_click1").length;
        chkbx_click_checked_len = $(".chkbxcmp_click1:checked").length;
        if (chkbx_click_checked_len == chkbx_click_len) {
            $("#chkall_cmp1").attr("checked", true);
            $("#chkall_cmp_div1").removeClass("selected_check");
        } else {
            $("#chkall_cmp1").attr("checked", false);
            $("#chkall_cmp_div1").addClass("selected_check");
        }
    });
});

function getComponents(val) {
    $.ajax({
        type: "POST",
        url: APP_URL + "/application/sendto/ajax",
        dataType: "html",
        data: {
            ajax_mode: 'get_componets',
            form_id: val
        },
        async: true,
        success: function (json) {
            $(".ap-st-st-5").html("");
            $(".ap-st-st-4").attr("checked", false);

            checkBox = "";
            components = JSON.parse(json);
            for (form_id in components) {
                check_count++;
                checkBox += "<div class='option_wrap'><input checked name='componentcheckbox[]' class='check_component' type='checkbox' value='" + form_id + "' ref='" + components[form_id] + "' >&nbsp;&nbsp;" + components[form_id] + "</div>";
            }
            checkBox += "<div class='clear'></div>";
            $(".ap-st-st-4").attr("checked", true);
            $(".ap-st-st-5").html(checkBox);

        },
        error: function () {
            var resultTranslated = js_xl("Something went wrong");
            alert(resultTranslated.msg);
        }
    });
}

function sendToEmrDirect() {
    var latest_ccda = getLatestCcda();
    var format = $('input:radio[name="phimail_format"]:checked').val();
    var combination = '';
    var components = '';
    var downloadformat_type = $('input[name="downloadformat_type"]:checked').val() || "";

    var comp = getComponentsString();
    const ccdaGenerateBaseUrl = APP_URL + "/encounterccdadispatch/index?view=1&emr_transfer=1&recipient=emr_direct"
        + "&downloadformat_type=" + downloadformat_type;
    const emrDirectBaseUrl = APP_URL + "/encountermanager/transmitCCD?xml_type=" + format
        + "&downloadformat_type=" + downloadformat_type;
    if ($("#ccda_pid").val()) {
        combination = $("#ccda_pid").val();
    } else {
        var pid_encounter = document.getElementsByName('ccda_pid[]');
        for (i = 0; i < pid_encounter.length; i++) {
            if (pid_encounter[i].checked) {
                if (combination) combination += '|';
                combination += pid_encounter[i].value;
            }
        }
    }
    if (combination == '') {
        $('.ap-st-st-12').fadeOut();
        $('.activity_indicator').css({"display": "none"});
        var resultTranslated = js_xl("Please select at least one patient.");
        alert(resultTranslated.msg);
        return false;
    }
    $(".chkbx_cmps").each(function () {
        if ($(this).is(":checked")) {
            i++;
            if (components) components += "|";
            components += $(this).val();
        }
    });
    var recipients = $(".emr_to_phimail").val();
    var referral_reason = $("#referral_reason").val();
    if (recipients != '') {
        var ccdaUrl = ccdaGenerateBaseUrl + "&combination=" + combination + "&sections=" + components + "&param=" + recipients
            + "&referral_reason=" + referral_reason + "&components=" + comp + "&latest_ccda=" + latest_ccda;
        $.ajax({
            type: "POST",
            url: ccdaUrl,
            dataType: "html",
            data: {},
        }).done(function () {
            $.ajax({
                type: "POST",
                url: emrDirectBaseUrl + "&combination=" + combination + "&recipients=" + recipients,
                dataType: "html",
                data: {},
                success: function (thedata) {
                    $('.ap-st-st-12').fadeOut();
                    $('.activity_indicator').css({"display": "none"});
                    var resultTranslated = js_xl(thedata);
                    alert(resultTranslated.msg);
                },
                error: function () {
                    $('.ap-st-st-12').fadeOut();
                    $('.activity_indicator').css({"display": "none"});
                    var resultTranslated = js_xl("Failed to send");
                    alert(resultTranslated.msg);
                }
            });
        }).fail(function () {
            $('.ap-st-st-12').fadeOut();
            $('.activity_indicator').css({"display": "none"});
            var resultTranslated = js_xl("Failed to send");
            alert(resultTranslated.msg);
        });

    } else {
        $('.activity_indicator').css({"display": "none"});
        var resultTranslated = js_xl("Please Specify at least One Direct Address");
        alert(resultTranslated.msg);
    }
}

function getLatestCcda() {
    var latest_ccda = '';
    if ($("#latest_ccda").is(":checked")) {
        latest_ccda = 1;
    }
    return latest_ccda;
}

function getComponentsString() {
    var i = 0;
    var comp = '';
    $(".check_component1").each(function () {
        if ($(this).is(":checked")) {
            i++;
            if (comp) comp += "|";
            comp += $(this).val();
        }
    });
    return comp;
}

function sendToDownloadAll() {
    var i;
    var count = 0;
    var pids;
    var pid;
    var latest_ccda = getLatestCcda();
    var comp = getComponentsString();

    if ($('#ccda_pid').val()) {
        pids = $('#ccda_pid').val();
        pids = pids.split("_");
        pid = pids[0];
        count++;
    } else {
        pids = document.getElementsByName('ccda_pid[]');
        for (i = 0; i < pids.length; i++) {
            if (pids[i].checked) {
                count++;
            }
        }
    }
    if (count == 0 && $('input:radio[name="downloadformat"]:checked').val() != 'qrda3') {
        $('.ap-st-st-12').fadeOut();
        $('.activity_indicator').css({"display": "none"});
        var resultTranslated = js_xl("Please select at least one patient.");
        alert(resultTranslated.msg);
        return false;
    } else {
        var download_format = $('input:radio[name="downloadformat"]:checked').val();
        if (download_format == 'ccda') {
            if ($('#ccda_pid').val()) {
                window.location.assign(WEB_ROOT + "/interface/modules/zend_modules/public/encountermanager/index?pid_ccda=" + pid + "&downloadccda=download_ccda&components=" + comp + "&latest_ccda=" + latest_ccda);
            } else {
                $('#components').val(comp);
                $("#latestccda").val(latest_ccda);
                $('#download_ccda').trigger("click");
                $(".check_pid").prop("checked", false);
            }
        } else if (download_format == 'qrda') {
            if ($('#ccda_pid').val()) {
                window.location.assign(WEB_ROOT + "/interface/modules/zend_modules/public/encountermanager/index?pid_ccda=" + pid + "&downloadqrda=download_qrda");
            } else {
                $('#download_qrda').trigger("click");
                $(".check_pid").prop("checked", false);
            }
        } else if (download_format == 'qrda3') {
            if ($('#ccda_pid').val()) {
                window.location.assign(WEB_ROOT + "/interface/modules/zend_modules/public/encountermanager/index?pid_ccda=" + pid + "&downloadqrda=download_qrda3");
            } else {
                $('#download_qrda3').trigger("click");
                $(".check_pid").prop("checked", false);
            }
        } else if (download_format == 'ccr') {
            if ($('#ccda_pid').val()) {
                window.location.assign(WEB_ROOT + "/interface/modules/zend_modules/public/encountermanager/index?pid_ccr=" + pid + "&downloadccr=download_ccr");
            } else {
                $('#download_ccr').trigger("click");
                $(".check_pid").prop("checked", false);
            }
        } else if (download_format == 'ccd') {
            if ($('#ccda_pid').val()) {
                window.location.assign(WEB_ROOT + "/interface/modules/zend_modules/public/encountermanager/index?pid_ccd=" + pid + "&downloadccd=download_ccd");
            } else {
                $('#download_ccd').trigger("click");
                $(".check_pid").prop("checked", false);
            }
        }
        //$(".check_pid").prop("checked",false);
        $('.ap-st-st-12').fadeOut();
        $('.activity_indicator').css({"display": "none"});
    }
}

function sendFiltered() {
    $("#form_filter_content").val('1');
    send();
}

function send() {
    var send_to = $('input:radio[name="send_to"]:checked').val();
    var cover_letter = 0;
    var latest_ccda = getLatestCcda();
    if ($("#include_coverletter").is(":checked")) {
        cover_letter = 1;
    }
    $('.activity_indicator').css({
        "display": "block"
    });
    $("#downloadccda").val('');
    $("#downloadccr").val('');
    $("#downloadccd").val('');
    $("#latestccda").val('');
    var comp = getComponentsString();
    if (send_to == "printer" || send_to == "fax") {
        formnames = "";
        formnames_title = "";
        var i = 0;
        $(".check_component").each(function () {
            if ($(this).is(":checked")) {
                i++;
                if (i > 1) {
                    formnames += "***";
                    formnames_title += "***";
                }
                formnames += $(this).val();
                formnames_title += $(this).attr("ref");
            }
        });
        if (send_to == "printer") {
            url = "";
            url += "covering_letter=" + cover_letter + "&selected_cform=" + $("#selected_cform").val();
            url += "&formnames=" + formnames + "&formnames_title=" + formnames_title;
            window.open(WEB_ROOT + "/interface/patient_file/encounter/print_report.php?" + url, "Print", 'width=1000,height=800,resizable=yes,scrollbars=yes');
            $('.ap-st-st-12').fadeToggle();
            $('.activity_indicator').css({"display": "none"});
        }
        if (send_to == "fax") {
            $.ajax({
                type: "POST",
                url: APP_URL + "/application/sendto/ajax",
                dataType: "html",
                data: {
                    ajax_mode: 'send_fax',
                    sendfax: 'send',
                    attentionto: $("#attention_to").val(),
                    selectedforms: formnames,
                    form_sel_title: formnames_title,
                    selected_cform: $("#selected_cform").val(),
                    covering_letter: $("#include_coverletter").val(),
                    visit_duration: $("#include_visitduration").val(),
                    fax_no: $('#fax_no').val()
                },
                async: true,
                success: function (data) {
                    $('.activity_indicator').css({"display": "none"});
                },
                error: function () {
                    var resultTranslated = js_xl("Fax sending failed");
                    alert(resultTranslated.msg);
                    $('.activity_indicator').css({"display": "none"});
                }
            });
        }
    } else if (send_to == "hie") {
        var str = '';
        var combination = '';
        components = document.getElementsByName('ccda');
        for (i = 0; i < components.length; i++) {
            if (components[i].checked) {
                if (str) str += '|';
                str += components[i].value;
            }
        }

        if (document.getElementById('ccda_pid')) {
            combination = document.getElementById('ccda_pid').value;
        } else {
            pid_encounter = document.getElementsByName('ccda_pid[]');
            for (i = 0; i < pid_encounter.length; i++) {
                if (pid_encounter[i].checked) {
                    if (combination) combination += '|';
                    combination += pid_encounter[i].value;
                }
            }
        }
        if (combination == '') {
            $('.ap-st-st-12').fadeToggle();
            $('.activity_indicator').css({"display": "none"});
            var resultTranslated = js_xl("Please select at least one patient.");
            alert(resultTranslated.msg);
            return false;
        }

        $.ajax({
            type: "POST",
            url: APP_URL + "/encounterccdadispatch/index?combination=" + combination + "&sections=" + str + "&send=1&recipient=hie&components=" + comp + "&latest_ccda=" + latest_ccda,
            dataType: "html",
            data: {},
            success: function (thedata) {
                $('.ap-st-st-12').fadeToggle();
                $('.activity_indicator').css({"display": "none"});
                var resultTranslated = js_xl('Successfully Sent');
                alert(resultTranslated.msg);
            },
            error: function () {
                $('.activity_indicator').css({"display": "none"});
                var resultTranslated = js_xl("Send To HIE failed");
                alert(resultTranslated.msg);
            }
        });
    } else if (send_to == "download") {
        $('.activity_indicator').css({"display": "none"});
        obj = document.getElementsByName('download_format');
        count = 0;
        for (i = 0; i < obj.length; i++) {
            if (obj[i].disabled == false && obj[i].checked == true) {
                count++;
            }
        }
        if (count == 0) {
            var resultTranslated = js_xl("Please select a format");
            alert(resultTranslated.msg);
            return false;
        }
        $('#hl7button').trigger('click');
    } else if (send_to == "emr_direct") {
        sendToEmrDirect();
    } else if (send_to == "download_all") {
        sendToDownloadAll();
    }
}
