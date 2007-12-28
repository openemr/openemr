<?php
// Copyright (C) 2007 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// Allows acl(php-gacl) administration. Heavily ajax and
// javascript/jquery dependent. All ajax functions are called
// from adminacl_ajax.php
//
include_once("../globals.php");
include_once("$srcdir/acl.inc");

//ensure user has proper access
if (!acl_check('admin', 'acl')) {
 echo "(" . xl('ACL Administration Not Authorized') . ")";
 exit;
}	
//ensure phpgacl is installed
if (!isset($phpgacl_location)) {
 echo "(" . xl('PHP-gacl is not installed') . ")";
 exit;
}	
?>
		
<html>
<head>
 <script type="text/JavaScript" src="../../library/js/jquery121.js"></script>
 <script type="text/JavaScript" src="../../library/js/jquery.livequery101.js"></script>	
 <script type="text/JavaScript">
	
 $(document).ready(function(){	
  
  //Show membership section by default
  $("#membership_show").click();
  membership_show();
  //Show membership section by default
				
  $("a.link_submit").livequery("click", function(){	
   generic_click(this);
   return false;
  });
  
  $("input.button_submit").livequery("click", function(){	
   generic_click(this);
   return false;
  });

  $("#membership_show").livequery("click", function(){
   membership_show();
   return;
  });	

  $("#acl_show").livequery("click", function(){
   acl_show();
   return;
  });	
  
  $("input.button_acl_add").livequery("click", function(){
   //if Clear, then reset form
   if (this.value == "Clear") {
    $("#acl_error").empty();
    $("#div_acl_add_form span.alert").empty();
    return;
   }	
   //if Cancel, then reset/hide form and show create/remove acl links
   if (this.value == "Cancel") {
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
     $("#acl_error").append("<span class='alert'>ERROR, unable to collect data from server<br></span>");
     $("#acl_error").show();
    }
   });
   return false;	
  });
  
  $("input.button_acl_remove").livequery("click", function(){	
   //if Clear, then reset form
   if (this.value == "Clear") {
    $("#acl_error").empty();
    $("#div_acl_remove_form span.alert").empty();
    return;
   }	
   //if Cancel, then reset/hide form and show create/remove acl links
   if (this.value == "Cancel") {
    $("#div_acl_remove_form").hide("slow");
    $("#acl_error").empty();
    $("#div_acl_remove_form span.alert").empty();
    $("#none_acl_returns").show();
    $("#none_acl_list").show();
    return;
   }
   //Ensure confirmed before deleting group
   confirmDelete = $("input[@name=acl_remove_confirm]:checked").val();
   if (confirmDelete == "no") { //send confirm alert and exit
    $("#remove_confirm_error").empty();
    $("#remove_confirm_error").append("Select Yes to confirm group deletion");    
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
     $("#acl_error").append("<span class='alert'>ERROR, unable to collect data from server<br></span>");
     $("#acl_error").show();
    }	
   });
   return false;
  });
  	    
  function membership_show() {		
   if (!$("#membership_show").attr("checked")) {
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
      $("#membership").append("<div id='link_" + username + "'><span class='text'>" + username + "</span><a class='link_submit' href='no_javascript' id='" + username + "_membership_list' title='Edit " + username + "'>(Edit)</a></span><a class='link_submit' href='no_javascript' id='" + username +  "_membership_hide' style='display: none' title='Hide " + username + "'>(Hide)</a><span class='alert' style='display: none;'>&nbsp;&nbsp;This user is not a member of any group!!!</span><span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LOADING...</span></div><div id='error_" + username + "'></div><div id='" + username +  "' style='display: none'><table class='lists' border='1' bgcolor='white' cellpadding='3' cellspacing='2'><tr><td align='center'><span class='bold'>Active</span></td><td align='center'><span class='bold'>Inactive</span></td></tr><tr><td align='center'><select name='active[]' multiple></select><br /><p align='center'><input class='button_submit' type='button' title='Remove' id='" + username  + "_membership_remove' value=' >> '></p></td><td align='center'><select name='inactive[]' multiple></select><br /><p align='center'><input class='button_submit' type='button' title='Add' id='" + username + "_membership_add' value=' << ' ></p></td></tr></table></div>");
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
     $("#membership_error").append("<span class='alert'>ERROR, unable to collect data from server<br><br></span>");
     $("#membership_error").show();
    }	
   });
   return;	
  }
		
  function acl_show() {
   if (!$("#acl_show").attr("checked")) {	
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
      title = $(this).find("title").text();
      titleDash = title.replace(" ","-");
      return_value = $(this).find("return").text();
      note = $(this).find("note").text();
      $("#acl").append("<div id='acl_link_" + titleDash + "_" + return_value + "'><span class='text' title='" + note  + "'>" + title + "-" + return_value  + "</span><a class='link_submit' href='no_javascript' id='" + titleDash  + "_aco_list_" + return_value  + "' title='Edit " + title + "-" + return_value  + "'>(Edit)</a></span><a class='link_submit' href='no_javascript' id='" + titleDash + "_acl_hide_" + return_value + "' style='display: none' title='" + title + "'>(Hide)</a><span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LOADING...</span></div><div id='acl_error_" + titleDash + "_" + return_value + "'></div><div id='acl_" + titleDash + "_" + return_value  + "' style='display: none'><table border='1' bgcolor='white' cellpadding='3' cellspacing='2'><tr><td align='center'><span class='bold'>Active</span></td><td align='center'><span class='bold'>Inactive</span></td></tr><tr><td align='center'><select name='active[]' size='6' multiple></select><br /><p align='center'><input class='button_submit' type='button' title='Remove' id='" + titleDash  +"_aco_remove_" + return_value  + "' value=' >> '></p></td><td align='center'><select name='inactive[]' size='6' multiple></select><br /><p align='center'><input class='button_submit' type='button' title='Add' id='" + titleDash  + "_aco_add_" + return_value  + "' value=' << ' ></p></td></tr></table></div>");	
     });
     //Show the acl list and add link. Remove loading indicator.
     $("#acl").show("slow");
     $("#acl_edit span.loading:first").hide();
     $("#none_acl_returns").show();
     $("#none_acl_list").show();	
    },
    beforeSend: function(){
     //Show Loading indicator
     $("#acl_edit span.loading:first").show();
    },
    error:function(){
     //Remove Loading indicator and previous error, if any, then show error
     $("#acl_edit span.loading:first").hide();
     $("#acl_error").empty();
     $("#acl_error").append("<span class='alert'>ERROR, unable to collect data from server<br><br></span>");
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
    contentPointer = "#" + identity;
    linkPointer = "#link_" + identity;
    linkPointerPost ="";
    errorPointer = "#error_" + identity;
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
        $(contentPointer + " select").append("<option>" + $(this).text() + "</option>");	
       });
      }
      else if (action == "list") {
       $(xml).find("acl").each(function(){
	$(contentPointer + " select").append("<option>" + $(this).find("title").text() + "-" + $(this).find("return").text()  + "</option>");
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
       $(contentPointer + " select:first").append("<option>" + $(this).text() + "</option>");
       counterActive = counterActive + 1;
      });
      $(xml).find("inactive").find("group").each(function(){
	$(contentPointer + " select:last").append("<option>" + $(this).text() + "</option>");
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
     $(errorPointer).append("<span class='alert'>ERROR, unable to collect data from server<br></span>");
     $(errorPointer).show();
    }	
   });
  return;
  }    				     
 });
 </script>
		
 <link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
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
   font-weight:	bold;
  }
  .alert {
   font-family:	sans-serif;
   font-size: 10pt;
   color: red;
   font-weight:	bold;
  }			
  .section {
  border: solid;
  border-width: 1px;
  border-color: #0000ff;
  margin: 0 0 10pt 10pt;
  padding: 5pt;	
  }
 </style>	
</head>

<body<?php echo $top_bg_line;?>>
 <span class='title'><?php xl('Access Control List Administration','e'); ?></span>
 <br><br>	
 <div id='membership_edit'>
  <span class=bold><input type='checkbox' id='membership_show'><?php xl('User Memberships','e'); ?></span>
  <span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php xl('LOADING','e'); ?>...</span>
  <div id='membership_error'>
  </div>
  <div class=section id='membership' style='display: none;'>
  </div>
 </div>
 <div id='acl_edit'>
  <span class=bold><input type='checkbox' id='acl_show'><?php xl('Groups and Access Controls','e'); ?></span>
  <a class='link_submit' href='no_javascript' id='none_acl_returns' title='Add New Group' style='display: none;'>(<?php xl('Add New Group','e'); ?>)</a>
  <a class='link_submit' href='no_javascript' id='none_acl_list' title='Remove Group' style='display: none;'>(<?php xl('Remove Group','e'); ?>)</a>  
  <span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php xl('LOADING','e'); ?>...</span>
  <div id='acl_error'>
  </div>
  <div id='div_acl_add_form' style='display: none;'>
   <form class="section" id="acl_add_form" action="no_javascript" method="post">
    <span class='bold'>New Group Information</span><span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php xl('LOADING','e'); ?>...</span>
    <table>
     <tr>
      <td>
       <span class='text'><?php xl('Title','e'); ?>:</span>
      </td>
      <td>	 
       <input type="text" id="title_field"><td><span class="alert" id="title_error"></span></td>
      </td>
     </tr>
     <tr>
      <td>
       <span class='text'><?php xl('Identifier(one word)','e'); ?>:</span>
      </td>
      <td>
       <input type="text" id="id_field"><td><span class="alert" id="identifier_error"></span></td>
      </td>
     </tr>
     <tr>
      <td>
       <span class='text'><?php xl('Return Value','e'); ?>:</span>
      </td>
      <td>
       <select id="return_field"></select><td><span class="alert" id="return_error"></span></td>
      </td>
     </tr>
     <tr>
      <td>
       <span class='text'><?php xl('Description','e'); ?>:</span>
      </td>
      <td>
       <input type="text" id="desc_field"><td><span class="alert" id="description_error"></span></td>	
      </td>
     </tr>
    </table>
    <input type="submit" class="button_acl_add" title="Submit" value="Submit">
    <input type="reset" class="button_acl_add" title="Clear" value="Clear">
    <input type="reset" class="button_acl_add" id="button_acl_add_cancel" title="Cancel" value="Cancel"> 
   </form>  
  </div>
  <div id='div_acl_remove_form' style='display: none;'>
   <form class="section" id="acl_remove_form" action="no_javascript" method="post">
    <span class='bold'>Remove Group Form</span><span class='loading' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php xl('LOADING','e'); ?>...</span>
    <table>
     <tr>
      <td align="right">
       <span class='text'><?php xl('Group','e'); ?>:</span>
      </td>
      <td>
       <select id="acl_field"></select><td><span class="alert" id="aclTitle_error"></span></td>
      </td>
     </tr>
     <tr>
      <td>
       <span class='text'><?php xl('Do you really want to delete this group','e'); ?>?</span>
      </td>
      <td>
	<input type="radio" name="acl_remove_confirm" value = "yes"><span class='text'><?php xl('Yes','e'); ?></span>
	<input type="radio" name="acl_remove_confirm" value = "no" checked><span class='text'><?php xl('No','e'); ?></span>
	<td><span class="alert" id="remove_confirm_error"></span></td>
      </td>
     </tr>
    </table>
    <input type="submit" class="button_acl_remove" title="Delete" value="Delete">
    <input type="reset" class="button_acl_remove" id="button_acl_remove_cancel" title="Cancel" value="Cancel">
   </form>
  </div>
  <div class=section id='acl' style='display: none;'>
  </div>
 </div>
</body>
</html>
