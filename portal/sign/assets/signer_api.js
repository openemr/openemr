/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const signhere = "data:image/svg+xml,%3C%3Fxml version='1.0' standalone='no'%3F%3E%3C!DOCTYPE svg PUBLIC '-//W3C//DTD SVG 20010904//EN' 'http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd'%3E%3Csvg version='1.0' xmlns='http://www.w3.org/2000/svg' width='852.000000pt' height='265.000000pt' viewBox='0 0 852.000000 265.000000' preserveAspectRatio='xMidYMid meet'%3E%3Cg transform='translate(0.000000,265.000000) scale(0.100000,-0.100000)'%0Afill='%23000000' stroke='none'%3E%3Cpath d='M390 1534 c-19 -14 -67 -57 -107 -96 l-71 -71 -31 34 c-16 19 -38 49%0A-48 67 -18 32 -53 44 -53 18 0 -17 47 -111 72 -144 17 -23 17 -25 -21 -75 -46%0A-62 -85 -143 -77 -163 3 -8 14 -14 25 -14 15 0 27 15 45 53 23 52 71 117 85%0A117 4 0 15 -15 25 -32 11 -18 34 -50 52 -70 18 -20 40 -51 49 -67 9 -19 23%0A-31 35 -31 38 0 18 71 -34 119 -33 31 -89 117 -84 130 1 6 49 57 106 115 56%0A57 102 111 102 120 0 23 -29 18 -70 -10z'/%3E%3C/g%3E%3C/svg%3E";

(function (global) {
    var channels = [];

    function BroadcastChannel(channel) {
        var $this = this;
        channel = String(channel);

        var id = '$BroadcastChannel$' + channel + '$';

        channels[id] = channels[id] || [];
        channels[id].push(this);

        this._name = channel;
        this._id = id;
        this._closed = false;
        this._mc = new MessageChannel();
        this._mc.port1.start();
        this._mc.port2.start();

        global.addEventListener('storage', function (e) {
            if (e.storageArea !== global.localStorage) return;
            if (e.newValue === null || e.newValue === '') return;
            if (e.key.substring(0, id.length) !== id) return;
            var data = JSON.parse(e.newValue);
            $this._mc.port2.postMessage(data);
        });
    }

    BroadcastChannel.prototype = {
        // BroadcastChannel API
        get name() {
            return this._name;
        },
        postMessage: function (message) {
            var $this = this;
            if (this._closed) {
                var e = new Error();
                e.name = 'InvalidStateError';
                throw e;
            }
            var value = JSON.stringify(message);

            // Broadcast to other contexts via storage events...
            var key = this._id + String(Date.now()) + '$' + String(Math.random());
            global.localStorage.setItem(key, value);
            setTimeout(function () {
                global.localStorage.removeItem(key);
            }, 500);

            // Broadcast to current context via ports
            channels[this._id].forEach(function (bc) {
                if (bc === $this) return;
                bc._mc.port2.postMessage(JSON.parse(value));
            });
        },
        close: function () {
            if (this._closed) return;
            this._closed = true;
            this._mc.port1.close();
            this._mc.port2.close();

            var index = channels[this._id].indexOf(this);
            channels[this._id].splice(index, 1);
        },

        // EventTarget API
        get onmessage() {
            return this._mc.port1.onmessage;
        },
        set onmessage(value) {
            this._mc.port1.onmessage = value;
        },
        addEventListener: function (type, listener /*, useCapture*/) {
            return this._mc.port1.addEventListener.apply(this._mc.port1, arguments);
        },
        removeEventListener: function (type, listener /*, useCapture*/) {
            return this._mc.port1.removeEventListener.apply(this._mc.port1, arguments);
        },
        dispatchEvent: function (event) {
            return this._mc.port1.dispatchEvent.apply(this._mc.port1, arguments);
        }
    };
    global.BroadcastChannel = global.BroadcastChannel || BroadcastChannel;
}(self));

let adminName = '';
let $lastEl = '';
let isAdmin = false;
if (typeof isPortal === 'undefined') {
    var isPortal = 0;
}
let useRemote = !isPortal;
if (typeof ptName === 'undefined') {
    var ptName = '';
}
if (typeof webRoot === 'undefined' && typeof top.webroot_url !== 'undefined') {
    var webRoot = top.webroot_url;
}
if (typeof isModule === 'undefined') {
    var isModule = false;
}

function signerAlertMsg(message, timer = 5000, type = 'danger', size = '') {
    $('#signerAlertBox').remove();
    size = (size == 'lg') ? 'left:25%;width:50%;' : 'left:35%;width:30%;';
    let style = "position:fixed;top:25%;" + size + " bottom:0;z-index:1020;";
    $("body").prepend("<div class='container text-center' id='signerAlertBox' style='" + style + "'></div>");
    let mHtml = '<div id="alertMessage" class="alert alert-' + type + ' alert-dismissable">' +
        '<button type="button" class="close btn btn-link btn-cancel" data-dismiss="alert" aria-hidden="true">&times;</button>' +
        '<h5 class="alert-heading text-center">Alert!</h5><hr>' +
        '<p>' + message + '</p>' +
        '</div>';
    $('#signerAlertBox').append(mHtml);
    $('#alertMessage').on('closed.bs.alert', function () {
        clearTimeout(AlertMsg);
        $('#signerAlertBox').remove();
    });
    let AlertMsg = setTimeout(function () {
        $('#alertMessage').fadeOut(800, function () {
            $('#signerAlertBox').remove();
        });
    }, timer);
}

function getSignature(othis, isInit = false, returnSignature = false) {
    return new Promise(resolve => {
            let signer, signerType = "";
            let isSigned = isDataURL($(othis).attr('src'));
            let libUrl = "./";

            if ($(othis).attr('src') != signhere && !isInit) {
                $(othis).attr('src', signhere);
                return;
            }
            if (typeof webRoot !== 'undefined' && typeof webRoot !== null) {
                libUrl = webRoot + '/portal/';
            } else {
                libUrl = top.webroot_url ? (top.webroot_url + '/portal/') : "./";
            }

            if (typeof cpid === 'undefined' && typeof cuser === 'undefined') {
                cpid = $(othis).data('pid');
                cuser = $(othis).data('user');
            }
            let otype = $(othis).attr('data-type');
            if (typeof otype === 'undefined' || typeof otype === null) {
                otype = $(othis).data('type');
            }
            if (otype == 'admin-signature') {
                signer = adminName ? adminName : cuser;
                signerType = "admin-signature";
                $("#isAdmin").prop('checked', true);
                isAdmin = true;
            } else {
                signer = ptName;
                signerType = "patient-signature";
                $("#isAdmin").prop('checked', false);
                isAdmin = false;
            }

            let params = {
                pid: cpid,
                user: cuser,
                is_portal: isPortal,
                type: signerType
            };

            let url = libUrl + "sign/lib/show-signature.php";
            fetch(url, {
                credentials: 'include',
                method: 'POST',
                body: JSON.stringify(params),
                headers: {
                    'Accept': 'application/json, text/plain, */*',
                    'Content-Type': 'application/json'
                }
            }).then(signature => signature.json()).then(signature => {
                if (returnSignature === true) {
                    return signature;
                }
                placeSignature(signature, othis).then(function (r) {
                    resolve(r)
                })

            }).catch(error => signerAlertMsg(error));
        }
    )
}

function placeSignature(responseData, el) {
    return new Promise(resolve => {
        if (responseData.message === "error") {
            $(el).attr('src', "");
            $(el).attr('alt', "No Signature on File");
            signerAlertMsg('Error Patient and or User Id missing');
            return;
        } else if (responseData.message === "insert error") {
            $(el).attr('src', "");
            $(el).attr('alt', "No Signature on File");
            signerAlertMsg('Error adding signature');
            return;
        } else if (responseData.message === "waiting" && $(el).attr('data-type') === 'patient-signature') {
            $(el).attr('src', "");
            $(el).attr('alt', "No Signature on File");
            $("#isAdmin").attr('checked', false);
            return;
        } else if (responseData.message === "waiting" && $(el).attr('data-type') === 'admin-signature') {
            $(el).attr('src', "");
            $(el).attr('alt', "No Signature on File");
            $("#isAdmin").attr('checked', true);
            return;
        } else if (responseData.message === "waiting") {
            $(el).attr('src', "");
            $(el).attr('alt', "No Signature on File");
            return;
        }
        let i = new Image();
        i.onload = function () {
            $(el).attr('src', i.src)
            resolve('done'); // display image
        };
        i.src = isDataURL(responseData) ? responseData : 'data:image/png;base64,' + responseData; // load image
    })
}

function archiveSignature(signImage = '', edata = '') {
    let libUrl, signer, signerType = "";
    let pid = 0;
    let data = {};

    if (typeof webRoot !== 'undefined' && typeof webRoot !== null) {
        libUrl = webRoot + '/portal/';
    } else {
        libUrl = "./";
    }

    if (edata) {
        data = {
            pid: edata.data.cpid,
            user: edata.data.cuser,
            is_portal: isPortal,
            signer: edata.data.signer,
            type: edata.data.type,
            output: signImage
        };
    } else {
        if ($("#isAdmin").is(':checked') === false) {
            pid = cpid;
            signer = ptName ? ptName : cuser;
            signerType = "patient-signature";
        } else {
            pid = 0;
            signer = adminName ? adminName : cuser;
            signerType = "admin-signature";
        }
        data = {
            pid: pid,
            user: cuser,
            is_portal: isPortal,
            signer: signer,
            type: signerType,
            output: signImage
        };
    }
    let url = libUrl + "sign/lib/save-signature.php";
    fetch(url, {
        credentials: 'include',
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
            'Content-Type': 'application/json',
            'Connection': 'close'
        }
    }).then(response => response.json()).then(function (response) {
            $("#openSignModal").modal("hide");
        }
    ).catch(error => signerAlertMsg(error));

    return true;
}

function isDataURL(dataUrl) {
    return !!dataUrl.match(isDataURL.regex);
}

isDataURL.regex = /^\s*data:([a-z]+\/[a-z]+(;[a-z\-]+\=[a-z\-]+)?)?(;base64)?,[a-z0-9\!\$\&\'\,\(\)\*\+\,\;\=\-\.\_\~\:\@\/\?\%\s]*\s*$/i;

let responseChannel = '';
let actionChannel = '';
let AckBack = '';
let isRemoteAvail = false;
let iAmRemote = false;
// call if need to bind pen clicks after a dynamic template load. ie templates.
var bindFetch = '';

// fetch modal template and append to body.
//
$(function () {
    let url = top.webroot_url ? top.webroot_url : webRoot;
    url += "/portal/sign/assets/signer_modal.php?isPortal=" + encodeURIComponent(isPortal);
    fetch(url, {
        credentials: 'include'
    }).then(jsonTemplate => jsonTemplate.json()).then(jsonTemplate => {
        $("body").append(jsonTemplate);
    }).then(function () {
        initSignerApi();
    }).catch((error) => alert("Modal Template Fetch:" + error));
});

function initSignerApi() {
    iAmRemote = (typeof remoteDevice !== 'undefined');
    responseChannel = new BroadcastChannel('signer-channel:response');
    if (useRemote) actionChannel = new BroadcastChannel('signer-channel:action');

    function ack() {
        actionChannel.postMessage({cmd: 'available'});
        AckBack = setTimeout(function () {
            $lastEl.attr('src', signhere);
            isRemoteAvail = false;
            signerAlertMsg(msgAnswering, 7000);
        }, 1000);
    }

    responseChannel.onmessage = function (r) {
        let command = r.data.cmd;
        switch (command) {
            case 'fetch_response':
                if (r.data.auth !== apiToken) {
                    break;
                }
                doConfirm(r);
                break;
            case 'fetch_canceled':
                if (r.data.auth !== apiToken) {
                    break;
                }
                $lastEl.attr('src', signhere);
                signerAlertMsg(msgNoSign);
                break;
            case 'is_busy':
                if (r.data.auth !== apiToken) {
                    break;
                }
                signerAlertMsg(msgBusy);
                break;
            case 'working':
                if (r.data.auth !== apiToken) {
                    break;
                }
                let msg = msgWaiting + ": " + r.data.device;
                signerAlertMsg(msg, 5000, 'success');
                break;
            default:
                break;
        }
    };
    actionChannel.onmessage = function (r) {
        let command = r.data.cmd;
        switch (command) {
            case 'fetch_signature':
                if (iAmRemote) {
                    currentAuth = r.data.auth;
                    let isBusy = ($("#openSignModal").data('bs.modal') || {isShown: false}).isShown;
                    if (isBusy) {
                        responseChannel.postMessage({cmd: 'is_busy', device: remoteDevice, auth: currentAuth});
                        break;
                    }
                    responseChannel.postMessage({cmd: 'working', device: remoteDevice, auth: currentAuth});
                    $(".save").hide();
                    $(".send").show();
                    $(".signer-banners").hide();
                    callModal(r);
                }
                break;
            case 'fetch_confirmed':
                $(".send").hide();
                $(".save").show();
                break;
            case 'error' :
                $(".send").hide();
                $(".save").show();
                signerAlertMsg(msgFail);
                break;
            case 'device_report':
                if (iAmRemote && useRemote) {
                    currentAuth = r.data.auth;
                    actionChannel.postMessage({cmd: 'device_ack', device: remoteDevice, auth: currentAuth});
                }
                break;
            case 'available':
                if (iAmRemote && useRemote) {
                    let isBusy = ($("#openSignModal").data('bs.modal') || {isShown: false}).isShown;
                    actionChannel.postMessage({cmd: 'device_ack', polled: true, busy: isBusy, device: remoteDevice, auth: currentAuth});
                }
                break;
            case 'device_ack':
                isRemoteAvail = true;
                if (r.data.check_in === true) {
                    // report check in to everybody. Someone may be waiting to sign.
                    signerAlertMsg(msgCheckIn, 5000, 'success');
                    isRemoteAvail = true;
                    break;
                }
                if (r.data.auth !== apiToken) {
                    break;
                }
                if (r.data.polled === true) {
                    isRemoteAvail = true;
                    clearTimeout(AckBack);
                    break;
                }
                let msg = msgCheckIn + ": " + r.data.device;
                signerAlertMsg(msg, 2000, 'success');
                break;

            default:
                if (useRemote) actionChannel.postMessage({cmd: 'error', type: 'transactional', comment: "Unknown Command Received"});
                break;
        }
    };

    // ya think there'd be more!
    function callModal(e) {
        cpid = e.data.cpid;
        cuser = e.data.cuser;
        let type = e.data.type;
        let signerName = e.data.signerName || '';

        $('#openSignModal #name').val(signerName);
        $('#openSignModal #labelName').html("&nbsp;" + msgSignator + ":&nbsp;<b>" + signerName + "</b>");
        $('#openSignModal #pid').val(cpid);
        $('#openSignModal #user').val(cuser);
        $('#openSignModal #signatureModal').data('type', type);

        if (type === 'admin-signature') {
            adminName = signerName;
            $("#isAdmin").prop('checked', true);
            isAdmin = true;
        } else {
            ptName = signerName;
            $("#isAdmin").prop('checked', false);
            isAdmin = false;
        }
        $("#openSignModal").modal('show');
    }

    function doConfirm(result) {
        placeSignature(result.data.signature, $lastEl);
        if (useRemote) actionChannel.postMessage({cmd: 'fetch_confirmed'});
    }

    // global binding for signer form/template tag clicks.
    // gets called after a new/edited template gets loaded.
    // mostly used with Patient Documents list however,
    // existing portal workflow is legacy and will deprecate here soon.
    bindFetch = function () {
        $("img[data-action=fetch_signature]").on("click", function (e) {
            e.stopPropagation();
            e.preventDefault();
            $lastEl = $(this);
            let pid = $lastEl.data('pid');
            let user = $lastEl.data('user');
            let type = $lastEl.data('type');
            let signerName = '';
            let url = webRoot + "/portal/sign/lib/show-signature.php";
            if (!cpid) {
                cpid = pid;
            }
            if (!cuser) {
                cuser = user;
            }
            if (type === "admin-signature" && isPortal) {
                // don't allow patient to change user signature.
                return false;
            } else if (isPortal) {
                getSignature(this);
                return false;
            }
            $lastEl.attr('src', signhere);

            fetch(url, {
                credentials: 'include',
                method: 'POST',
                body: JSON.stringify({
                    pid: pid,
                    user: user,
                    type: type,
                    is_portal: isPortal,
                    mode: 'fetch_info'
                }),
                headers: {
                    'Accept': 'application/json, text/plain, */*',
                    'Content-Type': 'application/json'
                }
            }).then(response => response.json()).then(function (response) {
                let signerName = '';
                if (type === "admin-signature") {
                    signerName = response.userName;
                    adminName = signerName;
                } else {
                    signerName = response.ptName;
                    ptName = signerName;
                } // response.signature is available if needed in future.
                if (!isRemoteAvail) {
                    let e = [];
                    e.data = {
                        cpid: pid,
                        cuser: user,
                        type: type,
                        signerName: signerName
                    };
                    callModal(e);
                    return;
                }
                if (useRemote) {
                    actionChannel.postMessage({
                        cmd: 'fetch_signature',
                        auth: apiToken,
                        cpid: pid,
                        cuser: user,
                        signerName: signerName,
                        type: type
                    });
                    ack();
                }
            }).catch(error => signerAlertMsg(error));
        });

        $(function () {
            // default all signatures to icon regardless of new or edit..
            $(".signature").each(function (index, value) {
                if (!$(this).attr('src'))
                    $(this).attr('src', signhere);
            });
        });
    };

    // initial binds for form pen clicks
    bindFetch();

//-------------------- Continue loading seq with init of modal buttons and various bindings --------------------//
    $(function (global) {
        var wrapper = document.getElementById("openSignModal");
        var canvasOptions = {
            minWidth: .75,
            maxWidth: 2.75,
            penColor: 'rgb(0, 0, 0)',
            minDistance: 4,
            /*throttle: 0,*/
            velocityFilterWeight: .2,
        };
        var openPatientButton = document.querySelector("[data-type=patient-signature]");
        var openAdminButton = document.querySelector("[data-type=admin-signature]");
        var placeSignatureButton = wrapper.querySelector("[data-action=place]");
        var showSignature = wrapper.querySelector("[data-action=show]");
        var saveSignature = wrapper.querySelector("[data-action=save_signature]");
        var sendSignature = wrapper.querySelector("[data-action=send_signature]");
        var clearButton = wrapper.querySelector("[data-action=clear]");
        var canvas = wrapper.querySelector("canvas");
        let signaturePad;

        // this offsets signature image to center on element somewhat
        // on any form (css) box height:70px length:auto center at 20px.
        $(function (e) {
            let els = this.querySelectorAll("img[data-action=fetch_signature]");
            let i; // caution using let in for
            for (i = 0; i < els.length; i++) {
                els[i].style.top = (els[i].offsetTop - 20) + 'px';
                els[i].setAttribute("data-offset", true);
            }
        });

        $("#openSignModal .close").on("click", function (e) {
            signaturePad.clear();
        });

        if (typeof placeSignatureButton === 'undefined' || !placeSignatureButton) {
            placeSignatureButton = wrapper.querySelector("[data-action=place]");
        }

        // for our dynamically added modal
        $("#openSignModal").on('show.bs.modal', function (e) {
            let type = $('#openSignModal #signatureModal').data('type');
            if (type) {
                if (type === "admin-signature") {
                    $("#isAdmin").prop('checked', true);
                    placeSignatureButton.setAttribute("data-type", type);
                    isAdmin = true;
                } else {
                    $("#isAdmin").prop('checked', false);
                    placeSignatureButton.setAttribute("data-type", type);
                    isAdmin = false;
                }
                $('#signatureModal').data('type', type);
            }
            let showElement = document.getElementById('signatureModal');
            $('#signatureModal').attr('src', signhere);
            $("#openSignModal").modal({backdrop: false});
            $('html').css({
                'overflow': 'hidden', 'padding-right': '15px'
            });
            $(this).css({
                'padding-right': '0px'
            });
            $('body').bind('selectstart', function () {
                return false;
            });
            $(this).modal('handleUpdate');
        }).on('shown.bs.modal', function (e) {
            signaturePad = new SignaturePad(canvas, canvasOptions);
            resizeCanvas();
        }).on('hide.bs.modal', function () {
            if ((typeof $lastEl !== 'undefined' || !$lastEl) && typeof event === "undefined" && !isRemoteAvail) {
                if (!signaturePad.isEmpty()) {
                    let dataURL = signaturePad.toDataURL();
                    placeSignature(dataURL, $lastEl);
                }
            }
        }).on('hidden.bs.modal', function () {
            if (iAmRemote) {
                $(".signer-banners").show();
                if (responseChannel !== '') {
                    if (signaturePad.isEmpty()) {
                        if (useRemote) responseChannel.postMessage({cmd: 'fetch_canceled', auth: currentAuth});
                    }
                }
            }
            $('html').css({
                'overflow': 'inherit'
            });
            $('body').unbind('selectstart');
        });

        clearButton.addEventListener("click", function (event) {
            signaturePad.clear();
        });

        sendSignature.addEventListener("click", function (event) {
            if (signaturePad.isEmpty()) {
                signerAlertMsg(msgNeedSign, 3000);
            } else {
                let dataURL = signaturePad.toDataURL();
                archiveSignature(encodeURIComponent(dataURL));
                if (useRemote)
                    responseChannel.postMessage({cmd: 'fetch_response', signature: dataURL, auth: currentAuth});
            }
        });

        saveSignature.addEventListener("click", function (event) {
            if (signaturePad.isEmpty()) {
                signerAlertMsg(msgNeedSign, 3000);
            } else {
                let signerName, type = '';
                if ($("#isAdmin").is(':checked') === true) {
                    type = "admin-signature";
                    isAdmin = true;
                } else {
                    type = "patient-signature";
                }
                webRoot = webRoot ? webRoot : top.webroot_url;
                let url = webRoot + "/portal/sign/lib/show-signature.php";
                fetch(url, {
                    credentials: 'include',
                    method: 'POST',
                    body: JSON.stringify({
                        pid: cpid,
                        user: cuser,
                        type: type,
                        is_portal: isPortal,
                        mode: 'fetch_info'
                    }),
                    headers: {
                        'Accept': 'application/json, text/plain, */*',
                        'Content-Type': 'application/json'
                    }
                }).then(response => response.json()).then(function (response) {
                    signerName = ptName = response.ptName;
                    if (isAdmin) {
                        signerName = adminName = response.userName;
                    }
                    let dataURL = signaturePad.toDataURL();
                    let e = [];
                    e.data = {
                        cpid: cpid,
                        cuser: cuser,
                        type: type,
                        signer: signerName
                    };
                    archiveSignature(encodeURIComponent(dataURL), e);
                });
            }
        });

        placeSignatureButton.addEventListener("click", function (event) {
            let thisElement = $(this);
            getSignature(thisElement, true).then(r => {
                let imgurl = thisElement.attr('src');
                signaturePad.fromDataURL(imgurl);
            });
        });

        if (showSignature !== null)
            showSignature.addEventListener("click", function (event) { // for modal view
                let showElement = document.getElementById('signatureModal');
                getSignature(showElement);
            });

        function resizeCanvas() {
            let ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        }

        // this is nifty JS download. leaving for future.
        // plus someone may come across and find useful.
        function download(dataURL, filename) {
            let blob = dataURLToBlob(dataURL);
            let url = window.URL.createObjectURL(blob);
            let a = document.createElement("a");
            a.style = "display: none";
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        }

        function dataURLToBlob(dataURL) {
            // Code taken from https://github.com/ebidel/filer.js
            let parts = dataURL.split(';base64,');
            let contentType = parts[0].split(":")[1];
            let raw = window.atob(parts[1]);
            let rawLength = raw.length;
            let uInt8Array = new Uint8Array(rawLength);

            for (var i = 0; i < rawLength; ++i) {
                uInt8Array[i] = raw.charCodeAt(i);
            }

            return new Blob([uInt8Array], {type: contentType});
        }

        // TODO create method to remove line on canvas save
        function drawSignatureLine() {
            let context = canvas.getContext('2d');
            context.lineWidth = .4;
            context.strokeStyle = '#333';
            context.beginPath();
            context.moveTo(0, 200);
            context.lineTo(900, 200);
            context.stroke();
        }

        // resize  event and initial resize
        window.onresize = function () {
            resizeCanvas();
        };
        resizeCanvas();

        if (iAmRemote) {
            // tell user that a signer is available
            if (useRemote) actionChannel.postMessage({cmd: 'device_ack', device: remoteDevice, check_in: true});
        } else {
            // check if a remote signer is available
            if (useRemote) actionChannel.postMessage({cmd: 'device_report', auth: apiToken});
        }

    }); // dom

}
