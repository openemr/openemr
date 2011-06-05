// Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// See interface/reports/players_report.php for an example of
// implementing tooltips with this module.

// Helper functions.
function ttGetX(elem) {
 var x = 0;
 while(elem != null) {
  x += elem.offsetLeft;
  elem = elem.offsetParent;
 }
 return x;
}
function ttGetY(elem) {
 var y = 0;
 while(elem != null) {
  y += elem.offsetTop;
  elem = elem.offsetParent;
 }
 return y;
}

var ttTimerId = 0;
var ttElem = null;
var ttobject = null;
var ttUrl = '';

function ttClearTimer() {
 if (ttTimerId) {
  clearTimeout(ttTimerId);
  ttTimerId = 0;
  ttElem = null;
  ttUrl = '';
 }
}

// timer completion handler
function ttMake() {
 ttobject = document.getElementById("tooltipdiv");
 // ttobject.innerHTML = (ttTitle.length > 0) ? ttTitle : 'Loading...';
 ttobject.innerHTML = '&nbsp;';
 var x = ttGetX(ttElem);
 var dw = window.innerWidth ? window.innerWidth - 20 : document.body.clientWidth;
 if (dw && dw < (x + ttobject.offsetWidth)) {
  x = dw - ttobject.offsetWidth;
  if (x < 0) x = 0;
 }
 // var y = ttGetY(ttElem) - ttobject.offsetHeight - 10;
 // if (y < 0) y = ttGetY(ttElem) + ttElem.offsetHeight + 10;
 var dh = window.innerHeight ? window.innerHeight : document.body.clientHeight;
 // var y = ttGetY(ttElem) + ttobject.offsetHeight + 10;
 var y = ttGetY(ttElem) + ttElem.offsetHeight;
 if (y + 40 > dh) y = 0;
 ttobject.style.left = x;
 ttobject.style.top  = y;
 ttobject.style.visibility='visible';
 // if (!ttElem.ttTitle) {
  myUrl = ttUrl;
  $.getScript(ttUrl);
 // }
 ttTimerId = 0;
 ttElem = null;
}

// onmouseover handler
function ttMouseOver(elem, url) {
 ttClearTimer();
 ttElem = elem;
 ttUrl = url;
 ttTimerId = setTimeout("ttMake()", 250);
 return false;
}

// onmouseout handler.
function ttMouseOut() {
 ttClearTimer();
 var ttobject = document.getElementById("tooltipdiv");
 ttobject.style.visibility='hidden';
 ttobject.style.left = '-1000px';
}

