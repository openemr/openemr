/**
 * canvasdraw.js
 *
 * Base concept code by Chtiwi Malek ===> CODICODE.COM
 * Adapted to process multiple canvases on a single page
 *
 * Copyright (C) 2016 Raymond Magauran <magauran@MedFetch.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Ray Magauran <magauran@MedFetch.com>
 * @link http://www.open-emr.org
 */



var mousePressed = false;
var lastX, lastY;
var ctx = new Array();
var image = new Array();
var canvasPic = new Array();
var zone;
var cPushArray = new Array();
var cStep = new Array();

function InitThis(zone) {
    ctx[zone] = document.getElementById('myCanvas_'+zone).getContext("2d");
    $('#myCanvas_'+zone).mousedown(function (e) {
        mousePressed = true;
        Draw(e.pageX - $(this).offset().left, e.pageY - $(this).offset().top, false, zone);
    });

    $('#myCanvas_'+zone).mousemove(function (e) {
        if (mousePressed) {
            Draw(e.pageX - $(this).offset().left, e.pageY - $(this).offset().top, true, zone);
        }
    });

    $('#myCanvas_'+zone).mouseup(function (e) {
        if (mousePressed) {
            mousePressed = false;
            cPush(zone);
        }
    });

    $('#myCanvas_'+zone).mouseleave(function (e) {
        if (mousePressed) {
            mousePressed = false;
            cPush(zone);
        }
    });
    drawImage(zone);
}
function drawImage(zone) {
    image[zone] = new Image();
        // We need to get the openEMR pointer for this image, which is either
        // a stored image from a previous drawing and is not editable (VIEW)
        // or the base image.
        // The PHP code determines which when the page is called
        // and stores it in this id-->
    image[zone].src = $("#url_"+zone).val();
    $(image[zone]).load(function () {
                        ctx[zone].drawImage(image[zone], 0, 0, 450, 225);
                        // using variable size canvas? -> adjust size for canvas
    cPush(zone);
    });    
}

function Draw(x, y, isDown,zone) {
    if (isDown) {
        ctx[zone].beginPath();
        ctx[zone].strokeStyle = $('#selColor_'+zone).val();
        ctx[zone].lineWidth = $('#selWidth_'+zone).val();
        ctx[zone].lineJoin = "round";
        ctx[zone].moveTo(lastX, lastY);
        ctx[zone].lineTo(x, y);
        ctx[zone].closePath();
        ctx[zone].stroke();
    }
    lastX = x;
    lastY = y;
}

function cPush(zone) {
    if (typeof(cStep[zone]) == 'undefined') { cStep[zone] = -1; }
    cStep[zone]++;
    if (typeof(cPushArray[zone]) == 'undefined') { cPushArray[zone] = new Array;}
    if (cStep[zone] < cPushArray[zone].length) { cPushArray[zone].length = cStep[zone]; }
    cPushArray[zone].push(document.getElementById('myCanvas_'+zone).toDataURL('image/jpeg'));
        //    document.title = cStep[zone] + ":" + cPushArray[zone].length + ":" +zone;
}

function cUndo(zone) {
    if (cStep[zone] > 0) {
        cStep[zone]--;
            // here = cStep[zone]
            //alert(cPushArray[zone][cStep[zone]]);
        canvasPic = new Image();
        canvasPic.src = cPushArray[zone][cStep[zone]];
        canvasPic.onload = function () { ctx[zone].drawImage(canvasPic, 0, 0); }
            //    document.title = cStep[zone] + ":" + cPushArray[zone].length + ":" +zone;
    }
}
function cRedo(zone) {
    if (cStep[zone] < cPushArray[zone].length-1) {
        cStep[zone]++;
        canvasPic = new Image();
        canvasPic.src = cPushArray[zone][cStep[zone]];
        canvasPic.onload = function () { ctx[zone].drawImage(canvasPic, 0, 0); }
            //   document.title = cStep[zone] + ":" + cPushArray[zone].length + ":" +zone;
    }
}
function cReload(zone) {
    $('#url_'+zone).val($('#base_url_'+zone).val());
    drawImage(zone);
}
function cBlank(zone) {
    $('#url_'+zone).val('../images/BLANK_BASE.png');
    drawImage(zone);
}
    //each canvas on your page must be initialized
    // add this to your own forms/js files where HPI is replaced with your canvas Identifier
    // myCanvas_HPI --> myCanvas_YOURIDENTIFIER
InitThis('HPI');
InitThis('PMH');
InitThis('EXT');
InitThis('ANTSEG');
InitThis('RETINA');
InitThis('NEURO');
InitThis('IMPPLAN');
