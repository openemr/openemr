// Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
// Copyright (C) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

(function (define) {
    define(['jquery'], function ($, root) {
        let opts_default = {};
        root = root || {};
        root.alert = alert;
        root.ajax = ajax;
        root.confirm = confirm;
        root.closeAjax = closeAjax;
        root.close = close;

        return root;

        function ajax(data) {
            let opts = {
                buttons: data.buttons,
                allowDrag: data.allowDrag,
                allowResize: data.allowResize,
                sizeHeight: data.sizeHeight,
                type: data.type,
                resolvePromiseOn: data.resolvePromiseOn,
                data: data.data,
                url: data.url,
                dataType: data.dataType // xml/json/text etc.
            };

            let title = data.title;

            return dlgopen('', '', data.size, 0, '', title, opts);
        }

        function alert(data, title) {
            title = title ? title : 'Alert';
            let alertTitle = '<span class="text-danger bg-light"><i class="fa fa-exclamation-triangle"></i>&nbsp;' + title + '</span>';
            return dlgopen('', '', 675, 0, '', alertTitle, {
                buttons: [
                    {text: '<i class="fa fa-thumbs-up mr-1"></i>OK', close: true, style: 'primary'}
                ],
                type: 'Alert',
                sizeHeight: 'auto',
                html: '<p>' + data + '</p>'
            });
        }

        function confirm(data, title) {
            title = title ? title : 'Confirm';
            let alertTitle = '<span class="text-info bg-light"><i class="fa fa-exclamation-triangle"></i>&nbsp;' + title + '</span>';
            return dlgopen('', '', "modal-md", 0, '', alertTitle, {
                buttons: [
                    {text: '<i class="fa fa-thumbs-up mr-1"></i>Yes', close: true, id: 'confirmYes', style: 'primary'},
                    {text: '<i class="fa fa-thumbs-down mr-1"></i>No', close: true, id: 'confirmNo', style: 'primary'},
                    {text: 'Nevermind', close: true, style: 'secondary'}
                ],
                type: 'Confirm',
                resolvePromiseOn: 'confirm',
                sizeHeight: 'auto',
                html: '<p>' + data + '</p>'
            });
        }

        function closeAjax() {
            dlgCloseAjax();
        }

        function close() {
            dlgclose();
        }
    });

    if (typeof window.xl !== 'function') {
        (async (utilfn) => {
            await includeScript(utilfn, 'script');
        })(top.webroot_url + '/library/js/utility.js').then(() => {
            console.log('Utilities Unavailable! loading:[ ' + utilfn + ' ] For: [ ' + location + ' ]');
        });
    }
}(typeof define == 'function' && define.amd ?
    define :
    function (args, mName) {
        this.dialog = typeof module != 'undefined' && module.exports ?
            mName(require(args[0], {}), module.exports) :
            mName(window.$);
    }));


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
    if (typeof top.restoreSession === 'function') {
        top.restoreSession();
    }

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
        return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    }

    return s4() + s4() + s4() + s4() + s4() + +s4() + s4() + s4();
}

// test for and/or remove dependency.
function inDom(dependency, type, remove) {
    let el = type;
    let attr = type === 'script' ? 'src' : type === 'link' ? 'href' : 'none';
    let all = document.getElementsByTagName(el);
    for (let i = all.length; i > -1; i--) {
        if (all[i] && all[i].getAttribute(attr) !== null && all[i].getAttribute(attr).indexOf(dependency) !== -1) {
            if (remove) {
                all[i].parentNode.removeChild(all[i]);
                console.log("Removed from DOM: " + dependency);
                return true;
            } else {
                return true;
            }
        }
    }
    return false;
}

// test to see if bootstrap theming is loaded (via standard or custom bootstrap library)
//  Will check for the badge-secondary class
//   - if exist, then assume bootstrap loaded
//   - if not exist, then assume bootstrap not loaded
function isBootstrapCss() {
    for (let i = 0; i < document.styleSheets.length; i++) {
        let rules = document.styleSheets[i].rules || document.styleSheets[i].cssRules;
        for (let x in rules) {
            if (rules[x].selectorText == '.badge-secondary') {
                return true;
            }
        }
    }
    return false;
}

// These functions may be called from scripts that may be out of scope with top so...
// if opener is tab then we need to be in tabs UI scope and while we're at it, let's bring webroot along...
//
if (typeof top.webroot_url === "undefined" && opener) {
    if (typeof opener.top.webroot_url !== "undefined") {
        top.webroot_url = opener.top.webroot_url;
    }
}
// We'll need these if out of scope
//
if (typeof top.set_opener !== "function") {
    var opener_list = [];

    function set_opener(window, opener) {
        top.opener_list[window] = opener;
    }

    function get_opener(window) {
        return top.opener_list[window];
    }
}

// universal alert popup message
if (typeof alertMsg !== "function") {
    function alertMsg(message, timer = 5000, type = 'danger', size = '', persist = '') {
        // this xl() is just so cool.
        let gotIt = xl("Got It");
        let title = xl("Alert");
        let dismiss = xl("Dismiss");
        $('#alert_box').remove();
        let oHidden = '';
        oHidden = !persist ? "hidden" : '';
        let oSize = (size == 'lg') ? 'left:10%;width:80%;' : 'left:25%;width:50%;';
        let style = "position:fixed;top:25%;" + oSize + " bottom:0;z-index:9999;";
        $("body").prepend("<div class='container text-center' id='alert_box' style='" + style + "'></div>");
        let mHtml = '<div id="alertmsg" class="alert alert-' + type + ' alert-dismissable">' +
            '<button type="button" class="btn btn-link ' + oHidden + '" id="dontShowAgain" data-dismiss="alert">' +
            gotIt + '&nbsp;<i class="fa fa-thumbs-up"></i></button>' +
            '<h4 class="alert-heading text-center">' + title + '!</h4><hr>' + '<p style="color:#000;">' + message + '</p>' +
            '<button type="button" class="pull-right btn btn-link" data-dismiss="alert">' + dismiss + '</button><br /></div>';
        $('#alert_box').append(mHtml);
        $('#alertmsg').on('closed.bs.alert', function () {
            clearTimeout(AlertMsg);
            $('#alert_box').remove();
            return false;
        });
        $('#dontShowAgain').on('click', function (e) {
            persistUserOption(persist, 1);
        });
        let AlertMsg = setTimeout(function () {
            $('#alertmsg').fadeOut(800, function () {
                $('#alert_box').remove();
            });
        }, timer);
    }

    const persistUserOption = function (option, value) {
        return $.ajax({
            url: top.webroot_url + "/library/ajax/user_settings.php",
            type: 'post',
            contentType: 'application/x-www-form-urlencoded',
            data: {
                csrf_token_form: top.csrf_token_js,
                target: option,
                setting: value
            },
            beforeSend: function () {
                if (typeof top.restoreSession === 'function') {
                    top.restoreSession();
                }
            },
            error: function (jqxhr, status, errorThrown) {
                console.log(errorThrown);
            }
        });
    };
}


// Test if supporting dialog callbacks and close dependencies are in scope.
// This is useful when opening and closing the dialog is in the same scope. Still use include_opener.js
// in script that will close a dialog that is not in the same scope dlgopen was used
// or use parent.dlgclose() if known decedent.
// dlgopen() will always have a name whether assigned by dev or created by function.
// Callback, onClosed and button clicks are still available either way.
// For a callback on close use: dlgclose(functionName, farg1, farg2 ...) which becomes: functionName(farg1,farg2, etc)
//
if (typeof dlgclose !== "function") {
    if (!opener) {
        opener = window;
    }

    function dlgclose(call, args) {
        var frameName = window.name;
        var wframe = top;
        if (frameName === '') {
            // try to find dialog. dialogModal is embedded dialog class
            // It has to be here somewhere.
            frameName = $(".dialogModal").attr('id');
            if (!frameName) {
                frameName = parent.$(".dialogModal").attr('id');
                if (!frameName) {
                    console.log("Unable to find dialog.");
                    return false;
                }
            }
        }
        var dialogModal = top.$('div#' + frameName);

        var removeFrame = dialogModal.find("iframe[name='" + frameName + "']");
        if (removeFrame.length > 0) {
            removeFrame.remove();
        }

        if (dialogModal.length > 0) {
            if (call) {
                wframe.setCallBack(call, args); // sets/creates callback function in dialogs scope.
            }
            dialogModal.modal('hide');
        } else {
            // no opener not iframe must be in here
            $(this.document).find(".dialogModal").modal('hide');
        }
    }
}

/*
* function dlgopen(url, winname, width, height, forceNewWindow, title, opts)
*
* @summary Stackable, resizable and draggable responsive ajax/iframe dialog modal.
*
* @param {url} string Content location.
* @param {String} winname If set becomes modal id and/or iframes name. Or, one is created/assigned(iframes).
* @param {Number| String} width|modalSize(modal-xl) For sizing: an number will be converted to a percentage of view port width.
* @param {Number} height Initial minimum height. For iframe auto resize starts at this height.
* @param {boolean} forceNewWindow Force using a native window.
* @param {String} title If exist then header with title is created otherwise no header and content only.
* @param {Object} opts Dialogs options.
* @returns {Object} dialog object reference.
* */
function dlgopen(url, winname, width, height, forceNewWindow, title, opts) {
    // First things first...
    if (typeof top.restoreSession === 'function') {
        top.restoreSession();
    }

    // A matter of Legacy
    if (forceNewWindow) {
        return dlgOpenWindow(url, winname, width, height);
    }

    // wait for DOM then check dependencies needed to run this feature.
    // dependency duration is while 'this' is in scope, temporary...
    // seldom will this get used as more of U.I is moved to Bootstrap
    // but better to continue than stop because of a dependency...
    //
    let jqurl = top.webroot_url + '/public/assets/jquery/dist/jquery.min.js';
    if (typeof jQuery === 'undefined') {
        (async (utilfn) => {
            await includeScript(utilfn, 'script');
        })(jqurl);
    }
    jQuery(function () {
        // Check for dependencies we will need.
        // webroot_url is a global defined in main_screen.php or main.php.
        let bscss = top.webroot_url + '/public/assets/bootstrap/dist/css/bootstrap.min.css';
        let bscssRtl = top.webroot_url + '/public/assets/bootstrap-v4-rtl/dist/css/bootstrap-rtl.min.css';
        let bsurl = top.webroot_url + '/public/assets/bootstrap/dist/js/bootstrap.bundle.min.js';

        let version = jQuery.fn.jquery.split(' ')[0].split('.');
        if ((version[0] < 2 && version[1] < 9) || (version[0] === 1 && version[1] === 9 && version[2] < 1)) {
            inDom('jquery-min', 'script', true);
            (async (utilfn) => {
                await includeScript(utilfn, 'script');
            })(jqurl).then(() => {
                console.log('Replacing jQuery version:[ ' + version + ' ]');
            });
        }
        if (!isBootstrapCss()) {
            (async (utilfn) => {
                await includeScript(utilfn, 'link');
            })(bscss);
            if (top.jsLanguageDirection == 'rtl') {
                (async (utilfn) => {
                    await includeScript(utilfn, 'link');
                })(bscssRtl);
            }
        }
        if (typeof jQuery.fn.modal === 'undefined') {
            if (!inDom('bootstrap.bundle.min.js', 'script', false)) {
                (async (utilfn) => {
                    await includeScript(utilfn, 'script');
                })(bsurl);
            }
        }
    });

    // onward
    var opts_defaults = {
        type: 'iframe', // POST, GET (ajax) or iframe
        async: true,
        frameContent: "", // for iframe embedded content
        html: "", // content for alerts, comfirm etc ajax
        allowDrag: false,
        allowResize: true,
        sizeHeight: 'auto', // 'full' will use as much height as allowed
        // use is onClosed: fnName ... args not supported however, onClosed: 'reload' is auto defined and requires no function to be created.
        onClosed: false,
        allowExternal: false, // allow a dialog window to a URL that is external to the current url
        callBack: false, // use {call: 'functionName, args: args, args} if known or use dlgclose.
        resolvePromiseOn: '' // this may be useful values are init, shown, show, confirm, alert and closed which coincide with dialog events.
    };

    if (!opts) {
        opts = {};
    }
    opts = jQuery.extend({}, opts_defaults, opts);
    opts.type = opts.type ? opts.type.toLowerCase() : '';
    opts.resolvePromiseOn = opts.resolvePromiseOn ?? 'init';
    var mHeight, mWidth, mSize, msSize, dlgContainer, fullURL, where; // a growing list...

    where = (opts.type === 'iframe') ? top : window;

    // get url straight...
    fullURL = "";
    if (opts.url) {
        url = opts.url;
    }
    if (url) {
        if (url[0] === "/") {
            fullURL = url
        } else if (opts.allowExternal === true) {
            var checkUrl = new URL(url);
            // we only allow http & https protocols to be launched
            if (checkUrl.protocol === "http:" || checkUrl.protocol == "https:") {
                fullURL = url;
            }
        } else {
            fullURL = window.location.href.substr(0, window.location.href.lastIndexOf("/") + 1) + url;
        }
    }

    // what's a window without a name. important for stacking and opener.
    winname = (winname === "_blank" || !winname) ? dialogID() : winname;

    // for small screens or request width is larger than viewport.
    if (where.innerWidth <= 768) {
        width = "modal-xl";
    }
    // Convert dialog size to percentages and/or css class.
    var sizeChoices = ['modal-sm', 'modal-md', 'modal-mlg', 'modal-lg', 'modal-xl', 'modal-full'];
    if (Math.abs(width) > 0) {
        width = Math.abs(width);
        mWidth = (width / where.innerWidth * 100).toFixed(1) + '%';
        msSize = '<style>.modal-custom-' + winname + ' {max-width:' + mWidth + ' !important;}</style>';
        mSize = 'modal-custom' + winname;
    } else if (jQuery.inArray(width, sizeChoices) !== -1) {
        mSize = width; // is a modal class
    } else {
        msSize = '<style>.modal-custom-' + winname + ' {max-width:35% !important;}</style>'; // standard B.S. modal default (modal-md)
    }
    // leave below for legacy
    if (mSize === 'modal-sm') {
        msSize = '<style>.modal-custom-' + winname + ' {max-width:25% !important;}</style>';
    } else if (mSize === 'modal-md') {
        msSize = '<style>.modal-custom-' + winname + ' {max-width:40% !important;}</style>';
    } else if (mSize === 'modal-mlg') {
        msSize = '<style>.modal-custom-' + winname + ' {max-width:55% !important;}</style>';
    } else if (mSize === 'modal-lg') {
        msSize = '<style>.modal-custom-' + winname + ' {max-width:75% !important;}</style>';
    } else if (mSize === 'modal-xl') {
        msSize = '<style>.modal-custom-' + winname + ' {max-width:92% !important;}</style>';
    } else if (mSize === 'modal-full') {
        msSize = '<style>.modal-custom-' + winname + ' {max-width:97% !important;}</style>';
    }
    mSize = 'modal-custom-' + winname;

    // Initial responsive height.
    var vpht = where.innerHeight;
    mHeight = height > 0 ? (height / vpht * 100).toFixed(1) + 'vh' : '';

    // Build modal template. For now !title = !header and modal full height.
    var mTitle = title > "" ? '<h5 class=modal-title>' + title + '</h5>' : '';

    var waitHtml =
        '<div class="loadProgress text-center">' +
        '<span class="fa fa-circle-notch fa-spin fa-3x text-primary"></span>' +
        '</div>';

    var headerhtml =
        ('<div class="modal-header">%title%<button type="button" class="close" data-dismiss="modal">' +
            '&times;</button></div>').replace('%title%', mTitle);

    var frameHtml =
        ('<iframe id="modalframe" class="modalIframe w-100 h-100 border-0" name="%winname%" %url%></iframe>').replace('%winname%', winname).replace('%url%', fullURL ? 'src=' + fullURL : '');

    var contentStyles = ('style="height:%initHeight%; max-height: 94vh"').replace('%initHeight%', opts.sizeHeight !== 'full' ? mHeight : '85vh');

    var altClose = '<div class="closeDlgIframe" data-dismiss="modal" ></div>';

    var mhtml =
        ('<div id="%id%" class="modal fade dialogModal" tabindex="-1" role="dialog">%sizeStyle%' +
            '<style>.drag-resize {touch-action:none;user-select:none;}</style>' +
            '<div %dialogId% class="modal-dialog %drag-action% %sizeClass%" role="dialog">' +
            '<div class="modal-content %resize-action%" %contentStyles%>' + '%head%' + '%altclose%' + '%wait%' +
            '<div class="modal-body px-1 h-100">' + '%body%' + '</div></div></div></div>').replace('%id%', winname).replace('%sizeStyle%', msSize ? msSize : '').replace('%dialogId%', opts.dialogId ? ('id=' + opts.dialogId + '"') : '').replace('%sizeClass%', mSize ? mSize : '').replace('%head%', mTitle !== '' ? headerhtml : '').replace('%altclose%', mTitle === '' ? altClose : '').replace('%drag-action%', (opts.allowDrag) ? 'drag-action' : '').replace('%resize-action%', (opts.allowResize) ? 'resize-action' : '').replace('%wait%', '').replace('%contentStyles%', contentStyles).replace('%body%', opts.type === 'iframe' ? frameHtml : '');

    // Write modal template.
    dlgContainer = where.jQuery(mhtml);
    dlgContainer.attr("name", winname);

    // No url and just iframe content
    if (opts.frameContent && opts.type === 'iframe') {
        var ipath = 'data:text/html,' + encodeURIComponent(opts.frameContent);
        dlgContainer.find("iframe[name='" + winname + "']").attr("src", ipath);
    }

    if (opts.buttons) {
        dlgContainer.find('.modal-content').append(buildFooter());
    }
    // Ajax setup
    if (opts.type === 'alert') {
        dlgContainer.find('.modal-body').html(opts.html);
    }
    if (opts.type === 'confirm') {
        dlgContainer.find('.modal-body').html(opts.html);
    }
    if (opts.type !== 'iframe' && opts.type !== 'alert' && opts.type !== 'confirm') {
        var params = {
            async: opts.async,
            method: opts.type || '', // if empty and has data object, then post else get.
            content: opts.data || opts.html, // ajax loads fetched content.
            url: opts.url || fullURL,
            dataType: opts.dataType || '' // xml/json/text etc.
        };

        dialogAjax(params, dlgContainer, opts);
    }

    // let opener array know about us.
    top.set_opener(winname, window);

    // Write the completed template to calling document or 'where' window.
    where.jQuery("body").append(dlgContainer);

    // We promised
    return new Promise((resolve, reject) => {
        jQuery(function () {
            // DOM Ready. Handle events and cleanup.
            if (opts.type === 'iframe') {
                var modalwin = where.jQuery('body').find("[name='" + winname + "']");
                jQuery('div.modal-dialog', modalwin).css({'margin': "0.75rem auto auto"});
                modalwin.on('load', function (e) {
                    setTimeout(function () {
                        if (opts.sizeHeight === 'auto' && opts.type === 'iframe') {
                            SizeModaliFrame(e, height);
                        } else if (opts.sizeHeight === 'fixed') {
                            sizing(e, height);
                        } else {
                            sizing(e, height); // must be full height of container
                        }
                    }, 800);
                });
            } else {
                var modalwin = where.jQuery('body').find("[name='" + winname + "']");
                jQuery('div.modal-dialog', modalwin).css({'margin': '15px auto auto'});
                modalwin.on('show.bs.modal', function (e) {
                    setTimeout(function () {
                        sizing(e, height);
                    }, 800);
                });
            }
            if (opts.resolvePromiseOn === 'confirm') {
                jQuery("#confirmYes").on('click', function (e) {
                    resolve(true);
                });
                jQuery("#confirmNo").on('click', function (e) {
                    resolve(false);
                });
            }
            // events chain.
            dlgContainer.on('show.bs.modal', function () {
                if (opts.allowResize || opts.allowDrag) {
                    initDragResize(where.document, where.document);
                }

                if (opts.resolvePromiseOn === 'show') {
                    resolve(dlgContainer);
                }
            }).on('shown.bs.modal', function () {
                // Remove waitHtml spinner/loader etc.
                jQuery(this).parent().find('div.loadProgress').fadeOut(function () {
                    jQuery(this).remove();
                });
                dlgContainer.modal('handleUpdate'); // allow for scroll bar

                if (opts.resolvePromiseOn === 'shown') {
                    resolve(dlgContainer);
                }
            }).on('hidden.bs.modal', function (e) {
                // clear cursor
                e.target.style.cursor = "pointer";
                // remove our dialog
                jQuery(this).remove();
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

                if (opts.resolvePromiseOn == 'close') {
                    resolve(dlgContainer);
                }
            });

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
            // dialog is completely built and events set
            // this is default returning our dialog container reference.
            if (opts.resolvePromiseOn == 'init') {
                resolve(dlgContainer);
            }
            // Finally Show Dialog after DOM settles
            dlgContainer.modal({backdrop: 'static', keyboard: true}, 'show');
        }); // end events
    }); /* Returning Promise */

    // Ajax call with promise via dialog
    function dialogAjax(data, $dialog, opts) {
        var params = {
            async: data.async,
            method: data.method || '',
            data: data.content,
            url: data.url,
            dataType: data.dataType || 'html'
        };

        if (data.url) {
            jQuery.extend(params, data);
        }

        jQuery.ajax(params).done(aOkay).fail(oops);

        return true;

        function aOkay(html) {
            opts.ajax = true;
            $dialog.find('.modal-body').html(data.success ? data.success(html) : html);

            return true;
        }

        function oops(r, s) {
            var msg = data.error ?
                data.error(r, s, params) :
                '<div class="alert alert-danger">' +
                '<strong>XHR Failed:</strong> [ ' + params.url + '].' + '</div>';

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
                if (typeof btnOp.class !== 'undefined') {
                    btnOp.class = btnOp.class.replace(/default/gi, 'secondary');
                    var btn = jQuery('<button>').addClass('btn ' + (btnOp.class || 'btn-primary'));
                } else { // legacy
                    btnOp.style = btnOp.style.replace(/default/gi, 'secondary');
                    var btn = jQuery('<button>').addClass('btn btn-' + (btnOp.style || 'primary'));
                    btnOp.style = "";
                }
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
                            case 'class':
                                break;
                            default:
                                //all other possible HTML attributes to button element
                                // name, id etc
                                btn.attr(index, btnOp[index]);
                        }
                    }
                }

                oFoot.append(btn);
            }
        }
        return oFoot; // jquery object of modal footer.
    }

    // dynamic sizing - special case for full height
    function sizing(e, height) {
        let viewPortHt = 0;
        if (opts.sizeHeight === 'auto') {
            dlgContainer.find('div.modal-body').css({'overflow-y': 'auto'});
            // let BS determine height for alerts etc
            return;
        }
        let $idoc = jQuery(e.currentTarget);
        viewPortHt = Math.max(window.document.documentElement.clientHeight, window.innerHeight || 0);
        let frameContentHt = opts.sizeHeight === 'full' ? viewPortHt : height;
        frameContentHt = frameContentHt >= viewPortHt ? viewPortHt : frameContentHt;
        size = (frameContentHt / viewPortHt * 100).toFixed(2);
        size = size + 'vh';
        dlgContainer.find('div.modal-content').css({'height': size});
        if (opts.type === 'iframe') {
            dlgContainer.find('div.modal-body').css({'overflow-y': 'hidden'});
        } else {
            dlgContainer.find('div.modal-body').css({'overflow-y': 'auto'});
        }


        return size;
    }

    // sizing for modals with iframes
    function SizeModaliFrame(e, minSize) {
        let viewPortHt = where.window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        let frameContentHt = 0;
        let idoc = null;
        try {
            idoc = e.currentTarget.contentDocument ? e.currentTarget.contentDocument : e.currentTarget.contentWindow.document;
            jQuery(e.currentTarget).parents('div.modal-content').css({'height': 0});
            frameContentHt = Math.max(jQuery(idoc).height(), idoc.body.offsetHeight) + 40;
        } catch(err){
            frameContentHt = minSize + 40;
        }
        frameContentHt = frameContentHt <= minSize ? minSize : frameContentHt;
        frameContentHt = frameContentHt >= viewPortHt ? viewPortHt : frameContentHt;
        size = (frameContentHt / viewPortHt * 100).toFixed(1);
        size = size + 'vh';
        jQuery(e.currentTarget).parents('div.modal-content').css({'height': size});

        return size;
    }
}
