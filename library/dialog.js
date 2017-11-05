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
    if (navigator.appName == 'Microsoft Internet Explorer') {
        var ua = navigator.userAgent;
        var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null)
            rv = parseFloat(RegExp.$1); // this holds the version number
        height = height + 28;
    }

    retval = window.open(url, winname, options +
        ",width=" + width + ",height=" + height +
        ",left=" + newx + ",top=" + newy +
        ",screenX=" + newx + ",screenY=" + newy);

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
    if (top.modaldialog && !top.modaldialog.closed) {
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
    var acodes = elem.value.split(';');
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

function dialogID() {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
    }

    return s4() + s4() + s4() + s4() + s4() + +s4() + s4() + s4();
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
        if (winname !== "_blank") {
            dlgIframe = dialogDiv.find("iframe[name='" + winname + "']");
        }
        else {
            winname = dialogID();
        }

        var mHeight, mSize, msSize, dlgDivContainer;

        // Convert legacy dialog size to percentages and css classes.
        var sizeChoices = ['modal-sm', 'modal-md', 'modal-lg', 'modal-xl'];
        if (Number.isInteger(width)) {
            width = Math.abs(width);
            if (width < 401) {
                mSize = 'modal-sm';
            } else if (width < 701) {
                mSize = 'modal-md';
            } else if (width < 901) {
                mSize = 'modal-lg';
            } else {
                mSize = 'modal-xl';
            }
        } else if ($.inArray(width, sizeChoices)) {
            mSize = width;
        } else {
            msSize = 'default'
        }

        if (mSize === 'modal-sm') {
            msSize = '<style>.modal-sm{width:25%;}</style>';
        } else if (mSize === 'modal-md') {
            msSize = '<style>.modal-md{width:50%;}</style>';
        } else if (mSize === 'modal-lg') {
            msSize = '<style>.modal-lg{width:70%;}</style>';
        } else {
            msSize = '<style>.modal-xl{width:96%;}</style>';
        }

        // Guess at initial responsive height. @TODO Make option for fixed called size.
        mHeight = (height / top.window.innerHeight * 100) + 5 + 'vh';
        console.log('Modal init Asked:' + height + ' Calc height:' + mHeight + ' Viewport height:' + top.window.innerHeight);

        // Build modal html
        title = title > "" ? title : "OpenEMR"; // @TODO maybe get title from document meta.
        var mTitle = '<h4>' + title + '</h4>';
        var waitHtml =
            '<div class="loadProgress text-center">' +
            '<span class="fa fa-circle-o-notch fa-spin fa-3x text-primary"></span>' +
            '</div>';
        var mhtml =
            ('<div id="dialogModal" class="modal fade dialogModal" tabindex="-1" role="dialog">%99%' +
                '<div class="modal-dialog %3%" role="document">' +
                '<div class="modal-content">' +
                '<div class="modal-header">%4%%1%</div>' +
                '<div class="closeDlgIframe" data-dismiss="modal" ></div>' +
                '<div class="modal-body" style="height:%5%;">' +
                '<iframe id="mIframe" class="modalIframe" name="%0%" frameborder=0 ' +
                'style="width:100%;height:100%;overflow-y:auto;display:block;" src="%2%">' +
                '</iframe></div></div></div></div>')
                .replace('%0%', winname)
                .replace('%1%', waitHtml)
                .replace('%2%', fullURL)
                .replace('%3%', mSize)
                .replace('%4%', mTitle)
                .replace('%5%', '0') // May have use for later for options.
                .replace('%99%', msSize !== "default" ? msSize : '');

        dlgDivContainer = top.$(mhtml);
        dlgDivContainer.attr("name", winname);
        top.$("body").append(dlgDivContainer);

        top.set_opener(winname, window);

        var modalwin = top.$('body').find("iframe[name='" + winname + "']");

        // Setup events needed to detect a window.close, cleanup and auto size modal height on show.
        modalwin.on('load', function (e) {
            // Larger dialog documents are not completely rendered even though load event is fired.
            // Need a little extra time. Adjust here if you encounter auto height default on long dialog content.
            setTimeout(function () {
                SizeModal(e); // auto size
            }, 700);

        });

        top.$('#dialogModal').on('shown.bs.modal', function () {
            // Remove spinner/loader
            $(this).parent().find('div.loadProgress')
                .fadeOut(function () {
                    $(this).remove();
                });
            // Setup resize and drag. Used jquery-ui plug-in that is setup in main.php header.
            top.$('.modal-content').resizable({
                alsoResize: ".modalIframe"
            });
            top.$('.modal-dialog').draggable();

            top.$('#dialogModal').modal('handleUpdate'); // allow for scroll bar
        });

        // Remove modal html on close. Hide is fired from include_opener.js where the frame is destroyed.
        top.$('#dialogModal').on('hidden.bs.modal', function () {
            console.log('Modal dialog hide and cleanup event fired!');
            $(this).remove();
        });

        // Show Modal
        top.$('#dialogModal').modal({backdrop: 'static', keyboard: true}, 'show');

    };

}

// Do sizing based on view port and frame content length.
function SizeModal(e) {
    var idoc = e.currentTarget.contentDocument ? e.currentTarget.contentDocument : e.currentTarget.contentWindow.document;

    var frameContentHt = idoc.body.offsetHeight + 10; // add 10 for margins
    var viewPortHt = top.window.innerHeight;
    var size = (frameContentHt / viewPortHt * 100) + 5; // scale to content plus 5% padding.

    if (size < 25) {
        size = 25; // set a min height.
    } else if (size > 87.5) {
        size = 87.5; // set max height
    }

    size = size + 'vh'; // to start the dialog as resposive. Any resize by user turns dialog to absolute positioning.

    top.$('.modal-body').css({'max-height': '90%', 'height': size}); // max-height needed for resizing.

    console.log('Frame loaded and sized! Content height:' + frameContentHt + ' Viewport height:' + viewPortHt);

    return;
}
