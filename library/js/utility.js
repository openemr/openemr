/**
 * Javascript utility functions for openemr
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
/* We should really try to keep this library jQuery free ie javaScript only! */

// Translation function
// This calls the i18next.t function that has been set up in main.php
function xl(string) {
    if (typeof top.i18next.t == 'function') {
        return top.i18next.t(string);
    } else {
        // Unable to find the i18next.t function, so log error
        console.log("xl function is unable to translate since can not find the i18next.t function");
        return string;
    }
}

// html escaping functions - special case when sending js string to html (see codebase for examples)
//   jsText (equivalent to text() )
//   jsAttr (equivalent to attr() )
// must be careful assigning const in this script. can't reinit a constant
if (typeof htmlEscapesText === 'undefined') {
    const htmlEscapesText = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;'
    };
    const htmlEscapesAttr = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#x27;'
    };
    const htmlEscaperText = /[&<>]/g;
    const htmlEscaperAttr = /[&<>"']/g;
    jsText = function (string) {
        return ('' + string).replace(htmlEscaperText, function (match) {
            return htmlEscapesText[match];
        });
    };
    jsAttr = function (string) {
        return ('' + string).replace(htmlEscaperAttr, function (match) {
            return htmlEscapesAttr[match];
        });
    };
}

// another useful function
async function syncFetchFile(fileUrl, type = 'text') {
    let content = '';
    let response = await fetch(fileUrl);
    if (type == 'text') {
        content = await response.text();
    }
    if (type == 'json') {
        content = await response.json();
    }

    return content;
}

/*
* function includeScript(srcUrl, type)
*
* @summary Dynamically include JS Scripts or Css.
*
* @param {string} url file location.
* @param {string} 'script' | 'link'.
*
* */
function includeScript(srcUrl, type) {
    return new Promise(function (resolve, reject) {
        if (type === 'script') {
            let newScriptElement = document.createElement('script');
            newScriptElement.src = srcUrl;
            newScriptElement.onload = () => resolve(newScriptElement);
            newScriptElement.onerror = () => reject(new Error(`Script load error for ${srcUrl}`));

            document.head.append(newScriptElement);
            console.log('Needed to load:[' + srcUrl + '] For: [' + location + ']');
        }
        if (type === "link") {
            let newScriptElement = document.createElement("link")
            newScriptElement.type = "text/css";
            newScriptElement.rel = "stylesheet";
            newScriptElement.href = srcUrl;
            newScriptElement.onload = () => resolve(newScriptElement);
            newScriptElement.onerror = () => reject(new Error(`Link load error for ${srcUrl}`));

            document.head.append(newScriptElement);
            console.log('Needed to load:[' + srcUrl + '] For: [' + location + ']');
        }
    });
}

/*
* @function initDragResize(dragContext, resizeContext)
* @summary call this function from scripts you may want to provide a different
*  context other than the page context of this utility
*
* @param {object} context of element to apply drag.
* @param {object} optional context of element. document is default.
*/
function initDragResize(dragContext, resizeContext = document) {
    let isLoaded = typeof window.interact;
    if (isLoaded !== 'function') {
        (async (utilfn) => {
            await includeScript(utilfn, 'script');
        })(top.webroot_url + '/public/assets/interactjs/dist/interact.js').then(() => {
            initInteractors(dragContext, resizeContext);
        });
    } else {
        initInteractors(dragContext, resizeContext);
    }
}

/* function to init all page drag/resize elements. */
function initInteractors(dragContext = document, resizeContext = '') {
    resizeContext = resizeContext ? resizeContext : dragContext;

    function dragMoveListener(event) {
        let target = event.target;
        let x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
        let y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

        if ('webkitTransform' in target.style || 'transform' in target.style) {
            target.style.webkitTransform =
                target.style.transform =
                    'translate(' + x + 'px, ' + y + 'px)';
        } else {
            target.style.left = x + 'px';
            target.style.top = y + 'px';
        }

        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    }

    /* Draggable */
    // reset
    interact(".drag-action", {context: dragContext}).unset();
    // init
    interact(".drag-action", {context: dragContext}).draggable({
        enabled: true,
        inertia: true,
        modifiers: [
            interact.modifiers.snap({
                targets: [
                    interact.createSnapGrid({x: 30, y: 30})
                ],
                range: Infinity,
                relativePoints: [{x: 0, y: 0}]
            }),
            interact.modifiers.restrict({
                restriction: "parent",
                elementRect: {top: 0, left: 0, bottom: 1, right: 1},
                endOnly: true
            })
        ],
        autoScroll: false,
        maxPerElement: 2
    }).on('dragstart', function (event) {
        event.preventDefault();
    }).on('dragmove', dragMoveListener);

    /* Resizable */
    interact(".resize-action", {context: resizeContext}).unset();

    interact(".resize-action", {context: resizeContext}).resizable({
        enabled: true,
        preserveAspectRatio: false,
        edges: {
            left: '.resize-s',
            right: true,
            bottom: true,
            top: '.resize-s'
        },
        inertia: {
            resistance: 30,
            minSpeed: 100,
            endSpeed: 50
        },
        snap: {
            targets: [
                interact.createSnapGrid({
                    x: 5, y: 5
                })
            ],
            range: Infinity,
            relativePoints: [{x: 0, y: 0}]
        },
    }).on('resizestart', function (event) {
        event.preventDefault();
    }).on('resizemove', function (event) {
        let target = event.target;
        let x = (parseFloat(target.getAttribute('data-x')) || 0);
        let y = (parseFloat(target.getAttribute('data-y')) || 0);

        target.style.width = event.rect.width + 'px';
        target.style.height = event.rect.height + 'px';
        x += event.deltaRect.left;
        y += event.deltaRect.top;

        target.style.webkitTransform = target.style.transform = 'translate(' + x + 'px,' + y + 'px)';
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    });

}

/*
* @function oeSortable(callBackFn)
* @summary call this function from scripts you may need to use sortable
*
* @param function A callback function which is called with the sorted elements as parameter
*/
function oeSortable(callBackFn) {
    if (typeof window.interact !== 'function') {
        (async (interactfn) => {
            await includeScript(interactfn, 'script');
        })(top.webroot_url + '/public/assets/interactjs/dist/interact.js').then(() => {
            load();
        });
    } else {
        load();
    }

    function clearTranslate(elem) {
        elem.style.webkitTransform =
            elem.style.transform =
                'translate(' + 0 + 'px, ' + 0 + 'px)'
        elem.setAttribute('data-x', 0)
        elem.setAttribute('data-y', 0)
    }

    function switchElem(elem1, elem2, clear = false) {
        $(elem2).append($(elem1).children()[0]);
        $(elem1).append($(elem2).children()[0]);
        if (clear) {
            clearTranslate($(elem2).children()[0]);
            clearTranslate($(elem1).children()[0]);
        }
    }

    function moveUp(elem) {
        if (elem) {
            let prevElem = $(elem).prev(".droppable");
            if (prevElem.length > 0) {
                let childIsDragging = prevElem.children("li.is-dragging")[0];
                if (childIsDragging) {
                    switchElem(elem, prevElem[0], true);
                    return true;
                } else {
                    if (prevElem[0]) {
                        if (moveUp(prevElem[0])) {
                            switchElem(elem, prevElem[0]);
                        }
                    }
                }
            }
        }
        return false;
    }

    function moveDown(elem) {
        if (elem) {
            let nxtElem = $(elem).next(".droppable");
            if (nxtElem.length > 0) {
                let childIsDragging = nxtElem.children("li.is-dragging")[0];
                if (childIsDragging) {
                    switchElem(elem, nxtElem[0], true);
                    return true;
                } else {
                    if (nxtElem[0]) {
                        if (moveDown(nxtElem[0])) {
                            switchElem(elem, nxtElem[0]);
                        }
                    }
                }
            }
        }
        return false;
    }

    function dragMoveListener(event) {
        var target = event.target
        var x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx
        var y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy
        target.style.webkitTransform =
            target.style.transform =
                'translate(' + x + 'px, ' + y + 'px)'
        target.setAttribute('data-x', x)
        target.setAttribute('data-y', y)
    }

    function load() {
        interact('.droppable').dropzone({
            accept: null,
            overlap: 0.9,
            ondropactivate: function (event) {
                event.relatedTarget.classList.add('is-dragging');
            },
            ondragenter: function (event) {
                let isUpper = moveUp(event.target);
                if (!isUpper) {
                    moveDown(event.target);
                }
            },
            ondropdeactivate: function (event) {
                if (event.target.firstChild.classList.contains('is-dragging')) {
                    let items = event.target.parentNode.children;
                    event.relatedTarget.classList.remove('is-dragging');
                    clearTranslate(event.relatedTarget);
                    callBackFn && callBackFn(items);
                }
            }
        })

        interact('.draggable').draggable({
            inertia: true,
            modifiers: [
                interact.modifiers.restrictRect({
                    restriction: null,
                    endOnly: true
                })
            ],
            autoScroll: true,
            listeners: {move: dragMoveListener}
        })
    }
}


/*
* Universal async BS alert message with promise
* Note the use of new javaScript translate function xl().
*
*/
if (typeof asyncAlertMsg !== "function") {
    function asyncAlertMsg(message, timer = 5000, type = 'danger', size = '') {
        let alertMsg = xl("Alert Notice");
        $('#alert_box').remove();
        size = (size == 'lg') ? 'left:25%;width:50%;' : 'left:35%;width:30%;';
        let style = "position:fixed;top:25%;" + size + " bottom:0;z-index:9999;";
        $("body").prepend("<div class='container text-center' id='alert_box' style='" + style + "'></div>");
        let mHtml = '<div id="alertmsg" class="alert alert-' + type + ' alert-dismissable">' +
            '<button type="button" class="close btn btn-link btn-cancel" data-dismiss="alert" aria-hidden="true"></button>' +
            '<h5 class="alert-heading text-center">' + alertMsg + '</h5><hr>' +
            '<p>' + message + '</p>' +
            '</div>';
        $('#alert_box').append(mHtml);
        return new Promise(resolve => {
            $('#alertmsg').on('closed.bs.alert', function () {
                clearTimeout(AlertMsg);
                $('#alert_box').remove();
                resolve('closed');
            });
            let AlertMsg = setTimeout(function () {
                $('#alertmsg').fadeOut(800, function () {
                    $('#alert_box').remove();
                    resolve('timedout');
                });
            }, timer);
        })
    }
}

/*
* function syncAlertMsg(()
*
* Universal sync BS alert message returns promise after resolve.
* Call below to return a promise after alert is resolved.
* Example: syncAlertMsg('Hello, longtime, 'success', 'lg').then( asyncRtn => ( ... log something });
*
* Or use as IIFE to run inline.
* Example:
*   (async (time) => {
*       await asyncAlertMsg('Waiting till x'ed out or timeout!', time); ...now go;
*   })(3000).then(rtn => { ... but then could be more });
*
* */
async function syncAlertMsg(message, timer = 5000, type = 'danger', size = '') {
    return await asyncAlertMsg(message, timer, type, size);
}

/* Handy function to set values in globals user_settings table */
if (typeof persistUserOption !== "function") {
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
                top.restoreSession();
            },
            error: function (jqxhr, status, errorThrown) {
                console.log(errorThrown);
            }
        });
    };
}

/**
 * User Debugging Javascript Errors
 * Turn on/off in Globals->Logging
 *
 * @package   OpenEMR Utilities
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 */

if (typeof top.userDebug !== 'undefined' && (top.userDebug === '1' || top.userDebug === '3')) {
    window.onerror = function (msg, url, lineNo, columnNo, error) {
        const is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
        const is_firefox = navigator.userAgent.indexOf('Firefox') > -1;
        const is_safari = navigator.userAgent.indexOf("Safari") > -1;

        var showDebugAlert = function (message) {
            let errorMsg = [
                'URL: ' + message.URL,
                'Line: ' + message.Line + ' Column: ' + message.Column,
                'Error object: ' + JSON.stringify(message.Error)
            ].join("\n");

            let msg = message.Message + "\n" + errorMsg;
            console.error(xl('User Debug Error Catch'), message);
            alert(msg);

            return false;
        };
        try {
            let string = msg.toLowerCase();
            let substring = xl("script error"); // translate to catch for language of browser.
            if (string.indexOf(substring) > -1) {
                let xlated = xl('Script Error: See Browser Console for Detail');
                showDebugAlert(xlated);
            } else {
                let message = {
                    Message: msg,
                    URL: url,
                    Line: lineNo,
                    Column: columnNo,
                    Error: JSON.stringify(error)
                };

                showDebugAlert(message);
            }
        } catch (e) {
            let xlated = xl('Unknown Script Error: See Browser Console for Detail');
            showDebugAlert(xlated);
        }

        return false;
    };
}

