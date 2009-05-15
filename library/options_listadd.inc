<?php
/* This include is just a HTML and JS *fragment* it CANNOT stand alone
 * 
 * This file is used to include the capability to have a pop-up DIV in which
 * a user can add a new option to a list box. Primarily this is for the 
 * demographics, history, and referral screens. This code could be used elsewhere
 * as long as it had the required supporting HTML and JS
 *
 * REQUIRED to make work:
 *  - jQuery
 *  - form element with the class '.addtolist'
 *  - be used in a file that already includes globals.php
 */
?>

<!-- hidden DIV used for creating new list items -->
<div id="newlistitem" class="body_top" style="border: 2px outset #666; display:none; position:absolute; padding:5px; ">
<form id="newlistitem_form" action="" style="margin:0px; padding:5px; display:inline;">
<input type="hidden" name="newlistitem_listid" id="newlistitem_listid" value="">
<input type="textbox" name="newlistitem_value" id="newlistitem_value" size="25" maxlength="50">
<input type="button" name="newlistitem_submit" id="newlistitem_submit" value="Add">
<input type="button" name="newlistitem_cancel" id="newlistitem_cancel" value="Cancel">
</form>
</div>

<script language="javascript">

// jQuery makes life easier (sometimes)

$(document).ready(function(){

    /********************************************************/
    /************ List-box modification functions ***********/
    /********************************************************/
   
    $("#newlistitem_form").keypress(function(evt) { if (evt.keyCode == 13) { SaveNewListItem(this, evt); return false; }});
    $(".addtolist").click(function(evt) { AddToList(this, evt); });
    $("#newlistitem_submit").click(function(evt) { SaveNewListItem(this, evt); });
    $("#newlistitem_cancel").click(function(evt) { CancelAddToList(this, evt); });

    // display the 'new list item' DIV at the mouse position
    var AddToList = function(btnObj, e) {
        // make the item visible before setting its x,y location
        $('#newlistitem').css('display', 'inline');
        $('#newlistitem_value').val("");
        //getting height and width of the message box
        var height = $('#newlistitem').height();
        var width = $('#newlistitem').width();
        //calculating offset for displaying popup message
        leftVal=e.pageX-(width/2)+"px";
        topVal=e.pageY-(height/2)+"px";
        //show the DIV and set cursor focus
        $('#newlistitem').css({left:leftVal,top:topVal}).show();
        $('#newlistitem_value').focus();
        // capture the ID of the list being modified from the object's ID
        $('#newlistitem_listid').val($(btnObj).attr("id").replace(/^addtolistid_/g, ""));
    };
   
    // hide the add-to-list DIV and clear its textbox
    var CancelAddToList = function(btnObj, e) {
        $('#newlistitem').hide();
    }
    
    // save the new list item to the given list
    var SaveNewListItem = function(btnObj, e) {
        // the group name field can only have letters, numbers, spaces and underscores
        // AND it cannot start with a number
        if ($("#newlistitem_value").val().match(/\W/)) {
            alert("List items can only contain letters, numbers, and spaces.");
            $("#newlistitem_value").focus();
            return false;
        }

        // make the AJAX call to save the new value to the specified list
        // upon returning successfully, refresh the list box and select 
        // the new list item
        $.getJSON("<?php echo $GLOBALS['webroot']; ?>/library/ajax/addlistitem.php",
                    {listid: $("#newlistitem_listid").val(), newitem: $('#newlistitem_value').val()},
                    function(jsondata, txtresponse) { 
                        var listboxname = ($('#addtolistid_'+$("#newlistitem_listid").val()).attr("fieldid"));
                        var listbox = document.getElementById(listboxname);
                        while (listbox.options.length > 0) { listbox.options[0] = null; }

                        $.each(jsondata.options, function () { 
                            listbox.options[listbox.options.length] = new Option(this.title, this.id);
                            if (this.title == $("#newlistitem_value").val()) {
                                listbox.selectedIndex = (listbox.options.length-1);
                            }
                        });
        
                        // now hide the DIV
                        $('#newlistitem').hide();
                    }
                );

    }  // end SaveNewListItem

}); // end jQuery .ready 
</script>

