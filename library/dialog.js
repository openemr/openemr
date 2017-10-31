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

// Call this when a "modal" dialog is desired.
// Note that the below function is used for the
// frames ui, and that a separate dlgopen function
// is used below (see if(top.tab_mode)...) for the tabs ui.
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

// This is called from del_related() which in turn is invoked by find_code_dynamic.php.
// Deletes the specified codetype:code from the indicated input text element.
function my_del_related(s, elem, usetitle) {
  if (!s) {
    // Deleting everything.
    elem.value = '';
    if (usetitle) {
      elem.title = '';
    }
    return;
  }
  // Convert the codes and their descriptions to arrays for easy manipulation.
  var acodes  = elem.value.split(';');
  var i = acodes.indexOf(s);
  if (i < 0) {
    return; // not found, should not happen
  }
  // Delete the indicated code and description and convert back to strings.
  acodes.splice(i, 1);
  elem.value = acodes.join(';');
  if (usetitle) {
    var atitles = elem.title.split(';');
    atitles.splice(i, 1);
    elem.title = atitles.join(';');
  }
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

if (top.tab_mode) {
    dlgOpenWindow = dlgopen;
    dlgopen = function (url, winname, width, height, forceNewWindow, title) {
        top.restoreSession();

        if (forceNewWindow) {
            return dlgOpenWindow(url, winname, width, height);
        }

        var fullURL;
        if (url[0] === "/") {
            fullURL = url
        }
        else {
            fullURL = window.location.href.substr(0, window.location.href.lastIndexOf("/") + 1) + url;
        }

        var dialogDiv = top.$("#dialogDiv");
        var dlgIframe = {};
        if (winname !== "_blank") { // @TODO Check if bug below
            // even if a frame is given this dlgIframe was over written by dlgIframe below.
            // a bug or not needed any longer ... check for intent or where/how used.
            dlgIframe = dialogDiv.find("iframe[name='" + winname + "']");
        }
        else {
            winname = dialogID();
        }

        // dlgIframe = top.$("<iframe></iframe>");
        // dlgIframe.attr("name", winname);

        // Convert legacy dialog size to percentages and css classes.
        if(width < 401) mSize = 'modal-sm';
        else if(width < 801) mSize = 'modal-md';
        else if(width < 1001) mSize = 'modal-lg';
        else mSize = 'modal-xl';
        //var mSize = 'modal-xl'; // test
        if(mSize == 'modal-sm') msSize = '<style>.modal-sm{width:20%;}</style>';
        else if(mSize == 'modal-md') msSize = '<style>.modal-md{width:40%;}</style>';
        else if(mSize == 'modal-lg') msSize = '<style>.modal-lg{width:60%;}</style>';
        else msSize = '<style>.modal-xl{width:96%;}</style>';

        // Build modal html
        title = title > "" ? title : "OpenEMR";
        var mTitle = "<h4>"+title+"</h4>";
        var waitHtml = '<div class="loadProgress text-center"><span><h5>Loading</h5>'+
            '<span class="fa fa-circle-o-notch fa-spin fa-3x text-primary"></span>'+
            '</span></div>';
        var mhtml = ('<div id="dialogModal" class="modal fade" tabindex="-1" role="dialog">%99%' +
            '<div class="modal-dialog %3%"><div class="modal-content"><div class="modal-header">%4%%1%</div>'+
            '<div class="closeDlgIframe" data-dismiss="modal" ></div>'+
            /*'<div class="modal-body"></div>' +*/
            '<iframe id="mIframe" class="embed-responsive-item modalIframe" name="%0%" frameborder=0 '+
                'style="width:100%;height:87.5vh;overflow-y:auto;display:block;" src="%2%"></iframe>' +
            '</div></div></div>')
            .replace('%0%', winname)
            .replace('%1%', waitHtml)
            .replace('%2%', fullURL)
            .replace('%3%', mSize)
            .replace('%4%', mTitle)
            .replace('%99%', msSize !== "default" ? msSize : '');

        var dlgDivContainer = top.$(mhtml);
        dlgDivContainer.attr("name", winname);
        top.$("body").append(dlgDivContainer);

        top.set_opener(winname, window); // @TODO Leave for now. Not sure this is needed any longer!

        // Setup events needed to detect a window.close, cleanup and auto size modal height on show.
        top.$("body").find("[name='" + winname + "']").on('load', function (e) {
            var fht = this.contentDocument.activeElement.offsetHeight;
            var vpht = top.window.innerHeight;
            var size = (fht / vpht * 100) + 5;
            this.style.maxHeight = '95vh';
            this.style.height = size + 'vh';

            $(this).parent().find('div.loadProgress')
                .fadeOut(function () {
                    $(this).remove();
                });

            this.contentWindow.onunload = function (e) {
                top.$('#dialogModal').modal('hide');
                console.log('window close event fired!');
            };
        });

        // Setup resize and drag
        top.$('.modal-content').resizable({
            alsoResize: ".modalIframe",
            classes: {
                "ui-resizable": "highlight"
            }
        });
        top.$('.modal-dialog').draggable();

        top.$('#dialogModal').on('show.bs.modal', function () {
            $(this).find('.modal-content').css({
                'max-height': '100%'
            });
        });

        // Remove modal html on close.
        top.$('#dialogModal').on('hidden.bs.modal', function (e) {
            top.$('.modal-content').resizable( "destroy" );
            $(this).remove();
        });

        // Show Modal
        top.$('#dialogModal').modal()

    };

}
