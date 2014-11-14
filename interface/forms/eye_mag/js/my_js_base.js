/**
 * forms/eye_mag/js/my_base_js.js
 *
 * JS Functions for eye_mag form(s)
 *
 * Copyright (C) 2010-14 Raymond Magauran <magauran@MedFetch.com>
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


function fill_QP_field(PEZONE, ODOSOU, LOCATION_text, selection,mult) {
        //if the background of the field we are writing to has a default value, erase it, otherwise add to it.
    
        //    alert(document.getElementById('ANTSEG_ODOSOU').value);
    if (ODOSOU > '') {
        var FIELDID =  ODOSOU  + LOCATION_text;
    } else {
        var FIELDID =  document.getElementById(PEZONE+'_'+ODOSOU).value  + LOCATION_text;
    }
    var bgcolor = $("#" +FIELDID).css("background-color");
        //   alert(bgcolor);
    var prefix = document.getElementById(PEZONE+'_prefix').value;
    var Fvalue = document.getElementById(FIELDID).value;
        // alert(prefix);
    if (prefix > '' && prefix !='off') {prefix = prefix + " ";}
    if (($("#" +FIELDID).css("background-color")=="rgb(245, 245, 220)") || (Fvalue ==''))  {
        $("#" +FIELDID).val(prefix+selection);
        $("#" +FIELDID).css("background-color","#C0C0C0");
    } else {
        if (Fvalue >'') prefix = ", "+prefix;
        $("#" +FIELDID).val(Fvalue + prefix +selection);
        $("#" +FIELDID).css("background-color","#C0C0C0");
            //$("#" +FIELDID).css("background-color","red");
    }
    submit_form(FIELDID);
}

function setformvalues(form_array){
    
        //Run through a list of all objects
    var str = '';
    for(key in form_array) {
        str += key + "=" + encodeURIComponent(form_array[key]) + "&";
    }
        //Then return the string values.
    return str;
}

    //END OF AJAX RELATED FUNCTIONS

function clear_vars() {
    document.eye_mag.var1.value = "white";
    document.eye_mag.var2.value = "white";
}

function dopopup(url) {
    
    top.restoreSession();
    window.open(url, '_blank', 'width=530,height=390,resizable=1,scrollbars=1');
}
function submit_form() {
    var url = "/openemr/interface/forms/eye_mag/save.php?mode=update&id=" + $("#id").val();
    var formData = $("form#eye_mag").serialize();
    $.ajax({
           type 	: 'POST', // define the type of HTTP verb we want to use (POST for our form)
           url 		: url, // the url where we want to POST
           data 	: formData, // our data object
           success : function(result){
           // alert("Tell Me should show this" + result);
           $("#tellme").html(result);
           }
           });
}

function update_PREFS() {
        // get the form data
        // there are many ways to get this data using jQuery (you can use the class or id also)
    var url = "/openemr/interface/forms/eye_mag/save.php";
    var formData = {
        'AJAX_PREFS'            : "1",
        'PREFS_VA'              : $('#PREFS_VA').val(),
        'PREFS_W'               : $('#PREFS_W').val(),
        'PREFS_MR'              : $('#PREFS_MR').val(),
        'PREFS_CR'              : $('#PREFS_CR').val(),
        'PREFS_CTL'             : $('#PREFS_CTL').val(),
        'PREFS_ADDITIONAL'      : $('#PREFS_ADDITIONAL').val(),
        'PREFS_CLINICAL'        : $('#PREFS_CLINICAL').val(),
        'PREFS_EXAM'            : $('#PREFS_EXAM').val(),
        'PREFS_CYL'             : $('#PREFS_CYL').val(),
        'PREFS_EXT_VIEW'        : $('#PREFS_EXT_VIEW').val(),
        'PREFS_ANTSEG_VIEW'     : $('#PREFS_ANTSEG_VIEW').val(),
        'PREFS_RETINA_VIEW'     : $('#PREFS_RETINA_VIEW').val(),
        'PREFS_NEURO_VIEW'      : $('#PREFS_NEURO_VIEW').val()
        
    };
        // process the form
    $.ajax({
           type 		: 'POST', // define the type of HTTP verb we want to use (POST for our form)
           url          : url, // the url where we want to POST
           data 		: formData, // our data object
           //      dataType 	: 'json', // what type of data do we expect back from the server
           //encode     : true,
           
           success      : function(result) {
           $("#tellme").html(result);
           }
           });
        // using the done promise callback
    /*                     .done(function(data) {
     
     // log data to the console so we can see
     console.log(data); 
     
     // here we will handle errors and validation messages
     });
     
     
     alert(url);
     $.post(url,$( "form" ).serialize(),function(data,status){
     $("#HPI").val(data);
     });
     */                  
    /*       can we ajax this result back to the server or just do it when we store this for future charts.
     Send it with submit and tore the prefs in dbSelectFindings
     //SET THE VALUE OF THE FIELD FOR THE DB PREFS
     */                                                                                                            
}
function hide_PRIORS() {
    $("#PRIORS_EXT_left_text").addClass("nodisplay");
    $("#PRIORS_ANTSEG_left_text").addClass("nodisplay");
    $("#PRIORS_RETINA_left_text").addClass("nodisplay");
    $("#PRIORS_NEURO_left_text").addClass("nodisplay");
}
function hide_QPDRAW() {
    $("#EXT_1").removeClass("size100").addClass("size50");
    $("#ANTSEG_1").removeClass("size100").addClass("size50");
    $("#NEURO_1").removeClass("size100").addClass("size50");
    $("#RETINA_1").removeClass("size100").addClass("size50");
    $("#EXT_right").addClass("nodisplay");
    $("#ANTSEG_right").addClass("nodisplay");
    $("#NEURO_right").addClass("nodisplay");
    $("#RETINA_right").addClass("nodisplay");
    $("#ANTSEG_1").removeClass("clear_both");
    $("#RETINA_1").removeClass("clear_both");
    $("#NEURO_1").removeClass("clear_both");
}
function show_QPDRAW() {
    $("#EXT_1").removeClass("size50").addClass("size100");
    $("#ANTSEG_1").removeClass("size50").addClass("size100");
    $("#NEURO_1").removeClass("size50").addClass("size100");
    $("#RETINA_1").removeClass("size50").addClass("size100");
    $("#EXT_right").removeClass("nodisplay");
    $("#ANTSEG_right").removeClass("nodisplay");
    $("#NEURO_right").removeClass("nodisplay");
    $("#RETINA_right").removeClass("nodisplay");
    $("#ANTSEG_1").addClass("clear_both");
    $("#RETINA_1").addClass("clear_both");
    $("#NEURO_1").addClass("clear_both");
    hide_PRIORS();
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
function toggle_visibility(id) {
    var e = document.getElementById(id);
    if(e.style.display == 'block')
        e.style.display = 'none';
    else
        e.style.display = 'block';
}
$(document).ready(function() {
                  // jQuery methods go here...
                 $("input,select,textarea,text").css("background-color","#FFF8DC");
                  $("#IOPTIME").css("background-color","#FFFFFF");
                  $("#refraction_width").css("width","8.5in");
                  //$("#LayerClinical").css("width","8.5in");
                  
                  $(window).resize(function() {
                                   //alert(window.innerWidth);
                                      
                                   //   $("#refraction_width").css("width","4.5in");
                                   //$("#LayerClinical").css("width","3.5in");
                                   if (window.innerWidth >'900') {
                                      $("#refraction_width").css("width","8.5in");
                                      $("#LayerVision2").css("padding","4px");
                                   } 
                                   if (window.innerWidth >'1200') {
                                   $("#refraction_width").css("width","12.8in");
                                   $("#LayerVision2").css("padding","4px");
                                   } 
                                   if (window.innerWidth >'1900') {
                                   $("#refraction_width").css("width","16.8in");
                                   $("#LayerVision2").css("padding","4px");
                                   } 
                                      
                                      });
                  $(window).resize();
               /*   $("#PrintButton").live("click", function () {
                                         var pat = $("#pat_name").html();
                                         $("#wearing_title").html(pat);
                                         $("#signature_W").toggleClass('nodisplay');
                                         
                                         var divContents = $("#wearing").html();
                                         var printWindow = window.open('', '', 'height=600,width=400');
                                         printWindow.document.write("<html><head><title>Rx Glasses</title>");
                                         printWindow.document.write("<link href='../../forms/eye_mag/style.css' rel='stylesheet' type='text/css' />");
                                         printWindow.document.write('</head><body >');
                                         printWindow.document.write(divContents);
                                         printWindow.document.close();
                                         printWindow.print();
                                         $("#wearing_title").html("Current Rx");
                                         $("#signature_W").toggleClass('nodisplay');

                                         });
                  */
                  //set the motility values
                  var hash_tag = '<i class="fa fa-minus">';
                  var index;
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
                  
                  var hash_tag = '<i class="fa fa-minus rotate-left">';
                  
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
                  
                                    $("input[name$='PRISM']").blur(function() {
                                                 //make it all caps
                                                 var str = $(this).val();
                                                 str = str.toUpperCase();
                                                 $(this).val(str);
                                                 });
                  $("input[name$='SPH']").blur(function() {
                                          var mid = $(this).val();
                                          if (!mid.match(/\./)) {
                                          var front = mid.match(/([\+\-]?\d{0,2})(\d{2})/)[1];
                                          var back  = mid.match(/(\d{0,2})(\d{2})/)[2];
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
                                          
                  $("[name$='CYL']").blur(function() {
                                          
                                          var mid = $(this).val();
                                          if (!mid.match(/\./)) {
                                          var front = mid.match(/([\+\-]?\d{0,2})(\d{2})/)[1];
                                          var back  = mid.match(/(\d{0,2})(\d{2})/)[2];
                                          mid = front + "." + back;
                                          }
                                          $(this).val(mid);
                                          if (!$('#PREFS_CYL').val()) {
                                          
                                          $('#PREFS_CYL').val('+');
                                          update_PREFS();
                                          } else {
                                          // alert("PREFS_CYL is ALSO set to "+ $('#PREFS_CYL').val());
                                          }
                                          
                                          if (!mid.match(/^(\+|\-){1}/)) {
                                              //no +/- sign in the field  
                                              //ok so there is a preference set
                                          //if it doesn't start with + or - then give it the preference value
                                          var plusminus = $('#PREFS_CYL').val() + mid;
                                          $(this).val(plusminus);  //set this cyl value to plusminus
                                          } else if (mid.match(/^(\+|\-){1}/)) {
                                              midmatch = mid.match(/^(\+|\-){1}/)[0];
                                              $(this).val(mid);
                                              $('#PREFS_CYL').val(midmatch);
                                              update_PREFS();
                                          //so they used a value + or - in the field. 
                                          //The only reason to work on this is to change to cylinder preference
                                              if ($('#PREFS_CYL').val() != mid.match(/^(\+|\-){1}/)[0]){
                                          //and that is what they are doing here
                                                pref = mid.match(/^(\+|\-){1}/)[0];
                                                $('#PREFS_CYL').val(pref);
                                            }
                                          }
                                          submit_form($(this));
                                          //        alert("Done");
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
                                                    //,classNameToAdd : 'refraction' 
                                                    }, 
                                                    leaveOpen: true,
                                                    printMode: 'popup',
                                                    overrideElementCSS: true,
                                                    overrideElementCSS: ['/openemr/interface/forms/eye_mag/style.css']
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
                                          printElem({ overrideElementCSS: ['/openemr/interface/forms/eye_mag/style.css'] });
                                          });                                    
                  $("input,textarea,text").focus(function(){
                                                        $(this).css("background-color","#ffff99");
                                                        });  
                  
                  $("input,textarea,text,checkbox").change(function(){
                                                       //    .autoSubmit
                                                         //submit_form($(this));
                                                       $(this).css("background-color","#F0F8FF");
                                                       submit_form($(this));

                                                       }); 
                  //we have to write this to work for every PRIORS select div
                  //select only shows up in the PRIORS select actions.
                  // no there are selects in Contact lens section
                  //on hold...
                  
        /*          $("selector").on('change',function(event){
                                   alert ("selector changed");
                                 //  alert($('#id').val() + " " + this.name);
                                 var new_section = this.name.match(/PRIOR_(.*)/);
                                 var newValue = this.value;//eg PRIOR_EXT
                                 //alert(new_section[1]);
                                 //$("#EXT_QP_block1").toggleClass('nodisplay');
                                 //$("#EXT_QP_block2").toggleClass('nodisplay');
                                 $("#PRIORS_"+ new_section[1] +"_left_text").toggleClass('nodisplay');
                                 $("#" + new_section[1] + "_right").toggleClass('nodisplay');
                                 //now go get the prior page via ajax
                                 var url = "/openemr/interface/forms/eye_mag/save.php?mode=retrieve&id=" + $('#id').val();
                                 // alert(newValue);
                                 var formData = {
                                 'PRIORS_query'          : "1",
                                 'zone'                  : new_section[1],
                                 'visit_number'          : $('#PRIOR_EXT').val(),
                                 'visit_date'            : newValue
                                 
                                 };
                                 // alert(formData[0]);
                                 

                                 // process the form
                                 $.ajax({
                                        type 		: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                                        url          : url, // the url where we want to POST
                                        data 		: formData, // our data object
                                        //      dataType 	: 'json', // what type of data do we expect back from the server
                                        //encode     : true,
                                        
                                        success      : function(result) {
                                        
                                        $("#PRIORS_" + new_section[1] + "_left_text").html(result);
                                        }
                                        });
                                 //alert(result);
                                 
                                 
                                 });
                  */
                  $("body").on("click","[name$='_text_view']" , function() {
                               //    alert (" Hello _text_view");
                                                  var header = this.id.match(/(.*)_text_view$/)[1];
                               // alert("over here" +header);
                                                  //PRIORS_EXT_left_text
                                                  //alert($("#PREFS_"+header+"_VIEW").val());
                                                  //PRIORS_EXT_left
                                                  $("#"+header+"_text_list").toggleClass('wide_textarea');
                                                  $("#"+header+"_text_list").toggleClass('narrow_textarea');
                                                  $(this).toggleClass('fa-plus-square-o');
                                                  $(this).toggleClass('fa-minus-square-o');
                                                  //  alert(header);
                                                  // $("#PRIORS_EXT_left").toggleClass('nodisplay');
                                                  if (header != /PRIOR/) {
                                                  
                                                  var imagine = $("#PREFS_"+header+"_VIEW").val();
                                                  imagine ^= true;
                                                  $("#PREFS_"+header+"_VIEW").val(imagine);
                                                  //  alert(imagine);
                                                  update_PREFS();
                                                  }
                                                  });

                  $("body").on("change", "select", function(e){
                               // alert("Hello body change select");
                               var new_section = this.name.match(/PRIOR_(.*)/);
                               var newValue = this.value;//eg PRIOR_EXT
                               $("#PRIORS_"+ new_section[1] +"_left_text").removeClass('nodisplay');
                               $("#" + new_section[1] + "_right").addClass('nodisplay');
                               //now go get the prior page via ajax
                               var url = "/openemr/interface/forms/eye_mag/save.php?mode=retrieve&id=" + $('#id').val();
                               //alert(new_section[1]);
                               
                               if (new_section[1] =="ALL") {
                                    getSection("ALL");
                                    getSection("EXT");
                                    getSection("ANTSEG");
                                    getSection("RETINA");
                                    getSection("NEURO");
                               } else {
                                    getSection(new_section[1]);
                               }
                               
                               function getSection(section) {
                               // alert("here you go "+section);
                               $("#PRIORS_"+ section +"_left_text").removeClass('nodisplay');
                               $("#" + section + "_right").addClass('nodisplay');
                               
                               var formData = {
                               'PRIORS_query'          : "1",
                               'zone'                  : section,
                               'visit_number'          : $('#PRIOR_EXT').val(),
                               'visit_date'            : newValue
                               
                               };
                               //alert(formData[0]);
                               
                               
                               // process the form
                               $.ajax({
                                      type 		: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                                      url          : url, // the url where we want to POST
                                      data 		: formData, // our data object
                                      //      dataType 	: 'json', // what type of data do we expect back from the server
                                      //encode     : true,
                                      
                                      success      : function(result) {
                                      
                                      $("#PRIORS_" + section + "_left_text").html(result);
                                      }
                                      });
                               // alert(result);
                               
                               }

                               //   alert("Goddbye!");
                               });
                  $("body").on("click","[id^='Close_PRIORS_']", function(e) {
                               // alert(this.val());
                               var new_section = this.id.match(/Close_PRIORS_(.*)$/)[1];
                               //  alert("We are closing a PRIORS and reshowing _right " + new_section);
                               //var new_section = $(this).id.match(/Close_PRIORS_(.*)/);
                               //var newValue = this.value;//eg PRIOR_EXT
                               // alert(new_section);
                               $("#PRIORS_"+ new_section +"_left_text").addClass('nodisplay');
                               $("#" + new_section + "_right").removeClass('nodisplay');
                               
                               });
                  $('#PRIOR_EXT').change(function() {
                                         //         alert( "Stop looking here.  PRIOR_EXT.change" );
                                         // $PRIORS_EXT_left_text = 1;
                                                  });
                  
                  //we are requesting an old record: EXT values to be placed in the PRIORS_EXT div.
              /*    $("[id$='_left_text']").click(function(e) {
                                            var $target = $(e.target);
                                            if ($target.hasClass("PRIORS")) {
                                            // do something
                                                var showme = $(this).val();
                                                var zone = $(this).id.match(/PRIOR_(.*)$/)[1];
                                                alert("hi " + showme + " the zone: " + zone);
                                                //hide the current exam, replace it with old, change the background color, top_right the date stamp
                                                //    $("#EXT_1").toggleClass('nodisplay');
                                            }
                                            });
                  $("[id$='_left_text']").on('click','span.PRIORS', function() {
                                             alert(this.id);
                                            var showme = this.value;
                                            var zone = this.id.match(/PRIOR_(.*)$/)[1];
                                            alert("hi " + showme + " the zone: " + zone);
                                            //hide the current exam, replace it with old, change the background color, top_right the date stamp
                                             //  $("#EXT_1").toggleClass('nodisplay');
                                            });
               */
                  $("#pupils").mouseover(function() {
                                         $("#pupils").toggleClass("red");
                                         });
                  
                  $("#pupils").mouseout(function() {
                                        $("#pupils").toggleClass("red");
                                        });
                  
                  
                  $("#pupils").click(function(){
                                     $("#dim_pupils_panel").toggleClass('nodisplay');
                                     });
                  $("#vision_tab").mouseover(function() {
                                             $("#vision_tab").toggleClass("red");
                                             });
                  $("#vision_tab").mouseout(function() {
                                            $("#vision_tab").toggleClass("red");
                                            });
                  $("#vision_tab").click(function(){
                                         $("#LayerVision2").toggle();
                                         ($("#PREFS_VA").val() =='1') ? ($("#PREFS_VA").val('0')) : $("#PREFS_VA").val('1');
                                         });
                  
                  
                  $("#vision_tab2").click(function(){
                                          //  $("#LayerVision2").slideToggle();
                                          //  $("refraction").css("display","none");
                                          });
                  //set wearing to single vision or bifocal? Bifocal
                  $(".WNEAR").show();
                  $("#WNEARODAXIS").hide();
                  $("#WNEARODCYL").hide();
                  $("#WNEARODPRISM").hide();
                  $("#WNEAROSAXIS").hide();
                  $("#WNEAROSCYL").hide();
                  $("#WNEAROSPRISM").hide();
                  $("[name=RX]").val(["1"]);  
                  
                  $("#SingleVision_span").click(function(){
                                                $(".WNEAR").hide();
                                                $(".WSPACER").show();
                                                $("[name=RX]").val(["0"]);  
                                                });
                  $("#Bifocal_span").click(function(){
                                           $(".WSPACER").hide();
                                           $(".WNEAR").show();
                                           $(".WMid").addClass('nodisplay');
                                           $(".WHIDECYL").removeClass('nodisplay');
                                           $("[name=RX]").val(["1"]);   
                                           $("#WNEARODAXIS").hide();
                                           $("#WNEARODCYL").hide();
                                           $("#WNEARODPRISM").hide();
                                           $("#WNEAROSAXIS").hide();
                                           $("#WNEAROSCYL").hide();
                                           $("#WNEAROSPRISM").hide();
                                           
                                           
                                           });
                  $("#Trifocal_span").click(function(){
                                            $(".WSPACER").hide();
                                            $(".WNEAR").show();
                                            $(".WMid").removeClass('nodisplay');
                                            $(".WHIDECYL").addClass('nodisplay');
                                            $("[name=RX]").val(["2"]);   
                                            $("#WNEARODAXIS").hide();
                                            $("#WNEARODCYL").hide();
                                            $("#WNEARODPRISM").hide();
                                            $("#WNEAROSAXIS").hide();
                                            $("#WNEAROSCYL").hide();
                                            $("#WNEAROSPRISM").hide();
                                            });
                  $("#Progressive_span").click(function(){
                                               $(".WSPACER").hide();
                                               $(".WNEAR").show();
                                               $(".WMid").addClass('nodisplay');
                                               $(".WHIDECYL").removeClass('nodisplay');
                                               $("[name=RX]").val(["3"]);   
                                               $("#WNEARODAXIS").hide();
                                               $("#WNEARODCYL").hide();
                                               $("#WNEARODPRISM").hide();
                                               $("#WNEAROSAXIS").hide();
                                               $("#WNEAROSCYL").hide();
                                               $("#WNEAROSPRISM").hide();
                                               });
                  
                  
                  $("#Amsler-Normal").change(function() {
                                             if ($(this).is(':checked')) {
                                             
                                             var number1 = document.getElementById("AmslerOD").src.match(/\d/)[0];
                                             document.getElementById("AmslerOD").src = document.getElementById("AmslerOD").src.replace(/\d/,"0");
                                             
                                             var number2 = document.getElementById("AmslerOS").src.match(/\d/)[0];
                                             document.getElementById("AmslerOS").src = document.getElementById("AmslerOS").src.replace(number2,"0");
                                             $("#AMSLEROD").val("0");
                                             $("#AMSLEROS").val("0");
                                             $("#AmslerODvalue").text("0");
                                             $("#AmslerOSvalue").text("0");
                                             submit_form("eye_mag");
                                             return;
                                             }
                                             //'unchecked' event code
                                             });
                  $("[name^='EXAM']").mouseover(function(){
                                                $(this).toggleClass("borderShadow2");
                                                
                                                });
                  $("[name^='EXAM']").mouseout(function(){
                            $(this).toggleClass("borderShadow2");	
                            });

                  $("#LayerVision, #LayerTension, #LayerMotility, #LayerAmsler, #LayerFields, #LayerPupils,#dim_pupils_panel").mouseover(function(){
                                                                                                            $(this).toggleClass("borderShadow2");
                                                                                                            });
                  $("#LayerVision, #LayerTension, #LayerMotility, #LayerAmsler, #LayerFields, #LayerPupils,#dim_pupils_panel").mouseout(function(){
                                                                                                            $(this).toggleClass("borderShadow2");	
                                                                                                            });
                  $("#LayerVision_W,#LayerVision_MR,#LayerVision_CR,#LayerVision_CTL,#LayerVision_ADDITIONAL,#LayerVision_ADDITIONAL_VISION").mouseover(function(){
                                                                                                            $(this).toggleClass("borderShadow2");
                                                                                                            });
                  $("#LayerVision_W,#LayerVision_MR,#LayerVision_CR,#LayerVision_CTL,#LayerVision_ADDITIONAL,#LayerVision_ADDITIONAL_VISION").mouseout(function(){
                                                                                                            $(this).toggleClass("borderShadow2");	
                                                                                                            });

                  $("#AmslerOD, #AmslerOS").click(function() {
                                                  //alert("1");
                                                  //console.dir(this);
                                                  //console.log("Hello this");
                                                  var number1 = this.src.match(/\d/)[0];
                                                  var number2 = +number1 +1;
                                                  this.src = this.src.replace(number1,number2);
                                                  this.src = this.src.replace('6','0');
                                                  $("#Amsler-Normal").removeAttr('checked');
                                                  var number3 = this.src.match(/\d/)[0];
                                                  this.html =  number3;
                                                  if (number3 =="6") {
                                                  number3 = "0";
                                                  }
                                                  if ($(this).attr("id")=="AmslerOD") {
                                                  //document.getElementById("AmslerODvalue").html(number3);
                                                  $("#AmslerODvalue").text(number3);
                                                  //alert(number3);
                                                  $('#AMSLEROD').val(number3);
                                                  } else {
                                                  $('#AMSLEROS').val(number3);
                                                  $("#AmslerOSvalue").text(number3);
                                                  }
                                                  var title = "#"+$(this).attr("id")+"_tag";
                                                  
                                                  });
                  $("#AmslerOD, #AmslerOS").dblclick(function() {
                                                     //console.dir(this);
                                                     //console.log("Hello this");
                                                     var number1 = this.src.match(/\d/)[0];
                                                     var number2 = +number1 -1;
                                                     this.src = this.src.replace(number1,number2);
                                                     this.src = this.src.replace('-1','6');
                                                     });
                  $("#AmslerOD, #AmslerOS").mouseout(function() {
                                                     submit_form("eye_mag");
                                                     });
                  $("[name^='ODVF'],[name^='OSVF']").click(function() {
                                                           // alert($(this).prop('checked'));
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
                                           //alert("We checked the Normal box");
                                           
                                           if ($(this).is(':checked')) {
                                           // alert("We checked the Normal box");
                                           $("#ODVF1").removeAttr('checked');
                                           $("#ODVF2").removeAttr('checked');
                                           $("#ODVF3").removeAttr('checked');
                                           $("#ODVF4").removeAttr('checked');
                                           $("#OSVF1").removeAttr('checked');
                                           $("#OSVF2").removeAttr('checked');
                                           $("#OSVF3").removeAttr('checked');
                                           $("#OSVF4").removeAttr('checked');
                                           //{
                                           //$("#ODVF1,#ODVF2,#ODVF3,#ODVF4,#OSVF1,#OSVF2,#OSVF3,#OSVF4").checked  = true;
                                           //,#ODVF2,#ODVF3,#ODVF4,#OSVF1,#OSVF2,#OSVF3,#OSVF4
                                           // alert("We checked the Normal box");
                                           //   }
                                           }
                                           });
                  
                  //Part of QP 
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
                  $("#NEURO_prefix").change(function() {
                                                     var newValue = $("#NEURO_prefix").val().replace('+', '');
                                                     if ($(this).value =="off") {$(this).val('');}
                                                     $("[name^='NEURO_prefix_']").removeClass('eye_button_selected');
                                                     $("#NEURO_prefix_"+ newValue).addClass("eye_button_selected");
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
                  $("#LayerVision_W_lightswitch, #LayerVision_CR_lightswitch,#LayerVision_MR_lightswitch,#LayerVision_ADDITIONAL_lightswitch,#LayerVision_CTL_lightswitch").mouseover(function() {  
                                                                                                                                                                                      $(this).addClass('buttonRefraction_selected');
                                                                                                                                                                                      });
                  $("#LayerVision_W_lightswitch, #LayerVision_CR_lightswitch,#LayerVision_MR_lightswitch,#LayerVision_ADDITIONAL_lightswitch,#LayerVision_CTL_lightswitch").mouseout(function() {  
                                                                                                                                                                                     var section2 = this.id.match(/(.*)_(.*)_lightswitch$/)[2];                                                                                                                                                                                   
                                                                                                                                                                                     var elem = document.getElementById("PREFS_"+section2);
                                                                                                                                                                                     
                                                                                                                                                                                     if (elem.value != "1") {                                                                $(this).removeClass('buttonRefraction_selected');
                                                                                                                                                                                     } else { 
                                                                                                                                                                                     $(this).addClass('buttonRefraction_selected');
                                                                                                                                                                                     }                                                                });
                  
                  $("#LayerVision_W_lightswitch, #LayerVision_CR_lightswitch,#LayerVision_MR_lightswitch,#LayerVision_ADDITIONAL_lightswitch,#LayerVision_CTL_lightswitch").click(function() {
                                                              var section = "#"+this.id.match(/(.*)_lightswitch$/)[1];
                                                              var section2 = this.id.match(/(.*)_(.*)_lightswitch$/)[2];                                                                                                                                                                                   
                                                              var elem = document.getElementById("PREFS_"+section2);
                                                              
                                                              if ($("#PREFS_VA").val() !='1') {
                                                                  $("#PREFS_VA").val('1');   
                                                                  $("#LayerVision2").show();
                                                                  elem.value="1";  
                                                                  $(section).removeClass('nodisplay');
                                                                  if (section2 =="ADDITIONAL") {
                                                                      $("#LayerVision_ADDITIONAL_VISION").removeClass('nodisplay');
                                                                  }
                                                                  $(this).addClass("buttonRefraction_selected");                                                                               
                                                              } else { 
                                                                  if (elem.value == "0") {
                                                                      elem.value='1';   
                                                                      if (section2 =="ADDITIONAL") {
                                                                          $("#LayerVision_ADDITIONAL_VISION").removeClass('nodisplay');                                                                   
                                                                      }
                                                                      $(section).removeClass('nodisplay');
                                                                      $(this).addClass("buttonRefraction_selected"); 
                                                                  } else {
                                                                      elem.value='0';
                                                                      $(section).addClass('nodisplay');
                                                                      if (section2 =="ADDITIONAL") {
                                                                          $("#LayerVision_ADDITIONAL_VISION").addClass('nodisplay');                                                                   
                                                                      }
                                                                      $(this).removeClass("buttonRefraction_selected");                                                             
                                                                  }
                                                              }
                                                              update_PREFS();                                                                                                                                                       });
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
                 
                  $("[name^='more_']").click(function() {
                                                  $("#Visions_A").toggleClass('nodisplay');
                                                  $("#Visions_B").toggleClass('nodisplay');
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
                                            $('#ODPERIPH').val('flat without tears, holes or RD').css("background-color","beige");
                                            $('#OSPERIPH').val('flat without tears, holes or RD').css("background-color","beige");
                                            submit_form("eye_mag");
                                            //  alert("Submitted!");
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
                                                //  var elem = document.getElementById("PREFS_"+section);
                                              if (section =="ACTMAIN") {
                                                //$("#"+section).toggleClass('nodisplay');
                                                $("#ACTTRIGGER").trigger( "click" );
                                              } else {                 
                                                $("#LayerVision_"+section+"_lightswitch").click();
                                              }
                                              });
                  
                  $("#MOTILITYORMAL").click(function() {
                                            
                                            //reset all motility measurements to zero if checked
                                            //if not, then leave alone...
                                            
                                            
                                            });
                  $("#EXAM_DRAW").click(function() {
                                        
                                        //   alert("here "+$("#PREFS_CLINICAL").val());
                                        if ($("#PREFS_CLINICAL").value !='0') {
                                        show_QPDRAW(); 
                                        $("#PREFS_CLINICAL").val('0');
                                        update_PREFS();
                                        }
                                        //alert($("#PREFS_EXAM").value);
                                        if ($("#PREFS_EXAM").value != 'DRAW') {
                                        $("#PREFS_EXAM").val('DRAW');
                                        $("#DrawExt").show();
                                        $("#DrawAntSeg").show();
                                        $("#DrawRetina").show();
                                        $("#QPExt").hide();
                                        $("#QPAntSeg").hide();
                                        $("#QPRetina").hide();
                                        $("#EXAM_QP").removeClass('button_selected');
                                        //.css("border","#000000").css("color", "yellow");  
                                        $("#EXAM_DRAW").addClass('button_selected');
                                        //.removeClass('button_selected');
                                        $("#EXAM_CLINICAL").removeClass('button_selected');
                                        /*.removeClass('button_selected');
                                        $("#EXAM_DRAW").css("border","#000000").css("color", "yellow");  
                                        $("#EXAM_QP").removeClass('button_selected');
                                        $("#EXAM_CLINICAL").removeClass('button_selected');*/
                                        update_PREFS();
                                        }
                                        
                                        
                                        });
                  $("#EXAM_QP").click(function() {
                                      //      alert($("#PREFS_CLINICAL").value);
                                      if ($("#PREFS_CLINICAL").value !='0') { 
                                      // when ==0 the draw or QP panel is shown, 1 just the text fields
                                      show_QPDRAW();
                                      hide_PRIORS();
                                      $("#PREFS_CLINICAL").val('0');
                                      update_PREFS();
                                      }

                                      //      alert("Hello EXAM_QP 1");
                                      if ($("#PREFS_EXAM").value != 'QP') {
                                      $("#PREFS_EXAM").val('QP');
                                      $("#EXAM_QP").addClass('button_selected');
                                      //.css("border","#000000").css("color", "yellow");  
                                      $("#EXAM_DRAW").removeClass('button_selected');
                                      //.removeClass('button_selected');
                                      $("#EXAM_CLINICAL").removeClass('button_selected');
                                      //.removeClass('button_selected');
                                      update_PREFS();
                                      $("#DrawExt").hide();
                                      $("#DrawAntSeg").hide();
                                      $("#DrawRetina").hide();
                                      $("#QPExt").show();
                                      $("#QPAntSeg").show();
                                      $("#QPRetina").show();
                                      }
                                                                            });
                  
                  $("#EXAM_CLINICAL").click(function() {
                                            //     alert("hi");
                                            //($("#PREFS_CLINICAL").val() =='1') ? ($("#PREFS_CLINICAL").val('0')) : $("#PREFS_CLINICAL").val('1');
                                            
                                            if ($("#PREFS_CLINICAL").val() !='1') { //we want to show TEXT
                                            $("#PREFS_CLINICAL").val('1');
                                            $("#PREFS_EXAM").val('TEXT');                                   
                                            hide_QPDRAW();
                                            hide_PRIORS();
                                            update_PREFS(); 
                                            }
                                            $("#EXAM_DRAW").removeClass('button_selected');
                                            $("#EXAM_QP").removeClass('button_selected');
                                            //css("border","white").css("color", "white");  
                                            //$("#EXAM_QP").removeClass('button_selected');
                                            //$("#EXAM_CLINICAL").css("border","#000000").css("color", "yellow");
                                            $("#EXAM_CLINICAL").addClass('button_selected');
                                            /* so now we need to save the default view - 50 or 100
                                             check to what state one of them is in and save that to hidden value for EXAM_CLINICAL
                                             */
                                            
                                            });
                  $("#EXAM_CLINICAL").addClass('button_selected');//css("border","#000000").css("color", "yellow");
                  
                  if ($("#PREFS_CLINICAL").val() !='1') {
                  var actionQ = "#EXAM_"+$("#PREFS_EXAM").val();
                  // alert(actionQ);
                  $(actionQ).trigger('click');
                  //$("#EXAM_QP").val("1").trigger('click');
                  } else {
                  $("#EXAM_CLINICAL").addClass('button_selected');//css("border","#000000").css("color", "yellow");
                  }
                  if ($("#ANTSEG_prefix").val() > '') {
                  $("#ANTSEG_prefix_"+$("#ANTSEG_prefix").val()).addClass('button_selected');
                  } else {
                  $("#ANTSEG_prefix").val('off').trigger('change');
                  }  
                  
                  $("[name^='ACT_tab_']").click(function()  {
                                            var section = this.id.match(/ACT_tab_(.*)/)[1];
                                                    //var section2 = this.id.match(/(.*)_(.*)_lightswitch$/)[2];                                                                                                                                                                                   
                                                    //var elem = document.getElementById("PREFS_"+section2);
                                                    //    alert(section);
                                            $("[name^='ACT_']").addClass('nodisplay');
                                                   $("[name^='ACT_tab_']").removeClass('nodisplay').removeClass('ACT_selected').addClass('ACT_deselected');
                                                    $("#ACT_tab_" + section).addClass('ACT_selected').removeClass('ACT_deselected');
                                                    $("#ACT_" + section).removeClass('nodisplay');
                                            });
                  $("#ACTTRIGGER").mouseover(function() {
                                             $("#ACTTRIGGER").toggleClass("red");
                                             
                                             });
                  $("#ACTTRIGGER").mouseout(function() {
                                             $("#ACTTRIGGER").toggleClass("red");
                                             });
                  $("#ACTTRIGGER").click(function() {
                                         $("#ACTMAIN").toggleClass('nodisplay'); //.toggleClass('fullscreen');
                                         $("#NPCNPA").toggleClass('nodisplay');
                                         $("#ACTNORMAL_CHECK").toggleClass('nodisplay');
                                         $("#ACTTRIGGER").toggleClass('underline');
                                         $("#Close_ACTMAIN").toggleClass('fa-random').toggleClass('fa-eye');
                                         
                  });
                  /* Now it is time to figure out how to blow-up each section for a tablet for example to fill the screen and look good */
                  $("[name^='MAX_']").click(function() {
                                            
                                            alert("This button will allow the user to enter a fullscreen mode useful for tablet operations.  It needs to be written yet but essentially it will present the data in a format specific to the device's screen size...");
                                            
                                            //let's add a class to make this frame fullscreen
                                            //var section = this.id.match(/MAX_(.*)/)[1];
                                            //$("#"+ section + "_left").toggleClass('fullscreen');
                                            
                                            //to show the prior visits on screen using the selector script scroller
                                            //click this and toggle class nodisplay for id=PRIORS_NEURO_1 and NEURO_left
                                            //  $("#PRIORS_NEURO_1").toggleClass('nodisplay');
                                            //  $("#NEURO_left").toggleClass('nodisplay');
                                            //we have to get the data to put here!
                                            
                                            });
                  $("#NEURO_COLOR").click(function() {
                                          $("#ODCOLOR").val("11/11");
                                          $("#OSCOLOR").val("11/11");
                                          submit_form("eye_mag");
                                          });
                  
                  $("#NEURO_COINS").click(function() {
                                          $("#ODCOINS").val("1.00"); //leave currency symbol out unless it is an openEMR defined option
                                          $("#OSCOINS").val("1.00");
                                          submit_form("eye_mag");
                                          });
                  
                  $("#NEURO_REDDESAT").click(function() {
                                          $("#ODREDDESAT").val("100");
                                          $("#OSREDDESAT").val("100");
                                          submit_form("eye_mag");
                                          });
          
                  
                  $("#construction").click(function() {
                                           //   alert("OVER HERE");
                                           $("[id^='CONSTRUCTION_']").toggleClass('nodisplay');
                                           });
                  
         /*          $(".fancybox2").fancybox({
                                          helpers : {
                                          overlay : {
                                          css : {
                                          'background' : 'rgba(58, 42, 45, 0.95)'
                                          }
                                          }
                                          }
                                          });
                  $(".fancybox").fancybox({
                                          openEffect: 'none',
                                          closeEffect: 'none',
                                          afterShow: function() {
                                          $('<div class="expander"></div>').appendTo(this.inner).click(function() {
                                                                                                       $(document).toggleFullScreen();
                                                                                                       });
                                          },
                                          afterClose: function() {
                                          $(document).fullScreen(false);
                                          }
                                          });
          */
                  $(document).bind("fullscreenerror", function() {
                                   alert("Browser rejected fullscreen change");
                                   });
                  window.addEventListener("beforeunload", function (e) {
                                          submit_form(e);
/*                                          var confirmationMessage = "\o/";
                                          
                                          (e || window.event).returnValue = confirmationMessage; //Gecko + IE
                                          return confirmationMessage;                            //Webkit, Safari, Chrome
  */
                                          });
                
});



