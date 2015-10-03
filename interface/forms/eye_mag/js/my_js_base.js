pupils/**
 * forms/eye_mag/js/my_js_base.js
 *
 * JS Functions for eye_mag form(s)
 *
 * Copyright (C) 2015 Raymond Magauran <magauran@MedFetch.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author Ray Magauran <magauran@MedFetch.com>
 * @link http://www.open-emr.org
 */

/** Undo feature
 *  RIGHT NOW THIS WORKS PER FIELD ONLY in FF. In Chrome it works great.  Not sure about IE at all.
 *  In FF, you select a field and CTRL-Z reverses/Shift-Ctrl-Z forwards value
 *  To get true Undo Redo, we will need to create two arrays, one with the command/field, prior value, next value to undo
 *  and when undone, add this to the REDO array.  When a Undo command is followed by anything other than Redo, it erases REDO array.
 **/


/**
 *  Function to add a Quick Pick selection/value to the corresponding text field.
 *  If the the field we are writing to has a default value in it, erase it, otherwise add to it.
 *  Since Default values give the field a bgcolor of rgb(245, 245, 220), we can use that.  OK for now.
 *  In the future, we can make an array of default values an see if this matches the fields current value.
 */
function fill_QP_field(PEZONE, ODOSOU, LOCATION_text, selection,fill_action) {
    if (ODOSOU > '') {
        var FIELDID =  ODOSOU  + LOCATION_text;
    } else {
        var FIELDID =  document.getElementById(PEZONE+'_'+ODOSOU).value  + LOCATION_text;
    }
    var bgcolor = $("#" +FIELDID).css("background-color");
    var prefix = document.getElementById(PEZONE+'_prefix').value;
    var Fvalue = document.getElementById(FIELDID).value;
    if (prefix > '' && prefix !='off') {prefix = prefix + " ";}
    if (prefix =='off') { prefix=''; }
    
    if (fill_action =="REPLACE") {
        $("#" +FIELDID).val(prefix +selection);
        $("#" +FIELDID).css("background-color","#F0F8FF");
    } else {
        if (($("#" +FIELDID).css("background-color")=="rgb(245, 245, 220)") || (Fvalue ==''))  {
            $("#" +FIELDID).val(prefix+selection);
            $("#" +FIELDID).css("background-color","#F0F8FF");
        } else {
            if (Fvalue >'') prefix = ", "+prefix;
            $("#" +FIELDID).val(Fvalue + prefix +selection);
            $("#" +FIELDID).css("background-color","#F0F8FF");
                //$("#" +FIELDID).css("background-color","red");
        }
    }
    submit_form(FIELDID);
}

function clear_vars() {
    document.eye_mag.var1.value = "white";
    document.eye_mag.var2.value = "white";
}

function dopopup(url) {
    top.restoreSession();
    window.open(url, 'clinical', 'width=fullscreen,height=fullscreen,resizable=1,scrollbars=1,directories=0,titlebar=0,toolbar=0,location=0,status=0,menubar=0');
}
function goto_url(url) {
    top.restoreSession();
    window.open(url);
}

function submit_form() {
    var url = "../../forms/eye_mag/save.php?mode=update&id=" + $("#form_id").val();
        //$("#UNDO_ID").val(parseInt($("#UNDO_ID").val()) + 1);
        //client side variable with all fields incremented with these new save values
    formData = $("form#eye_mag").serialize();
        // formFields.push = serializeArray;
    $("#menustate").val('0');
    $.ajax({
           type 	: 'POST',   // define the type of HTTP verb we want to use (POST for our form)
           url 		: url,      // the url where we want to POST
           data 	: formData // our data object
           }).done(function(o) {
                   if (o == 'Code 400') {
                    if (confirm('LOCKED: Do you wish to take ownership?')) {
                        var value_2 = $("#uniqueID").val();
                        var locked_by = $("#LOCKEDBY").val();
                        $("#ownership").val(locked_by);
                        $("#LOCKEDBY").val(value_2);
                        $("#warning").addClass("nodisplay");
                        $.ajax({
                               type 	: 'POST',   // define the type of HTTP verb we want to use (POST for our form)
                               url 		: url,      // the url where we want to POST
                               data     : {
                                    'ownership'     : locked_by,  //this contains the new strokes, the sketch.js foreground
                                    'LOCKEDBY'        : value_2,
                               'uniqueID'           : value_2,
                               'form_id'    : $("#form_id").val()
                                }
                               }).done(function(d) {
                                       //console.log(d);
                                       }
                                       )
                   }
                   //nice to flash a "saved" widget in menu bar if fullscreen or elsewhere if not
                   // console.log(o);
                   }});
};


/*
 * Function to save a canvas by zone
 */
function submit_canvas(zone) {
    var id_here = document.getElementById('myCanvas_'+zone);
    var dataURL = id_here.toDataURL();
    $.ajax({
           type: "POST",
           url: "../../forms/eye_mag/save.php?canvas="+zone+"&id="+$("#form_id").val(),
           data: {
           imgBase64     : dataURL,  //this contains the new strokes, the sketch.js foreground
           'zone'        : zone,
           'visit_date'  : $("#visit_date").val(),
           'encounter'   : $("#encounter").val(),
           'pid'         : $("#pid").val()
           }
           
           }).done(function(o) {
                   //            console.log(o);
                   $("#tellme").html(o);
                   });
}
/*
 *  Function to update the user's preferences
 */
function update_PREFS() {
    var url = "../../forms/eye_mag/save.php";
    var formData = {
        'AJAX_PREFS'            : "1",
        'PREFS_VA'              : $('#PREFS_VA').val(),
        'PREFS_W'               : $('#PREFS_W').val(),
        'PREFS_MR'              : $('#PREFS_MR').val(),
        'PREFS_CR'              : $('#PREFS_CR').val(),
        'PREFS_CTL'             : $('#PREFS_CTL').val(),
        'PREFS_ADDITIONAL'      : $('#PREFS_ADDITIONAL').val(),
        'PREFS_IOP'             : $('#PREFS_IOP').val(),
        'PREFS_CLINICAL'        : $('#PREFS_CLINICAL').val(),
        'PREFS_EXAM'            : $('#PREFS_EXAM').val(),
        'PREFS_CYL'             : $('#PREFS_CYL').val(),
        'PREFS_EXT_VIEW'        : $('#PREFS_EXT_VIEW').val(),
        'PREFS_ANTSEG_VIEW'     : $('#PREFS_ANTSEG_VIEW').val(),
        'PREFS_RETINA_VIEW'     : $('#PREFS_RETINA_VIEW').val(),
        'PREFS_NEURO_VIEW'      : $('#PREFS_NEURO_VIEW').val(),
        'PREFS_ACT_VIEW'        : $('#PREFS_ACT_VIEW').val(),
        'PREFS_ACT_SHOW'        : $('#PREFS_ACT_SHOW').val(),
        'PREFS_HPI_RIGHT'       : $('#PREFS_HPI_RIGHT').val(),
        'PREFS_PMH_RIGHT'       : $('#PREFS_PMH_RIGHT').val(),
        'PREFS_EXT_RIGHT'       : $('#PREFS_EXT_RIGHT').val(),
        'PREFS_ANTSEG_RIGHT'    : $('#PREFS_ANTSEG_RIGHT').val(),
        'PREFS_RETINA_RIGHT'    : $('#PREFS_RETINA_RIGHT').val(),
        'PREFS_NEURO_RIGHT'     : $('#PREFS_NEURO_RIGHT').val(),
        'PREFS_PANEL_RIGHT'     : $('#PREFS_PANEL_RIGHT').val(),
        'PREFS_IMPPLAN_RIGHT'   : $('#PREFS_IMPPLAN_DRAW').val(),
        'PREFS_KB'              : $('#PREFS_KB').val()
        
        
    };
    $.ajax({
           type 		: 'POST',
           url          : url,
           data 		: formData
           }).done(function(o) {
                   //     console.log(o);
                   $("#tellme").html(o);
                   });
}

/**
 *  Function to finalize chart - delete temp images from drawing, esign??
 */
function finalize() {
    var url = "../../forms/eye_mag/save.php?mode=update&id=" + $("#form_id").val();
    var formData = {
        'action'           : "finalize",
        'finalize'         : "1",
        'encounter'        : $('#encounter').val(),
        'pid'              : $('#pid').val(),
        'form_id'    : $("#form_id").val()

    };
    $.ajax({
           type 		: 'POST',
           url          : url,
           data 		: formData }).done(function(o) {
                                           //console.log(o);
                                           $("#tellme").html(o);
                                           });
}
function alter_issue(issue_number,issue_type,subtype) {
        // alert("alert_issue fired");
    result = '<center><iframe src="/openemr/interface/forms/eye_mag/a_issue.php?issue=' + issue_number + '&thistype=' + issue_type +  '&subtype=' + subtype + '&pid=' + $('#pid').val() +'&encounter=' + $('#encounter').val() + '&form_id='+ $('#form_id').val() +'" title="MyForm" width="435" height="320" scrolling = "yes" frameBorder = "0" ></iframe></center>';
        //show_QP();
    $("#Enter_PMH").html(result);
}

function delete_issue(issue_number,issue_type,subtype) {
    result = '<center><iframe src="/openemr/interface/forms/eye_mag/a_issue.php?issue=' + issue_number + '&thistype=' + issue_type +  '&subtype=' + subtype +'&pid=' + $('#pid').val() +'&encounter=' + $('#encounter').val() + '" title="MyForm" width="435" height="320" scrolling = "yes" frameBorder = "0" ></iframe></center>';
    show_QP();
    $("#Enter_PMH").html(result);
}

function refreshIssues() {
    var url = "../../forms/eye_mag/view.php?display=PMSFH";
    var formData = {
        'action'           : "refresh",
        'id'               : $('#id').val(),
        'encounter'        : $('#encounter').val(),
        'pid'              : $('#pid').val(),
        'refresh'          : 'PMSFH'
    };
    $.ajax({
           type 		: 'POST',
           url          : url,
           data 		: formData,
           success:(function(result) {
                    $("#QP_PMH").html(result);
                    //console.log(result);
                    })
           })
    .fail(function() { //alert("error");
          })
    .always(function() { //alert("complete");
            });
    
    return false;
}
function refresh_panel() {
        //now refresh the panel
    var url = "../../forms/eye_mag/view.php?display=PMSFH_panel";
    var formData = {
        'action'           : "refresh",
        'id'               : $('#id').val(),
        'encounter'        : $('#encounter').val(),
        'pid'              : $('#pid').val(),
        'refresh'          : 'PMSFH_panel'
    };
    $.ajax({
           type 		: 'GET',
           url          : url,
           data 		: formData,
           success:(function(result2) {
                    $("#right-panel").html(result2);
                    // console.log(result2);
                    })
           })
    .fail(function() { //alert("error");
          })
    .always(function() { //alert("complete panel_refresh");
            });
    
}

function show_right() {
    $("#HPI_1").removeClass("size50").addClass("size100");
    $("#PMH_1").removeClass("size50").addClass("size100");
    $("#EXT_1").removeClass("size50").addClass("size100");
    $("#ANTSEG_1").removeClass("size50").addClass("size100");
    $("#NEURO_1").removeClass("size50").addClass("size100");
    $("#RETINA_1").removeClass("size50").addClass("size100");
    $("#IMPPLAN_1").removeClass("size50").addClass("size100");
    $("#HPI_right").removeClass('nodisplay');
    $("#PMH_right").removeClass('nodisplay');
    $("#EXT_right").removeClass('nodisplay');
    $("#ANTSEG_right").removeClass('nodisplay');
    $("#NEURO_right").removeClass('nodisplay');
    $("#RETINA_right").removeClass('nodisplay');
    $("#PMH_1").addClass("clear_both");
    $("#ANTSEG_1").addClass("clear_both");
    $("#RETINA_1").addClass("clear_both");
    $("#NEURO_1").addClass("clear_both");
    hide_PRIORS();
}
function hide_right() {
    $("#HPI_1").removeClass("size100").addClass("size50");
    $("#PMH_1").removeClass("size100").addClass("size50");
    $("#EXT_1").removeClass("size100").addClass("size50");
    $("#ANTSEG_1").removeClass("size100").addClass("size50");
    $("#NEURO_1").removeClass("size100").addClass("size50");
    $("#RETINA_1").removeClass("size100").addClass("size50");
    $("#IMPPLAN_1").removeClass("size100").addClass("size50");
    $("#HPI_right").addClass('nodisplay');
    $("#PMH_right").addClass('nodisplay');
    $("#EXT_right").addClass('nodisplay');
    $("#ANTSEG_right").addClass('nodisplay');
    $("#NEURO_right").addClass('nodisplay');
    $("#RETINA_right").addClass('nodisplay');
    $("#PMH_1").removeClass("clear_both");
    $("#ANTSEG_1").removeClass("clear_both");
    $("#RETINA_1").removeClass("clear_both");
    $("#NEURO_1").removeClass("clear_both");
}

function show_DRAW() {
    hide_QP();
    hide_TEXT();
    hide_PRIORS();
    hide_left();
    show_right();
        //$("#LayerTechnical_sections").addClass('nodisplay');
        //$("#REFRACTION_sections").addClass('nodisplay');
    $("#HPI_right").addClass('canvas');
    $("#PMH_right").addClass('canvas');
    $("#EXT_right").addClass('canvas');
    $("#ANTSEG_right").addClass('canvas');
    $("#RETINA_right").addClass('canvas');
    $("#NEURO_right").addClass('canvas');
    $("#IMPPLAN_right").addClass('canvas');
    $(".Draw_class").removeClass('nodisplay');
    if ($("#PREFS_CLINICAL").val() !='1') {
            // we want to show text_only which are found on left half
        $("#PREFS_CLINICAL").val('1');
        $("#PREFS_EXAM").val('DRAW');
    }
}

function show_DRAW_section(zone) {
        //hide_QP();
        //hide_TEXT();
        //hide_PRIORS();
    $("#QP_"+zone).addClass('nodisplay');
    $("#"+zone+"_1").removeClass('nodisplay');
    $("#"+zone+"_left").removeClass('nodisplay');
    $("#"+zone+"_right").addClass('canvas').removeClass('nodisplay');
    $("#Draw_"+zone).addClass('canvas');
    
    $("#Draw_"+zone).removeClass('nodisplay');
    /*
     $("#"+zone+"_1").removeClass('nodisplay');
     $("#"+zone+"_right").addClass('canvas').removeClass('nodisplay');
     $("#QP_"+zone).addClass('nodisplay');
     $("#PRIORS_"+zone+"_left_text").addClass('nodisplay');
     $("#DRAW_"+zone).removeClass('nodisplay');
     */
    $("#PREFS_"+zone+"_DRAW").val(1);
    
    
}
    //shows a PRIOR visit section
function getSection(section,newValue) {
    var url = "../../forms/eye_mag/save.php?mode=retrieve";
    
    var formData = {
        'PRIORS_query'          : "1",
        'zone'                  : section,
        'id_to_show'            : newValue,
        'pid'                   : $('#pid').val(),
        'orig_id'               : $('#form_id').val()
    }
    $.ajax({
           type 		: 'POST',
           url       : url,
           data 		: formData,
           success   : function(result) {
           $("#PRIORS_" + section + "_left_text").html(result);
           }
           });
}
function show_TEXT() {
        //   alert("show_TEXT");
    $("#PMH_1").removeClass('nodisplay');
    $("#NEURO_1").removeClass('nodisplay');
    $("#IMPPLAN_1").removeClass('nodisplay');
    $(".TEXT_class").removeClass('nodisplay');
    show_left();
    hide_right(); //this hides the right half
    hide_QP();
    hide_DRAW();
    hide_PRIORS();
    if ($("#PREFS_CLINICAL").val() !='1') {
            // we want to show text_only which are found on left half
        $("#PREFS_CLINICAL").val('1');
        $("#PREFS_EXAM").val('TEXT');
    }
    
}
function show_PRIORS() {
    $("#NEURO_sections").removeClass('nodisplay');
    hide_QP();
    hide_DRAW();
    $("#EXT_right").addClass("PRIORS_color");
    show_TEXT();
    show_right();
    $("#QP_HPI").removeClass('nodisplay');//no PRIORS yet here, show QP
                                          //$("#QP_PMH").removeClass('nodisplay');//no PRIORS yet here, show QP
    $("#HPI_right").addClass('canvas');
    $("#PMH_right").addClass('canvas');
    $("#IMPPLAN_right").addClass('canvas');
    $("#EXT_right").addClass('canvas');
    $("#ANTSEG_right").addClass('canvas');
    $("#RETINA_right").addClass('canvas');
    $("#NEURO_right").addClass('canvas');
    $(".PRIORS_class").removeClass('nodisplay');
    $(document).scrollTop( $("#EXT_anchor").offset().top -50);
    if ($("#PREFS_CLINICAL").val() !='1') {
            // we want to show text_only which are found on left half
        $("#PREFS_CLINICAL").val('1');
        $("#PREFS_EXAM").val('PRIORS');
    }
}

function hide_left() {
    $("[name$='_1']").removeClass("size100").addClass("size50");
    $("#HPI_left").addClass('nodisplay');
    $("#PMH_left").addClass('nodisplay');
    $("#EXT_left").addClass('nodisplay');
    $("#ANTSEG_left").addClass('nodisplay');
    $("#RETINA_left").addClass('nodisplay');
    $("#NEURO_left").addClass('nodisplay');
    $("#IMPPLAN_left").addClass('nodisplay');
    $("[name $='_left']").addClass('nodisplay');
}
function show_left() {
    $("[name$='_1']").removeClass("size100").addClass("size50");
    $("#HPI_left").removeClass('nodisplay');
    $("#PMH_left").removeClass('nodisplay');
    $("#EXT_left").removeClass('nodisplay');
    $("#ANTSEG_left").removeClass('nodisplay');
    $("#RETINA_left").removeClass('nodisplay');
    $("#NEURO_left").removeClass('nodisplay');
    $("#IMPPLAN_left").removeClass('nodisplay');
    $("[name$='_left']").removeClass('nodisplay');
}

function show_QP() {
    hide_DRAW();
    hide_PRIORS();
    show_TEXT();
    show_right();
    show_left();
    $("#HPI_right").addClass('canvas');
    $("#PMH_right").addClass('canvas');
    $("#EXT_right").addClass('canvas');
    $("#ANTSEG_right").addClass('canvas');
    $("#RETINA_right").addClass('canvas');
    $("#NEURO_right").addClass('canvas');
    $("#IMPPLAN_right").addClass('canvas');
    $(".QP_class").removeClass('nodisplay');
    $("#PREFS_EXAM").val('QP');
}

function show_QP_section(zone) {
        //show_left();
    $("#"+zone+"_right").addClass('canvas').removeClass('nodisplay');
    $("#QP_"+zone).removeClass('nodisplay');
    $("#DRAW_"+zone).addClass('nodisplay');
    $("#"+zone+"_1").removeClass('nodisplay');
    $("#"+zone+"_left").removeClass('nodisplay');
    $("#PREFS_"+zone+"_RIGHT").val('QP');
    if (zone == "PMH") {
            //alter_issue('','','');
    }
}

function menu_select(zone,che) {
    $("#menu_"+zone).addClass('active');
    if (zone =='PREFERENCES') {
        var url = "/openemr/interface/super/edit_globals.php";
        var formData = {
            'id'               : $('#id').val(),
            'encounter'        : $('#encounter').val(),
            'pid'              : $('#pid').val(),
        };
        $.ajax({
               type 		: 'GET',
               url          : url,
               data 		: formData,
               success      : function(result) {
               // alert(result);
               $("#Layer1").addClass('nodisplay');
               $("#Layer3").addClass('nodisplay');
               $("#left_menu").addClass('nodisplay');
               $("#Layer2").removeClass('nodisplay');
               $("#Layer2").html(result);//.replace("../..","../../..");
               }
               });
        
        
    }
}
function hide_DRAW() {
    $(".Draw_class").addClass('nodisplay');
    hide_right();
    $("#LayerTechnical_sections").removeClass('nodisplay');
    $("#REFRACTION_sections").removeClass('nodisplay');
    $("#PMH_sections").removeClass('nodisplay');
    $("#HPI_right").addClass('nodisplay');
    $("#HPI_right").removeClass('canvas');
    $("#EXT_right").removeClass('canvas');
    $("#RETINA_right").removeClass('canvas');
    $("#ANTSEG_right").removeClass('canvas');
}
function hide_QP() {
    $(".QP_class").addClass('nodisplay');
    $("[name$='_right']").removeClass('canvas');
}
function hide_TEXT() {
    $(".TEXT_class").addClass('nodisplay');
}
function hide_PRIORS() {
    $("#EXT_right").removeClass("PRIORS_color");
    $("#PRIORS_EXT_left_text").addClass('nodisplay');
    $("#PRIORS_ANTSEG_left_text").addClass('nodisplay');
    $("#PRIORS_RETINA_left_text").addClass('nodisplay');
    $("#PRIORS_NEURO_left_text").addClass('nodisplay');
    $(".PRIORS_class").addClass('nodisplay');
}

function printElem(options){
    var pat = $("#pat_name").html();
    $("#wearing_title").html("<h2>Eye Prescription</h2><span style='text-align:left;font-weight:bold;'>NAME: </span><u>"+pat+"</u>");
    $("#signature_W").toggleClass('nodisplay');
    $("#simplePrint").toggleClass('nodisplay');
    $('#wearing').printElement(options);
    $("#wearing_title").html("Current Rx");
    $("#signature_W").toggleClass('nodisplay');
    $("#simplePrint").toggleClass('nodisplay');
}

shortcut.add("Meta+T",function() {
             show_TEXT();
             });
shortcut.add("Control+T",function() {
             show_TEXT();
             });
shortcut.add("Meta+P",function() {
             show_QP();
             });
shortcut.add("Meta+D",function() {
             show_DRAW;
             });
shortcut.add("Control+P",function() {
             show_QP();
             });
shortcut.add("Control+D",function() {
             show_DRAW;
             });
shortcut.add("Meta+S",function() {
             submit_form('eye_mag');
             });
shortcut.add("Meta+ZZ", function() {
             alert("This will move you back a step...");
             //undo;
             //reload the form from UNDO_ID -1
             //the form will submit to add current changes upping UNDO by one
             //we are retrieving one less.
             //gotoURL http://www.oculoplasticsllc.com/openemr/interface/patient_file/encounter/view_form.php?formname=eye_mag&id=215&pid=1&display=fullscreen&encounter=171&UNDO=
             // alert(document.URL);
             window.location ='http://www.oculoplasticsllc.com/openemr/interface/patient_file/encounter/view_form.php?formname=eye_mag&id=215&pid=1&display=fullscreen&encounter=171&&UNDO_go='+$("#UNDO_ID").val();
             
             });
shortcut.add("Control+S",function() {
             submit_form('eye_mag');
             });

shortcut.add("Meta+Shift+ZZ", function() {
             ("This will move you forward a step...");
             //redo;
             });
shortcut.add("Control+ZZ", function() {
             alert("This will move you back a step...");
             window.location ='http://www.oculoplasticsllc.com/openemr/interface/patient_file/encounter/view_form.php?formname=eye_mag&id=215&pid=1&display=fullscreen&encounter=171&&UNDO='+$("#UNDO_ID").val();
             //undo;
             });
shortcut.add("Control+Shift+Z", function() {
             alert("This will move you forward a step...");
             //redo;
             });
shortcut.add("Alt+1", function() {
             // markCalled("alt1");
             });
shortcut.add("Shift+1", function() {
             //markCalled("shift1");
             });
shortcut.add("Ctrl+Alt+1", function() {
             //markCalled("ctrlalt1");
             });
shortcut.add("Ctrl+Shift+1", function() {
             //markCalled("ctrlshift1");
             });
shortcut.add("Shift+Alt+1", function() {
             //markCalled("shiftalt1");
             });
shortcut.add("Ctrl+2", function() {
             //markCalled("ctrl2");
             });
/*shortcut.add("3", function() {
 //markCalled("just3");
 //             },{"disable_in_input":true});
 shortcut.add("Ctrl+a", function() {
 //markCalled("ctrla");
 },{"propagate":true});
 shortcut.add("",function() {
 //markCalled("just4");
 },{"keycode":52});
 */
    // plot the current graph
    //------------------------------------------------------
function plot_graph(checkedBoxes, theitems, thetrack, thedates, thevalues, trackCount){
    top.restoreSession();
    return $.ajax({ url: '/openemr/library/openflashchart/graph_track_anything.php',
                  type: 'POST',
                  data: {
                  dates:  thedates,   //$the_date_array
                  values: thevalues,  //$the_value_array
                  items:  theitems,   //$the_item_names
                  track:  thetrack,   //$titleGraph
                  thecheckboxes: checkedBoxes //$the_checked_cols
                  },
                  dataType: "json",
                  success: function(returnData){
                  // ofc will look after a variable named "ofc"
                  // inside of the flashvar
                  // However, we need to set both
                  // data and flashvars.ofc
                  data=returnData;
                  flashvars.ofc = returnData;
                  // call ofc with proper falshchart
                  swfobject.embedSWF('/openemr/library/openflashchart/open-flash-chart.swf',
                                     "graph"+trackCount, "650", "200", "9.0.0","",flashvars);
                  },
                  error: function (XMLHttpRequest, textStatus, errorThrown) {
                  alert(XMLHttpRequest.responseText);
                  //alert("XMLHttpRequest="+XMLHttpRequest.responseText+"\ntextStatus="+textStatus+"\nerrorThrown="+errorThrown);
                  }
                  
                  }); // end ajax query
}

function openImage() {
    dlgopen('/openemr/controller.php?document&retrieve&patient_id=3&document_id=10&as_file=false', '_blank', 600, 475);
}

function show_Section(section) {
        //hide everything, show the section.  For fullscreen perhaps Tablet view per section
    show_right();
        //$('#accordion').addClass('nodisplay');
    $("div[name='_sections']").style.display= "none"; //
    $('#'+section+'_sections').style.display= "block";
        //.show().appendTo('form_container');
}
function show_CC(CC_X) {
    $("[name^='CC_']").addClass('nodisplay');
    $("#CC_"+CC_X).removeClass('nodisplay');
    $("#CC_"+CC_X).index;
}

function check_CPT_92060() {
    var neuro1='';
    var neuro2 ='';
    if ($("#STEREOPSIS").val() > '') (neuro1="1");
    $(".neurosens2").each(function(index) {
                          if ($( this ).val() > '') {
                          neuro2="1";
                          }
                          });
    
    if (neuro1 && neuro2){
        $("#neurosens_code").removeClass('nodisplay');
    } else {
        $("#neurosens_code").addClass('nodisplay');
    }
    
}
function check_exam_detail() {
    var detail_reached_HPI ='0';
    var chronic_reached_HPI= '0';
    $(".count_HPI").each(function(index) {
                         // console.log( index + ": " + $( this ).val() );
                         if ($( this ).val() > '') detail_reached_HPI++;
                         
                         });
    if (detail_reached_HPI > '3') {
        $(".detail_4_elements").css("color","red");
        $(".CODE_HIGH").removeClass("nodisplay");
        $(".detailed_HPI").css("color","red");
    } else {
        $(".detail_4_elements").css("color","#C0C0C0");
    }
    $(".chronic_HPI").each(function(index) {
                           if ($( this ).val() > '') chronic_reached_HPI++;
                           });
    if (chronic_reached_HPI == '3') {
        $(".chronic_3_elements").css("color","red");
        $(".CODE_HIGH").removeClass("nodisplay");
        $(".detailed_HPI").css("color","red");
        
    } else {
        $(".chronic_3_elements").css("color","#C0C0C0");
    }
    if ((chronic_reached_HPI == '3')||(detail_reached_HPI > '3')) {
        $(".CODE_HIGH").removeClass("nodisplay");
        $(".detailed_HPI").css("color","red");
    } else {
        $(".CODE_HIGH").addClass("nodisplay");
        $(".detailed_HPI").css("color","#C0C0C0");
    }
}

function kb_EXT(field,text,field2,appendix) {
    text = text.replace(/\binf\b/g,"inferior")
    .replace(/\bsup\b/g,"superior")
    .replace(/\bnas /g,"nasal")
    .replace(/\btemp /g,"temporal")
    .replace(/\bmed\b/g,"medial")
    .replace(/\blat\b/g,"lateral")
    .replace(/\bdermato\b/g,"dermatochalasis")
    .replace(/w\/ /g,"with")
    .replace(/\blac(\s+)/g,"laceration")
    .replace(/\blacr\b/g,'lacrimal')
    .replace(/\bdcr\b/ig,"DCR")
    .replace(/\bbcc\b/ig,"BCC")
    .replace(/\bscc\b/ig,"SCC")
    .replace(/\bsebc\b/ig,"sebaceous cell carcinoma")
    .replace(/\bfh\b/ig,"forehead")
    .replace(/\bglab\b/ig,"glabellar")
    .replace(/\bcic\b/ig,"cicatricial")
    .replace(/\bentrop\b/i,"entropion")
    .replace(/\bectrop\b/i,"ectropion")
    .replace(/\bect\b/,"ectropion")
    .replace(/\bent\b/i,"entropion")
    .replace(/\btr\b/ig,"trace");
    if (field == 'RB' || field == 'RBROW')  field2 = "RBROW";
    if (field == 'LB' || field == 'LBROW')  field2 = "LBROW";
    if (field == 'RUL') field2 = "RUL";
    if (field == 'LUL') field2 = "LUL";
    if (field == 'RLL') field2 = "RLL";
    if (field == 'LLL') field2 = "LLL";
    if (field == 'RMC' || field == 'RMCT') field2 = "RMCT";
    if (field == 'LMC' || field == 'LMCT') field2 = "LMCT";
    if (field == 'RAD') field2 = "RADNEXA";
    if (field == 'LAD') field2 = "LADNEXA";
    if (field == 'RLF') field2 = "RLF";
    if (field == 'LLF') field2 = "LLF";
    if (field == 'RMRD') field2 = "RMRD";
    if (field == 'LMRD') field2 = "LMRD";
    if (field == 'RVF') field2 = "RVFISSURE";
    if (field == 'LVF') field2 = "LVFISSURE";
    if (field == 'RCAR') field2 = "RCAROTID";
    if (field == 'LCAR') field2 = "LCAROTID";
    if (field == 'RTA') field2 = "RTEMPART";
    if (field == 'LTA') field2 = "LTEMPART";
    if (field == 'RCN5') field2 = "RCNV";
    if (field == 'LCN5') field2 = "LCNVI";
    if (field == 'RCN7') field2 = "RCMVII";
    if (field == 'LCN7') field2 = "LCNVII";
    if (field == 'RCNV') field2 = "RCNV";
    if (field == 'LCNV') field2 = "LCNV";
    if (field == 'RCNVII') field2 = "RCNVII";
    if (field == 'LCNVII') field2 = "LCNVII";
    if (field == 'RH') field2 = "ODHERTEL";
    if (field == 'LH') field2 = "OLHERTEL";
    if (field == 'LHERT') field2 = "HERTELBASE";
    if ((field == 'EXTCOM')||(field =='EXT_COMMENTS')) field2 = 'EXT_COMMENTS';
    
    if (field == 'HERT') {
        $('#ODHERTEL').val(text.match(/(\d{2})-(\d{1,3})-(\d{2})/)[1]).css("background-color","#F0F8FF");
        $('#OSHERTEL').val(text.match(/(\d{2})-(\d{1,3})-(\d{2})/)[3]).css("background-color","#F0F8FF");
        $('#HERTELBASE').val(text.match(/(\d{2})-(\d{1,3})-(\d{2})/)[2]).css("background-color","#F0F8FF");                                            }
    if ((field == 'BLF')||(field == 'LF')) {
        field = "RLF";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LLF";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#RLF').css("background-color","#F0F8FF");
        $('#LLF').css("background-color","#F0F8FF");
    } else if (field == 'BMRD') {
        field = "RMRD";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LMRD";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#RMRD').css("background-color","#F0F8FF");
        $('#LMRD').css("background-color","#F0F8FF");
    } else if (field == 'BVF') {
        field = "RVFISSURE";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LVFISSURE";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#RVFISSURE').css("background-color","#F0F8FF");
        $('#LVFISSURE').css("background-color","#F0F8FF");
    } else if ((field == 'BCAR')||(field == 'CAR')) {
        field = "RCAROTID";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LCAROTID";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#RCAROTID').css("background-color","#F0F8FF");
        $('#LCAROTID').css("background-color","#F0F8FF");
    } else if ((field == 'BTA')||(field == 'TA')) {
        field = "RTEMPART";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LTEMPART";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#RTEMPART').css("background-color","#F0F8FF");
        $('#LTEMPART').css("background-color","#F0F8FF");
    } else if ((field == 'BCNV') || (field == 'BCN5')||(field == 'CNV')||(field=='CN5')) {
        field = "RCNV";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LCNV";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#RCNV').css("background-color","#F0F8FF");
        $('#LCNV').css("background-color","#F0F8FF");
    } else if ((field == 'BCNVII') || (field == 'BCNVII')||(field == 'CNVII')||(field == 'CN7')) {
        field = "RCNV";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LCNV";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#RCNV').css("background-color","#F0F8FF");
        $('#LCNV').css("background-color","#F0F8FF");
    } else if ((field == 'BLL')||(field=='LL')) {
        field = "RLL";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LLL";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#RLL').css("background-color","#F0F8FF");
        $('#LLL').css("background-color","#F0F8FF");
    } else if ((field == '4XL')||(field == 'Lx4')||(field=='LL')) {
        field = "RLL";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "RUL";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LUL";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LLL";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#RLL').css("background-color","#F0F8FF");
        $('#LLL').css("background-color","#F0F8FF");
        $('#RUL').css("background-color","#F0F8FF");
        $('#LUL').css("background-color","#F0F8FF");
    } else if ((field == 'BUL')||(field=='UL')) {
        field = "RUL";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LUL";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#RUL').css("background-color","#F0F8FF");
        $('#LUL').css("background-color","#F0F8FF");
    } else if (field == 'BAD') {
        field = "RAD";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LAD";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#RAD').css("background-color","#F0F8FF");
        $('#LAD').css("background-color","#F0F8FF");
    } else if ((field == 'FH')||(field == "BB")) {
        field = "RBROW";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "LBROW";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#RBROW').val(text).css("background-color","#F0F8FF");
        $('#LBROW').val(text).css("background-color","#F0F8FF");
    } else {
        (appendix == ".a") ? ($('#'+field2).val($('#'+field2).val() +", "+text)) : $('#'+field2).val(text);
        $('#'+field2).css("background-color","#F0F8FF");
    }
    return field2;
}
function kb_ANTSEG(field,text,field2,appendix) {
    text = text.replace(/\binf\b/g,"inferior")
    .replace(/\bsup\b/g,"superior")
    .replace(/\bnas\b/g,"nasal")
    .replace(/\btemp\b/g,"temporal")
    .replace(/\bmed\b/g,"medial")
    .replace(/\blat\b/g,"lateral")
    .replace(/\bgut\b/g,"guttata")
    .replace(/\bw\/\b/g,"with")
    .replace(/\btr\b/ig,"trace")
    .replace(/\blac\b/g,"laceration")
    .replace(/\bpter\b/g,'pterygium')
    .replace(/\bpig\b/g,'pigmented')
    .replace(/\binj\b/ig,"injection")
    .replace(/\bfc\b/ig,"flare/cell")
    .replace(/\bks\b/ig,"kruckenberg spindle")
    .replace(/\bsebc\b/ig,"sebaceous cell carcinoma")
    .replace(/\bspk\b/ig,"SPK")
    .replace(/\bpek\b/ig,"PEK")
    .replace(/\bstr\b/ig,"stromal")
    .replace(/\bendo?\b/ig,"endothelial")
    .replace(/\brec\b/ig,"recession")
    .replace(/\b1 o\b/ig,"1 o'clock")
    .replace(/\b2 o\b/ig,"2 o'clock")
    .replace(/\b3 o\b/ig,"3 o'clock")
    .replace(/\b4 o\b/ig,"4 o'clock")
    .replace(/\b5 o\b/ig,"5 o'clock")
    .replace(/\b6 o\b/ig,"6 o'clock")
    .replace(/\b7 o\b/ig,"7 o'clock")
    .replace(/\b8 o\b/ig,"8 o'clock")
    .replace(/\b9 o\b/ig,"9 o'clock")
    .replace(/\b10 o\b/ig,"10 o'clock")
    .replace(/\b11 o\b/ig,"11 o'clock")
    .replace(/\b12 o\b/ig,"12 o'clock")
    .replace(/\blimb\b/i,"limbus")
    .replace(/\btl\b/i,"tear lake");
    if (field == 'RC')      field2 = "ODCONJ";
    if (field == 'LC')      field2 = "OSCONJ";
    if (field == 'RK')      field2 = "ODCORNEA";
    if (field == 'LK')      field2 = "OSCORNEA";
    if (field == 'RAC')     field2 = "ODAC";
    if (field == 'LAC')     field2 = "OSAC";
    if (field == 'RL')      field2 = "ODLENS";
    if (field == 'LL')      field2 = "OSLENS";
    if (field == 'RI')      field2 = "ODIRIS";
    if (field == 'LI')      field2 = "OSIRIS";
    if (field == 'RG')      field2 = "OSGONIO";
    if (field == 'LG')      field2 = "OSGONIO";
    if (field == 'RPACH')   field2 = "OSKTHICKNESS";
    if (field == 'LPACH')   field2 = "OSKTHICKNESS";
    if (field == 'RSCH1')   field2 = "OSSCHIRMER1";
    if (field == 'LSCH1')   field2 = "OSSCHIRMER1";
    if (field == 'RSCH2')   field2 = "OSSCHIRMER2";
    if (field == 'LSCH2')   field2 = "OSSCHIRMER2";
    if (field == 'RTBUT')   field2 = "ODTBUT";
    if (field == 'LTBUT')   field2 = "OSTBUT";
    if ((field == 'ASCOM')||(field =='ANTSEG_COMMENTS')) field2 = 'ANTSEG_COMMENTS';
    if ((field == 'BC')||(field=='C')) {
        field = "ODCONJ";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSCONJ";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ORCONJ').css("background-color","#F0F8FF");
        $('#OSCONJ').css("background-color","#F0F8FF");
    } else if ((field == 'BK')||(field=='K')) {
        field = "ODCORNEA";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSCORNEA";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODCORNEA').css("background-color","#F0F8FF");
        $('#OSCORNEA').css("background-color","#F0F8FF");
    } else if ((field == 'BAC')||(field=='AC')) {
        field = "ODAC";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSAC";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODAC').css("background-color","#F0F8FF");
        $('#OSAC').css("background-color","#F0F8FF");
    } else if ((field == 'BL')||(field=='L')) {
        field = "ODLENS";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSLENS";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODLENS').css("background-color","#F0F8FF");
        $('#OSLENS').css("background-color","#F0F8FF");
    } else if ((field == 'BI')||(field=='I')) {
        field = "ODIRIS";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSIRIS";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODIRIS').css("background-color","#F0F8FF");
        $('#OSIRIS').css("background-color","#F0F8FF");
    } else if ((field == 'BPACH')||(field=='PACH')) {
        field = "ODKTHICKNESS";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSKTHICKNESS";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODKTHICKNESS').css("background-color","#F0F8FF");
        $('#OSKTHICKNESS').css("background-color","#F0F8FF");
    } else if ((field == 'BG')||(field=='G')) {
        field = "ODGONIO";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSGONIO";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODGONIO').css("background-color","#F0F8FF");
        $('#OSGONIO').css("background-color","#F0F8FF");
    } else if ((field == 'BTBUT')||(field=='TBUT')) {
        field = "ODTBUT";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSTBUT";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODTBUT').css("background-color","#F0F8FF");
        $('#OSTBUT').css("background-color","#F0F8FF");
    } else {
        (appendix == ".a") ? ($('#'+field2).val($('#'+field2).val() +", "+text)) : $('#'+field2).val(text);
        $('#'+field2).css("background-color","#F0F8FF");
    }
    return field2;

}
function kb_RETINA(field,text,field2,appendix) {
    text = text.replace(/\binf\b/g,"inferior")
    .replace(/\bsup\b/g,"superior")
    .replace(/\bnas\b/g,"nasal")
    .replace(/\btemp\b/g,"temporal")
    .replace(/\bmed\b/g,"medial")
    .replace(/\blat\b/g,"lateral")
    .replace(/\bcsme\b/ig,"CSME")
    .replace(/\bw\/\b/g,"with")
    .replace(/\bbdr(\b)/ig,"BDR")
    .replace(/\bppdr\b/g,'PPDR')
    .replace(/\bht\b/ig,"horseshoe tear")
    .replace(/(\b)ab(\b)/ig,"air bubble")
    .replace(/\bc3f8\b/ig,"C3F8")
    .replace(/\bma\b/ig,"macroaneurysm")
    .replace(/\btr\b/ig,"trace")
    .replace(/\bmias\b/ig,"microaneurysm")
    .replace(/\bped\b/ig,"PED")
    .replace(/\b1 o\b/ig," 1 o'clock")
    .replace(/\b2 o\b/ig,"2 o'clock")
    .replace(/\b3 o\b/ig," 3 o'clock")
    .replace(/\b4 o\b/ig," 4 o'clock")
    .replace(/\b5 o\b/ig," 5 o'clock")
    .replace(/\b6 o\b/ig," 6 o'clock")
    .replace(/\b7 o\b/ig," 7 o'clock")
    .replace(/\b8 o\b/ig," 8 o'clock")
    .replace(/\b9 o\b/ig," 9 o'clock")
    .replace(/\b10 o\b/ig," 10 o'clock")
    .replace(/\b11 o\b/ig," 11 o'clock")
    .replace(/(\b)12 o(\b)/ig," 12 o'clock")
    .replace(/\bmac\b/i,"macula")
    .replace(/\bfov\b/i,"fovea")
    .replace(/\bvh\b/i,"vitreous hemorrhage");
    if (field == 'RD' || field =='ODDISC')      field2 = "ODDISC";
    if (field == 'LD' || field =='OSDISC')      field2 = "OSDISC";
    if (field == 'RCUP' || field =='ODCUP')     field2 = "ODCUP";
    if (field == 'LCUP' || field =='OSCUP')     field2 = "OSCUP";
    if (field == 'RMAC')    field2 = "ODMACULA";
    if (field == 'LMAC')    field2 = "OSMACULA";
    if (field == 'RV')      field2 = "ODVESSELS";
    if (field == 'LV')      field2 = "OSVESSELS";
    if (field == 'RP')      field2 = "ODPERIPH";
    if (field == 'LP')      field2 = "OSPERIPH";
    if (field == 'RCMT')    field2 = "ODCMT";
    if (field == 'LCMT')    field2 = "OSCMT";
    if (field == 'ODCMT')   field2 = "ODCMT";
    if (field == 'OSCMT')   field2 = "OSCMT";
    if ((field == 'RCOM')||(field =='RETINA_COMMENTS')) field2 = 'RETINA_COMMENTS';
    
    if (field == 'BD') {
        field = "ODDISC";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSDISC";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODDISC').css("background-color","#F0F8FF");
        $('#OSDISC').css("background-color","#F0F8FF");
    } else if ((field == 'BC')||(field == 'C')) {
        field = "ODCUP";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSCUP";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODCUP').css("background-color","#F0F8FF");
        $('#OSCUP').css("background-color","#F0F8FF");
    } else if ((field == 'BMAC')||(field == 'MAC')||(field=='BM')) {
        field = "ODMACULA";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSMACULA";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODMACULA').css("background-color","#F0F8FF");
        $('#OSMACULA').css("background-color","#F0F8FF");
    } else if ((field == 'BV')||(field == 'V')) {
        field = "ODVESSELS";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSVESSELS";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODVESSELS').css("background-color","#F0F8FF");
        $('#OSVESSELS').css("background-color","#F0F8FF");
    } else if ((field == 'BP')||(field == 'P')) {
        field = "ODPERIPH";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSPERIPH";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODPERIPH').css("background-color","#F0F8FF");
        $('#OSPERIPH').css("background-color","#F0F8FF");
    } else if ((field == 'BCMT')||(field == 'CMT')) {
        field = "ODCMT";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        field = "OSCMT";
        (appendix == ".a") ? ($('#'+field).val($('#'+field).val() +', '+text)) : $('#'+field).val(text);
        $('#ODCMT').css("background-color","#F0F8FF");
        $('#OSCMT').css("background-color","#F0F8FF");
    } else {
        (appendix == ".a") ? ($('#'+field2).val($('#'+field2).val() +", "+text)) : $('#'+field2).val(text);
        $('#'+field2).css("background-color","#F0F8FF");
    }
    return field2;
}
function kb_NEURO(field,text,field2,appendix) {
    if (field.match(/^(.CDIST|.CNEAR)/i)) {
        field = field.toUpperCase();
        if (field == 'SCDIST') $('#NEURO_ACT_zone').val('SCDIST').trigger('change');
        if (field == 'CCDIST') $('#NEURO_ACT_zone').val('CCDIST').trigger('change');
        if (field == 'SCNEAR') $('#NEURO_ACT_zone').val('SCNEAR').trigger('change');
        if (field == 'CCNEAR') $('#NEURO_ACT_zone').val('CCNEAR').trigger('change');
        zone = $("#NEURO_ACT_zone").val();
        return zone;
    }
    //if not strabismus, then color or comments etc
    if ((field == 'RCOL')||(field =='RCOLOR')) field2 = 'ODCOLOR';
    if ((field == 'LCOL')||(field =='LCOLOR')) field2 = 'OSCOLOR';
    if ((field == 'RCOIN')||(field =='RCOINS')) field2 = 'ODCOINS';
    if ((field == 'LCOIN')||(field =='LCOINS')) field2 = 'OSCOINS';
    if (field == 'RRED') field2 = 'ODREDDESAT';
    if (field == 'LRED') field2 = 'OSREDDESAT';
    if (field == 'RNPC') field2 = 'ODNPC';
    if (field == 'LNPC') field2 = 'OSNPC';
    if (field == 'RNPA') field2 = 'ODNPA';
    if (field == 'LNPA') field2 = 'OSNPA';
    if (field == 'STEREO') field2 = 'STERIOPSIS';
    if (field == 'VERTFUS') field2 = 'VERTFUSAMPS';
    if (field == 'CAD') field2 = 'CACCDIST';
    if (field == 'CAN') field2 = 'CACCNEAR';
    if (field == 'DAD') field2 = 'DACCDIST';
    if (field == 'DAN') field2 = 'DACCNEAR';
    if ((field == 'NCOM')||(field =='NEURO_COMMENTS')) field2 = 'EXT_COMMENTS';
    
    if (field.match(/^(\d{1,2})$/)) {
        var data = text.match(/(\d{0,2}||ortho)(.*)/i);
        var PD = data[1];
        if (PD >'') PD = PD + ' ';
        var strab = data[2].toUpperCase().replace (/I(.)/g,"$1(T)").replace(/\s*(\d)/,'\n$1');
        $('#ACT'+field+zone).val(PD+strab);
        $('#ACT'+field+zone).css("background-color","#F0F8FF");
    }
    return field2;
}

$(document).ready(function() {
                 
                  if ($("#PREFS_KB").val() !='1') {
                    $(".kb").addClass('nodisplay');
                  }
                  $("[id$='_kb']").click(function() {
                                         $('.kb').toggleClass('nodisplay');
                                         if ($("#PREFS_KB").value > 0) {
                                         $("#PREFS_KB").val('0');
                                         } else {
                                         $("#PREFS_KB").val('1');
                                         }
                                         update_PREFS();
                                         });
                  

                  $('.ke').mouseover(function() {
                                     $(this).toggleClass('yellow');
                                     });
                  $('.ke').mouseout(function() {
                                    
                                    $(this).toggleClass('yellow');
                                    });
                  $("[id$='_keyboard'],[id$='_keyboard_left']").on('keydown', function(e) {
                                                                   // This will take the keyboard textarea values and store them in the correct zone
                                                                   // triggered by the enter key
                                                                   // fieldnumbers can be entered individually, followed by "RETURN/ENTER" each time OR
                                                                   // separated by semicolons, then hit enter.  Semi-colons speed it up.
                                                                    //if its not the enter or TAB key, ignore it.
                                                                   if (e.which == 13|| e.keyCode == 13||e.which == 9|| e.keyCode == 9) {
                                                                   e.preventDefault();
                                                                   var exam = this.id.match(/(.*)_keyboard/)[1];
                                                                   var data_all = $(this).val();
                                                                   var data_seg = data_all.replace(/^[\s]*/,'').match(/([^;]*)/g);
                                                                   var field2 ='';
                                                                   var appendix =".a";
                                                                   var zone;
                                                                   
                                                                   switch (exam) {
                                                                   
                                                                   case 'ALL':
                                                                   for (index=0; index < data_seg.length; ++index) {
                                                                       if (data_seg[index] =='') continue;
                                                                   if ((index =='0') && (data_seg[index].match(/^D($|;)/i))) {
                                                                           $("#EXT_defaults").trigger("click");
                                                                           $("#ANTSEG_defaults").trigger("click");
                                                                           $("#RETINA_defaults").trigger("click");
                                                                           $("#NEURO_defaults").trigger("click");
                                                                           continue;
                                                                       }
                                                                       data_seg[index] = data_seg[index].replace(/^[\s]*/,'');
                                                                       var data = data_seg[index].match(/^(\w*)\.?(.*)/);
                                                                       (data[2].match(/\.a$/))?(data[2] = data[2].replace(/\.a$/,'')):(appendix = "nope");
                                                                   
                                                                       var field = data[1].toUpperCase();
                                                                       var text = data[2];
                                                                        field2='';
                                                                       field2 = kb_EXT(field,text,field2,appendix);
                                                                       if (field2 =='') {
                                                                        field2 = kb_ANTSEG(field,text,field2,appendix);
                                                                       }
                                                                       if (field2 =='') {
                                                                        field2 = kb_RETINA(field,text,field2,appendix);
                                                                       }
                                                                       if (field2 =='') {
                                                                        zone = kb_NEURO(field,text,field2,appendix);
                                                                       }
                                                                   }
                                                                   submit_form();
                                                                   $(this).val('');
                                                                   break;
                                                                   
                                                                   case 'EXT':
                                                                   for (index=0; index < data_seg.length; ++index) {
                                                                       if (data_seg[index] =='') continue;
                                                                       if ((index =='0') && (data_seg[index].match(/^D/i))) {
                                                                            $("#EXT_defaults").trigger("click");
                                                                            continue;
                                                                       }
                                                                       var data = data_seg[index].match(/^(\w*)\.(.*)/);
                                                                       (data[2].match(/\.a$/))?(data[2] = data[2].replace(/\.a$/,'')):(appendix = "nope");
                                                                       var field = data[1].toUpperCase();
                                                                       var text = data[2];
                                                                       field2 = kb_EXT(field,text,field2,appendix);
                                                                   }
                                                                   submit_form();
                                                                   $(this).val('');
                                                                   break;
                                                                   
                                                                   case 'ANTSEG':
                                                                   for (index=0; index < data_seg.length; ++index) {
                                                                   if (data_seg[index] =='') continue;
                                                                   if ((index =='0') && (data_seg[index].match(/^D/i))) {
                                                                   $("#ANTSEG_defaults").trigger("click");
                                                                   continue;
                                                                   }
                                                                   var data = data_seg[index].match(/^(\w*)\.(.*)/);
                                                                   (data[1].match(/\.a$/))?(data[1] = data[1].replace(/\.a$/,'')):(appendix = "nope");
                                                                   var field = data[1].toUpperCase();
                                                                   var text = data[2];
                                                                   field2 = kb_ANTSEG(field,text,field2,appendix);
                                                                   }
                                                                   submit_form();
                                                                   $(this).val('');
                                                                   break;
                                                                   
                                                                   
                                                                   case 'RETINA':
                                                                   for (index=0; index < data_seg.length; ++index) {
                                                                       if (data_seg[index] =='') continue;
                                                                       if ((index =='0') && (data_seg[index].match(/^D/i))) {
                                                                           $("#RETINA_defaults").trigger("click");
                                                                           continue;
                                                                       }
                                                                       var data = data_seg[index].match(/^(\w*)\.(.*)/);
                                                                       (data[2].match(/\.a$/))?(data[2] = data[2].replace(/\.a$/,'')):(appendix = "nope");
                                                                       var field = data[1].toUpperCase();
                                                                       var text = data[2];
                                                                       field2 = kb_ANTSEG(field,text,field2,appendix);
                                                                   }
                                                                   submit_form();
                                                                   $(this).val('');
                                                                   break;
                                                                   
                                                                   // Formatting rules for the NEURO/ACT textarea:
                                                                   // TEXTAREA DATA:  fieldnumberA.#PD(i?)(\w)+ ; fieldnumberB.#PD(i?)(\w)+
                                                                   // convert auto to caps
                                                                   // save some typing steps to speed it up: iX=X(T) ie=E(T) rh=RHT lih=LH(T)

                                                                   case 'NEURO':
                                                                   var zone = $("#NEURO_ACT_zone").val();
                                                                   for (index=0; index < data_seg.length; ++index) {
                                                                       if (data_seg[index] =='') continue;
                                                                       if ((index =='0') && (data_seg[index].match(/^D/i))) {
                                                                           //there are currently no NEURO defaults though...
                                                                           $("#NEURO_defaults").trigger("click");
                                                                           continue;
                                                                       }
                                                                       var data = data_seg[index].match(/^(\w*)\.(.*)/);
                                                                       (data[2].match(/\.a$/))?(data[2] = data[2].replace(/\.a$/,'')):(appendix = "nope");
                                                                       var field = data[1].toUpperCase();
                                                                       var text = data[2];
                                                                       zone = kb_NEURO(field,text,field2,appendix);
                                                                   }
                                                                   submit_form();
                                                                   $(this).val('');
                                                                   break;
                                                                   
                                                                
                                                                   } //end switch
                                                                   } //end if enter/return pressed
                                                                   });
                  $("[id^='sketch_tools_']").click(function() {
                                                   var zone = this.id.match(/sketch_tools_(.*)/)[1];
                                                   $("[id^='sketch_tools_"+zone+"']").css("height","30px");
                                                   $(this).css("height","50px");
                                                   
                                                   // $("#selColor_"+zone).value = $(this).val();
                                                   });
                  $("[id^='sketch_sizes_']").click(function() {
                                                   var zone = this.id.match(/sketch_sizes_(.*)/)[1];
                                                   $("[id^='sketch_sizes_"+zone+"']").css("background","").css("border-bottom","");
                                                   // $(this).css("background-color","white");
                                                   $(this).css("border-bottom","2pt solid black");
                                                   //$("#selWidth_"+zone).value = $(this).val();
                                                   
                                                   });
                  
                  //$("#tab1_CC").trigger("click");
                  alter_issue('','',''); // on ready displays the PMH engine.
                  //  Here we get CC1 to show
                  $(".tab_content").addClass('nodisplay');
                  $("#tab1_CC_text").removeClass('nodisplay');
                  $("#tab1_HPI_text").removeClass('nodisplay');
                  $("[id$='_CC'],[id$='_HPI_tab']").click(function() {
                                                          //  First remove class "active" from currently active tabs
                                                          $("[id$='_CC']").removeClass('active');
                                                          $("[id$='_HPI_tab']").removeClass('active');
                                                          //  Hide all tab content
                                                          $(".tab_content").addClass('nodisplay');
                                                          
                                                          //  Here we get the href value of the selected tab
                                                          var selected_tab = $(this).find("a").attr("href");
                                                          
                                                          //  Now add class "active" to the selected/clicked tab and content
                                                          $(selected_tab+"_CC").addClass('active');
                                                          $(selected_tab+"_CC_text").removeClass('nodisplay');
                                                          $(selected_tab+"_HPI_tab").addClass('active');
                                                          $(selected_tab+"_HPI_text").removeClass('nodisplay');
                                                          
                                                          //  At the end, we add return false so that the click on the link is not executed
                                                          return false;
                                                          });
                  $("[id^='CONSTRUCTION_']").toggleClass('nodisplay');
                  $("input,textarea,text").css("background-color","#FFF8DC");
                  $("#IOPTIME").css("background-color","#FFFFFF");
                  $("#refraction_width").css("width","8.5in");
                  $(".Draw_class").addClass('nodisplay');
                  $(".PRIORS_class").addClass('nodisplay');
                  hide_DRAW();
                  hide_right();
                  $(window).resize(function() {
                                   if (window.innerWidth >'900') {
                                   $("#refraction_width").css("width","900px");
                                   $("#LayerVision2").css("padding","4px");
                                   }
                                   if (window.innerWidth >'1200') {
                                   $("#refraction_width").css("width","1200px");
                                   $("#LayerVision2").css("padding","4px");
                                   }
                                   if (window.innerWidth >'1900') {
                                   //$("#refraction_width").css("width","16.8in");
                                   //$("#LayerVision2").css("padding","4px");
                                   }
                                   
                                   });
                  $(window).resize();
                  
                  var hash_tag = '<i class="fa fa-minus"></i>';
                  var index;
                  // display any stored MOTILITY values
                  $("#MOTILITY_RS").value = parseInt($("#MOTILITY_RS").val());
                  if ($("#MOTILITY_RS").val() > '0') {
                  $("#MOTILITYNORMAL").removeAttr('checked');
                  for (index =1; index <= ($("#MOTILITY_RS").val()); ++index) {
                  $("#MOTILITY_RS_"+index).html(hash_tag);
                  }
                  }
                  $("#MOTILITY_RI").value = parseInt($("#MOTILITY_RI").val());
                  if ($("#MOTILITY_RI").val() > '0') {
                  $("#MOTILITYNORMAL").removeAttr('checked');
                  for (index =1; index <= ($("#MOTILITY_RI").val()); ++index) {
                  $("#MOTILITY_RI_"+index).html(hash_tag);
                  }
                  }
                  $("#MOTILITY_LS").value = parseInt($("#MOTILITY_LS").val());
                  if ($("#MOTILITY_LS").val() > '0') {
                  $("#MOTILITYNORMAL").removeAttr('checked');
                  for (index =1; index <= ($("#MOTILITY_LS").val()); ++index) {
                  $("#MOTILITY_LS_"+index).html(hash_tag);
                  }
                  }
                  $("#MOTILITY_LI").value = parseInt($("#MOTILITY_LI").val());
                  if ($("#MOTILITY_LI").val() > '0') {
                  $("#MOTILITYNORMAL").removeAttr('checked');
                  for (index =1; index <= ($("#MOTILITY_LI").val()); ++index) {
                  $("#MOTILITY_LI_"+index).html(hash_tag);
                  }
                  }
                  
                  var hash_tag = '<i class="fa fa-minus rotate-left"></i>';
                  $("#MOTILITY_LR").value = parseInt($("#MOTILITY_LR").val());
                  if ($("#MOTILITY_LR").val() > '0') {
                  $("#MOTILITYNORMAL").removeAttr('checked');
                  for (index =1; index <= ($("#MOTILITY_LR").val()); ++index) {
                  $("#MOTILITY_LR_"+index).html(hash_tag);
                  }
                  }
                  $("#MOTILITY_LL").value = parseInt($("#MOTILITY_LL").val());
                  if ($("#MOTILITY_LL").val() > '0') {
                  $("#MOTILITYNORMAL").removeAttr('checked');
                  for (index =1; index <= ($("#MOTILITY_LL").val()); ++index) {
                  $("#MOTILITY_LL_"+index).html(hash_tag);
                  }
                  }
                  $("#MOTILITY_RR").value = parseInt($("#MOTILITY_RR").val());
                  if ($("#MOTILITY_RR").val() > '0') {
                  $("#MOTILITYNORMAL").removeAttr('checked');
                  for (index =1; index <= ($("#MOTILITY_RR").val()); ++index) {
                  $("#MOTILITY_RR_"+index).html(hash_tag);
                  }
                  }
                  $("#MOTILITY_RL").value = parseInt($("#MOTILITY_RL").val());
                  if ($("#MOTILITY_RL").val() > '0') {
                  $("#MOTILITYNORMAL").removeAttr('checked');
                  for (index =1; index <= ($("#MOTILITY_RL").val()); ++index) {
                  $("#MOTILITY_RL_"+index).html(hash_tag);
                  }
                  }
                  
                  // AUTO- CODING FEATURES
                  // onload determine if detailed HPI hit
                  check_CPT_92060();
                  check_exam_detail();
                  $(".chronic_HPI,.count_HPI").blur(function() {
                                                    check_exam_detail();
                                                    });
                  
                  // Dilation status
                  //onload and onchange
                  if ($("#DIL_RISKS").is(':checked')) { ($(".DIL_RISKS").removeClass("nodisplay"));}
                  $("#DIL_RISKS").change(function(o) {
                                         ($(this).is(':checked')) ? ($(".DIL_RISKS").removeClass("nodisplay")) : ($(".DIL_RISKS").addClass("nodisplay"));
                                         });
                  $(".dil_drug").change(function(o) {
                                        if ($(this).is(':checked')) {
                                        ($(".DIL_RISKS").removeClass("nodisplay"));
                                        $("#DIL_RISKS").prop("checked","checked");
                                        
                                        }});
                  
                  //neurosens exam = stereopsis + strab||NPC||NPA||etc
                  $(".neurosens,.neurosens2").blur(function() {
                                                   var neuro1='';
                                                   var neuro2 ='';
                                                   if ($("#STEREOPSIS").val() > '') (neuro1="1");
                                                   $(".neurosens2").each(function(index) {
                                                                         if ($( this ).val() > '') {
                                                                         neuro2="1";
                                                                         }
                                                                         });
                                                   
                                                   if (neuro1 && neuro2){
                                                   $("#neurosens_code").removeClass('nodisplay');
                                                   } else {
                                                   $("#neurosens_code").addClass('nodisplay');
                                                   }
                                                   });
                  
                  // END AUTO-CODING FEATURES
                  
                  //  functions to improve flow of refraction input
                  
                  $("input[name$='PRISM']").blur(function() {
                                                 //make it all caps
                                                 var str = $(this).val();
                                                 str = str.toUpperCase();
                                                 $(this).val(str);
                                                 });
                  $("input[name$='SPH'],#WOSADD2").blur(function() {
                                               var mid = $(this).val();
                                               if (!mid.match(/\./)) {
                                               var front = mid.match(/([\+\-]?\d{0,2})(\d{2})/)[1];
                                               var back  = mid.match(/(\d{0,2})(\d{2})/)[2];
                                               if (front =='') front ='0';
                                               if (front =='-') front ='-0';
                                               mid = front + "." + back;
                                               }
                                               if (!mid.match(/^(\+|\-){1}/)) {
                                               mid = "+" + mid;
                                               }
                                               $(this).val(mid);
                                               });
                  $("input[name$='ADD']").blur(function() {
                                               var add = $(this).val();
                                               if (!add.match(/\./)) {
                                               var front = add.match(/([\+\-]?\d{0,2})(\d{2})/)[1];
                                               var back  = add.match(/(\d{0,2})(\d{2})/)[2];
                                               add = front + "." + back;
                                               }
                                               if (!add.match(/^(\+|\-){1}/)) {
                                               add = "+" + add;
                                               }
                                               $(this).val(add);
                                               submit_form();
                                               });
                  
                  $("input[name$='AXIS']").blur(function() {
                                                //hmmn.  Make this a 3 digit leading zeros number.
                                                // we are not translating text to numbers, just numbers to
                                                // a 3 digit format with leading zeroes as needed.
                                                // assume the end user KNOWS there are only numbers presented and
                                                // more than 3 digits is a mistake...
                                                // (although this may change with topographic answer)
                                                var axis = $(this).val();
                                                // if (!axis.match(/\d/)) return; How do we say this?
                                                var front = this.id.match(/(.*)AXIS$/)[1];
                                                var cyl = $("#"+front+"CYL").val();
                                                if (cyl > '') {
                                                    if (!axis.match(/\d\d\d/)) {
                                                        if (!axis.match(/\d\d/)) {
                                                            if (!axis.match(/\d/)) {
                                                                axis = '0';
                                                            }
                                                            axis = '0' + axis;
                                                        }
                                                        axis = '0' + axis;
                                                    }
                                                }
                                                //we can utilize a phoropter dial feature, we can start them at their age appropriate with/against the rule value.
                                                //requires touch screen. requires complete touch interface development. Exists in refraction lanes. Would
                                                //be nice to tie them all together.  Would require manufacturers to publish their APIs to communicate with
                                                //the devices.
                                                $(this).val(axis);
                                                submit_form('eye_mag');
                                                });
                  
                  
                  $("input[name$='CYL']").blur(function() {
                                               var mid = $(this).val();
                                               if (!mid.match(/\./)) {
                                                var front = mid.match(/([\+\-]?\d{0,2})(\d{2})/)[1];
                                                var back  = mid.match(/(\d{0,2})(\d{2})/)[2];
                                                if (front =='') front ='0';
                                                mid = front + "." + back;
                                               }
                                               
                                               //if mid is -2.5 make it -2.50
                                               if (mid.match(/\.\d$/)) {
                                               mid = mid + '0';
                                               // mid = this.val() + '0';
                                               }
                                               $(this).val(mid);
                                               if (!$('#PREFS_CYL').val()) {
                                               $('#PREFS_CYL').val('+');
                                               update_PREFS();
                                               }
                                               
                                               if (!mid.match(/^(\+|\-){1}/)) {
                                               //no +/- sign at the start of the field.
                                               //ok so there is a preference set
                                               //Since it doesn't start with + or - then give it the preference value
                                               var plusminus = $('#PREFS_CYL').val() + mid;
                                               $(this).val(plusminus);  //set this cyl value to plus or minus
                                               } else if (mid.match(/^(\+|\-){1}/)) {
                                               pref = mid.match(/^(\+|\-){1}/)[0];
                                               //so they used a value + or - at the start of the field.
                                               //The only reason to work on this is to change to cylinder preference
                                               if ($('#PREFS_CYL').val() != pref){
                                               //and that is what they are doing here
                                               $('#PREFS_CYL').val(pref);
                                               update_PREFS();
                                               }
                                               }
                                               submit_form($(this));
                                               });
                  
                  $('#WODADD1').blur(function() {
                                     var mid = $('#WODADD1').val();
                                     if (!mid.match(/\./)) {
                                     var front = mid.match(/(\d{0,2})(\d{2})/)[1];
                                     var back = mid.match(/(\d{0,2})(\d{2})/)[2];
                                     mid = front + "." + back;
                                     }
                                     if (!mid.match(/^(\+)/)) {
                                     mid = "+" + mid;
                                     }
                                     $('#WODADD1').val(mid);
                                     $('#WOSADD1').val(mid);
                                     submit_form($('#WOSADD1'));
                                     });
                  $('#WODADD2').blur(function() {
                                     var near = $('#WODADD2').val();
                                     if (!near.match(/\./)) {
                                     var front = near.match(/(\d{0,2})(\d{2})/)[1];
                                     var back = near.match(/(\d{0,2})(\d{2})/)[2];
                                     near = front + "." + back;
                                     }
                                     if (!near.match(/^(\+)/)) {
                                     near= "+" + near;
                                     }
                                     $('#WODADD2').val(near);
                                     $('#WOSADD2').val(near);
                                     submit_form($('#WOSADD2'));
                                     });
                  
                  $("#simplePrint").click(function() {
                                          printElem({
                                                    pageTitle: 'Spectacle_Rx.html',
                                                    printBodyOptions:
                                                    {
                                                    styleToAdd:'padding:10px;background-color:white;margin:10px;color:#000000 !important;'
                                                    },
                                                    leaveOpen: true,
                                                    printMode: 'popup',
                                                    overrideElementCSS: true,
                                                    overrideElementCSS: ['../../forms/eye_mag/style.css']
                                                    });
                                          });
                  $("#ChangeTitle").click(function() {
                                          printElem({  });
                                          });
                  $("#PopupandLeaveopen").click(function() {
                                                printElem({  printMode: 'popup' });
                                                });
                  $("#stripCSS").click(function() {
                                       printElem({ overrideElementCSS: true });
                                       });
                  $("#externalCSS").click(function() {
                                          printElem({ overrideElementCSS: ['../../forms/eye_mag/style.css'] });
                                          });
                  $("input,textarea,text").focus(function(){
                                                 $(this).css("background-color","#ffff99");
                                                 });
                  //fullscreen menu functions
                  $("[class='dropdown-toggle']").hover(function(){
                                                       $("[class='dropdown-toggle']").parent().removeClass('open');
                                                       var menuitem = this.id.match(/(.*)/)[1];
                                                       //if the menu is active through a prior click, show it
                                                       // Have to override Bootstrap then
                                                       if ($("#menustate").val() !="1") { //menu not active -> ignore
                                                       $("#"+menuitem).css("background-color", "#C9DBF2");
                                                       $("#"+menuitem).css("color","#000"); /*#262626;*/
                                                       } else { //menu is active -> respond
                                                       $("#"+menuitem).css("background-color", "#1C5ECF");
                                                       $("#"+menuitem).css("color","#fff"); /*#262626;*/
                                                       $("#"+menuitem).css("text-decoration","none");
                                                       $("#"+menuitem).parent().addClass('open');
                                                       }
                                                       },function() {
                                                       var menuitem = this.id.match(/(.*)/)[1];
                                                       $("#"+menuitem).css("color","#000"); /*#262626;*/
                                                       $("#"+menuitem).css("background-color", "#C9DBF2");
                                                       
                                                       }
                                                       );
                  
                  $("[class='dropdown-toggle']").click(function() {
                                                       $("#menustate").val('1');
                                                       var menuitem = this.id.match(/(.*)/)[1];
                                                       $("#"+menuitem).css("background-color", "#1C5ECF");
                                                       $("#"+menuitem).css("color","#fff"); /*#262626;*/
                                                       $("#"+menuitem).css("text-decoration","none");
                                                       });
                  
                  $("#right-panel-link, #close-panel-bt").click(function() {
                                                                if ($("#PREFS_PANEL_RIGHT").val() =='1') {
                                                                $("#PREFS_PANEL_RIGHT").val('0');
                                                                } else {
                                                                $("#PREFS_PANEL_RIGHT").val('1');
                                                                }
                                                                update_PREFS();
                                                                });
                  $("[name^='menu_']").click(function() {
                                             $("[name^='menu_']").removeClass('active');
                                             var menuitem = this.id.match(/menu_(.*)/)[1];
                                             $(this).addClass('active');
                                             $("#menustate").val('1');
                                             menu_select(menuitem);
                                             });
                  // set display functions for Draw panel appearance
                  // for each DRAW area, if the value AREA_DRAW = 1, show it.
                  
                  var zones = ["PMH","HPI","EXT","ANTSEG","RETINA","NEURO","IMPPLAN"];
                  for (index = '0'; index < zones.length; ++index) {
                  if ($("#PREFS_"+zones[index]+"_RIGHT").val() =='DRAW') {
                  show_DRAW_section(zones[index]);
                  } else if ($("#PREFS_"+zones[index]+"_RIGHT").val() =='QP') {
                  show_QP_section(zones[index]);
                  }                  }
                  
                  $("input,textarea,text,checkbox").change(function(){
                                                           $(this).css("background-color","#F0F8FF");
                                                           submit_form($(this));
                                                           });
                  
                  $("body").on("click","[name$='_text_view']" , function() {
                               var header = this.id.match(/(.*)_text_view$/)[1];
                               
                               $("#"+header+"_text_list").toggleClass('wide_textarea');
                               $("#"+header+"_text_list").toggleClass('narrow_textarea');
                               $(this).toggleClass('fa-plus-square-o');
                               $(this).toggleClass('fa-minus-square-o');
                               if (header != /PRIOR/) {
                               var imagine = $("#PREFS_"+header+"_VIEW").val();
                               imagine ^= true;
                               $("#PREFS_"+header+"_VIEW").val(imagine);
                               update_PREFS();
                               }
                               });
                  
                  $("body").on("change", "select", function(e){
                               var new_section = this.name.match(/PRIOR_(.*)/);
                               if (new_section[1] =='') return;
                               if (new_section[1] == /\_/){
                                return;
                               }
                               
                               var newValue = this.value;
                               //now go get the prior page via ajax
                               var newValue = this.value;
                               $("#PRIORS_"+ new_section[1] +"_left_text").removeClass('nodisplay');
                               $("#DRAWS_" + new_section[1] + "_right").addClass('nodisplay');
                               $("#QP_" + new_section[1]).addClass('nodisplay');
                               
                               if (new_section[1] =="ALL") {
                               show_PRIORS();
                               getSection("ALL",newValue);
                               getSection("EXT",newValue);
                               getSection("ANTSEG",newValue);
                               getSection("RETINA",newValue);
                               getSection("NEURO",newValue);
                               } else {
                               getSection(new_section[1],newValue);
                               }
                               
                               });
                  $("body").on("click","[id^='Close_PRIORS_']", function() {
                               var new_section = this.id.match(/Close_PRIORS_(.*)$/)[1];
                               $("#PRIORS_"+ new_section +"_left_text").addClass('nodisplay');
                               $("#QP_" + new_section).removeClass('nodisplay');
                               });
                  
                  $("#pupils").mouseover(function() {
                                         $("#pupils").toggleClass('buttonRefraction_selected').toggleClass('underline');
                                         });
                  
                  $("#pupils").mouseout(function() {
                                        $("#pupils").toggleClass("red");
                                        });
                  $("#pupils").click(function(){
                                     $("#dim_pupils_panel").toggleClass('nodisplay');
                                     });
                  $("#vision_tab").mouseover(function() {
                                             $("#vision_tab").toggleClass('buttonRefraction_selected').toggleClass('underline');
                                             });
                  $("#vision_tab").mouseout(function() {
                                            $("#vision_tab").toggleClass('buttonRefraction_selected').toggleClass('underline');
                                            });
                  $("#vision_tab").click(function(){
                                         $("#LayerVision2").toggle();
                                         ($("#PREFS_VA").val() =='1') ? ($("#PREFS_VA").val('0')) : $("#PREFS_VA").val('1');
                                         });
                  //set wearing to single vision or bifocal? Bifocal
                  $(".WNEAR").removeClass('nodisplay');
                  $("#WNEARODAXIS").addClass('nodisplay');
                  $("#WNEARODCYL").addClass('nodisplay');
                  $("#WNEARODPRISM").addClass('nodisplay');
                  $("#WNEAROSAXIS").addClass('nodisplay');
                  $("#WNEAROSCYL").addClass('nodisplay');
                  $("#WNEAROSPRISM").addClass('nodisplay');
                  
                  $("#Single").click(function(){
                                     $("#WNEARODAXIS").addClass('nodisplay');
                                     $("#WNEARODCYL").addClass('nodisplay');
                                     $("#WNEARODPRISM").addClass('nodisplay');
                                     $("#WODADD2").addClass('nodisplay');
                                     $("#WOSADD2").addClass('nodisplay');
                                     $("#WNEAROSAXIS").addClass('nodisplay');
                                     $("#WNEAROSCYL").addClass('nodisplay');
                                     $("#WNEAROSPRISM").addClass('nodisplay');
                                     
                                     // $(".WNEAR").addClass('nodisplay');
                                     $(".WSPACER").removeClass('nodisplay');
                                     //$("[id=Single]").prop('checked','checked');
                                     });
                  $("#Bifocal").click(function(){
                                      $(".WSPACER").addClass('nodisplay');
                                      $(".WNEAR").removeClass('nodisplay');
                                      $(".WMid").addClass('nodisplay');
                                      $(".WHIDECYL").removeClass('nodisplay');
                                      $("[name=RX]").val(["1"]);
                                      $("#WNEARODAXIS").addClass('nodisplay');
                                      $("#WNEARODCYL").addClass('nodisplay');
                                      $("#WNEARODPRISM").addClass('nodisplay');
                                      $("#WNEAROSAXIS").addClass('nodisplay');
                                      $("#WNEAROSCYL").addClass('nodisplay');
                                      $("#WNEAROSPRISM").addClass('nodisplay');
                                      $("#WODADD2").removeClass('nodisplay');
                                      $("#WOSADD2").removeClass('nodisplay');
                                      
                                      });
                  $("#Trifocal").click(function(){
                                       $(".WSPACER").addClass('nodisplay');
                                       $(".WNEAR").removeClass('nodisplay');
                                       $(".WMid").removeClass('nodisplay');
                                       $(".WHIDECYL").addClass('nodisplay');
                                       $("[name=RX]").val(["2"]);
                                       $("#WNEARODAXIS").addClass('nodisplay');
                                       $("#WNEARODCYL").addClass('nodisplay');
                                       $("#WNEARODPRISM").addClass('nodisplay');
                                       $("#WNEAROSAXIS").addClass('nodisplay');
                                       $("#WNEAROSCYL").addClass('nodisplay');
                                       $("#WNEAROSPRISM").addClass('nodisplay');
                                       $("#WODADD2").removeClass('nodisplay');
                                       $("#WOSADD2").removeClass('nodisplay');
                                       
                                       });
                  $("#Progressive").click(function(){
                                          $(".WSPACER").addClass('nodisplay');
                                          $(".WNEAR").removeClass('nodisplay');
                                          $(".WMid").addClass('nodisplay');
                                          $(".WHIDECYL").removeClass('nodisplay');
                                          $("[name=RX]").val(["3"]);
                                          $("#WNEARODAXIS").addClass('nodisplay');
                                          $("#WNEARODCYL").addClass('nodisplay');
                                          $("#WNEARODPRISM").addClass('nodisplay');
                                          $("#WNEAROSAXIS").addClass('nodisplay');
                                          $("#WNEAROSCYL").addClass('nodisplay');
                                          $("#WNEAROSPRISM").addClass('nodisplay');
                                          $("#WODADD2").removeClass('nodisplay');
                                          $("#WOSADD2").removeClass('nodisplay');
                                          
                                          });
                  $("#Amsler-Normal").change(function() {
                                             if ($(this).is(':checked')) {
                                             var number1 = document.getElementById("AmslerOD").src.match(/(Amsler_\d)/)[1];
                                             document.getElementById("AmslerOD").src = document.getElementById("AmslerOD").src.replace(number1,"Amsler_0");
                                             var number2 = document.getElementById("AmslerOS").src.match(/(Amsler_\d)/)[1];
                                             document.getElementById("AmslerOS").src = document.getElementById("AmslerOS").src.replace(number2,"Amsler_0");
                                             $("#AMSLEROD").val("0");
                                             $("#AMSLEROS").val("0");
                                             $("#AmslerODvalue").text("0");
                                             $("#AmslerOSvalue").text("0");
                                             submit_form("eye_mag");
                                             return;
                                             }
                                             });
                  $("#PUPIL_NORMAL").change(function() {
                                            alert('Hello');
                                            if ($(this).is(':checked')) {
                                            $("#ODPUPILSIZE1").val('3.0');
                                            $("#OSPUPILSIZE1").val('3.0');
                                            $("#ODPUPILSIZE2").val('2.0');
                                            $("#OSPUPILSIZE2").val('2.0');
                                            $("#ODPUPILREACTIVITY").val('+2');
                                            $("#OSPUPILREACTIVITY").val('+2');
                                            $("#ODAPD").val('0');
                                            $("#OSAPD").val('0');
                                            submit_form("eye_mag");
                                            return;
                                            }
                                            });
                  
                  $("[name^='EXAM']").mouseover(function(){
                                                $(this).toggleClass("borderShadow2");
                                                });
                  $("[name^='EXAM']").mouseout(function(){
                                               $(this).toggleClass("borderShadow2");
                                               });
                  $("#AmslerOD, #AmslerOS").click(function() {
                                                  var number1 = this.src.match(/Amsler_(\d)/)[1];
                                                  var number2 = +number1 +1;
                                                  this.src = this.src.replace('Amsler_'+number1,'Amsler_'+number2);
                                                  this.src = this.src.replace('Amsler_6','Amsler_0');
                                                  $("#Amsler-Normal").removeAttr('checked');
                                                  var number3 = this.src.match(/Amsler_(\d)/)[1];
                                                  this.html =  number3;
                                                  if (number3 =="6") {
                                                  number3 = "0";
                                                  }
                                                  if ($(this).attr("id")=="AmslerOD") {
                                                  $("#AmslerODvalue").text(number3);
                                                  $('#AMSLEROD').val(number3);
                                                  } else {
                                                  $('#AMSLEROS').val(number3);
                                                  $("#AmslerOSvalue").text(number3);
                                                  }
                                                  var title = "#"+$(this).attr("id")+"_tag";
                                                  });
                  
                  $("#AmslerOD, #AmslerOS").mouseout(function() {
                                                     submit_form("eye_mag");
                                                     });
                  $("[name^='ODVF'],[name^='OSVF']").click(function() {
                                                           if ($(this).is(':checked') == true) {
                                                           $("#FieldsNormal").prop('checked', false);
                                                           $(this).val('1');
                                                           }else{
                                                           $(this).val('0');
                                                           $(this).prop('checked', false);
                                                           }
                                                           submit_form("eye_mag");
                                                           });
                  $("#FieldsNormal").click(function() {
                                           if ($(this).is(':checked')) {
                                           $("#ODVF1").removeAttr('checked');
                                           $("#ODVF2").removeAttr('checked');
                                           $("#ODVF3").removeAttr('checked');
                                           $("#ODVF4").removeAttr('checked');
                                           $("#OSVF1").removeAttr('checked');
                                           $("#OSVF2").removeAttr('checked');
                                           $("#OSVF3").removeAttr('checked');
                                           $("#OSVF4").removeAttr('checked');
                                           }
                                           });
                  $("[id^='EXT_prefix']").change(function() {
                                                 var newValue =$('#EXT_prefix').val();
                                                 newValue = newValue.replace('+', '');
                                                 if (newValue =="off") {$(this).val('');}
                                                 $("[name^='EXT_prefix_']").removeClass('eye_button_selected');
                                                 $("#EXT_prefix_"+ newValue).addClass("eye_button_selected");
                                                 });
                  $("#ANTSEG_prefix").change(function() {
                                             var newValue = $(this).val().replace('+', '');
                                             if ($(this).value =="off") {$(this).val('');}
                                             $("[name^='ANTSEG_prefix_']").removeClass('eye_button_selected');
                                             $("#ANTSEG_prefix_"+ newValue).addClass("eye_button_selected");
                                             });
                  $("#RETINA_prefix").change(function() {
                                             var newValue = $("#RETINA_prefix").val().replace('+', '');
                                             if ($(this).value =="off") {$(this).val('');}
                                             $("[name^='RETINA_prefix_']").removeClass('eye_button_selected');
                                             $("#RETINA_prefix_"+ newValue).addClass("eye_button_selected");
                                             });
                  $("#NEURO_ACT_zone").change(function() {
                                              var newValue = $(this).val();
                                              $("[name^='NEURO_ACT_zone']").removeClass('eye_button_selected');
                                              $("#NEURO_ACT_zone_"+ newValue).addClass("eye_button_selected");
                                              $("#PREFS_ACT_SHOW").val(newValue);
                                              update_PREFS;
                                              $("#ACT_tab_"+newValue).trigger('click');
                                              });
                  $("#NEURO_side").change(function() {
                                          var newValue = $(this).val();
                                          $("[name^='NEURO_side']").removeClass('eye_button_selected');
                                          $("#NEURO_side_"+ newValue).addClass("eye_button_selected");
                                          });
                  $('.ACT').focus(function() {
                                  var id = this.id.match(/ACT(\d*)/);
                                  $('#NEURO_field').val(''+id[1]).trigger('change');
                                  });
                  $("#NEURO_field").change(function() {
                                           var newValue = $(this).val();
                                           $("[name^='NEURO_field']").removeClass('eye_button_selected');
                                           $("#NEURO_field_"+ newValue).addClass("eye_button_selected");
                                           $('.ACT').each(function(i){
                                                          var color = $(this).css('background-color');
                                                          if ((color == 'rgb(255, 255, 153)')) {// =='blue' <- IE hack
                                                          $(this).css("background-color","red");
                                                          }
                                                          });
                                           //change to highlight field in zone entry is for
                                           var zone = $("#NEURO_ACT_zone").val();
                                           $("#ACT"+newValue+zone).css("background-color","yellow");
                                           });
                  $("[name^='NEURO_ACT_strab']").click(function() {
                                                       var newValue = $(this).val();
                                                       $("[name^='NEURO_ACT_strab']").removeClass('eye_button_selected');
                                                       $(this).addClass("eye_button_selected");
                                                       });
                  $("#NEURO_value").change(function() {
                                           var newValue = $(this).val();
                                           $("[name^='NEURO_value']").removeClass('eye_button_selected');
                                           $("#NEURO_value_"+ newValue).addClass("eye_button_selected");
                                           if (newValue == "ortho") {
                                           $("#NEURO_ACT_strab").val('');
                                           $("[name^='NEURO_ACT_strab']").removeClass('eye_button_selected');
                                           $("#NEURO_side").val('');
                                           $("[name^='NEURO_side']").removeClass('eye_button_selected');
                                           }
                                           });
                  $("#NEURO_RECORD").mouseover(function() {
                                               $("#NEURO_RECORD").addClass('borderShadow2');
                                               });
                  $("#NEURO_RECORD").mouseout(function() {
                                              $("#NEURO_RECORD").removeClass('borderShadow2');
                                              });
                  $("#NEURO_RECORD").mousedown(function() {
                                               $("#NEURO_RECORD").removeClass('borderShadow2');
                                               $(this).toggleClass('button_over');
                                               });
                  $("#NEURO_RECORD").mouseup(function() {
                                             $("#NEURO_RECORD").removeClass('borderShadow2');
                                             $(this).toggleClass('button_over');
                                             });
                  $("#NEURO_RECORD").click(function() {
                                           //find out the field we are updating
                                           var number = $("#NEURO_field").val();
                                           var zone = $("#NEURO_ACT_zone").val();
                                           var strab = $("#NEURO_value").val() + ' '+ $("#NEURO_side").val() + $("#NEURO_ACT_strab").val();
                                           
                                           $("#ACT"+number+zone).val(strab).css("background-color","#F0F8FF");
                                           
                                           
                                           });
                  
                  $("AntSegSpan,#AntSegOD,#AntSegOU,#AntSegOS,#EXTOD,#EXTOU,#EXTOS,#RETINAOD,#RETINAOU,#RETINAOS").mouseover(function() {
                                                                                                                             $(this).toggleClass('button_over');
                                                                                                                             
                                                                                                                             });
                  $("AntSegSpan,#AntSegOD,#AntSegOU,#AntSegOS,#EXTOD,#EXTOU,#EXTOS,#RETINAOD,#RETINAOU,#RETINAOS").mouseout(function() {
                                                                                                                            $(this).toggleClass('button_over');
                                                                                                                            });
                  
                  $("AntSegSpan,#AntSegOD,#AntSegOU,#AntSegOS,#EXTOD,#EXTOU,#EXTOS,#RETINAOD,#RETINAOU,#RETINAOS").click(function() {
                                                                                                                         var section = this.id.match(/(.*)O.$/)[1];
                                                                                                                         var tabOU = "#"+section +"OU";
                                                                                                                         var tabOS = "#"+section +"OS";
                                                                                                                         var tabOD = "#"+section +"OD";
                                                                                                                         $(tabOU).removeClass('button_selected');
                                                                                                                         $(tabOD).removeClass('button_selected');
                                                                                                                         $(tabOS).removeClass('button_selected');
                                                                                                                         $(this).toggleClass('button_selected');
                                                                                                                         });
                  $("#LayerVision, #LayerTension, #LayerMotility, #LayerAmsler, #LayerFields, #LayerPupils,#dim_pupils_panel,#PRIORS_ALL_left_text").mouseover(function(){
                                                                                                                                         $(this).toggleClass("borderShadow2");
                                                                                                                                         });
                  $("#LayerVision, #LayerTension, #LayerMotility, #LayerAmsler, #LayerFields, #LayerPupils,#dim_pupils_panel,#PRIORS_ALL_left_text").mouseout(function(){
                                                                                                                                        $(this).toggleClass("borderShadow2");
                                                                                                                                        });
                  $("[id^=LayerVision_]").mouseover(function(){
                                                    $(this).toggleClass("borderShadow2");
                                                    });
                  $("[id^=LayerVision_]").mouseout(function(){
                                                   $(this).toggleClass("borderShadow2");
                                                   });
                  $("#LayerVision_W_lightswitch, #LayerVision_CR_lightswitch,#LayerVision_MR_lightswitch,#LayerVision_ADDITIONAL_lightswitch,#LayerVision_CTL_lightswitch,#LayerVision_VAX_lightswitch,#LayerVision_IOP_lightswitch").click(function() {
                                                                                                                                                                                                                                            var section = "#"+this.id.match(/(.*)_lightswitch$/)[1];
                                                                                                                                                                                                                                            var section2 = this.id.match(/(.*)_(.*)_lightswitch$/)[2];
                                                                                                                                                                                                                                            var elem = document.getElementById("PREFS_"+section2);
                                                                                                                                                                                                                                            if ($("#PREFS_VA").val() !='1') {
                                                                                                                                                                                                                                            $("#PREFS_VA").val('1');
                                                                                                                                                                                                                                            $("#LayerVision2").removeClass('nodisplay');
                                                                                                                                                                                                                                            elem.value="1";
                                                                                                                                                                                                                                            $(section).removeClass('nodisplay');
                                                                                                                                                                                                                                            if (section2 =="ADDITIONAL") {
                                                                                                                                                                                                                                            $("#LayerVision_ADDITIONAL").removeClass('nodisplay');
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            if (section2 =="VAX") {
                                                                                                                                                                                                                                            $("#LayerVision_ADDITIONAL_VISION").removeClass('nodisplay');
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            if (section2 =="IOP") {
                                                                                                                                                                                                                                            $("#LayerVision_IOP").removeClass('nodisplay');
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            this.addClass("buttonRefraction_selected");
                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                            
                                                                                                                                                                                                                                            if (elem.value == "0" || elem.value =='') {
                                                                                                                                                                                                                                            elem.value='1';
                                                                                                                                                                                                                                            if (section2 =="ADDITIONAL") {
                                                                                                                                                                                                                                            $("#LayerVision_ADDITIONAL").removeClass('nodisplay');
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            if (section2 =="IOP") {
                                                                                                                                                                                                                                            $("#LayerVision_IOP").removeClass('nodisplay');
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            $(section).removeClass('nodisplay');
                                                                                                                                                                                                                                            $(this).addClass("buttonRefraction_selected");
                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                            elem.value='0';
                                                                                                                                                                                                                                            $(section).addClass('nodisplay');
                                                                                                                                                                                                                                            if (section2 =="VAX") {
                                                                                                                                                                                                                                            $("#LayerVision_ADDITIONAL_VISION").addClass('nodisplay');
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            if (section2 =="IOP") {
                                                                                                                                                                                                                                            $("#LayerVision_IOP").addClass('nodisplay');
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            $(this).removeClass("buttonRefraction_selected");
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                            update_PREFS();
                                                                                                                                                                                                                                            //$("#tab1").removeClass('nodisplay');
                                                                                                                                                                                                                                            });
                  
                  
                  $("#LayerVision_W_lightswitch, #LayerVision_CR_lightswitch,#LayerVision_MR_lightswitch,#LayerVision_ADDITIONAL_lightswitch,#LayerVision_CTL_lightswitch,#LayerVision_VAX_lightswitch").mouseover(function() {
                                                                                                                                                                                                                   $(this).addClass('buttonRefraction_selected');
                                                                                                                                                                                                                   });
                  $("#LayerVision_W_lightswitch, #LayerVision_CR_lightswitch,#LayerVision_MR_lightswitch,#LayerVision_ADDITIONAL_lightswitch,#LayerVision_CTL_lightswitch,#LayerVision_VAX_lightswitch,#LayerVision_IOP_lightswitch").mouseout(function() {
                                                                                                                                                                                                                                               var section2 = this.id.match(/(.*)_(.*)_lightswitch$/)[2];
                                                                                                                                                                                                                                               var elem = document.getElementById("PREFS_"+section2);
                                                                                                                                                                                                                                               
                                                                                                                                                                                                                                               if (elem.value != "1") {                                                                $(this).removeClass('buttonRefraction_selected');
                                                                                                                                                                                                                                               } else {
                                                                                                                                                                                                                                               $(this).addClass('buttonRefraction_selected');
                                                                                                                                                                                                                                               }                                                                });
                  
                  
                  //useful to make two VA fields stay in sync
                  
                  $("input[name$='VA']").blur(function() {
                                              var hereValue = $(this).val();
                                              var newValue = $(this).attr('name').replace('VA', 'VA_copy');
                                              $("#" + newValue).val(hereValue);
                                              $("#" + newValue + "_brd").val(hereValue);
                                              });
                  $("input[name$='_copy']").blur(function() {
                                                 var hereValue = $(this).val();
                                                 var newValue = $(this).attr('name').replace('VA_copy', 'VA');
                                                 $("#" + newValue).val(hereValue);
                                                 $("#" + newValue + "_copy_brd").val(hereValue);
                                                 submit_form("eye_mag");
                                                 });
                  $("input[name$='_copy_brd']").change(function() {
                                                       var hereValue = $(this).val();
                                                       var newValue = $(this).attr('name').replace('VA_copy_brd', 'VA');
                                                       $("#" + newValue).val(hereValue);
                                                       $("#" + newValue + "_copy").val(hereValue);
                                                       submit_form("eye_mag");
                                                       });
                  $("[name^='more_']").mouseover(function() {
                                                 $(this).toggleClass('buttonRefraction_selected').toggleClass('underline');
                                                 });
                  $("[name^='more_']").mouseout(function() {
                                                $(this).toggleClass('buttonRefraction_selected').toggleClass('underline');
                                                });
                  $("[name^='more_']").click(function() {
                                             $("#Visions_A").toggleClass('nodisplay');
                                             $("#Visions_B").toggleClass('nodisplay');
                                             });
                  // These defaults can also be set server side and retrieved via an ajax call allowing customization at the DB server level via openEMR,
                  // rather than here.  Here however the end user would need to manually edit this file.  Perhaps shifting defaults into the DB makes sense.
                  // FEATURE REQUEST.
                  // Perfect.  This will go under the framework of starting and modifying the record according to ICD-10 codes,
                  // rather than or in addition to the other way around.
                  // Enter the correct ICD-10 code, fill in/addend the field, add Dx code in impression area.
                  // Lots of work in getting the last sentence to work...
                  
                  $("#EXT_defaults").click(function() {
                                           $('#RUL').val('normal lids and lashes').css("background-color","beige");
                                           $('#LUL').val('normal lids and lashes').css("background-color","beige");
                                           $('#RLL').val('good tone').css("background-color","beige");
                                           $('#LLL').val('good tone').css("background-color","beige");
                                           $('#RBROW').val('no brow ptosis').css("background-color","beige");
                                           $('#LBROW').val('no brow ptosis').css("background-color","beige");
                                           $('#RMCT').val('no masses').css("background-color","beige");
                                           $('#RADNEXA').val('normal lacrimal gland and orbit').css("background-color","beige");
                                           $('#LADNEXA').val('normal lacrimal gland and orbit').css("background-color","beige");
                                           $('#LMCT').val('no masses').css("background-color","beige");
                                           $('#RMRD').val('+3').css("background-color","beige");
                                           $('#LMRD').val('+3').css("background-color","beige");
                                           $('#RLF').val('17').css("background-color","beige");
                                           $('#LLF').val('17').css("background-color","beige");
                                           submit_form("eye_mag");
                                           });
                  
                  $("#ANTSEG_defaults").click(function() {
                                              $('#ODCONJ').val('quiet').css("background-color","beige");
                                              $('#OSCONJ').val('quiet').css("background-color","beige");
                                              $('#ODCORNEA').val('clear').css("background-color","beige");
                                              $('#OSCORNEA').val('clear').css("background-color","beige");
                                              $('#ODAC').val('deep and quiet').css("background-color","beige");
                                              $('#OSAC').val('deep and quiet').css("background-color","beige");
                                              $('#ODLENS').val('clear').css("background-color","beige");
                                              $('#OSLENS').val('clear').css("background-color","beige");
                                              $('#ODIRIS').val('round').css("background-color","beige");
                                              $('#OSIRIS').val('round').css("background-color","beige");
                                              submit_form("eye_mag");
                                              });
                  $("#RETINA_defaults").click(function() {
                                              $('#ODDISC').val('pink').css("background-color","beige");
                                              $('#OSDISC').val('pink').css("background-color","beige");
                                              $('#ODCUP').val('0.3').css("background-color","beige");
                                              $('#OSCUP').val('0.3').css("background-color","beige");
                                              $('#ODMACULA').val('flat').css("background-color","beige");
                                              $('#OSMACULA').val('flat').css("background-color","beige");
                                              $('#ODVESSELS').val('2:3').css("background-color","beige");
                                              $('#OSVESSELS').val('2:3').css("background-color","beige");
                                              $('#ODPERIPH').val('flat, no tears, holes or RD').css("background-color","beige");
                                              $('#OSPERIPH').val('flat, no tears, holes or RD').css("background-color","beige");
                                              submit_form("eye_mag");
                                              });
                  $("#NEURO_defaults").click(function() {
                                             $('#ODPUPILSIZE1').val('3.0').css("background-color","beige");
                                             $('#ODPUPILSIZE2').val('2.0').css("background-color","beige");
                                             $('#ODPUPILREACTIVITY').val('+2').css("background-color","beige");
                                             $('#ODAPD').val('0').css("background-color","beige");
                                             $('#OSPUPILSIZE1').val('3.0').css("background-color","beige");
                                             $('#OSPUPILSIZE2').val('2.0').css("background-color","beige");
                                             $('#OSPUPILREACTIVITY').val('+2').css("background-color","beige");
                                             $('#OSAPD').val('0').css("background-color","beige");
                                             $('#ODVFCONFRONTATION1').val('0').css("background-color","beige");
                                             $('#ODVFCONFRONTATION2').val('0').css("background-color","beige");
                                             $('#ODVFCONFRONTATION3').val('0').css("background-color","beige");
                                             $('#ODVFCONFRONTATION4').val('0').css("background-color","beige");
                                             $('#ODVFCONFRONTATION5').val('0').css("background-color","beige");
                                             $('#OSVFCONFRONTATION1').val('0').css("background-color","beige");
                                             $('#OSVFCONFRONTATION2').val('0').css("background-color","beige");
                                             $('#OSVFCONFRONTATION3').val('0').css("background-color","beige");
                                             $('#OSVFCONFRONTATION4').val('0').css("background-color","beige");
                                             $('#OSVFCONFRONTATION5').val('0').css("background-color","beige");
                                             submit_form("eye_mag");
                                             });
                  
                  $("#EXAM_defaults").click(function() {
                                            $('#RUL').val('normal lids and lashes').css("background-color","beige");
                                            $('#LUL').val('normal lids and lashes').css("background-color","beige");
                                            $('#RLL').val('good tone').css("background-color","beige");
                                            $('#LLL').val('good tone').css("background-color","beige");
                                            $('#RBROW').val('no brow ptosis').css("background-color","beige");
                                            $('#LBROW').val('no brow ptosis').css("background-color","beige");
                                            $('#RMCT').val('no masses').css("background-color","beige");
                                            $('#LMCT').val('no masses').css("background-color","beige");
                                            $('#RADNEXA').val('normal lacrimal gland and orbit').css("background-color","beige");
                                            $('#LADNEXA').val('normal lacrimal gland and orbit').css("background-color","beige");
                                            $('#RMRD').val('+3').css("background-color","beige");
                                            $('#LMRD').val('+3').css("background-color","beige");
                                            $('#RLF').val('17').css("background-color","beige");
                                            $('#LLF').val('17').css("background-color","beige");
                                            $('#OSCONJ').val('quiet').css("background-color","beige");
                                            $('#ODCONJ').val('quiet').css("background-color","beige");
                                            $('#ODCORNEA').val('clear').css("background-color","beige");
                                            $('#OSCORNEA').val('clear').css("background-color","beige");
                                            $('#ODAC').val('deep and quiet, -F/C').css("background-color","beige");
                                            $('#OSAC').val('deep and quiet, -F/C').css("background-color","beige");
                                            $('#ODLENS').val('clear').css("background-color","beige");
                                            $('#OSLENS').val('clear').css("background-color","beige");
                                            $('#ODIRIS').val('round').css("background-color","beige");
                                            $('#OSIRIS').val('round').css("background-color","beige");
                                            $('#ODPUPILSIZE1').val('3.0').css("background-color","beige");
                                            $('#ODPUPILSIZE2').val('2.0').css("background-color","beige");
                                            $('#ODPUPILREACTIVITY').val('+2').css("background-color","beige");
                                            $('#ODAPD').val('0').css("background-color","beige");
                                            $('#OSPUPILSIZE1').val('3.0').css("background-color","beige");
                                            $('#OSPUPILSIZE2').val('2.0').css("background-color","beige");
                                            $('#OSPUPILREACTIVITY').val('+2').css("background-color","beige");
                                            $('#OSAPD').val('0').css("background-color","beige");
                                            $('#ODVFCONFRONTATION1').val('0').css("background-color","beige");
                                            $('#ODVFCONFRONTATION2').val('0').css("background-color","beige");
                                            $('#ODVFCONFRONTATION3').val('0').css("background-color","beige");
                                            $('#ODVFCONFRONTATION4').val('0').css("background-color","beige");
                                            $('#ODVFCONFRONTATION5').val('0').css("background-color","beige");
                                            $('#OSVFCONFRONTATION1').val('0').css("background-color","beige");
                                            $('#OSVFCONFRONTATION2').val('0').css("background-color","beige");
                                            $('#OSVFCONFRONTATION3').val('0').css("background-color","beige");
                                            $('#OSVFCONFRONTATION4').val('0').css("background-color","beige");
                                            $('#OSVFCONFRONTATION5').val('0').css("background-color","beige");
                                            $('#ODDISC').val('pink').css("background-color","beige");
                                            $('#OSDISC').val('pink').css("background-color","beige");
                                            $('#ODCUP').val('0.3').css("background-color","beige");
                                            $('#OSCUP').val('0.3').css("background-color","beige");
                                            $('#ODMACULA').val('flat').css("background-color","beige");
                                            $('#OSMACULA').val('flat').css("background-color","beige");
                                            $('#ODVESSELS').val('2:3').css("background-color","beige");
                                            $('#OSVESSELS').val('2:3').css("background-color","beige");
                                            $('#ODPERIPH').val('flat, no tears, holes or RD').css("background-color","beige");
                                            $('#OSPERIPH').val('flat, no tears, holes or RD').css("background-color","beige");
                                            submit_form("eye_mag");
                                            });
                  
                  $("#MOTILITYNORMAL").click(function() {
                                             $("#MOTILITY_RS").val('0');
                                             $("#MOTILITY_RI").val('0');
                                             $("#MOTILITY_RR").val('0');
                                             $("#MOTILITY_RL").val('0');
                                             $("#MOTILITY_LS").val('0');
                                             $("#MOTILITY_LI").val('0');
                                             $("#MOTILITY_LR").val('0');
                                             $("#MOTILITY_LL").val('0');
                                             for (index = '0'; index < 5; ++index) {
                                             $("#MOTILITY_RS_"+index).html('');
                                             $("#MOTILITY_RI_"+index).html('');
                                             $("#MOTILITY_RR_"+index).html('');
                                             $("#MOTILITY_RL_"+index).html('');
                                             $("#MOTILITY_LS_"+index).html('');
                                             $("#MOTILITY_LI_"+index).html('');
                                             $("#MOTILITY_LR_"+index).html('');
                                             $("#MOTILITY_LL_"+index).html('');
                                             }
                                             submit_form('eye_mag');
                                             });
                  
                  $("[name^='MOTILITY_']").click(function()  {
                                                 $("#MOTILITYNORMAL").removeAttr('checked');
                                                 
                                                 var zone = this.id.match(/(MOTILITY_..)_(.)/);
                                                 var valued = isNaN($("#"+zone[1]).val());
                                                 if (valued != true && $("#"+zone[1]).val() <'4') {
                                                 valued=$("#"+zone[1]).val();
                                                 valued++;
                                                 } else {
                                                 valued = '0';
                                                 $("#"+zone[1]).val('0');
                                                 }
                                                 
                                                 $("#"+zone[1]).val(valued);
                                                 var section = this.id.match(/MOTILITY_(.)(.)_/);
                                                 var section2 = section[2];
                                                 var Eye = section[1];
                                                 var SupInf = section2.search(/S|I/);
                                                 var RorLside   = section2.search(/R|L/);
                                                 var index   = '0';
                                                 
                                                 if (RorLside =='0') {
                                                 var hash_tag = '<i class="fa fa-minus rotate-left">';
                                                 } else {
                                                 var hash_tag = '<i class="fa fa-minus">';
                                                 }
                                                 for (index = '0'; index < 5; ++index) {
                                                 $("#"+zone[1]+"_"+index).html('');
                                                 }
                                                 if (valued > '0') {
                                                 for (index =1; index < (valued+1); ++index) {
                                                 $("#"+zone[1]+"_"+index).html(hash_tag);
                                                 }
                                                 }
                                                 submit_form();
                                                 });
                  
                  $("[name^='Close_']").click(function()  {
                                              var section = this.id.match(/Close_(.*)$/)[1];
                                              if (section =="ACTMAIN") {
                                              $("#ACTTRIGGER").trigger( "click" );
                                              } else {
                                              $("#LayerVision_"+section+"_lightswitch").click();
                                              }
                                              });
                  
                  
                  $("#EXAM_DRAW, #BUTTON_DRAW_menu, #PANEL_DRAW").click(function() {
                                                                        
                                                                             if ($("#PREFS_CLINICAL").value !='0') {
                                                                             show_right();
                                                                             $("#PREFS_CLINICAL").val('0');
                                                                             update_PREFS();
                                                                             }
                                                                             if ($("#PREFS_EXAM").value != 'DRAW') {
                                                                             $("#PREFS_EXAM").val('DRAW');
                                                                             $("#EXAM_QP").removeClass('button_selected');
                                                                             $("#EXAM_DRAW").addClass('button_selected');
                                                                             $("#EXAM_TEXT").removeClass('button_selected');
                                                                             update_PREFS();
                                                                             }
                                                                             show_DRAW();
                                                                             $(document).scrollTop( $("#EXT_anchor").offset().top -50);
                                                                             });
                    $("#EXAM_QP,#PANEL_QP").click(function() {
                                      if ($("#PREFS_CLINICAL").value !='0') {
                                      $("#PREFS_CLINICAL").val('0');
                                      update_PREFS();
                                      }
                                      if ($("#PREFS_EXAM").value != 'QP') {
                                      $("#PREFS_EXAM").val('QP');
                                      $("#EXAM_QP").addClass('button_selected');
                                      $("#EXAM_DRAW").removeClass('button_selected');
                                      $("#EXAM_TEXT").removeClass('button_selected');
                                      update_PREFS();
                                      }
                                      show_QP();
                                      $(document).scrollTop( $("#EXT_anchor").offset().top -50 );
                                      });
                  
                  $("#EXAM_TEXT,#PANEL_TEXT").click(function() {
                                        
                                        // also hide QP, DRAWs, and PRIORS
                                        hide_DRAW();
                                        hide_QP();
                                        hide_PRIORS();
                                        hide_right();
                                        show_TEXT();
                                        for (index = '0'; index < zones.length; ++index) {
                                        $("#PREFS_"+zones[index]+"_RIGHT").val(0);
                                        }
                                        update_PREFS();
                                        
                                        $("#EXAM_DRAW").removeClass('button_selected');
                                        $("#EXAM_QP").removeClass('button_selected');
                                        $("#EXAM_TEXT").addClass('button_selected');
                                        // document.getElementById("LayerTechnical_sections").scrollIntoView();
                                        $(document).scrollTop( $("#EXT_anchor").offset().top -50);
                                        });
                  $("[id^='BUTTON_TEXT_']").click(function() {
                                                  var zone = this.id.match(/BUTTON_TEXT_(.*)/)[1];
                                                  if (zone != "menu") {
                                                  $("#"+zone+"_right").addClass('nodisplay');
                                                  $("#"+zone+"_left").removeClass('display');
                                                  $("#"+zone+"_left_text").removeClass('display');
                                                  $("#PREFS_"+zone+"_RIGHT").val(0);
                                                  update_PREFS();
                                                  }
                                                  });
                  $("[id^='BUTTON_TEXTD_']").click(function() {
                                                   var zone = this.id.match(/BUTTON_TEXTD_(.*)/)[1];
                                                   if (zone != "menu") {
                                                       if ((zone =="PMH") || (zone == "HPI")) {
                                                       $("#PMH_right").addClass('nodisplay');
                                                       $("#PREFS_PMH_RIGHT").val(1);
                                                       $("#HPI_right").addClass('nodisplay');
                                                       $("#PREFS_HPI_RIGHT").val(1);
                                                   if (zone == "PMH") {
                                                   $(document).scrollTop( $("#"+zone+"_anchor").offset().top - 25);
                                                   }

                                                   } else {
                                                       $("#"+zone+"_right").addClass('nodisplay');
                                                       // $("#"+zone+"_COMMENTS_DIV").removeClass('QP_lengthen');
                                                       // $("#"+zone+"_keyboard_left").removeClass('nodisplay');
                                                       $("#PREFS_"+zone+"_RIGHT").val(1);
                                                   
                                                   }
                                                   update_PREFS();

                                                   }
                                                   });
                  
                  $("#EXAM_TEXT").addClass('button_selected');
                  
                  if (($("#PREFS_CLINICAL").val() !='1')) {
                  var actionQ = "#EXAM_"+$("#PREFS_EXAM").val();
                  $(actionQ).trigger('click');
                  } else {
                  $("#EXAM_TEXT").addClass('button_selected');
                  }
                  if ($("#ANTSEG_prefix").val() > '') {
                  $("#ANTSEG_prefix_"+$("#ANTSEG_prefix").val()).addClass('button_selected');
                  } else {
                  $("#ANTSEG_prefix").val('off').trigger('change');
                  }
                  
                  $("[name^='ACT_tab_']").click(function()  {
                                                var section = this.id.match(/ACT_tab_(.*)/)[1];
                                                $("[name^='ACT_']").addClass('nodisplay');
                                                $("[name^='ACT_tab_']").removeClass('nodisplay').removeClass('ACT_selected').addClass('ACT_deselected');
                                                $("#ACT_tab_" + section).addClass('ACT_selected').removeClass('ACT_deselected');
                                                $("#ACT_" + section).removeClass('nodisplay');
                                                $("#PREFS_ACT_SHOW").val(section);
                                                //selection correctt QP zone
                                                $("[name^='NEURO_ACT_zone']").removeClass('eye_button_selected');
                                                $("#NEURO_ACT_zone_"+ section).addClass("eye_button_selected");
                                                $("#NEURO_ACT_zone").val(section);
                                                update_PREFS();
                                                });
                  $("#ACTTRIGGER").mouseover(function() {
                                             $("#ACTTRIGGER").toggleClass('buttonRefraction_selected').toggleClass('underline');
                                             
                                             });
                  $("#ACTTRIGGER").mouseout(function() {
                                            $("#ACTTRIGGER").toggleClass('buttonRefraction_selected').toggleClass('underline');
                                            });
                  if ($("#PREFS_ACT_VIEW").val() == '1') {
                  $("#ACTMAIN").toggleClass('nodisplay'); //.toggleClass('fullscreen');
                  $("#NPCNPA").toggleClass('nodisplay');
                  $("#ACTNORMAL_CHECK").toggleClass('nodisplay');
                  $("#ACTTRIGGER").toggleClass('underline');
                  var show = $("#PREFS_ACT_SHOW").val();
                  $("#ACT_tab_"+show).trigger('click');
                  }
                  $("#ACTTRIGGER").click(function() {
                                         $("#ACTMAIN").toggleClass('nodisplay').toggleClass('ACT_TEXT'); //.toggleClass('fullscreen');
                                         $("#NPCNPA").toggleClass('nodisplay');
                                         $("#ACTNORMAL_CHECK").toggleClass('nodisplay');
                                         $("#ACTTRIGGER").toggleClass('underline');
                                         if ($("#PREFS_ACT_VIEW").val()=='1') {
                                         $("#PREFS_ACT_VIEW").val('0');
                                         } else {
                                         $("#PREFS_ACT_VIEW").val('1');
                                         }
                                         var show = $("#PREFS_ACT_SHOW").val();
                                         $("#ACT_tab_"+show).trigger('click');
                                         update_PREFS();
                                         });
                  
                  
/* Now it is time to figure out how to blow-up each section for a tablet for example to fill the screen and look good */
                  $("[name^='MAX_']").click(function() {
                                            
                                            alert("This button will allow the user to enter a fullscreen mode useful for tablet operations.  It needs to be written yet but essentially it will present the data in a format specific to the device's screen size...");
                                            
                                            //let's add a class to make this frame fullscreen
                                            //var section = this.id.match(/MAX_(.*)/)[1];
                                            //$("#"+ section + "_left").toggleClass('fullscreen');
                                            
                                            //to show the prior visits on screen using the selector script scroller
                                            //click this and toggle class nodisplay for id=PRIORS_NEURO_sections and NEURO_left
                                            //  $("#PRIORS_NEURO_sections").toggleClass('nodisplay');
                                            //  $("#NEURO_left").toggleClass('nodisplay');
                                            //we have to get the data to put here!
                                            
                                            });
                  $("#NEURO_COLOR").click(function() {
                                          $("#ODCOLOR").val("11/11");
                                          $("#OSCOLOR").val("11/11");
                                          submit_form("eye_mag");
                                          });
                  
                  $("#NEURO_COINS").click(function() {
                                          $("#ODCOINS").val("1.00");
                                          //leave currency symbol out unless it is an openEMR defined option
                                          $("#OSCOINS").val("1.00");
                                          submit_form("eye_mag");
                                          });
                  
                  $("#NEURO_REDDESAT").click(function() {
                                             $("#ODREDDESAT").val("100");
                                             $("#OSREDDESAT").val("100");
                                             submit_form("eye_mag");
                                             });
                  
                  $("[id^='myCanvas_']").mouseout(function() {
                                                  var zone = this.id.match(/myCanvas_(.*)/)[1];
                                                  submit_canvas(zone);
                                                  });
                  $("[id^='Undo_']").click(function() {
                                           var zone = this.id.match(/Undo_Canvas_(.*)/)[1];
                                           submit_canvas(zone);
                                           });
                  $("[id^='Redo_']").click(function() {
                                           var zone = this.id.match(/Redo_Canvas_(.*)/)[1];
                                           submit_canvas(zone);
                                           });
                  $("[id^='Clear_']").click(function() {
                                            var zone = this.id.match(/Clear_Canvas_(.*)/)[1];
                                            submit_canvas(zone);
                                            });
                  $("[id^='Base_']").click(function() {
                                           var zone = this.id.match(/Base_Canvas_(.*)/)[1];
                                           //To change the base img
                                           //delete current image from server
                                           //re-ajax the canvas div
                                           var id_here = document.getElementById('myCanvas_'+zone);
                                           var dataURL = id_here.toDataURL();
                                           $.ajax({
                                                  type: "POST",
                                                  url: "../../forms/eye_mag/save.php?canvas="+zone+"&id="+$("#form_id").val(),
                                                  data: {
                                                  imgBase64     : dataURL,  //this contains the new strokes, the sketch.js foreground
                                                  'zone'        : zone,
                                                  'visit_date'  : $("#visit_date").val(),
                                                  'encounter'   : $("#encounter").val(),
                                                  'pid'         : $("#pid").val()
                                                  }
                                                  
                                                  }).done(function(o) {
                                                          //            console.log(o);
                                                          $("#tellme").html(o);
                                                          });
                                           
                                           $("#url_"+zone).val("/interface/forms/eye_mag/images/OU_"+zone+"_BASE.png");
                                           canvas.renderAll();
                                           //submit_canvas(zone);
                                           });
                  
                  
                  
                  
                  
                  $("#COPY_SECTION").change(function() {
                                            var start = $("#COPY_SECTION").val();
                                            var value = start.match(/(\w*)-(\w*)/);
                                            var zone = value[1];
                                            var copy_from = value[2];
                                            var data = {
                                            "action"      : "copy",
                                            'copy'        : zone,
                                            'zone'        : zone,
                                            'copy_to'     : $("#form_id").val(),
                                            'copy_from'   : copy_from,
                                            'pid'         : $("#pid").val()
                                            };
                                            data = $("#"+zone+"_left_text").serialize() + "&" + $.param(data);
                                            $.ajax({
                                                   type 	: 'POST',
                                                   dataType : 'json',
                                                   url      :  "../../forms/eye_mag/save.php?copy="+zone,
                                                   data 	: data,
                                                   success  : function(result) {
                                                   $.map(result, function(valhere, keyhere) {
                                                         if ($("#"+keyhere).val() != valhere) {  $("#"+keyhere).val(valhere).css("background-color","#CCF");}
                                                         if (keyhere.match(/MOTILITY_/)) {
                                                         //copy forward ductions and versions visually
                                                         //make each blank, and rebuild them
                                                         $("[name='"+keyhere+"_1']").html('');
                                                         $("[name='"+keyhere+"_2']").html('');
                                                         $("[name='"+keyhere+"_3']").html('');
                                                         $("[name='"+keyhere+"_4']").html('');
                                                         if (keyhere.match(/(_RS|_LS|_RI|_LI)/)) {  //show a horizontal (minus) tag
                                                         hash_tag = '<i class="fa fa-minus"></i>';
                                                         } else { //show vertical tag
                                                         hash_tag = '<i class="fa fa-minus rotate-left"></i>';
                                                         }
                                                         for (index =1; index <= valhere; ++index) {
                                                            $("#"+keyhere+"_"+index).html(hash_tag);
                                                         }
                                                         }
                                                         });
                                                   }
                                                   }).done(function (){
                                                           submit_form("eye_mag");
                                                           });
                                            });
                  
                  
                  $("[id^='BUTTON_DRAW_']").click(function() {
                                                  var zone =this.id.match(/BUTTON_DRAW_(.*)$/)[1];
                                                  //hide_PRIORS();
                                                  if (zone =="ALL") {
                                                  //show_DRAW();
                                                  } else {
                                                  $("#"+zone+"_1").removeClass('nodisplay');
                                                  $("#"+zone+"_right").addClass('canvas').removeClass('nodisplay');
                                                  $("#QP_"+zone).addClass('nodisplay');
                                                  $("#PRIORS_"+zone+"_left_text").addClass('nodisplay');
                                                  $("#Draw_"+zone).removeClass('nodisplay');
                                                  $("#PREFS_"+zone+"_RIGHT").val('DRAW');
                                                  //$("#"+zone+"_COMMENTS_DIV").removeClass('QP_lengthen');
                                                  //$("#"+zone+"_keyboard_left").removeClass('nodisplay');
                                                  //$(document).scrollTop( $("#"+zone+"_anchor").offset().top );
                                                  
                                                  update_PREFS();
                                                  }
                                                  });
                  $("[id^='BUTTON_QP_']").click(function() {
                                                var zone = this.id.match(/BUTTON_QP_(.*)$/)[1].replace(/_\d*/,'');
                                                    $("#PRIORS_"+zone+"_left_text").addClass('nodisplay');
                                                    $("#Draw_"+zone).addClass('nodisplay');
                                                    show_QP_section(zone);
                                                    $("#PREFS_"+zone+"_RIGHT").val('QP');
                                                    if ((zone != 'PMH')&&(zone != 'HPI')) {
                                                //    $(document).scrollTop( $("#"+zone+"_anchor").offset().top );
                                                    }
                                                if (zone == 'PMH') {
                                                if($('#HPI_right').css('display') == 'none') {
                                                $("#PRIORS_HPI_left_text").addClass('nodisplay');
                                                $("#Draw_HPI").addClass('nodisplay');
                                                show_QP_section('HPI');
                                                $("#PREFS_HPI_RIGHT").val('QP');
                                                $(document).scrollTop('400');
                                                }
                                                }
                                                if (zone == 'HPI') {
                                                if($('#PMH_right').css('display') == 'none') {
                                                $("#PRIORS_PMH_left_text").addClass('nodisplay');
                                                $("#Draw_PMH").addClass('nodisplay');
                                                show_QP_section('PMH');
                                                $("#PREFS_PMH_RIGHT").val('QP');
                                                //$(document).scrollTop('400');
                                                }
                                                }
                                                
                                                update_PREFS();
                                                });
                  
                  $("#construction").click(function() {
                                           $("[id^='CONSTRUCTION_']").toggleClass('nodisplay');
                                           });
                  
                  $("#take_ownership").click(function() {
                                            //<form method="post" action="'.$rootdir.'/forms/'.$form_folder.'/save.php?mode=update&ownership='.$LOCKEDBY.'&LOCKEDBY='.$take_ownership.'" id="eye_mag" class="eye_mag pure-form" name="eye_mag">
                                             var url = "../../forms/eye_mag/save.php";
                                             var randomnumber=Math.floor(Math.random()*100000)
                                             var formData = {
                                             'mode'                 : "update",
                                             'ownership'            : $("#LOCKEDBY").val(),
                                             'LOCKEDBY'             : $("#uniqueID").val(),
                                             'form_id'              : $("#form_id").val()
                                             };
                                            $.ajax({
                                                   type 	: 'POST',   // define the type of HTTP verb we want to use (POST for our form)
                                                   url 		: url,      // the url where we want to POST
                                                   data 	: formData // our data object
                                                   }).done(function(o) {
                                                                                                                      //nice to flash a "saved" widget in menu bar if fullscreen or elsewhere if not
                                                           //  console.log(o);
                                                           //if (o == 'locked') {
                                                           //  alert("OK.  Now you can edit it!");
                                                           // if yes we send a request to unlock and relock it for us, which may not require the page to refresh...
                                                           //}
                                                          
                                                           //       $("#IMP").html(formData);
                                                           });
                                             });
                                             
                  window.onunload = finalize;
                  window.onbeforeunload = finalize;
                  // set default to ccDist.  Change as desired.
                  $('#NEURO_ACT_zone').val('CCDIST').trigger('change');
                  $("[name$='_loading']").addClass('nodisplay');
                  $("[name$='_sections']").removeClass('nodisplay');
                  
                  $('#left-panel').css("right","0px");
                  
                  });




