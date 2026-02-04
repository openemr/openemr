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
// This calls the i18next.t function that has been set up in main.php, portal/base.html.twig, etc.
function xl(string) {
    // safety check if for some reason the i18next is not included.
    if (top.i18next && typeof top.i18next.t == 'function') {
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
    jsXla = function (string) {
        return jsAttr(xl(string));
    };
    jsXlt = function (string) {
        return jsText(xl(string));
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

function setInteractorPosition(x, y, target) {
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

/* function to init all page drag/resize elements. */
function initInteractors(dragContext = document, resizeContext = '') {
    resizeContext = resizeContext ? resizeContext : dragContext;

    function dragMoveListener(event) {
        let target = event.target;
        let x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
        let y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

        setInteractorPosition(x, y, target);
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

        // TODO: @adunsulag not sure why this only does webkitTransform, seems like it should do the same
        // as our other move here: setInteractorPosition(x, y, target);
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

// Password Strength Meter JavaScript
function checkPasswordStrength(inputElement) {
    var number = /[\p{N}]/u;  // Matches any Unicode number character
    var alphabets = /[\p{L}]/u;  // Matches any Unicode letter character
    var special_characters = /[^\p{N}\p{L}]/u; // Matches any character that is not a letter, number, or whitespace

    var pwd = inputElement.value;
    var strength = 0;

    if (pwd.length < 6) {
        document.getElementById('password_strength_meter').style.backgroundColor = "#ff6666";
        document.getElementById('password_strength_text').innerText = xl('Very Weak');
    } else {
        if (pwd.match(number) && pwd.match(alphabets) && pwd.match(special_characters)) {
            strength += 3;
        } else if (pwd.match(number) && pwd.match(alphabets)) {
            strength += 2;
        } else if (pwd.match(alphabets)) {
            strength += 1;
        }

        switch (strength) {
            case 1:
                document.getElementById('password_strength_meter').style.backgroundColor = "#ffcc00";
                document.getElementById('password_strength_text').innerText = xl('Weak');
                break;
            case 2:
                document.getElementById('password_strength_meter').style.backgroundColor = "#ffcc66";
                document.getElementById('password_strength_text').innerText = xl('Good');
                break;
            case 3:
                document.getElementById('password_strength_meter').style.backgroundColor = "#99cc00";
                document.getElementById('password_strength_text').innerText = xl('Strong');
                break;
            default:
                document.getElementById('password_strength_meter').style.backgroundColor = "#ff6666";
                document.getElementById('password_strength_text').innerText = xl('Very Weak');
                break;
        }
    }
}

/*
* Universal async Bootstrap alert message with promise.
* Note the use of new javaScript translate function xl().
*
* @param {string} message - The message to display (use \n for line breaks)
* @param {number} timer - Time in ms before auto-close (default: 5000)
* @param {string} type - Bootstrap alert type: 'danger', 'warning', 'success', 'info' (default: 'danger')
* @param {string} size - Size: '' for normal, 'lg' for large (default: '')
* @returns {Promise<string>} Resolves to 'closed' or 'timedout'
*
* Example:
*   asyncAlertMsg('Hello, longtime', 5000, 'success', 'lg').then(result => { console.log(result); });
*
* Or use as IIFE to run inline:
*   (async (time) => {
*       await asyncAlertMsg('Waiting till x\'ed out or timeout!', time);
*   })(3000).then(rtn => { ... });
*
*/
if (typeof asyncAlertMsg !== "function") {
    const VALID_ALERT_TYPES = ['danger', 'warning', 'success', 'info', 'primary', 'secondary', 'light', 'dark'];

    /* eslint-disable-next-line no-inner-declarations */
    function asyncAlertMsg(message, timer = 5000, type = 'danger', size = '') {
        // Validate type parameter
        if (!VALID_ALERT_TYPES.includes(type)) {
            type = 'danger';
        }

        const alertTitle = xl("Alert Notice");
        $('#alert_box').remove();

        const sizeStyle = (size === 'lg') ? 'left:25%;width:50%;' : 'left:35%;width:30%;';
        const containerStyle = "position:fixed;top:25%;" + sizeStyle + "bottom:0;z-index:9999;";

        // Build DOM elements safely using textContent (auto-escapes)
        const alertBox = document.createElement('div');
        alertBox.id = 'alert_box';
        alertBox.className = 'container text-center';
        alertBox.style.cssText = containerStyle;

        const alertDiv = document.createElement('div');
        alertDiv.id = 'alertmsg';
        alertDiv.className = 'alert alert-' + type + ' alert-dismissable';

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'close btn btn-link btn-cancel';
        closeBtn.setAttribute('data-dismiss', 'alert');
        closeBtn.setAttribute('aria-hidden', 'true');

        const heading = document.createElement('h5');
        heading.className = 'alert-heading text-center';
        heading.textContent = alertTitle;

        const hr = document.createElement('hr');

        const msgPara = document.createElement('p');
        // Escape HTML via textContent, then convert \n to <br>
        const textNode = document.createElement('span');
        textNode.textContent = message;
        msgPara.innerHTML = textNode.innerHTML.replace(/\n/g, '<br>');

        alertDiv.appendChild(closeBtn);
        alertDiv.appendChild(heading);
        alertDiv.appendChild(hr);
        alertDiv.appendChild(msgPara);
        alertBox.appendChild(alertDiv);
        document.body.insertBefore(alertBox, document.body.firstChild);

        return new Promise(resolve => {
            $('#alertmsg').on('closed.bs.alert', function () {
                clearTimeout(alertTimeout);
                $('#alert_box').remove();
                resolve('closed');
            });
            const alertTimeout = setTimeout(function () {
                $('#alertmsg').fadeOut(800, function () {
                    $('#alert_box').remove();
                    resolve('timedout');
                });
            }, timer);
        });
    }
}

/*
* Wrapper for asyncAlertMsg - use with await to block execution
* when called from within an async function or async IIFE.
*
* Examples:
*   // Inside async function:
*   await syncAlertMsg('Wait for this!', 5000);
*
*   // As IIFE:
*   (async () => {
*       await syncAlertMsg('Wait for this!', 5000);
*       console.log('Now continues...');
*   })();
*/
async function syncAlertMsg(message, timer = 5000, type = 'danger', size = '') {
    return asyncAlertMsg(message, timer, type, size);
}

/* Handy function to set values in globals user_settings table */
async function persistUserOption(option, value) {
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

(function (window, oeSMART) {
    oeSMART.initLaunch = function (webroot, csrfToken) {
        // allows this to be lazy defined
        let xl = window.top.xl || function (text) {
            return text;
        };
        let smartLaunchers = document.querySelectorAll('.smart-launch-btn');
        for (let launch of smartLaunchers) {
            launch.addEventListener('click', function (evt) {
                let node = evt.target;
                let intent = node.dataset.intent;
                let clientId = node.dataset.clientId;
                if (!intent || !clientId) {
                    console.error("missing intent parameter or client-id parameter");
                    return;
                }

                const params = new URLSearchParams({
                    client_id: clientId,
                    csrf_token: csrfToken,
                    intent: intent
                });
                let url = webroot + '/interface/smart/ehr-launch-client.php?' + params;
                let title = node.dataset.smartName || JSON.stringify(xl("Smart App"));
                // we allow external dialog's  here because that is what a SMART app is
                let height = window.top.innerHeight; // do our full height here
                dlgopen(url, '_blank', 'modal-full', height, '', title, {allowExternal: true});
            });
        }

        let dsiHelpNodes = document.querySelectorAll(".smart-launch-dsi-info");
        for (let dsiHelp of dsiHelpNodes) {
            dsiHelp.addEventListener('click', function (evt) {
                let node = evt.target;
                let dsi = node.dataset.dsiServiceId || "";
                if (typeof dsi != "string" || dsi == "") {
                    console.error("missing data-dsi-service-id parameter for .smart-launch-dsi-info");
                    return;
                }


                // need to add a window message listener for editing the source attributes
                let windowMessageHandler = function () {
                    console.log("received message ", event);
                    if (event.origin !== window.location.origin) {
                        return;
                    }
                    let data = event.data;
                    if (data && data.type === 'smart-dsi-edit-source') {
                        window.name = event.source.name;
                        dlgclose();
                        window.top.removeEventListener('message', windowMessageHandler);
                        // loadFrame already handles webroot and /interface/ prefix.
                        const editParams = new URLSearchParams({
                            csrf_token: csrfToken,
                            action: "external-cdr/edit/" + data.dsiId
                        });
                        let editUrl = '/smart/admin-client.php?' + editParams;
                        window.parent.left_nav.loadFrame('adm', 'adm0', editUrl);
                    }
                };
                window.top.addEventListener('message', windowMessageHandler);

                const params = new URLSearchParams({
                    action: "external-cdr/cdr-info",
                    csrf_token: csrfToken,
                    serviceId: dsi
                });
                let url = webroot + '/interface/smart/admin-client.php?' + params;
                let title = node.dataset.smartName || JSON.stringify(xl("Smart App"));
                // we allow external dialog's  here because that is what a SMART app is
                let height = window.top.innerHeight; // do our full height here
                dlgopen(url, 'smartDsiEditSource', 'modal-full', height, '', title, {
                    allowExternal: false, onClose: function () {
                        window.top.removeEventListener('message', windowMessageHandler);
                    }
                });
            });
        }
    };
    window.oeSMART = oeSMART;
})(window, window.top.oeSMART || {});

/*
* @function isValidEmail(emailAddress)
* @summary call this function where you need to validate an email address
*  is formatted correctly, function will return bool true/false
*
* @param string An email address to validate, e.g. e.g. first.last@gmail.com
*/
function isValidEmail(emailAddress) {
    // RegEx from https://owasp.org/www-community/OWASP_Validation_Regex_Repository
    var mailformat = /^[a-zA-Z0-9_+&*-]+(?:\.[a-zA-Z0-9_+&*-]+)*@(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/;
    if (emailAddress.match(mailformat)) {
        return true;
    } else {
        return false;
    }
}
function normalizeToFilename(str) {
    const controlCharsRegex = new RegExp(
        '[' +
        String.fromCharCode(0) + '-' + String.fromCharCode(31) +
        String.fromCharCode(128) + '-' + String.fromCharCode(159) +
        ']',
        'g'
    );

    return str
    .replace(/[<>:"/\\|?*]/g, '') // Remove illegal filename characters
    .replace(controlCharsRegex, '') // Remove control characters
    .replace(/[\s.,()[\]]/g, '_') // Replace spaces and punctuation with underscore
    .replace(/[&+]/g, 'and') // Replace & and + with "and"
    .replace(/_+/g, '_') // Collapse multiple underscores
    .replace(/^_|_$/g, '') // Trim leading/trailing underscores
    .toLowerCase()
    .substring(0, 100); // Limit length
}

/*
* @function js_uniqid()
* @summary call this function where you need a unique id, based on php uniqid()
*
* @param string prefix to go before unique id that is generated
* @param boolean
*/
function js_uniqid(prefix = "", moreEntropy = true) {

    // Get microseconds since Unix epoch
    const time = Date.now();
    const micro = (performance.now() * 1000) % 1000000;
    const uniqidTime = Math.floor(time / 1000) * 1000000 + Math.floor(micro);

    // Convert to hex (PHP uses 8 chars for seconds + 5 for microseconds)
    let id = uniqidTime.toString(16);

    if (moreEntropy) {
        // Ensure at least 4 random digits, exactly 13 total
        const entropyDigits = 4;
        const timestampDigits = 13 - entropyDigits; // 9 digits

        // Trim timestamp if needed to make room for entropy
        if (id.length > timestampDigits) {
            id = id.slice(0, timestampDigits); // Keep the first 9 digits
        }

        // Generate random hex string
        const maxEntropy = Math.pow(16, entropyDigits);
        const entropy = Math.floor(Math.random() * maxEntropy);
        id += entropy.toString(16).padStart(entropyDigits, '0');
    }

    return prefix + id;
}
