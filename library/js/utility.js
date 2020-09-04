/* eslint-disable no-var */
/**
 * Javascript utility functions for openemr
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
/* We should really try to keep this library jQuery free ie javaScript only! */

// html escaping functions - special case when sending js string to html (see codebase for examples)
//   jsText (equivalent to text() )
//   jsAttr (equivalent to attr() )
var htmlEscapesText = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
};
var htmlEscapesAttr = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#x27;',
};
var htmlEscaperText = /[&<>]/g;
var htmlEscaperAttr = /[&<>"']/g;

// Translation function
// This calls the i18next.t function that has been set up in main.php
function xl(string) {
    if (typeof window.top.i18next.t === 'function') {
        return window.top.i18next.t(string);
    }
    // Unable to find the i18next.t function, so log error
    console.log('xl function is unable to translate since can not find the i18next.t function');
    return string;
}

jsText = function (string) {
    return (`${string}`).replace(htmlEscaperText, function (match) {
        return htmlEscapesText[match];
    });
};
jsAttr = function (string) {
    return (`${string}`).replace(htmlEscaperAttr, function (match) {
        return htmlEscapesAttr[match];
    });
};

// another useful function
async function syncFetchFile(fileUrl, type = 'text') {
    let content = '';
    const response = await fetch(fileUrl);
    if (type === 'text') {
        content = await response.text();
    }
    if (type === 'json') {
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
    return new Promise((resolve, reject) => {
        if (type === 'script') {
            const newScriptElement = document.createElement('script');
            newScriptElement.src = srcUrl;
            newScriptElement.onload = () => resolve(newScriptElement);
            newScriptElement.onerror = () => reject(new Error(`Script load error for ${srcUrl}`));

            document.head.append(newScriptElement);
            console.log(`Needed to load:[ ${srcUrl} ] For: [ ${window.location} ]`);
        }
        if (type === 'link') {
            const newScriptElement = document.createElement('link');
            newScriptElement.type = 'text/css';
            newScriptElement.rel = 'stylesheet';
            newScriptElement.href = srcUrl;
            newScriptElement.onload = () => resolve(newScriptElement);
            newScriptElement.onerror = () => reject(new Error(`Link load error for ${srcUrl}`));

            document.head.append(newScriptElement);
            console.log(`Needed to load:[ ${srcUrl} ] For: [ ${window.location} ]`);
        }
    });
}

/* function to init all page drag/resize elements. */
function initInteractors(dragContext = document, resizeContext = '') {
    if (!resizeContext) {
        resizeContext = dragContext;
    }

    function dragMoveListener(event) {
        const { target } = event;
        const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
        const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

        if ('webkitTransform' in target.style || 'transform' in target.style) {
            target.style.webkitTransform = `translate(${x}px,${y}px)`;
            target.style.transform = `translate(${x}px,${y}px)`;
        } else {
            target.style.left = `${x}px`;
            target.style.top = `${y}px`;
        }

        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    }

    /* Draggable */
    // reset
    interact('.drag-action', {
        context: dragContext,
    }).unset();
    // init
    interact('.drag-action', {
        context: dragContext,
    }).draggable({
        enabled: true,
        inertia: true,
        modifiers: [
            interact.modifiers.snap({
                targets: [
                    interact.createSnapGrid({
                        x: 30,
                        y: 30,
                    }),
                ],
                range: Infinity,
                relativePoints: [{
                    x: 0,
                    y: 0,
                }],
            }),
            interact.modifiers.restrict({
                restriction: 'parent',
                elementRect: {
                    top: 0,
                    left: 0,
                    bottom: 1,
                    right: 1,
                },
                endOnly: true,
            }),
        ],
        autoScroll: false,
        maxPerElement: 2,
    }).on('dragstart', (event) => {
        event.preventDefault();
    }).on('dragmove', dragMoveListener);

    /* Resizable */
    interact('.resize-action', {
        context: resizeContext,
    }).unset();

    interact('.resize-action', {
        context: resizeContext,
    }).resizable({
        enabled: true,
        preserveAspectRatio: false,
        edges: {
            left: '.resize-s',
            right: true,
            bottom: true,
            top: '.resize-s',
        },
        inertia: {
            resistance: 30,
            minSpeed: 100,
            endSpeed: 50,
        },
        snap: {
            targets: [
                interact.createSnapGrid({
                    x: 5,
                    y: 5,
                }),
            ],
            range: Infinity,
            relativePoints: [{
                x: 0,
                y: 0,
            }],
        },
    }).on('resizestart', (event) => {
        event.preventDefault();
    }).on('resizemove', (event) => {
        const { target } = event;
        let x = (parseFloat(target.getAttribute('data-x')) || 0);
        let y = (parseFloat(target.getAttribute('data-y')) || 0);

        target.style.width = `${event.rect.width}px`;
        target.style.height = `${event.rect.height}px`;
        x += event.deltaRect.left;
        y += event.deltaRect.top;

        target.style.webkitTransform = `translate(${x}px,${y}px)`;
        target.style.transform = `translate(${x}px,${y}px)`;
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
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
    const isLoaded = typeof window.interact;
    if (isLoaded !== 'function') {
        (async (utilfn) => {
            await includeScript(utilfn, 'script');
        })(`${window.top.webroot_url}/public/assets/interactjs/dist/interact.js`)
            .then(() => {
                initInteractors(dragContext, resizeContext);
            });
    } else {
        initInteractors(dragContext, resizeContext);
    }
}

/*
 *  This is where we want to decide what we need for the instance
 *  We only want to load any needed dependencies.
 *
 */
document.addEventListener('DOMContentLoaded', () => {
    const isNeeded = document.querySelectorAll('.drag-action').length;
    const isNeededResize = document.querySelectorAll('.resize-action').length;
    if (isNeeded || isNeededResize) {
        initDragResize();
    }
}, false);

/*
 * @function oeSortable(callBackFn)
 * @summary call this function from scripts you may need to use sortable
 *
 * @param function A callback function which is called with the sorted elements as parameter
 */
function oeSortable(callBackFn) {
    function clearTranslate(elem) {
        const element = elem;
        element.style.webkitTransform = `translate(${x}px,${y}px)`;
        element.style.transform = `translate(${x}px,${y}px)`;
        element.setAttribute('data-x', 0);
        element.setAttribute('data-y', 0);
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
            const prevElem = $(elem).prev('.droppable');
            if (prevElem.length > 0) {
                const childIsDragging = prevElem.children('li.is-dragging')[0];
                if (childIsDragging) {
                    switchElem(elem, prevElem[0], true);
                    return true;
                }
                if (prevElem[0] && moveUp(prevElem[0])) {
                    switchElem(elem, prevElem[0]);
                }
            }
        }
        return false;
    }

    function moveDown(elem) {
        if (elem) {
            const nxtElem = $(elem).next('.droppable');
            if (nxtElem.length > 0) {
                const childIsDragging = nxtElem.children('li.is-dragging')[0];
                if (childIsDragging) {
                    switchElem(elem, nxtElem[0], true);
                    return true;
                }
                if (nxtElem[0] && moveDown(nxtElem[0])) {
                    switchElem(elem, nxtElem[0]);
                }
            }
        }
        return false;
    }

    function dragMoveListener(event) {
        const { target } = event;
        const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
        const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
        target.style.webkitTransform = `translate(${x}px,${y}px)`;
        target.style.transform = `translate(${x}px,${y}px)`;
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    }

    function load() {
        interact('.droppable').dropzone({
            accept: null,
            overlap: 0.9,
            ondropactivate: (event) => {
                event.relatedTarget.classList.add('is-dragging');
            },
            ondragenter: (event) => {
                const isUpper = moveUp(event.target);
                if (!isUpper) {
                    moveDown(event.target);
                }
            },
            ondropdeactivate: (event) => {
                if (event.target.firstChild.classList.contains('is-dragging')) {
                    const items = event.target.parentNode.children;
                    event.relatedTarget.classList.remove('is-dragging');
                    clearTranslate(event.relatedTarget);
                    // eslint-disable-next-line no-unused-expressions
                    callBackFn && callBackFn(items);
                }
            },
        });

        interact('.draggable')
            .draggable({
                inertia: true,
                modifiers: [
                    interact.modifiers.restrictRect({
                        restriction: null,
                        endOnly: true,
                    }),
                ],
                autoScroll: true,
                listeners: {
                    move: dragMoveListener,
                },
            });
    }

    if (typeof window.interact !== 'function') {
        (async (interactfn) => {
            await includeScript(interactfn, 'script');
        })(`${window.top.webroot_url}/public/assets/interactjs/dist/interact.js`).then(() => {
            load();
        });
    } else {
        load();
    }
}

/*
 * Universal async BS alert message with promise
 * Note the use of new javaScript translate function xl().
 *
 */
if (typeof asyncAlertMsg !== 'function') {
    function asyncAlertMsg(message, timer = 5000, type = 'danger', size = '') {
        const alertMsg = xl('Alert Notice');
        $('#alert_box').remove();
        mSize = (size === 'lg') ? 'left:25%;width:50%;' : 'left:35%;width:30%;';
        const style = `position:fixed;top:25%;${mSize}bottom:0;z-index:9999;`;
        $('body').prepend(`<div class='container text-center' id='alert_box' style='${style}'></div>`);

        const mHtml = `
            <div id="alertmsg" class="alert alert-${type} alert-dismissable">
                <button type="button" class="close btn btn-link btn-cancel" data-dismiss="alert" aria-hidden="true"></button>
                <h5 class="alert-heading text-center">${alertMsg}</h5><hr>
                <p>${message}</p>
            </div>`;

        $('#alert_box').append(mHtml);
        return new Promise((resolve) => {
            const AlertMsg = setTimeout(() => {
                $('#alertmsg').fadeOut(800, () => {
                    $('#alert_box').remove();
                    resolve('timedout');
                });
            }, timer);

            $('#alertmsg').on('closed.bs.alert', () => {
                clearTimeout(AlertMsg);
                $('#alert_box').remove();
                resolve('closed');
            });
        });
    }
}

/*
 * function syncAlertMsg(()
 *
 * Universal sync BS alert message returns promise after resolve.
 * Call below to return a promise after alert is resolved.
 * Example: syncAlertMsg('Hello', 5000, 'success', 'lg').then(asyncRtn => ( ... log something });
 *
 * Or use as IIFE to run inline.
 * Example:
 *   (async (time) => {
 *       await asyncAlertMsg('Waiting till x'ed out or timeout!', time); ...now go;
 *   })(3000).then(rtn => { ... but then could be more });
 *
 * */
async function syncAlertMsg(message, timer = 5000, type = 'danger', size = '') {
    return asyncAlertMsg(message, timer, type, size);
}

/* Handy function to set values in globals user_settings table */
if (typeof persistUserOption !== 'function') {
    const persistUserOption = function (option, value) {
        return $.ajax({
            // url: top.webroot_url + "/library/ajax/user_settings.php",
            url: `${window.top.webroot_url}/library/ajax/user_settings.php`,
            type: 'post',
            contentType: 'application/x-www-form-urlencoded',
            data: {
                csrf_token_form: window.top.csrf_token_js,
                target: option,
                setting: value,
            },
            beforeSend: () => {
                window.top.restoreSession();
            },
            error: (jqxhr, status, errorThrown) => {
                console.log(errorThrown);
            },
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

if (typeof window.top.userDebug !== 'undefined' && (window.top.userDebug === '1' || window.top.userDebug === '3')) {
    window.onerror = (msg, url, lineNo, columnNo, error) => {
        const isChrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
        const isFirefox = navigator.userAgent.indexOf('Firefox') > -1;
        const isSafari = navigator.userAgent.indexOf('Safari') > -1;

        const showDebugAlert = (message) => {
            const errorMsg = [
                `URL: ${message.URL}`,
                `Line: ${message.Line} Column: ${message.Column}`,
                `Error object: ${JSON.stringify(message.Error)}`,
            ].join('\n');

            const messages = `${message.Message}\n${errorMsg}`;

            console.error(xl('User Debug Error Catch'), message);
            alert(messages);

            return false;
        };

        const string = msg.toLowerCase();
        const substring = xl('script error'); // translate to catch for language of browser.
        if (string.indexOf(substring) > -1) {
            const xlated = xl('Script Error: See Browser Console for Detail');
            showDebugAlert(xlated);
        } else {
            const message = {
                Message: msg,
                URL: url,
                Line: lineNo,
                Column: columnNo,
                Error: JSON.stringify(error),
            };

            showDebugAlert(message);
        }

        return false;
    };
}
