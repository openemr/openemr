<?php
/**
 * Allows acl(php-gacl) administration. Heavily ajax and
 * javascript/jquery dependent. All ajax functions are called
 * from adminacl_ajax.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak01@hotmail.com>
 * @copyright Copyright (c) 2007-2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Ranganath Pathak <pathak01@hotmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/acl.inc");

use OpenEMR\Core\Header;

//ensure user has proper access
if (!acl_check('admin', 'acl')) {
    echo "(" . xlt('ACL Administration Not Authorized') . ")";
    exit;
}

//ensure phpgacl is installed
if (!isset($phpgacl_location)) {
    echo "(" . xlt('PHP-gacl is not installed') . ")";
    exit;
}
?>

<html>
<head>
    <title><?php echo xlt("Access Control List Administration"); ?></title>

    <?php Header::setupHeader(); ?>

    <script type="text/JavaScript">
        $(document).ready(function(){
            var groupTitle = "<?php echo xla('This section allows you to create and remove groups and modify or grant access privileges to existing groups. Check the check box to display section'); ?>";
            $('#advanced-tooltip').tooltip({title: "<?php echo xla('Click to manually configure access control, recommended for advanced users'); ?>"});
            $('#user-tooltip').tooltip({title: "<?php echo xla('Click the pencil icon to grant and remove access privileges to the selected user'); ?>"});
            $('#group-tooltip').tooltip({title: groupTitle});
            $('#new-group-tooltip').tooltip({title: "<?php echo xla('Enter values in this section to create a new group also known as Access Request Object (ARO)'); ?>"});
            $('#remove-group-tooltip').tooltip({title: "<?php echo xla('Use this section to delete existing groups or Access Request Objects (AROs)'); ?>"});

            //Show membership section by default
            $("#membership_show").click();
            membership_show();
            //Show membership section by default

            $("body").on("click", ".link_submit", function(){
                generic_click(this);
                return false;
            });

            $("body").on("click", ".button_submit", function(){
                generic_click(this);
                return false;
            });

            $("body").on("click", "#membership_show", function(){
                membership_show();
                return;
            });

            $("body").on("click", "#acl_show", function(){
                acl_show();
                return;
            });

            $("body").on("click", ".button_acl_add", function(){
                //if Clear, then reset form
                if (this.id == "button_acl_add_clear") {
                    $("#acl_error").empty();
                    $("#div_acl_add_form span.alert").empty();
                    return;
                }
                //if Cancel, then reset/hide form and show create/remove acl links
                if (this.id == "button_acl_add_cancel") {
                    $("#div_acl_add_form").hide("slow");
                    $("#acl_error").empty();
                    $("#div_acl_add_form span.alert").empty();
                    $("#none_acl_returns").show();
                    $("#none_acl_list").show();
                    return;
                }
                //Submit selected, so send ajax request
                title = $("#title_field").val();
                identifier = $("#id_field").val();
                return_value = $("#return_field").val();
                description = $("#desc_field").val();
                $.ajax({
                    type: "POST",
                    url: "../../library/ajax/adminacl_ajax.php",
                    dataType: "xml",
                    data: {
                        control: "acl",
                        action: "add",
                        title: title,
                        identifier: identifier,
                        return_value: return_value,
                        description: description
                    },
                    success: function(xml){
                        //if successful, then show new group
                        if ($(xml).find("success").text() == "SUCCESS") {
                            $("#button_acl_add_cancel").click();
                            acl_show();
                        }
                        //Remove Loading indicator and old errors, then display new errors
                        $("#div_acl_add_form span.loading").hide();
                        $("#acl_error").empty();
                        $("#div_acl_add_form span.alert").empty();
                        $(xml).find("error").each(function(){
                            temparray = $(this).text().split("_");
                            $("#" + temparray[0] + "_error").append(temparray[1]);
                        });
                        $("#acl_error").show();
                        $("#div_acl_add_form span.alert").show();
                    },
                    beforeSend: function(){
                        //Show Loading indicator
                        $("#div_acl_add_form span.loading").show();
                    },
                    error: function(){
                        //Remove Loading indicator and show errors
                        $("#div_acl_add_form span.loading").hide();
                        $("#acl_error").empty();
                        $("#acl_error").append("<span class='alert'><?php echo xla('ERROR, unable to collect data from server'); ?><br></span>");
                        $("#acl_error").show();
                    }
                });
                return false;
            });

            $("body").on("click", ".button_acl_remove", function(){
                //if Clear, then reset form
                if (this.id == "button_acl_remove_clear") {
                    $("#acl_error").empty();
                    $("#div_acl_remove_form span.alert").empty();
                    return;
                }
                //if Cancel, then reset/hide form and show create/remove acl links
                if (this.id == "button_acl_remove_cancel") {
                    $("#div_acl_remove_form").hide("slow");
                    $("#acl_error").empty();
                    $("#div_acl_remove_form span.alert").empty();
                    $("#none_acl_returns").show();
                    $("#none_acl_list").show();
                    return;
                }
                //Ensure confirmed before deleting group
                confirmDelete = $("input[name=acl_remove_confirm]:checked").val();
                if (confirmDelete == "no") { //send confirm alert and exit
                    $("#remove_confirm_error").empty();
                    $("#remove_confirm_error").append("<?php echo xla('Select Yes to confirm group deletion'); ?>");
                    return false;
                }
                //Delete and confirmed, so send ajax request
                temparray = $("#acl_field").val().split("-");
                title = temparray[0];
                return_value = temparray[1];
                $.ajax({
                    type: "POST",
                    url: "../../library/ajax/adminacl_ajax.php",
                    dataType: "xml",
                    data: {
                        control: "acl",
                        action: "remove",
                        title: title,
                        return_value: return_value
                    },
                    success: function(xml){
                        //if successful, then show new group
                        if ($(xml).find("success").text() == "SUCCESS") {
                            $("#button_acl_remove_cancel").click();
                            acl_show();
                        }
                        //Remove Loading indicator and old errors, then display new errors
                        $("#div_acl_remove_form span.loading").hide();
                        $("#acl_error").empty();
                        $("#div_acl_remove_form span.alert").empty();
                        $(xml).find("error").each(function(){
                            temparray = $(this).text().split("_");
                            $("#" + temparray[0] + "_error").append(temparray[1]);
                        });
                        $("#acl_error").show();
                        $("#div_acl_remove_form span.alert").show();
                    },
                    beforeSend: function(){
                        //Show Loading indicator
                        $("#div_acl_remove_form span.loading").show();
                    },
                    error: function(){
                        //Remove Loading indicator and show errors
                        $("#div_acl_remove_form span.loading").hide();
                        $("#acl_error").empty();
                        $("#acl_error").append("<span class='alert'><?php echo xla('ERROR, unable to collect data from server'); ?><br></span>");
                        $("#acl_error").show();
                    }
                });
                return false;
            });

            function membership_show() {
                if (!$("#membership_show").prop('checked')) {
                    $("#membership_error").empty();
                    $("#membership").hide("slow");
                    return;
                }
                //Send ajax request
                $.ajax({
                    type: "POST",
                    url: "../../library/ajax/adminacl_ajax.php",
                    dataType: "xml",
                    data: {
                        control: "username",
                        action: "list"
                    },
                    success: function(xml){
                        $("#membership_error").empty();
                        $("#membership").empty();
                        $(xml).find("user").each(function(){
                            username = $(this).find("username").text();
                            $("#membership").append("<div id='link_" + username + "'><span class='text'>" + username + "</span><a class='link_submit' href='no_javascript' id='" + username + "_membership_list' title='<?php echo xla('Edit'); ?> " + username + "'>&nbsp;<i class='fa fa-pencil' aria-hidden='true'></i></a></span><a class='link_submit' href='no_javascript' id='" + username +  "_membership_hide' style='display: none' title='<?php echo xla('Hide'); ?> " + username + "'>&nbsp;<i class='fa fa-eye-slash' aria-hidden='true'></i></a><span class='alert' style='display: none;'>&nbsp;&nbsp;<?php echo xla('This user is not a member of any group'); ?>!!!</span><span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo xla('LOADING'); ?>...</span></div><div id='error_" + username + "'></div><div id='" + username +  "' style='display: none'><div class='table-responsive'><table class='head'><thead><tr><th class='text-center'><span class='bold'><?php echo xla('Active'); ?></span></th><th class='text-center'><span class='bold'><?php echo xla('Inactive'); ?></span></th></tr><tbody><tr><td align='center'><select name='active[]' multiple></select><br /><p align='center'><input class='button_submit' type='button' title='<?php echo xla('Remove'); ?>' id='" + username  + "_membership_remove' value=' >> '></p></td><td align='center'><select name='inactive[]' multiple></select><br /><p align='center'><input class='button_submit' type='button' title='<?php echo xla('Add'); ?>' id='" + username + "_membership_add' value=' << ' ></p></td></tr></tbody></table></div></div>");
                            if ($(this).find("alert").text() == "no membership") {
                                $("#link_" + username + " span.alert").show();
                            }
                        });
                        //Show the username list and remove loading indicator
                        $("#membership").show("slow");
                        $("#membership_edit span.loading:first").hide();
                    },
                    beforeSend: function(){
                        //Show Loading indicator
                        $("#membership_edit span.loading:first").show();
                    },
                    error: function(){
                        //Remove Loading indicator and previous error, if any, then show error
                        $("#membership_edit span.loading:first").hide();
                        $("#membership_error").empty();
                        $("#membership_error").append("<span class='alert'><?php echo xla('ERROR, unable to collect data from server'); ?><br><br></span>");
                        $("#membership_error").show();
                    }
                });
                return;
            }

            function acl_show() {
                if (!$("#acl_show").prop('checked')) {
                    $("#acl_error").empty();
                    $("#none_acl_returns").hide();
                    $("#none_acl_list").hide();
                    $("#acl").hide("slow");
                    $("#div_acl_add_form").hide("slow");
                    $("#div_acl_remove_form").hide("slow");
                    return;
                }
                //Send ajax request
                $.ajax({
                    type: "POST",
                    url: "../../library/ajax/adminacl_ajax.php",
                    dataType: "xml",
                    data: {
                        control: "acl",
                        action: "list"
                    },
                    success: function(xml){
                        $("#acl_error").empty();
                        $("#acl").empty();
                        $(xml).find("acl").each(function(){
                            value_acl = $(this).find("value").text();
                            title = $(this).find("title").text();
                            titleDash = value_acl.replace(" ","-");
                            return_value = $(this).find("returnid").text();
                            return_title = $(this).find("returntitle").text();
                            note = $(this).find("note").text();
                            $("#acl").append("<div id='acl_link_" + titleDash + "_" + return_value + "'><span class='text' title='" + note  + "'>" + title + "-" + return_title  + "</span><a class='link_submit' href='no_javascript' id='" + titleDash  + "_aco_list_" + return_value  + "' title='<?php echo xla('Edit'); ?> " + title + "-" + return_title  + "'>&nbsp;<i class='fa fa-pencil' aria-hidden='true'></i></a></span><a class='link_submit' href='no_javascript' id='" + titleDash + "_acl_hide_" + return_value + "' style='display: none' title='<?php echo xla('Hide'); ?> " + title + "-" + return_title  + "'>&nbsp;<i class='fa fa-eye-slash' aria-hidden='true'></i></a><span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo xla('LOADING'); ?>...</span></div><div id='acl_error_" + titleDash + "_" + return_value + "'></div><div id='acl_" + titleDash + "_" + return_value  + "' style='display: none'><div class='table-responsive'><table class='head'><thead><tr><th class='text-center'><span class='bold'><?php echo xla('Active'); ?></span></th><th  class='text-center'><span class='bold'><?php echo xla('Inactive'); ?></span></th></tr></thead><tbody><tr><td align='center'><select name='active[]' size='6' multiple class='form-control'></select><br /><p align='center'><input class='button_submit' type='button' title='<?php echo xla('Remove'); ?>' id='" + titleDash  +"_aco_remove_" + return_value  + "' value=' >> '></p></td><td align='center'><select name='inactive[]' size='6' multiple class='form-control'></select><br /><p align='center'><input class='button_submit' type='button' title='<?php echo xla('Add'); ?>' id='" + titleDash  + "_aco_add_" + return_value  + "' value=' << ' ></p></td></tr></tbody></table></div></div>");
                        });
                        //Show the acl list and add link. Remove loading indicator.
                        $("#acl").show("slow");
                        $("#acl_edit div span.loading:first").hide();
                        $("#none_acl_returns").show();
                        $("#none_acl_list").show();
                    },
                    beforeSend: function(){
                        //Show Loading indicator
                        $("#acl_edit div span.loading:first").show();
                    },
                    error:function(){
                        //Remove Loading indicator and previous error, if any, then show error
                        $("#acl_edit div span.loading:first").hide();
                        $("#acl_error").empty();
                        $("#acl_error").append("<span class='alert'><?php echo xla('ERROR, unable to collect data from server'); ?><br><br></span>");
                        $("#acl_error").show();
                    }
                });
                return;
            }

            function generic_click(cthis) {
                //set up variables and html page pointers
                temparray = cthis.id.split("_");
                identity = temparray[0];
                identityFormatted = identity.replace("-"," ");
                control = temparray[1];
                action = temparray[2];
                return_value = temparray[3];
                if (control == "membership") {
                    contentPointer = "#" + identity.replace(" ","\\ ");
                    linkPointer = "#link_" + identity.replace(" ","\\ ");
                    linkPointerPost ="";
                    errorPointer = "#error_" + identity.replace(" ","\\ ");
                }
                if (control == "acl" || control == "aco") {
                    contentPointer = "#acl_" + identity + "_" + return_value;
                    linkPointer = "#acl_link_" + identity + "_" + return_value;
                    linkPointerPost ="";
                    errorPointer = "#acl_error_" + identity + "_" + return_value;
                }
                //special cases, show add/remove acl forms
                if (identity == "none" && control == "acl") { //action == "returns"
                    if (action == "returns") {
                        contentPointer = "#div_acl_add_form";
                    }
                    else if (action == "list") {
                        contentPointer = "#div_acl_remove_form";
                    }
                    linkPointer = "#acl_edit";
                    linkPointerPost =":first";
                    errorPointer = "#acl_error";
                }

                //If clicked Hide link
                if (action == "hide") {
                    //Remove stuff and  show Edit link
                    $(contentPointer).hide("slow");
                    $(errorPointer).hide();
                    $(linkPointer + " a.link_submit:last").hide();
                    $(linkPointer + " a.link_submit:first").show();
                    return;
                }

                //If clicked Add with ACO or membership, then collect selections
                if (action == "add" && !(control == "acl")) {
                    var selected = [];
                    selected = $(contentPointer + " select:last").val();
                }

                //If clicked Remove with ACO or membership, then collect selections
                if (action == "remove" && !(control == "acl")) {
                    var selected = [];
                    selected = $(contentPointer + " select:first").val();
                }

                //Send ajax request
                $.ajax({
                    type: "POST",
                    url: "../../library/ajax/adminacl_ajax.php",
                    dataType: "xml",
                    data: {
                        name: identityFormatted,
                        control: control,
                        action: action,
                        'selection[]': selected,
                        return_value: return_value
                    },
                    success: function(xml){

                        //SPECIAL CASES to show the add/remove acl form, then exit
                        if (identity == "none" && control == "acl") {
                            $(contentPointer + " select").empty();
                            if (action == "returns") {
                                $(xml).find("return").each(function(){
                                    $(contentPointer + " select").append("<option value='" + $(this).find("returnid").text() + "'>" + $(this).find("returntitle").text() + "</option>");
                                });
                            }
                            else if (action == "list") {
                                $(xml).find("acl").each(function(){
                                    $(contentPointer + " select").append("<option value='" + $(this).find("value").text() + "-" + $(this).find("returnid").text() + "'>" + $(this).find("title").text() + "-" + $(this).find("returntitle").text() + "</option>");
                                });
                            }
                            $(contentPointer + " option").removeAttr('selected');
                            $(contentPointer).show("slow");
                            $("#none_acl_returns").hide();
                            $("#none_acl_list").hide();
                            $(linkPointer + " span.loading" + linkPointerPost).hide();
                            return;
                        }

                        if (control == "membership") {
                            //Remove, then re-populate, then set size of selection boxes
                            $(contentPointer + " select").empty();
                            counterActive = 0;
                            counterInactive = 0;
                            $(xml).find("active").find("group").each(function(){
                                $(contentPointer + " select:first").append("<option value='" + $(this).find("value").text() + "'>" + $(this).find("label").text() + "</option>");
                                counterActive = counterActive + 1;
                            });
                            $(xml).find("inactive").find("group").each(function(){
                                $(contentPointer + " select:last").append("<option value='" + $(this).find("value").text() + "'>" + $(this).find("label").text() + "</option>");
                                counterInactive = counterInactive + 1;
                            });
                            $(contentPointer + " option").removeAttr('selected');
                            if (counterActive > counterInactive) {
                                size = counterActive;
                            }
                            else {
                                size = counterInactive;
                            }
                            if (size > 10) {
                                size = 10;
                            }
                            if (counterActive > 0) {
                                //ensure remove the no active group alert
                                $(linkPointer  + " span.alert").hide();
                            }
                        }

                        if (control == "acl" || control == "aco") {
                            //Remove, then re-populate, then set size of selection boxes
                            $(contentPointer + " select").empty();
                            counterActive = 0;
                            counterInactive = 0;
                            $(xml).find("active").find("section").each(function(){
                                $(contentPointer + " select:first").append("<optgroup label='" + $(this).find("name").text() + "'>");
                                counterActive = counterActive + 1;
                                $(this).find("aco").each(function(){
                                    $(contentPointer + " select:first").append("<option value='" + $(this).find("id").text() + "'>" + $(this).find("title").text() + "</option>");
                                    counterActive = counterActive + 1;
                                });
                                $(contentPointer + " select:first").append("</optgroup>");
                            });
                            $(xml).find("inactive").find("section").each(function(){
                                $(contentPointer + " select:last").append("<optgroup label='" + $(this).find("name").text() + "'>");
                                counterInactive = counterInactive + 1;
                                $(this).find("aco").each(function(){
                                    $(contentPointer + " select:last").append("<option value='" + $(this).find("id").text() + "'>" + $(this).find("title").text() + "</option>");
                                counterInactive = counterInactive + 1;
                                });
                                $(contentPointer + " select:last").append("</optgroup>");
                            });
                            $(contentPointer + " option").removeAttr('selected');
                            if (counterActive > counterInactive) {
                                size = counterActive;
                            }
                            else {
                                size = counterInactive;
                            }
                            if (size > 15) {
                                size = 15;
                            }
                        }

                        //display the selection boxes
                        $(contentPointer + " select").attr('size', size);
                        $(contentPointer).show("slow");

                        if (action == "list") {
                        //Remove Edit link and show Hide link
                            $(linkPointer + " a.link_submit:first").hide();
                            $(linkPointer + " a.link_submit:last").show();
                        }

                        //Remove Loading indicator
                        $(linkPointer + " span.loading" + linkPointerPost).hide();

                        //Remove old errors, then display any new errors to user
                        $(errorPointer).empty();
                        $(xml).find("error").each(function(){
                            $(errorPointer).append("<span class='alert'>" + $(this).text() + "<br></span>");
                            $(errorPointer).show();
                        });
                    },
                    beforeSend:  function(){
                        //Show Loading indicator
                        $(linkPointer + " span.loading" + linkPointerPost).show();
                    },
                    error: function(){
                        //Remove Loading indicator and show errors
                        $(linkPointer + " span.loading" + linkPointerPost).hide();
                        $(errorPointer).empty();
                        $(errorPointer).append("<span class='alert'><?php echo xla('ERROR, unable to collect data from server'); ?><br></span>");
                        $(errorPointer).show();
                    }
                });
                return;
            }
        });
    </script>

    <style type="text/css">
        body {
            padding: 5pt 15pt 5pt 5pt;
            margin: 0pt;
        }
        .loading {
            font-family: sans-serif;
            text-decoration: blink;
            font-size: 10pt;
            color: red;
            font-weight: bold;
        }
        .alert {
            font-family: sans-serif;
            font-size: 10pt;
            color: red;
            font-weight: bold;
        }
        .section {
            border: solid;
            border-width: 1px;
            border-color: #0000ff;
            margin: 10pt 0 10pt 0pt;
            padding: 5pt;
        }
        select[multiple], select[size] {
            height: auto !Important;
            width: 400px;
        }
        .section a, .section a:visited, .section a:hover {
            text-decoration:none;
            color:#000000 ! Important;
        }
    </style>
</head>
<body class="body_top">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="page-header clearfix">
                    <h2 class="clearfix"><span id='header_text'><?php echo xlt("Access Control List Administration"); ?></span> &nbsp;&nbsp; <?php echo ($phpgacl_location) ? "<a href='../../gacl/admin/acl_admin.php' onclick='top.restoreSession()'><i id='advanced-tooltip' class='fa fa-external-link fa-2x small' aria-hidden='true'></i> </a>" : ""; ?><a class="pull-right" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#000000"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div id='membership_edit'>
                    <span class="bold"><input id='membership_show' type='checkbox'><?php echo xlt('User Memberships'); ?></span> <i id='user-tooltip' class="fa fa-info-circle text-primary" aria-hidden="true"></i>
                    <span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo xlt('LOADING'); ?>...</span>
                    <div id='membership_error'></div>
                    <div class="section" id='membership' style='display: none;'></div>
                </div>
                <div id='acl_edit'>
                    <div style='margin-bottom:5px'>
                        <span class="bold" ><input id='acl_show' type='checkbox'><?php echo xlt('Groups and Access Controls'); ?></span> <i id='group-tooltip' class="fa fa-info-circle text-primary" aria-hidden="true"></i>
                    </div>
                    <a class='link_submit btn btn-default btn-add' href='no_javascript' id='none_acl_returns' style='display: none;' title='<?php echo xla('Add New Group'); ?>'><?php echo xlt('Add New Group'); ?></a>
                    <a class='link_submit btn btn-default btn-cancel' href='no_javascript' id='none_acl_list' style='display: none;' title='<?php echo xla('Remove Group'); ?>'><?php echo xlt('Remove Group'); ?></a>
                    <span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo xlt('LOADING'); ?>...</span>
                    <div id='acl_error'></div>
                    <div id='div_acl_add_form'  style='display: none;'>
                        <form action="no_javascript" class="section clearfix" id="acl_add_form" method="post" name="acl_add_form">
                            <span class='bold'><?php echo xlt('New Group Information'); ?></span>  <i id='new-group-tooltip' class="fa fa-info-circle text-primary" aria-hidden="true"></i>
                            <span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo xlt('LOADING'); ?>...</span>
                            <div class='col-xs-12'>
                                <div class='row'>
                                    <div class='col-xs-4'>
                                        <label class="control-label" for="title_field"><?php echo xlt('Title'); ?>:</label>
                                        <input id="title_field" type="text" class="form-control">
                                    </div>
                                    <div class='col-xs-6'>
                                        <br><span class="alert" id="title_error"></span>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-xs-4'>
                                        <label class="control-label" for="id_field"><?php echo xlt('Identifier(one word)'); ?>:</label>
                                        <input id="id_field" type="text" class="form-control">
                                    </div>
                                    <div class='col-xs-6'>
                                       <br><span class="alert" id="identifier_error"></span>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-xs-4'>
                                        <label class="control-label" for="return_field"><?php echo xlt('Return Value'); ?>:</label>
                                        <select id="return_field" class="form-control">
                                        </select>
                                    </div>
                                    <div class='col-xs-6'>
                                        <br><span class="alert" id="return_error"></span>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-xs-4'>
                                        <label class="control-label" for="desc_field"><?php echo xlt('Description'); ?>:</label>
                                        <input id="desc_field" type="text" class="form-control">
                                    </div>
                                    <div class='col-xs-6'>
                                        <br><span class="alert" id="description_error"></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12" style="padding:15px 18px">
                                        <button type="submit" class="button_acl_add btn btn-default" id="button_acl_add_submit" title='<?php echo xla('Add Group'); ?>'><?php echo xlt('Add Group'); ?></button>
                                        <button type="reset" class="button_acl_add btn btn-link" id="button_acl_add_clear" title='<?php echo xla('Clear'); ?>'><?php echo xlt('Clear'); ?></button>
                                        <button type="reset" class="button_acl_add btn btn-link btn-cancel oe-opt-btn-separate-left" id="button_acl_add_cancel" title='<?php echo xla('Cancel'); ?>'><?php echo xlt('Cancel'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id='div_acl_remove_form' style='display: none;'>
                        <form action="no_javascript" class="section clearfix" id="acl_remove_form" method="post" name="acl_remove_form">
                            <div style='margin-bottom:5px'>
                                <span class='bold'><?php echo xlt('Remove Group Form'); ?></span>   <i id='remove-group-tooltip' class="fa fa-info-circle text-primary" aria-hidden="true"></i>
                                <span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo xlt('LOADING'); ?>...</span>
                            </div>
                            <div class='col-xs-12'>
                                <div class='row'>
                                    <div class='col-xs-4'>
                                        <label class="control-label" for="acl_field"><?php echo xlt('Group'); ?>:</label>
                                        <select id="acl_field" class='form-control'>
                                        </select>
                                    </div>
                                    <div class='col-xs-6'>
                                        <br><span class="alert" id="aclTitle_error"></span>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-xs-12'>
                                        <br>
                                        <span class='text'><?php echo xlt('Do you really want to delete this group'); ?>?</span>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-xs-4'>
                                        <br>
                                        <input type="radio" name="acl_remove_confirm" value="yes"><span class='text'><?php echo xlt('Yes'); ?></span>
                                        <input type="radio" name="acl_remove_confirm" value="no" checked><span class='text'><?php echo xlt('No'); ?></span>
                                    </div>
                                    <div class='col-xs-6'>
                                        <br><span class="alert" id="remove_confirm_error"></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12" style="padding:15px 18px">
                                        <button type="submit" class="button_acl_remove btn btn-default" id="button_acl_remove_delete" title='<?php echo xla('Delete Group'); ?>'><?php echo xlt('Delete Group'); ?></button>
                                        <button type="reset" class="button_acl_remove btn btn-link btn-cancel oe-opt-btn-separate-left" id="button_acl_remove_cancel" title='<?php echo xla('Cancel'); ?>'><?php echo xlt('Cancel'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="section hideaway" id='acl' style='display: none;'></div>
                </div>
            </div>
        </div>
    </div><!--end of container div-->
     <div class="row">
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content oe-modal-content">
                    <div class="modal-header clearfix"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:#000000; font-size:1.5em;">×</span></button></div>
                    <div class="modal-body">
                        <iframe src="" id="targetiframe" style="height:75%; width:100%; overflow-x: hidden; border:none" allowtransparency="true"></iframe>
                    </div>
                    <div class="modal-footer" style="margin-top:0px;">
                       <button class="btn btn-link btn-cancel pull-right" data-dismiss="modal" type="button"><?php echo xlt('close'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $( document ).ready(function() {
            $('#help-href').click (function(){
                document.getElementById('targetiframe').src ='adminacl_help.php';
            })
        });
    </script>
</body>
</html>
