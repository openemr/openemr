// Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// open a new cascaded window
function cascwin(url, winname, width, height, options) {
 var mywin = window.parent ? window.parent : window;
 var newx = 25, newy = 25;
 if (!isNaN(mywin.screenX)) {
  newx += mywin.screenX;
  newy += mywin.screenY;
 } else if (!isNaN(mywin.screenLeft)) {
  newx += mywin.screenLeft;
  newy += mywin.screenTop;
 }
 if ((newx + width) > screen.width || (newy + height) > screen.height) {
  newx = 0;
  newy = 0;
 }
 top.restoreSession();

 // MS IE version detection taken from
 // http://msdn2.microsoft.com/en-us/library/ms537509.aspx
 // to adjust the height of this box for IE only -- JRM
 if (navigator.appName == 'Microsoft Internet Explorer')
 {
    var ua = navigator.userAgent;
    var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
    if (re.exec(ua) != null)
    rv = parseFloat( RegExp.$1 ); // this holds the version number
    height = height + 28;
 }

retval=window.open(url, winname, options +
 ",width="   + width + ",height="  + height +
 ",left="    + newx  + ",top="     + newy   +
 ",screenX=" + newx  + ",screenY=" + newy);
  
return retval;
}
// recursive window focus-event grabber
function grabfocus(w) {
 for (var i = 0; i < w.frames.length; ++i) grabfocus(w.frames[i]);
 w.onfocus = top.imfocused;

 // the following was tried and discarded because it's too invasive and
 // does not help anyway, but i left it here for the curious.
 //
 // for (var i = 0; i < w.document.forms.length; ++i) {
 //  var e = w.document.forms[i].elements;
 //  for (var j = 0; j < e.length; ++j) {
 //   e[j].onfocus = top.imfocused;
 //  }
 // }
}

// call this when a "modal" dialog is desired

 
 function dlgopen(url, winname, width, height) {
 if (top.modaldialog && ! top.modaldialog.closed) {
  if (window.focus) top.modaldialog.focus();
  if (top.modaldialog.confirm(top.oemr_dialog_close_msg)) {
   top.modaldialog.close();
   top.modaldialog = null;
  } else {
   return false;
  }
 }
 top.modaldialog = cascwin(url, winname, width, height,
  "resizable=1,scrollbars=1,location=0,toolbar=0");
 grabfocus(top);
 return false;
}


function dialogID()
{
  function s4() {
    return Math.floor((1 + Math.random()) * 0x10000)
      .toString(16)
      .substring(1);
  }
  return s4() + s4() + s4() + s4() + s4() + + s4() + s4() + s4();
}

if(top.tab_mode)
{
    dlgOpenWindow=dlgopen;
    dlgopen=function(url,winname,width,height,forceNewWindow)
    {
        if(forceNewWindow)
        {
            return dlgOpenWindow(url,winname,width,height);
        }
        width=width+80;
        height=height+80;
        var fullURL;
        if(url[0]==="/")
        {
            fullURL=url
        }
        else
        {
            fullURL=window.location.href.substr(0,window.location.href.lastIndexOf("/")+1)+url;
        }
        var dialogDiv=top.$("#dialogDiv");
        var dlgIframe={};
        if(winname!=="_blank")
        {
            dlgIframe=dialogDiv.find("iframe[name='"+winname+"']");
        }
        else
        {
            winname=dialogID();
        }


        dlgIframe=top.$("<iframe></iframe>");
        dlgIframe.attr("name",winname);
                   
        var dlgDivContainer=top.$("<div class='dialogIframe'></div>");
        var closeDlg=top.$("<div class='closeDlgIframe'></div>");
        dlgDivContainer.append(closeDlg);
        closeDlg.click(function()
        {
            var body=top.$("body");
            var closeItems=body.find("[name='"+winname+"']");
            closeItems.remove();
            if(body.children("div.dialogIframe").length===0)
            {   
                dialogDiv.hide();
            };            
        })
        dlgDivContainer.attr("name",winname);
        dlgDivContainer.append(dlgIframe);
        dlgDivContainer.css({"left":(top.$("body").width()-width)/2
                       ,"top": "5em"
                       ,"height":height
                       ,"width":width});
        
        top.$("body").append(dlgDivContainer);
        top.set_opener(winname,window);
        dlgIframe.get(0).src=fullURL;

        dialogDiv.show();


    }
}