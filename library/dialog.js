// Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
// Copyright (C) 2018 Jerry Padgett <sjpadgett@gmail.com>
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

// Call this when a "modal" windowed dialog is desired.
// Note that the below function is free standing for either
// ui's.Use dlgopen() for responsive b.s modal dialogs.
// Can now use anywhere to cascade natives...12/1/17 sjp
//
function dlgOpenWindow(url, winname, width, height) {
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
* function includeScript(url, async)
*
* @summary Dynamically include JS Scripts or Css.
*
* @param {string} url file location.
* @param {boolean} async true/false load asynchronous/synchronous.
* @param {string} 'script' | 'link'.
*
* */
function includeScript(url, async, type) {

    try {
        let rqit = new XMLHttpRequest();
        if (type === "link") {
            let headElement = document.getElementsByTagName("head")[0];
            let newScriptElement = document.createElement("link")
            newScriptElement.type = "text/css";
            newScriptElement.rel = "stylesheet";
            newScriptElement.href = url;
            headElement.appendChild(newScriptElement);
            console.log('Needed to load:[ ' + url + ' ] For: [ ' + location + ' ]');
            return false;
        }

        rqit.open("GET", url, async); // false = synchronous.
        rqit.send(null);

        if (rqit.status === 200) {
            if (type === "script") {
                let headElement = document.getElementsByTagName("head")[0];
                let newScriptElement = document.createElement("script");
                newScriptElement.type = "text/javascript";
                newScriptElement.text = rqit.responseText;
                headElement.appendChild(newScriptElement);
                console.log('Needed to load:[ ' + url + ' ] For: [ ' + location + ' ]');
                return false; // in case req comes from a submit form.
            }
        }

        throw new Error('<?php echo xlt("Failed to get URL:") ?>' + url);

    }
    catch (e) {
        throw e;
    }

}

// test for and/or remove dependency.
function inDom(dependency, type, remove) {
    let el = type;
    let attr = type === 'script' ? 'src' : type === 'link' ? 'href' : 'none';
    let all = document.getElementsByTagName(el)
    for (let i = all.length; i > -1; i--) {
        if (all[i] && all[i].getAttribute(attr) != null && all[i].getAttribute(attr).indexOf(dependency) != -1) {
            if (remove) {
                all[i].parentNode.removeChild(all[i]);
                console.log("Removed from DOM: " + dependency)
                return true
            } else {
                return true;
            }
        }
    }
    return false;
}

/*
* function dlgopen(url, winname, width, height, forceNewWindow, title, opts)
*
* @summary Stackable, resizable and draggable responsive ajax/iframe dialog modal.
*
* @param {url} string Content location.
* @param {String} winname If set becomes modal id and/or iframes name. Or, one is created/assigned(iframes).
* @param {Number| String} width|modalSize(modal-xlg) For sizing: an number will be converted to a percentage of view port width.
* @param {Number} height Initial height. For iframe auto resize starts here.
* @param {boolean} forceNewWindow Force using a native window.
* @param {String} title If exist then header with title is created otherwise no header and content only.
* @param {Object} opts Dialogs options.
* @returns {Object} dialog object reference.
* */
function dlgopen(url, winname, width, height, forceNewWindow, title, opts) {
    // First things first...
    top.restoreSession();
    // A matter of Legacy
    if (forceNewWindow) {
        return dlgOpenWindow(url, winname, width, height);
    }

    // wait for DOM then check dependencies needed to run this feature.
    // dependency duration is while 'this' is in scope, temporary...
    // seldom will this get used as more of U.I is moved to Bootstrap
    // but better to continue than stop because of a dependency...
    //
    let jqurl = top.webroot_url + '/public/assets/jquery-min-1-9-1/index.js';
    if (typeof jQuery.fn.jquery === 'undefined') {
        includeScript(jqurl, false, 'script'); // true is async
    }
    jQuery(function () {
        // Check for dependencies we will need.
        // webroot_url is a global defined in main_screen.php or main.php.

        let bscss = top.webroot_url + '/public/assets/bootstrap-3-3-4/dist/css/bootstrap.min.css';
        let bscssRtl = top.webroot_url + '/public/assets/bootstrap-rtl-3-3-4/dist/css/bootstrap-rtl.min.css';
        let bsurl = top.webroot_url + '/public/assets/bootstrap-3-3-4/dist/js/bootstrap.min.js';
        let jqui = top.webroot_url + '/public/assets/jquery-ui-1-12-1/jquery-ui.min.js';

        let version = jQuery.fn.jquery.split(' ')[0].split('.');
        if ((version[0] < 2 && version[1] < 9) || (version[0] == 1 && version[1] == 9 && version[2] < 1)) {
            inDom('jquery-min', 'script', true);
            includeScript(jqurl, false, 'script');
            console.log('Replacing jQuery version:[ ' + version + ' ]');
        }
        if (!inDom('bootstrap.min.css', 'link', false)) {
            includeScript(bscss, false, 'link');
            if (top.jsLanguageDirection === 'rtl') {
                includeScript(bscssRtl, false, 'link');
            }
        }
        if (typeof jQuery.fn.modal === 'undefined') {
            if (!inDom('bootstrap.min.js', 'script', false))
                includeScript(bsurl, false, 'script');
        }
        if (typeof jQuery.ui === 'undefined') {
            includeScript(jqui, false, 'script');
        }
    });

    // onward
    var opts_defaults = {
        type: 'iframe',
        allowDrag: true,
        allowResize: true,
        sizeHeight: 'auto', // fixed in works...
        onClosed: false,
        callBack: false
    };

    if (!opts) var opts = {};

    opts = jQuery.extend({}, opts_defaults, opts);

    var mHeight, mWidth, mSize, msSize, dlgContainer, fullURL, where; // a growing list...

    if (top.tab_mode) {
        where = opts.type === 'iframe' ? top : window;
    } else { // if frames u.i, this will search for the first body node so we have a landing place for stackable's
        let wframe = window;
        if (wframe.name !== 'left_nav') {
            for (let i = 0; wframe.name !== 'RTop' && wframe.name !== 'RBot' && i < 6; wframe = wframe.parent) {
                if (i === 5) {
                    wframe = window;
                }
                i++;
            }
        } else {
            wframe = top.window['RTop'];
        }
        for (let i = 0; wframe.document.body.localName !== 'body' && i < 6; wframe = wframe[i++]) {
            if (i === 5) {
                alert('<?php echo xlt("Unable to find window to build") ?>');
                return false;
            }
        }

        where = wframe; // A moving target for Frames UI.
    }

    // get url straight...
    if (opts.url) {
        url = opts.url;
    }
    if (url[0] === "/") {
        fullURL = url
    }
    else {
        fullURL = window.location.href.substr(0, window.location.href.lastIndexOf("/") + 1) + url;
    }
    // what's a window without a name. important for stacking and opener.
    winname = (winname === "_blank" || !winname) ? dialogID() : winname;

    // Convert dialog size to percentages and/or css class.
    var sizeChoices = ['modal-sm', 'modal-md', 'modal-mlg', 'modal-lg', 'modal-xl'];
    if (Math.abs(width) > 0) {
        width = Math.abs(width);
        mWidth = (width / where.innerWidth * 100).toFixed(4) + '%';
        msSize = '<style>.modal-custom' + winname + ' {width:' + mWidth + ';}</style>';
        mSize = 'modal-custom' + winname;
    } else if (jQuery.inArray(width, sizeChoices) !== -1) {
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
    } else if (mSize === 'modal-xl') {
        msSize = '<style>.modal-xl {width:96%;}</style>';
    }

    // Initial responsive height.
    var vpht = where.innerHeight;
    mHeight = height > 0 ? (height / vpht * 100).toFixed(4) + 'vh' : '';

    // Build modal template. For now !title = !header and modal full height.
    var mTitle = title > "" ? '<h4 class=modal-title>' + title + '</h4>' : '';

    var waitHtml =
        '<div class="loadProgress text-center">' +
        '<span class="fa fa-circle-o-notch fa-spin fa-3x text-primary"></span>' +
        '</div>';

    var headerhtml =
        ('<div class=modal-header><span type=button class="x close" data-dismiss=modal>' +
            '<span aria-hidden=true>&times;</span>' +
            '</span><h5 class=modal-title>%title%</h5></div>')
            .replace('%title%', mTitle);

    var frameHead =
        ('<div><span class="close data-dismiss=modal aria-hidden="true">&times;</span></div>');

    var frameHtml =
        ('<iframe id="modalframe" class="embed-responsive-item modalIframe" name="%winname%" frameborder=0 src="%url%">' +
            '</iframe>')
            .replace('%winname%', winname)
            .replace('%url%', fullURL);

    var embedded = 'embed-responsive embed-responsive-16by9';

    var bodyStyles = (' style="margin:2px;padding:2px;height:%initHeight%;max-height:94vh;overflow-y:auto;"')
        .replace('%initHeight%', opts.sizeHeight !== 'full' ? mHeight : '94vh');

    var altClose = '<div class="closeDlgIframe" data-dismiss="modal" ></div>';

    var mhtml =
        ('<div id="%id%" class="modal fade dialogModal" tabindex="-1" role="dialog">%sStyle%' +
            '<style>.modal-backdrop{opacity:0; transition:opacity 1s;}.modal-backdrop.in{opacity:0.2;}</style>' +
            '<div %dialogId% class="modal-dialog %szClass%" role="document">' +
            '<div class="modal-content">' +
            '%head%' + '%altclose%' + '%wait%' +
            '<div class="modal-body %embedded%" %bodyStyles%>' +
            '%body%' + '</div></div></div></div>')
            .replace('%id%', winname)
            .replace('%sStyle%', msSize !== "default" ? msSize : '')
            .replace('%dialogId%', opts.dialogId ? ('id="' + opts.dialogId + '"') : '')
            .replace('%szClass%', mSize ? mSize : '')
            .replace('%head%', mTitle !== '' ? headerhtml : '')
            .replace('%altclose%', mTitle === '' ? altClose : '')
            .replace('%wait%', '') // maybe option later
            .replace('%bodyStyles%', bodyStyles)
            .replace('%embedded%', opts.type === 'iframe' ? embedded : '')
            .replace('%body%', opts.type === 'iframe' ? frameHtml : '');

    // Write modal template.
    //
    dlgContainer = where.jQuery(mhtml);
    dlgContainer.attr("name", winname);

    if (opts.buttons) {
        dlgContainer.find('.modal-content').append(buildFooter());
    }
    if (opts.type !== 'iframe') {
        var params = {
            type: opts.type || '', // if empty and has data object, then post else get.
            data: opts.data || opts.html || '', // ajax loads fetched content or supplied html. think alerts.
            url: opts.url || fullURL,
            dataType: opts.dataType || '' // xml/json/text etc.
        };

        dialogAjax(params, dlgContainer);
    }

    // let opener array know about us.
    top.set_opener(winname, window);

    // Write the completed template to calling document or 'where' window.
    where.jQuery("body").append(dlgContainer);

    jQuery(function () {
        // DOM Ready. Handle events and cleanup.
        if (opts.type === 'iframe') {
            var modalwin = where.jQuery('body').find("[name='" + winname + "']");
            jQuery('div.modal-dialog', modalwin).css({'margin': '15px auto'});
            modalwin.on('load', function (e) {
                setTimeout(function () {
                    if (opts.sizeHeight === 'auto') {
                        SizeModaliFrame(e, height);
                    } else if (opts.sizeHeight === 'fixed') {
                        sizing(e, height);
                    } else {
                        sizing(e, height); // must be full height of container
                    }
                }, 500);
            });
        }

        dlgContainer.on('show.bs.modal', function () {
            if (opts.allowResize) {
                jQuery('.modal-content', this).resizable({
                    grid: [5, 5],
                    animate: true,
                    animateEasing: "swing",
                    animateDuration: "fast",
                    alsoResize: jQuery('div.modal-body', this)
                })
            }
            if (opts.allowDrag) {
                jQuery('.modal-dialog', this).draggable({
                    iframeFix: true,
                    cursor: false
                });
            }
        }).on('shown.bs.modal', function () {
            // Remove waitHtml spinner/loader etc.
            jQuery(this).parent().find('div.loadProgress')
                .fadeOut(function () {
                    jQuery(this).remove();
                });
            dlgContainer.modal('handleUpdate'); // allow for scroll bar
        }).on('hidden.bs.modal', function (e) {
            // remove our dialog
            jQuery(this).remove();
            console.log('Modal hidden then removed!');

            // now we can run functions in our window.
            if (opts.onClosed) {
                console.log('Doing onClosed:[' + opts.onClosed + ']');
                if (opts.onClosed === 'reload') {
                    window.location.reload();
                } else {
                    window[opts.onClosed]();
                }
            }
            if (opts.callBack.call) {
                console.log('Doing callBack:[' + opts.callBack.call + '|' + opts.callBack.args + ']');
                if (opts.callBack.call === 'reload') {
                    window.location.reload();
                } else {
                    window[opts.callBack.call](opts.callBack.args);
                }
            }

        }).modal({backdrop: 'static', keyboard: true}, 'show');// Show Modal

        // define local dialog close() function. openers scope
        window.dlgCloseAjax = function (calling, args) {
            if (calling) {
                opts.callBack = {call: calling, args: args};
            }
            dlgContainer.modal('hide'); // important to clean up in only one place, hide event....
            return false;
        };

        // define local callback function. Set with opener or from opener, will exe on hide.
        window.dlgSetCallBack = function (calling, args) {
            opts.callBack = {call: calling, args: args};
            return false;
        };

        // in residents dialog scope
        where.setCallBack = function (calling, args) {
            opts.callBack = {call: calling, args: args};
            return true;
        };

        where.getOpener = function () {
            return where;
        };

        // Return the dialog ref. looking towards deferring...
        return dlgContainer;

    }); // end events

    function dialogAjax(data, $dialog) {
        var params = {
            async: true,
            url: data.url || data,
            dataType: data.dataType || 'text'
        };

        if (data.url) {
            jQuery.extend(params, data);
        }

        jQuery.ajax(params)
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
        var oFoot = jQuery('<div>').addClass('modal-footer').prop('id', 'oefooter');
        if (opts.buttons) {
            for (var i = 0, k = opts.buttons.length; i < k; i++) {
                var btnOp = opts.buttons[i];
                var btn = jQuery('<button>').addClass('btn btn-' + (btnOp.style || 'primary'));

                for (var index in btnOp) {
                    if (btnOp.hasOwnProperty(index)) {
                        switch (index) {
                            case 'close':
                                //add close event
                                if (btnOp[index]) {
                                    btn.attr('data-dismiss', 'modal');
                                }
                                break;
                            case 'click':
                                //binds button to click event of fn defined in calling document/form
                                var fn = btnOp.click.bind(dlgContainer.find('.modal-content'));
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

    // dynamic sizing - special case for full height - @todo use for fixed wt and ht
    function sizing(e, height) {
        let viewPortHt = 0;
        let $idoc = jQuery(e.currentTarget);
        if (top.tab_mode) {
            viewPortHt = Math.max(top.window.document.documentElement.clientHeight, top.window.innerHeight || 0);
            viewPortWt = Math.max(top.window.document.documentElement.clientWidth, top.window.innerWidth || 0);
        } else {
            viewPortHt = window.innerHeight || 0;
            viewPortWt = window.innerWidth || 0;
        }
        let frameContentHt = opts.sizeHeight === 'full' ? viewPortHt : height;
        frameContentHt = frameContentHt > viewPortHt ? viewPortHt : frameContentHt;
        let hasHeader = $idoc.parents('div.modal-content').find('div.modal-header').height() || 0;
        let hasFooter = $idoc.parents('div.modal-content').find('div.modal-footer').height() || 0;
        frameContentHt = frameContentHt - hasHeader - hasFooter;
        size = (frameContentHt / viewPortHt * 100).toFixed(4);
        let maxsize = hasHeader ? 90 : hasFooter ? 87.5 : 96;
        maxsize = hasHeader && hasFooter ? 80 : maxsize;
        maxsize = maxsize + 'vh';
        size = size + 'vh';
        $idoc.parents('div.modal-body').css({'height': size, 'max-height': maxsize, 'max-width': '96vw'});
        console.log('Modal loaded and sized! Content:' + frameContentHt + ' Viewport:' + viewPortHt + ' Modal height:' +
            size + ' Type:' + opts.sizeHeight + ' Width:' + hasHeader + ' isFooter:' + hasFooter);

        return size;
    }

    // sizing for modals with iframes
    function SizeModaliFrame(e, minSize) {
        let viewPortHt;
        let idoc = e.currentTarget.contentDocument ? e.currentTarget.contentDocument : e.currentTarget.contentWindow.document;
        jQuery(e.currentTarget).parents('div.modal-content').height('');
        jQuery(e.currentTarget).parent('div.modal-body').css({'height': 0});
        if (top.tab_mode) {
            viewPortHt = top.window.innerHeight || 0;
        } else {
            viewPortHt = where.window.innerHeight || 0;
        }
        //minSize = 100;
        let frameContentHt = Math.max(jQuery(idoc).height(), idoc.body.offsetHeight || 0) + 30;
        frameContentHt = frameContentHt < minSize ? minSize : frameContentHt;
        frameContentHt = frameContentHt > viewPortHt ? viewPortHt : frameContentHt;
        let hasHeader = jQuery(e.currentTarget).parents('div.modal-content').find('div.modal-header').length;
        let hasFooter = jQuery(e.currentTarget).parents('div.modal-content').find('div.modal-footer').length;
        size = (frameContentHt / viewPortHt * 100).toFixed(4);
        let maxsize = hasHeader ? 90 : hasFooter ? 87.5 : 96;
        maxsize = hasHeader && hasFooter ? 80 : maxsize;
        maxsize = maxsize + 'vh';
        size = size + 'vh'; // will start the dialog as responsive. Any resize by user turns dialog to absolute positioning.

        jQuery(e.currentTarget).parent('div.modal-body').css({'height': size, 'max-height': maxsize}); // Set final size. Width was previously set.
        //jQuery(e.currentTarget).parent('div.modal-body').height(size)
        console.log('Modal loaded and sized! Content:' + frameContentHt + ' Viewport:' + viewPortHt + ' Modal height:' +
            size + ' Max height:' + maxsize + ' isHeader:' + (hasHeader > 0 ? 'True ' : 'False ') + ' isFooter:' + (hasFooter > 0 ? 'True' : 'False'));

        return size;
    }

}
