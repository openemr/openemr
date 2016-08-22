// Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

var modaldialog = null;

// called from onfocus handler of various documents:
function imfocused() {
 if (modaldialog) {
  if (modaldialog.closed) {
   modaldialog = null;
  } else {
   if (window.focus) modaldialog.focus();
  }
 }
}

// call this from the top-level frameset's or body's onunload
function imclosing() {
 if (modaldialog && ! modaldialog.closed) modaldialog.close();
 modaldialog = null;
}
