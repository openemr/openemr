/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function getSignature(othis) {
    let libUrl, signer, signerType = "";
    let isLink = $(othis).attr('src').indexOf('signhere');

    if ($(othis).attr('src') != signhere && isLink == -1) {
        $(othis).attr('src', signhere);
        return;
    }
    try {
        if (webRoot !== undefined && webRoot !== null) {
            libUrl = webRoot + '/portal/';
        }
    } catch (e) {
        libUrl = "./";
    }
    if ($(othis).attr('type') == 'admin-signature') {
        signer = cuser;
        signerType = "admin-signature";
    } else {
        signer = ptName;
        signerType = "patient-signature";
    }
    let params = {
        pid: cpid,
        user: cuser,
        signer: signer,
        type: signerType
    };

    let url = libUrl + "sign/lib/show-signature.php";
    fetch(url, {
        method: 'POST',
        body: JSON.stringify(params),
        headers: {
            'Accept': 'application/json, text/plain, */*',
            'Content-Type': 'application/json'
        }
    }).then(signature => signature.json())
      .then(signature => {
        placeImg(signature, othis)
    }).catch(error => alert(error));
}

function placeImg(responseData, el) {
    if (responseData == "error") {
        $(el).attr('src', "");
        alert('Error Patient and or User Id missing');
        return;
    }
    else if (responseData == "insert error") {
        $(el).attr('src', "");
        alert('Error adding signature');
        return;
    }
    else if (responseData == "waiting" && $(el).attr('type') == 'patient-signature') {
        $(el).attr('src', "");
        alert('Signature not on file. Please sign');
        $("#isAdmin").attr('checked', false);
        $("#openSignModal").modal("show");
        return;
    }
    else if (responseData == "waiting" && $(el).attr('type') == 'admin-signature') {
        $(el).attr('src', "");
        alert('Signature not on file. Please sign');
        $("#isAdmin").attr('checked', true);
        $("#openSignModal").modal("show");
        return;
    }
    let i = new Image();
    i.onload = function () {
        $(el).attr('src', i.src); // display image
    };
    i.src = isDataURL(responseData) ? responseData : 'data:image/png;base64,' + responseData; // load image
}

function signDoc(signImage) {
    let libUrl, signer, signerType = "";
    let pid = 0;

    try {
        if (webRoot !== undefined && webRoot !== null)
            libUrl = webRoot + '/portal/';
    } catch (e) {
        libUrl = "./";
    }
    if ($("#isAdmin").is(':checked') == false) {
        pid = cpid;
        signer = ptName;
        signerType = "patient-signature";
    } else {
        pid = 0;
        signer = cuser;
        signerType = "admin-signature";
    }
    let data = {
        pid: pid,
        user: cuser,
        signer: signer,
        type: signerType,
        output: signImage
    };

    let url = libUrl + "sign/lib/save-signature.php";
    fetch(url, {
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
            'Content-Type': 'application/json',
            'Connection': 'close'
        }
    }).then(response => response.text())
      .then(
        $("#loading").toggle(),
        $("#openSignModal").modal("hide")
      ).catch(error => alert(error));

    $("#loading").toggle();
}

function isDataURL(dataUrl) {
    return !!dataUrl.match(isDataURL.regex);
}
isDataURL.regex = /^\s*data:([a-z]+\/[a-z]+(;[a-z\-]+\=[a-z\-]+)?)?(;base64)?,[a-z0-9\!\$\&\'\,\(\)\*\+\,\;\=\-\.\_\~\:\@\/\?\%\s]*\s*$/i;

$(function () {
    let isAdmin = 0;
    let url = top.webroot_url ? top.webroot_url : webRoot;
    url += "/portal/sign/assets/signer_modal.tpl.php?isAdmin=" + encodeURIComponent(isAdmin);
    fetch(url)
        .then(jsonTemplate => jsonTemplate.json())
        .then(jsonTemplate => {
            $("body").append(jsonTemplate);
        })
        .then(function () {
            initSignerApi();
        })
        .catch((error) => alert(error));
});

function initSignerApi() {
    $(function () {
        const canvasOptions = {
            minWidth: 1,
            maxWidth: 2,
            minDistance: 4,
            throttle: 0,
            velocityFilterWeight: .2,
            penColor: 'rgb(0, 0, 255)',
        };
        var wrapper = document.getElementById("openSignModal");
        var placeSignature = wrapper.querySelector("[data-action=place]");
        var showSignature = wrapper.querySelector("[data-action=show]");
        var clearButton = wrapper.querySelector("[data-action=clear]");
        var saveSignatureButton = wrapper.querySelector("[data-action=save-png]");
        var canvas = wrapper.querySelector("canvas");
        var signaturePad;
        var isAdmin = false;

        $("#openSignModal").on('show.bs.modal', function (e) {
            let triggeredBy = $(e.relatedTarget);
            let type = triggeredBy.prop('type');
            if (type === "admin-signature") {
                $("#isAdmin").prop('checked', true);
                placeSignature.setAttribute("type", type);
                isAdmin = true;
            }
            $(this).data('bs.modal').options.backdrop = 'static';
            $('html').css({
                'overflow': 'hidden'
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
            $('html').css({
                'overflow': 'inherit'
            });
            $('body').unbind('selectstart');
        });

        clearButton.addEventListener("click", function (event) {
            signaturePad.clear();
        });

        saveSignatureButton.addEventListener("click", function (event) {
            if (signaturePad.isEmpty()) {
                alert("Please provide a signature first.");
            } else {
                let dataURL = signaturePad.toDataURL();
                signDoc(encodeURIComponent(dataURL));
            }
        });

        placeSignature.addEventListener("click", function (event) {
            let thisElement = $(this);
            getSignature(thisElement);
        });

        showSignature.addEventListener("click", function (event) {
            let thisElement = $(this);
            let showElement = document.getElementById('signatureModal');
            getSignature(showElement);
        });

        function resizeCanvas() {
            let ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        }

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
        window.onresize = resizeCanvas;
        resizeCanvas();
    });
}

const signhere = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFQAAABUCAYAAAAcaxDBAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEwAACxMBAJqcGAAAAphJREFUeJzt2j1oE2Ecx/FvMdW+biK+1EVxEnESm0Xcxero6Gqto+iqKHQTcXUQHBTqUKiL0KUWFAQp6KQdRKPopA2+VjHn8L/jee54ckm0zeXq7wOBp/dcycOXa3N5EhAREREREREREREREREREZH1sqnoBfSIHcBpoAp8AOqFrqaktgH9wBEsYBQ/vgHHC1xXKe0EXgDzwGcs5BLwOB6vAhOFra5kkpiR95gHBoEKMIOLeqygNZaGH/M98DMe38NiQjrqJ2C0+8ssBz/ma2APcBIXdYZ01Fp8/Gi3F1oGoZiJUNQq9icfAfu7utIS2I6LWSMdM+FHfYB71Z/r0hpLZRgXtI5dfSEncFEjYCH+XcmYBBq4UHVgvMm5E6RfqPTGJ8OPeQNYJD/qGe/8a11aY2lM4q7KJM4I8BAX9bB3vmLmCMVMjGD/HyNgBYuqmDnyYiaGcVfqFxSzqbO0jgl2X/rSO1cxA/4mZg141OL8/1K7MXeRjrkXGFj31ZXMv8SUjCk6j/kGxQzqJOYyiplLMddQFRdzCehrcl42ZmiXSYD7tL5/HEMx23IQi/Qb2+wIRVXMDtzFQt2Jf75IOqofM7szLxn7sCuzARzwjvtRk512xWzDTSzWbOb4AG73SDHbtBu3k34oPjYKXMC+OpPEfIVituU6FqwBnAMuAx9JX5VT9MD78mb3cJ0YBLZkjv0CvsbjIWBzZn4V+B6PK9imry/5fwiwFQs2FHjuZWAauB0/54ZwhfR9YYS9GiduBeanvfnxwPyKN38pMP8MOEUPfohWaX1K4ca88RPgKvZ5eVTMcvKtRdCFwLHn3ngOeJuZX/TG77BIvh/e+Dy2U/QU+1KXiIiIiIiIiIiIiIiIiIiIiIhsfH8AekL6s5feEc0AAAAASUVORK5CYII=";
