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

/*
* @summary Responsive dialog modal for tabs interface.
* Currently for iframe w/wo header. Still uses opener and window close and resides in top frame.
*
* @param string url iframe Content location.
* @param string winname Already opened frame name. (depreciate)
* @param number width|modalType For sizing: an int will be converted to a percentage of view port width.
* @param number height Ignored for now.
* @param boolean forceNewWindow Force using a native window.
* @param string title If exist then header with title is created otherwise no header and content only.
* @returns none.
* */
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

        /* Depreciate 11/5/17 sjp.
        var dialogDiv = top.$("#dialogDiv");
            var dlgIframe = {};
            if (winname !== "_blank") {
            dlgIframe = dialogDiv.find("iframe[name='" + winname + "']");
            }
            else {
            winname = dialogID();
        }
        */

        winname = dialogID();
        var mHeight, mWidth, mSize, msSize, dlgDivContainer;

        // Convert legacy dialog size to percentages and/or css classes.
        var sizeChoices = ['modal-sm', 'modal-md', 'modal-mlg', 'modal-lg', 'modal-xl'];
        if (Number.isInteger(width)) {
            width = Math.abs(width);
            mWidth = (width / top.window.innerWidth * 100).toFixed(3) + '%';
            msSize = '<style>.modal-custom' + winname + ' {width:' + mWidth + ';}</style>';
            mSize = 'modal-custom' + winname;
        } else if ($.inArray(width, sizeChoices) !== -1) {
            mSize = width; // is a modal class
        } else {
            msSize = 'default'; // standard B.S. modal default (modal-md)
        }

        if (mSize === 'modal-sm') {
            msSize = '<style>.modal-sm {width:25%;}</style>';
        } else if (mSize === 'modal-md') {
            msSize = '<style>.modal-md {width:50%;}</style>';
        } else if (mSize === 'modal-mlg') {
            msSize = '<style>.modal-mlg {width:60%;}</style>';
        } else if (mSize === 'modal-lg') {
            msSize = '<style>.modal-lg {width:75%;}</style>';
        } else if (mSize === 'modal-xlg') {
            msSize = '<style>.modal-xl {width:96%;}</style>';
        }

        // Guess at initial responsive height. @TODO Make option for fixed modal size.
        var vpht = top.window.innerHeight;
        mHeight = height > 0 ? (height / vpht * 100).toFixed(4) + 'vh' : '';

        // Build modal html
        var mTitle = title > "" ? '<h4>' + title + '</h4>' : ''; // For now !title = !header and modal full height. Emulates legacy.

        var waitHtml =
            '<div class="loadProgress text-center">' +
            '<span class="fa fa-circle-o-notch fa-spin fa-3x text-primary"></span>' +
            '</div>';

        var headerhtml =
            ('<div class="modal-header">%title%</div>')
                .replace('%title%', mTitle);

        var mhtml =
            ('<div id="%id%" class="modal fade dialogModal" tabindex="-1" role="dialog">%sStyle%' +
                '<div class="modal-dialog %szClass%" role="document">' +
                '<div class="modal-content">' +
                '%head%' +
                '<div class="closeDlgIframe" data-dismiss="modal" ></div>' +
                '<div class="modal-body" style="height:%initHeight%;margin:auto;padding:0 2px;max-height:90vh%;overflow-y:auto;">' +
                '<iframe id="mIframe" class="modalIframe" name="%winname%" frameborder=0 ' +
                'style="width:100%;height:100%;overflow-y:auto;display:block;" src="%url%">' +
                '</iframe></div></div></div></div>')
                .replace('%winname%', winname)
                .replace('%id%', winname)
                .replace('%head%', mTitle !== "" ? headerhtml : "")
                .replace('%url%', fullURL)
                .replace('%szClass%', mSize)
                .replace('%initHeight%', mHeight) // May have use later for options.
                .replace('%sStyle%', msSize !== "default" ? msSize : ''); // default is bootstrap's default.

        // Write modal html to top window where opener can manage.
        dlgDivContainer = top.$(mhtml);
        dlgDivContainer.attr("name", winname);
        top.$("body").append(dlgDivContainer);

        // let opener array know about us.
        top.set_opener(winname, window);

        // Setup events needed to Calc size, detect window.close, cleanup and auto size modal height on show.
        $(function () { // DOM Ready.
            var modalwin = top.$('body').find("[name='" + winname + "']");
            modalwin.on('load', function (e) {
                // Larger dialog content may not be completely parsed/rendered even though load event is fired.
                // Need a little extra time. Adjust here if you encounter auto height default on long frame content.
                // Not sure why this is but, needed to get accurate content height for auto sizing!!
                //
                setTimeout(function () {
                    SizeModaliFrame(e); // auto size
                }, 250);

            });

            top.$('#' + winname).on('show.bs.modal', function () {

                $('div.modal-dialog', this).css({'margin': '15px auto'});

            });

            top.$('#' + winname).on('shown.bs.modal', function () {
                // Remove waitHtml spinner/loader etc.
                $(this).parent().find('div.loadProgress')
                    .fadeOut(function () {
                        $(this).remove();
                    });

                // Using jquery-ui plug-in that is loaded in main.php header class.
                // Cursor styles are in tab sheets.
                //
                top.$('.modal-content', this).resizable({
                    alsoResize: top.$('div.modal-body', this)
                });
                top.$('.modal-dialog', this).draggable();

                top.$('#' + winname).modal('handleUpdate'); // allow for scroll bar

            });

            // Remove modal html on close. Event is fired from modal close x or window.close()
            // via include_opener.js where the iframe is destroyed.
            //
            top.$('#' + winname).on('hidden.bs.modal', function () {
                console.log('Modal hidden then removed!');
                $(this).remove();

            });

            // Show Modal
            top.$('#' + winname).modal({backdrop: 'static', keyboard: true}, 'show');

        });


    };

}

/*
* oeModal(url, winname, width, height, title, opts)
*
* @summary Stackable, resizable and draggable responsive ajax/iframe dialog modal.
*
* @param {url} string Content location.
* @param {String} winname If set becomes modal id and/or iframes name. Or, one is created/assigned(iframes).
* @param {Number| String} width|modalSize(modal-xlg) For sizing: an number will be converted to a percentage of view port width.
* @param {Number} height Initial height. For iframe auto resize starts here.
* @param {String} title If exist then header with title is created otherwise no header and content only.
* @param {Object} opts Dialogs options.
* @returns {Object} dialog object reference.
* */
function oeModal(url, winname, width, height, title, opts) {

    // turn off both these if you don't want to have to load jquery-ui.
    opts.allowDrag = opts.allowDrag ? opts.allowDrag : false; // default on.
    opts.allowResize = opts.allowResize ? opts.allowResize : false; // default off

    // Not sure this is needed as it once was.
    // Several core timers refresh session anyway.
    //
    //top.restoreSession();

    var mHeight, mWidth, mSize, msSize, $dlgContainer, fullURL; // a growing list...

    var where = opts.type === 'iframe' ? top : window;
    where.opener = window;

    if (opts.url) {
        fullUrl = opts.url;
    }
    if (url[0] === "/") {
        fullURL = url
    }
    else {
        fullURL = window.location.href.substr(0, window.location.href.lastIndexOf("/") + 1) + url;
    }

    if (!winname) {
        winname = dialogID();
    }

    // Convert dialog size to percentages and/or css class.
    var sizeChoices = ['modal-sm', 'modal-md', 'modal-mlg', 'modal-lg', 'modal-xl'];
    if (Number.isInteger(width)) {
        width = Math.abs(width);
        mWidth = (width / window.innerWidth * 100).toFixed(4) + '%';
        msSize = '<style>.modal-custom' + winname + ' {width:' + mWidth + ';}</style>';
        mSize = 'modal-custom' + winname;
    } else if ($.inArray(width, sizeChoices) !== -1) {
        mSize = width; // is a modal class
    } else {
        msSize = 'default'; // standard B.S. modal default (modal-md)
    }

    if (mSize === 'modal-sm') {
        msSize = '<style>.modal-sm {width:25%;}</style>';
    } else if (mSize === 'modal-md') {
        msSize = '<style>.modal-md {width:40%;}</style>';
    } else if (mSize === 'modal-mlg') {
        msSize = '<style>.modal-mlg {width:55%;}</style>';
    } else if (mSize === 'modal-lg') {
        msSize = '<style>.modal-lg {width:75%;}</style>';
    } else if (mSize === 'modal-xlg') {
        msSize = '<style>.modal-xl {width:96%;}</style>';
    }

    // Initial responsive height.
    var vpht = where.innerHeight;
    mHeight = height > 0 ? (height / vpht * 100).toFixed(4) + 'vh' : '';

    // Build modal template
    var mTitle = title > "" ? '<h4 class=modal-title>' + title + '</h4>' : ''; // For now !title = !header and modal full height.

    var waitHtml =
        '<div class="loadProgress text-center">' +
        '<span class="fa fa-circle-o-notch fa-spin fa-3x text-primary"></span>' +
        '</div>';

    var headerhtml =
        ('<div class="modal-header">' +
            '<span><i class="close fa fa-close fa-1x fa-border fa-pull-right" data-dismiss=modal aria-hidden="true"></i></span>%title%</div>')
            .replace('%title%', mTitle);
    var frameHead =
        ('<span><i class="close fa fa-close fa-border fa-1x fa-pull-right" data-dismiss=modal aria-hidden="true"></i></span>');

    var frameHtml =
        ('<iframe id="mIframe" class="modalIframe" name="%winname%" frameborder=0 ' +
            'style="width:100%;height:100%;overflow-y:auto;display:block;" src="%url%"></iframe>')
            .replace('%winname%', winname)
            .replace('%url%', fullURL);

    var bodyStyles = (' style="height:%initHeight%;margin:auto;padding:0 5px;max-height:100vh%;overflow-y:auto;"')
        .replace('%initHeight%', mHeight);

    var mhtml =
        ('<div id="%id%" class="modal fade dialogModal" tabindex="-1" role="dialog">%sStyle%' +
            '<div %dialogId% class="modal-dialog %szClass%" role="document">' +
            '<div class="modal-content">' +
            '%head%' +
            '<div class="modal-body" %bodyStyles%>%wait%' +
            '%body%' +
            '</div></div></div></div>')
            .replace('%id%', winname)
            .replace('%sStyle%', msSize !== "default" ? msSize : '')
            .replace('%dialogId%', opts.dialogId ? ('id="' + opts.dialogId + '"') : '')
            .replace('%szClass%', mSize ? mSize : '')
            .replace('%head%', mTitle !== "" ? headerhtml : frameHead)
            .replace('%wait%', '') // maybe option later
            .replace('%bodyStyles%', bodyStyles)
            .replace('%body%', opts.type === 'iframe' ? frameHtml : '');

    // Write modal template.
    //
    $dlgContainer = where.$(mhtml);
    $dlgContainer.attr("name", winname);
    if (opts.buttons) {
        $dlgContainer.find('.modal-content').append(buildFooter());
    }
    if (opts.type !== 'iframe') {
        var params = {
            type: opts.type || '', // get/post but if empty and has data object then post else get.
            data: opts.data || opts.html || '', // ajax loads fetched content or supplied html. think alerts.
            url: opts.url || fullURL,
            dataType: opts.dataType || '' // xml/json/text etc.
        };

        dialogAjax(params, $dlgContainer);
    }

    // Write the completed template to calling document or 'where' window.
    where.$("body").append($dlgContainer);

    $(function () { // DOM Ready. Handle events and cleanup.

        if (opts.type === 'iframe') {
            var modalwin = where.$('body').find("[name='" + winname + "']");
            $('div.modal-dialog', modalwin).css({'margin': '15px auto'});
            modalwin.on('load', function (e) {
                setTimeout(function () {
                    SizeModaliFrame(e, height);
                }, 150);
            });
        }

        where.$('#' + winname).on('show.bs.modal', function () {
            if (opts.allowResize) {
                $('.modal-content', this).resizable({
                    alsoResize: $('div.modal-body', this)
                });
            }

            if (opts.allowDrag) {
                $('.modal-dialog', this).draggable();
            }

            where.$('#' + winname).modal('handleUpdate'); // allow for scroll bar
        });

        where.$('#' + winname).on('shown.bs.modal', function () {
            // Remove waitHtml spinner/loader etc.
            $(this).parent().find('div.loadProgress')
                .fadeOut(function () {
                    $(this).remove();
                });
        });

        // Remove modal html on close.
        //
        where.$('#' + winname).on('hidden.bs.modal', function () {
            console.log('Modal hidden then removed!');
            $(this).remove();
        });

        // define this dialog close() function.
        where.oeModalClose = function (id) { // @TODO add close by dialogs id.
            $dlgContainer.modal('hide').off('hide.bs.modal');
            return false;
        };

        // Show Modal @todo move to load/done event after ajax/iframe promise.
        where.$('#' + winname).modal({backdrop: 'static', keyboard: true}, 'show'); // @todo add backdrop/keyboard to options

        return $dlgContainer; // return the dialog ref. looking towards deferring...

    }); // end events

    function dialogAjax(data, $dialog) {
        var params = {
            async: true,
            url: data.url || data,
            dataType: data.dataType || 'text'
        };

        if (data.url) {
            $.extend(params, data);
        }

        $.ajax(params)
            .done(aOkay)
            .fail(oops);

        return true;

        function aOkay(html) {
            $dialog.find('.modal-body').html(data.success ? data.success(html) : html);

            return true;
        }

        function oops(r, s) {
            var msg = data.error ?
                data.error(r, s, params) :
                '<div class="alert alert-danger">' +
                '<strong><?php echo xlt("XHR Failed:") ?> </strong> [ ' + params.url + '].' + '</div>';

            $dialog.find('.modal-body').html(msg);

            return false;
        }
    }

    function buildFooter() {
        if (opts.buttons === false) {
            return '';
        }
        var oFoot = $('<div>').addClass('modal-footer').prop('id', 'oefooter');
        if (opts.buttons) {
            for (var i = 0, k = opts.buttons.length; i < k; i++) {
                var btnOp = opts.buttons[i];
                var btn = $('<button>').addClass('btn btn-' + (btnOp.style || 'primary'));

                for (var index in btnOp) {
                    if (btnOp.hasOwnProperty(index)) {
                        switch (index) {
                            case 'close':
                                //add close event
                                if (btnOp[index]) {
                                    btn.attr('data-dismiss', 'modal')
                                        .addClass('closeBtn');
                                }
                                break;
                            case 'click':
                                //binds button to click event of fn defined in calling document/form
                                var fn = btnOp.click.bind($dlgContainer.find('.modal-content'));
                                btn.click(fn);
                                break;
                            case 'text':
                                btn.html(btnOp[index]);
                                break;
                            default:
                                //all other possible HTML attributes to button element
                                btn.attr(index, btnOp[index]);
                        }
                    }
                }

                oFoot.append(btn);
            }
        } else {
            //if no buttons defined by user, add a standard close button.
            oFoot.append('<button class="closeBtn btn btn-default" data-dismiss=modal type=button><?php echo xlt("Close") ?></button>');
        }

        return oFoot; // jquery object of modal footer.
    }

}

function SizeModaliFrame(e, minSize) {

    var idoc = e.currentTarget.contentDocument ? e.currentTarget.contentDocument : e.currentTarget.contentWindow.document;
    $(e.currentTarget).parent('div.modal-body').css({'height': ''});
    var viewPortHt = Math.max(top.window.document.documentElement.clientHeight, top.window.innerHeight || 0);
    var frameContentHt = Math.max($(idoc).height(), idoc.body.offsetHeight || 0) + 25;
    frameContentHt = frameContentHt < minSize ? minSize : frameContentHt;
    var hasHeader = $(e.currentTarget).parents('div.modal-content').find('div.modal-header').length;
    var hasFooter = $(e.currentTarget).parents('div.modal-content').find('div.modal-footer').length;
    size = (frameContentHt / viewPortHt * 100).toFixed(4);
    var maxsize = hasHeader ? 90 : hasFooter ? 87.5 : 96;
    maxsize = hasHeader && hasFooter ? 84 : maxsize;
    maxsize = maxsize + 'vh';
    size = size + 'vh'; // will start the dialog as responsive. Any resize by user turns dialog to absolute positioning.

    $(e.currentTarget).parent('div.modal-body').css({'height': size, 'max-height': maxsize}); // Set final size. Width was previously set.

    console.log('Modal loaded and sized! Content:' + frameContentHt + ' Viewport:' + viewPortHt + ' Modal height:' +
        size + ' Max height:' + maxsize + ' isHeader:' + (hasHeader > 0 ? 'True' : 'False') + ' isFooter:' + (hasFooter > 0 ? 'True' : 'False'));

    return size; // may be better to set size from calling scope..
}


