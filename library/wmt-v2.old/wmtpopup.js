// Copyright (C) 2013 Williams Medical Techonologies
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// open a new cascaded window
function wmtCascWin(url, winname, width, height, options) {
 var mywin = window.parent ? window.parent : window;
 var newx = 25, newy = 25;
 var userestore= false;
 var numargs= arguments.length;
 if(numargs > 5) {
	userestore= arguments[5];
 }
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
 if(width == 'max') (width = screen.availWidth * .9);
 if(height == 'max') (height = screen.availHeight * .9);
 if(isNaN(width)) {
  if(width.substr(-1) == '%') {
    width = (width.slice(0,-1) / 100);
    width = (screen.availWidth * width);
  }
 }
 if(isNaN(height)) {
  if(height.substr(-1) == '%') {
   height = (height.slice(0,-1) / 100);
   height = (screen.availHeight * height);
  }
 }
 if(userestore) {
	top.restoreSession();
 }	

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
function wmtGrabFocus(w) {
 for (var i = 0; i < w.frames.length; ++i) wmtGrabFocus(w.frames[i]);
 w.onfocus = top.imfocused;
}

// call this when a "modal" dialog is desired
function wmtOpen(url, winname, width, height) {
 var restore= false;
 var numargs= arguments.length;
 if(numargs > 4) {
	restore= arguments[4];
 }

 if (top.modaldialog && ! top.modaldialog.closed) {
  if (window.focus) top.modaldialog.focus();
  if (top.modaldialog.confirm("OK to close this other popup window?")) {
   top.modaldialog.close();
   top.modaldialog = null;
  } else {
   return false;
  }
 }
 top.modaldialog = wmtCascWin(url, winname, width, height,
  "resizable=yes,scrollbars=yes,location=no,toolbar=no,menubar=yes,titlebar=no,status=no", restore);
 wmtGrabFocus(top);
 return false;
}
