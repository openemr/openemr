// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
function moveOptions_11(theSelFrom, theSelTo){
    document.getElementById(theSelFrom).style.color="red";
    document.getElementById(theSelFrom).style.fontStyle="italic";
    var str=document.getElementById(theSelFrom).innerHTML;
    if(window.frames[0].document.body.innerHTML=='<br>')
    window.frames[0].document.body.innerHTML="";
    var patt=/\?\?/;
    var result=patt.test(str);
    if(result){
        url = 'quest_popup.php?content='+str;
        window.open(url,'quest_pop','width=640,height=190,menubar=no,toolbar=0,location=0, directories=0, status=0,left=400,top=375');
        //dlgopen(url,'quest_pop', '', 640, 190);
    }
    else{
        val = str;
       CKEDITOR.instances.textarea1.insertText(val);
    }
}
function movePD(val,theSelTo){
    var textAreaContent = window.frames[0].document.body.innerHTML;
    var textFrom = val;
    if(textAreaContent != '')
    textAreaContent += "  "+textFrom;
    else
    textAreaContent += textFrom;
    window.frames[0].document.body.innerHTML=textAreaContent;
}
function edit(id){
    val=window.opener.document.getElementById(id).value;
    arr=val.split("|*|*|*|");
    document.getElementById('textarea1').value=arr[0];
}
function ascii_write(asc, theSelTo){
    var is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
    var is_ie = navigator.userAgent.toLowerCase().indexOf('msie') > -1;
    var is_apple = navigator.userAgent.toLowerCase().indexOf('apple') > -1;
    if(asc == 13)
    {
        if(!is_ie){
        var plugin = CKEDITOR.plugins.enterkey,
		enterBr = plugin.enterBr,
        editor=CKEDITOR.instances.textarea1;
        forceMode = editor.config.forceEnterMode;
        mode = editor.config.enterMode;
        editor.fire( 'saveSnapshot' );	// Save undo step.
                enterBr( editor, mode, null, forceMode );
                if(is_chrome || is_apple)
                enterBr( editor, mode, null, forceMode );
        }
        else{
            CKEDITOR.instances.textarea1.insertText('\r\n');
        }
    }
    else{
        if (asc == 'para'){
        var textFrom = "\r\n\r\n";
        CKEDITOR.instances.textarea1.insertText(textFrom);
        if(is_chrome || is_apple)
        CKEDITOR.instances.textarea1.insertText(textFrom);
        }
        else
        {
            if (asc == 32)
            var textFrom = "  ";
            else
            var textFrom = String.fromCharCode(asc);
            CKEDITOR.instances.textarea1.insertText(textFrom);
        }
    }
}
function SelectToSave(textara){
    var textAreaContent = window.frames[0].document.body.innerHTML;
    mainform=window.opener.document;
    if(mainform.getElementById(textara+'_div'))
    mainform.getElementById(textara+'_div').innerHTML = textAreaContent;
    if(mainform.getElementById(textara+'_optionTD') && document.getElementById('options'))
    mainform.getElementById(textara+'_optionTD').innerHTML =document.getElementById('options').innerHTML;
    if(mainform.getElementById(textara)){
    mainform.getElementById(textara).value = textAreaContent;
    if(document.getElementById('options'))
    mainform.getElementById(textara).value +="|*|*|*|"+document.getElementById('options').innerHTML;
    }
    dlgclose();
}
function removeHTMLTags(strInputCode){
            /*
                    This line is optional, it replaces escaped brackets with real ones,
                    i.e. < is replaced with < and > is replaced with >
            */
            strInputCode = strInputCode.replace(/&(lt|gt);/g, function (strMatch, p1){
                    return (p1 == "lt")? "<" : ">";
            });
            var strTagStrippedText = strInputCode.replace(/<\/?[^>]+(>|$)/g, "");
}
function TemplateSentence(val){
    if(val){
        document.getElementById('share').style.display='';
    }
    else{
        document.getElementById('share').style.display='none';
    }
    $.ajax({
    type: "POST",
    url: "ajax_code.php",
    dataType: "html",
    data: {
         templateid: 	val
    },
    success: function(thedata){
                //alert(thedata)
                document.getElementById('template_sentence').innerHTML = thedata;
                },
    error:function(){
        //alert("fail");
    }
   });
   return;
}
function delete_item(id){
    //alert(id);
    if(confirm("Do you really wants to delete this?")){
    $.ajax({
    type: "POST",
    url: "ajax_code.php",
    dataType: "html",
    data: {
         templateid: document.getElementById('template').value,
         item: id,
         source: "delete_item"
    },
    success: function(thedata){
                //alert(thedata)
                document.getElementById('template_sentence').innerHTML = thedata;
                },
    error:function(){
        //alert("fail");
    }
   });
   return;
    }
    return false;
}
function add_item(){
    document.getElementById('new_item').style.display='';
    document.getElementById('item').focus();
}
function cancel_item(id){
    if(document.getElementById('new_item'))
    document.getElementById('new_item').style.display='none';
    if(document.getElementById('update_item'+id))
    document.getElementById('update_item'+id).style.display='none';
}
function save_item(){
    $.ajax({
    type: "POST",
    url: "ajax_code.php",
    dataType: "html",
    data: {
         item: 	document.getElementById('item').value,
         templateid: document.getElementById('template').value,
         source: "add_item"

    },
    success: function(thedata){
                //alert(thedata)
                document.getElementById('template_sentence').innerHTML = thedata;
                cancel_item('');
                },
    error:function(){
        //alert("fail");
    }
   });
   return;
}
function update_item_div(id){
    document.getElementById('update_item'+id).style.display='';
    document.getElementById('update_item_txt'+id).focus();
}
function update_item(id){
    $.ajax({
    type: "POST",
    url: "ajax_code.php",
    dataType: "html",
    data: {
         item: 	id,
         templateid: document.getElementById('template').value,
         content: document.getElementById('update_item_txt'+id).value,
         source: "update_item"

    },
    success: function(thedata){
                //alert(thedata)
                document.getElementById('template_sentence').innerHTML = thedata;
                cancel_item(id);
                },
    error:function(){
        //alert("fail");
    }
   });
   return;
}
