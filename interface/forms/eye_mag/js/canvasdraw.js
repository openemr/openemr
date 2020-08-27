/**
 * canvasdraw.js
 *
 * Base concept code by Chtiwi Malek ===> CODICODE.COM
 * Adapted to process multiple canvases on a single page
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <magauran@MedFetch.com>
 * @copyright Copyright (c) 2016 Raymond Magauran <magauran@MedFetch.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// eslint-disable-next-line no-var
var mousePressed = false;
// eslint-disable-next-line no-var
var lastX;
// eslint-disable-next-line no-var
var lastY;
// eslint-disable-next-line no-var
var ctx = [];
// eslint-disable-next-line no-var
var image = [];
// eslint-disable-next-line no-var
var canvasPic = [];
// eslint-disable-next-line no-var
var zone;
// eslint-disable-next-line no-var
var cPushArray = [];
// eslint-disable-next-line no-var
var cStep = [];

function cPush(zone) {
    if (typeof (cStep[zone]) === 'undefined') {
        cStep[zone] = -1;
    }
    cStep[zone] += 1;
    if (typeof (cPushArray[zone]) === 'undefined') {
        cPushArray[zone] = [];
    }
    if (cStep[zone] < cPushArray[zone].length) {
        cPushArray[zone].length = cStep[zone];
    }
    cPushArray[zone].push(document.getElementById(`myCanvas_${zone}`).toDataURL('image/jpeg'));
}

function drawImage(zone) {
    image[zone] = new Image();
    // We need to get the openEMR pointer for this image, which is either
    // a stored image from a previous drawing and is not editable (VIEW)
    // or the base image.
    // The PHP code determines which when the page is called
    // and stores it in this id-->
    image[zone].src = $(`#url_${zone}`).val();
    $(image[zone]).on('load', function () {
        ctx[zone].drawImage(image[zone], 0, 0, 450, 225);
        // using variable size canvas? -> adjust size for canvas
        cPush(zone);
    });
}

function Draw(x, y, isDown, zone) {
    if (isDown) {
        ctx[zone].beginPath();
        ctx[zone].strokeStyle = $(`#selColor_${zone}`).val();
        ctx[zone].lineWidth = $(`#selWidth_${zone}`).val();
        ctx[zone].lineJoin = 'round';
        ctx[zone].moveTo(lastX, lastY);
        ctx[zone].lineTo(x, y);
        ctx[zone].closePath();
        ctx[zone].stroke();
    }
    lastX = x;
    lastY = y;
}

function cUndo(zone) {
    if (cStep[zone] > 0) {
        cStep[zone] -= 1;
        const canvasPic = new Image();
        canvasPic.src = cPushArray[zone][cStep[zone]];
        canvasPic.onload = function () {
            ctx[zone].drawImage(canvasPic, 0, 0);
        };
    }
}

function cRedo(zone) {
    if (cStep[zone] < cPushArray[zone].length - 1) {
        cStep[zone] += 1;
        const canvasPic = new Image();
        canvasPic.src = cPushArray[zone][cStep[zone]];
        canvasPic.onload = function () {
            ctx[zone].drawImage(canvasPic, 0, 0);
        };
    }
}

function cReload(zone) {
    $(`#url_${zone}`).val($(`#base_url_${zone}`).val());
    drawImage(zone);
}

function cReplace(zone) {
    $(`#${zone}_olddrawing`).addClass('nodisplay');
    $(`#${zone}_canvas`).show();
    const canvasPic = new Image();
    canvasPic.src = $(`#url_${zone}`).val();
    ctx[zone].drawImage(canvasPic, 0, 0);
    cPush(zone);
    drawImage(zone);
}

function cBlank(zone) {
    $(`#url_${zone}`).val('../images/BLANK_BASE.png');
    drawImage(zone);
    cPush(zone);
}

function InitThis(zone) {
    ctx[zone] = document.getElementById(`myCanvas_${zone}`).getContext('2d');

    $(`#myCanvas_${zone}`).on('touchstart', function (e) {
        mousePressed = true;
        Draw(e.pageX - $(this).offset().left, e.pageY - $(this).offset().top, false, zone);
    });

    $(`#myCanvas_${zone}`).on('touchmove', function (e) {
        if (mousePressed) {
            e.preventDefault();
            Draw(e.pageX - $(this).offset().left, e.pageY - $(this).offset().top, true, zone);
        }
    });
    $(`#myCanvas_${zone}`).on('touchend', function (e) {
        mousePressed = false;
        cPush(zone);
    });

    $(`#myCanvas_${zone}`).mousedown(function (e) {
        mousePressed = true;
        Draw(e.pageX - $(this).offset().left, e.pageY - $(this).offset().top, false, zone);
    });
    $(`#myCanvas_${zone}`).mousemove(function (e) {
        if (mousePressed) {
            Draw(e.pageX - $(this).offset().left, e.pageY - $(this).offset().top, true, zone);
        }
    });
    $(`#myCanvas_${zone}`).mouseup(function (e) {
        if (mousePressed) {
            mousePressed = false;
            cPush(zone);
        }
    });

    $(`#myCanvas_${zone}`).mouseleave(function (e) {
        if (mousePressed) {
            mousePressed = false;
            cPush(zone);
        }
    });
    drawImage(zone);
}

// Each canvas on your page must be initialized
// add this to your own forms/js files where HPI is replaced with your canvas Identifier
// myCanvas_HPI --> myCanvas_YOURIDENTIFIER
InitThis('HPI');
InitThis('PMH');
InitThis('EXT');
InitThis('ANTSEG');
InitThis('RETINA');
InitThis('NEURO');
InitThis('IMPPLAN');
