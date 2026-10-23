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

var mousePressed = false;
var lastX, lastY;
var ctx = new Array();
var image = new Array();
var canvasPic = new Array();
var zone;
var cPushArray = new Array();
var cStep = new Array();
var eyeCanvasDirty = Object.create(null);
// Keep newly drawn ink separate so reusable overlays retain real transparency.
var eyeCanvasInk = Object.create(null);
var eyeCanvasInkHistory = Object.create(null);
var eyeCanvasFlattened = Object.create(null);
var eyeCanvasFlattenedHistory = Object.create(null);
var eyeCanvasRestoreGeneration = Object.create(null);
var eyeCanvasRestoring = Object.create(null);
var eyeCanvasReady = Object.create(null);
var eyeCanvasFinishLoading = Object.create(null);
var eyeCanvasRestoreError = Object.create(null);
function eyeDrawingIsRestoring(zone) { return eyeCanvasRestoring[zone] === true; }
function eyeDrawingInkReady(zone) {
    var pending = eyeCanvasReady[zone];
    return Promise.resolve(pending).then(function () {
        if (pending !== eyeCanvasReady[zone]) return eyeDrawingInkReady(zone);
        if (eyeCanvasRestoreError[zone]) throw eyeCanvasRestoreError[zone];
    });
}
function eyeDrawingInkCanvas(zone) { return eyeCanvasInk[zone] || null; }
function eyeDrawingHasFlattenedBase(zone) { return eyeCanvasFlattened[zone] === true; }
function resetEyeDrawingInk(zone, canvas, source) {
    var ink = document.createElement('canvas');
    ink.width = canvas.width;
    ink.height = canvas.height;
    eyeCanvasInk[zone] = ink;
    eyeCanvasRestoreGeneration[zone] = (eyeCanvasRestoreGeneration[zone] || 0) + 1;
    eyeCanvasRestoring[zone] = false;
    eyeCanvasRestoreError[zone] = null;
    eyeCanvasReady[zone] = Promise.resolve();
    eyeCanvasFlattened[zone] = /controller\.php\?document(?:&|$)/.test(source);
}

function markEyeCanvasDirty(zone)
{
    eyeCanvasDirty[zone] = true;
}

function clearEyeCanvasDirty(zone)
{
    eyeCanvasDirty[zone] = false;
}

function isEyeCanvasDirty(zone)
{
    return eyeCanvasDirty[zone] === true;
}

function InitThis(zone)
{
    clearEyeCanvasDirty(zone);
    ctx[zone] = document.getElementById('myCanvas_' + zone).getContext("2d");

    $('#myCanvas_' + zone).on('touchstart', function (e) {
        mousePressed = true;
        Draw(e.pageX - $(this).offset().left, e.pageY - $(this).offset().top, false, zone);
    });

    $('#myCanvas_' + zone).on('touchmove',function (e) {
        if (mousePressed) {
            e.preventDefault();
            Draw(e.pageX - $(this).offset().left, e.pageY - $(this).offset().top, true, zone);
        }
    });
    $('#myCanvas_' + zone).on('touchend', function (e) {
        mousePressed = false;
        cPush(zone);
    });

    $('#myCanvas_' + zone).mousedown(function (e) {
        mousePressed = true;
        Draw(e.pageX - $(this).offset().left, e.pageY - $(this).offset().top, false, zone);
    });
    $('#myCanvas_' + zone).mousemove(function (e) {
        if (mousePressed) {
            Draw(e.pageX - $(this).offset().left, e.pageY - $(this).offset().top, true, zone);
        }
    });
    $('#myCanvas_' + zone).mouseup(function (e) {
        if (mousePressed) {
            mousePressed = false;
            cPush(zone);
        }
    });

     $('#myCanvas_' + zone).mouseleave(function (e) {
        if (mousePressed) {
            mousePressed = false;
            cPush(zone);
        }
     });
    drawImage(zone);
}

function drawImage(zone, preparedImage)
{
    if (preparedImage && (!preparedImage.complete || !(preparedImage.naturalWidth > 0) || !(preparedImage.naturalHeight > 0))) {
        return false;
    }
    var loadedImage = preparedImage || new Image();
    if (eyeCanvasFinishLoading[zone]) eyeCanvasFinishLoading[zone]();
    eyeCanvasRestoreGeneration[zone] = (eyeCanvasRestoreGeneration[zone] || 0) + 1;
    eyeCanvasRestoring[zone] = true;
    eyeCanvasRestoreError[zone] = null;
    var finishLoading;
    eyeCanvasReady[zone] = new Promise(function (resolve) { finishLoading = resolve; });
    eyeCanvasFinishLoading[zone] = finishLoading;
    image[zone] = loadedImage;
        // We need to get the openEMR pointer for this image, which is either
        // a stored image from a previous drawing and is not editable (VIEW)
        // or the base image.
        // The PHP code determines which when the page is called
        // and stores it in this id-->
    function renderLoadedImage() {
                        // A newer request owns this canvas. An old image load must
                        // never redraw it or trigger the newer request's save hook.
                        if (image[zone] !== loadedImage) { finishLoading(); return false; }
                        var canvas = document.getElementById('myCanvas_' + zone);
                        if (!canvas || !ctx[zone] || !(loadedImage.naturalWidth > 0) || !(loadedImage.naturalHeight > 0)) {
                            eyeCanvasRestoring[zone] = false;
                            eyeCanvasRestoreError[zone] = new Error('The drawing could not be loaded.');
                            finishLoading();
                            return false;
                        }
                        ctx[zone].save();
                        ctx[zone].fillStyle = '#fff';
                        ctx[zone].fillRect(0, 0, canvas.width, canvas.height);
                        ctx[zone].restore();
                        var isRopBase = zone === 'SDRETINA' &&
                            ($('#current_background_SDRETINA').val() || $('#background_SDRETINA').val()) === 'retina' &&
                            /_RETINA_BASE\.jpg(?:[?#].*)?$/i.test(loadedImage.src);
                        if (isRopBase) {
                            // The ROP source contains unused white space below the
                            // retinal diagrams. Crop that space, preserve the source
                            // aspect ratio, and align the disc/macula axis to the
                            // canvas midpoint. Do not stretch the clinical diagram.
                            var sourceHeight = Math.min(202, loadedImage.naturalHeight);
                            var sourceAxisY = Math.min(105, sourceHeight / 2);
                            var scale = Math.min(canvas.width / loadedImage.naturalWidth, canvas.height / sourceHeight);
                            var drawWidth = loadedImage.naturalWidth * scale;
                            var drawHeight = sourceHeight * scale;
                            var drawX = (canvas.width - drawWidth) / 2;
                            var drawY = (canvas.height / 2) - (sourceAxisY * scale);
                            ctx[zone].save();
                            ctx[zone].fillStyle = '#fff';
                            ctx[zone].fillRect(0, 0, canvas.width, canvas.height);
                            ctx[zone].drawImage(
                                loadedImage,
                                0, 0, loadedImage.naturalWidth, sourceHeight,
                                drawX, drawY, drawWidth, drawHeight
                            );
                            // Match the header baseline of the standard 1000 x 500
                            // retinal drawing while centering each label over its ROP
                            // diagram.
                            ctx[zone].fillStyle = '#111';
                            ctx[zone].font = 'bold 20px Arial, sans-serif';
                            ctx[zone].textAlign = 'center';
                            ctx[zone].textBaseline = 'middle';
                            ctx[zone].fillText('OD', drawX + (110 * scale), 25);
                            ctx[zone].fillText('OS', drawX + (342 * scale), 25);
                            ctx[zone].restore();
                        } else if (loadedImage.naturalWidth !== canvas.width || loadedImage.naturalHeight !== canvas.height) {
                            var scale = Math.min(canvas.width / loadedImage.naturalWidth, canvas.height / loadedImage.naturalHeight);
                            var drawWidth = loadedImage.naturalWidth * scale;
                            var drawHeight = loadedImage.naturalHeight * scale;
                            ctx[zone].save();
                            ctx[zone].fillStyle = '#fff';
                            ctx[zone].fillRect(0, 0, canvas.width, canvas.height);
                            ctx[zone].drawImage(loadedImage, (canvas.width - drawWidth) / 2, (canvas.height - drawHeight) / 2, drawWidth, drawHeight);
                            ctx[zone].restore();
                        } else {
                            ctx[zone].drawImage(loadedImage, 0, 0);
                        }
                        resetEyeDrawingInk(zone, canvas, loadedImage.src);
        finishLoading();
        cPush(zone);
        $(canvas).trigger('eye:image-loaded');
        return true;
    }
    if (preparedImage) {
        return renderLoadedImage();
    }
    // Attach handlers before src so cached images cannot outrun them.
    loadedImage.onerror = function () {
        if (image[zone] === loadedImage) {
            eyeCanvasRestoring[zone] = false;
            eyeCanvasRestoreError[zone] = new Error('The drawing could not be loaded.');
        }
        finishLoading();
    };
    $(loadedImage).on('load', renderLoadedImage);
    loadedImage.src = $("#url_" + zone).val();
    return true;
}

function Draw(x, y, isDown,zone)
{
    if (eyeCanvasRestoring[zone]) return;
    if (isDown) {
        markEyeCanvasDirty(zone);
        ctx[zone].beginPath();
        ctx[zone].strokeStyle = $('#selColor_' + zone).val();
        ctx[zone].lineWidth = $('#selWidth_' + zone).val();
        ctx[zone].lineJoin = "round";
        ctx[zone].moveTo(lastX, lastY);
        // Offset drawing coordinates so hash marks appear inside H pattern bars
        // rather than ON vertical lines. OD (left side) shifts right, OS (right side) shifts left.
        var drawX = x;
        var canvasWidth = ctx[zone].canvas.width;
        var zoneOffset = 4; // pixels to offset inward from vertical lines
        if (x < canvasWidth / 2) {
            drawX = x + zoneOffset;   // OD side: shift right into H bar
        } else {
            drawX = x - zoneOffset;   // OS side: shift left into H bar
        }
        ctx[zone].lineTo(drawX, y);
        ctx[zone].closePath();
        ctx[zone].stroke();
        var ink = eyeDrawingInkCanvas(zone);
        if (ink) {
            var inkContext = ink.getContext('2d');
            inkContext.beginPath();
            inkContext.strokeStyle = ctx[zone].strokeStyle;
            inkContext.lineWidth = ctx[zone].lineWidth;
            inkContext.lineJoin = ctx[zone].lineJoin;
            inkContext.moveTo(lastX, lastY);
            inkContext.lineTo(drawX, y);
            inkContext.closePath();
            inkContext.stroke();
        }
    }
    lastX = x;
    lastY = y;
}

function cPush(zone)
{
    if (eyeCanvasRestoring[zone]) return;
    if (typeof(cStep[zone]) == 'undefined') {
        cStep[zone] = -1; }
    cStep[zone]++;
    if (typeof(cPushArray[zone]) == 'undefined') {
        cPushArray[zone] = new Array;}
    if (cStep[zone] < cPushArray[zone].length) {
        cPushArray[zone].length = cStep[zone]; }
    cPushArray[zone].push(document.getElementById('myCanvas_' + zone).toDataURL('image/png'));
    if (!eyeCanvasInkHistory[zone]) eyeCanvasInkHistory[zone] = [];
    if (!eyeCanvasFlattenedHistory[zone]) eyeCanvasFlattenedHistory[zone] = [];
    eyeCanvasInkHistory[zone].length = cStep[zone];
    eyeCanvasFlattenedHistory[zone].length = cStep[zone];
    var ink = eyeDrawingInkCanvas(zone);
    eyeCanvasInkHistory[zone].push(ink ? ink.toDataURL('image/png') : '');
    eyeCanvasFlattenedHistory[zone].push(eyeCanvasFlattened[zone] === true);
}

function restoreEyeDrawingStep(zone) {
    var step = cStep[zone];
    var generation = (eyeCanvasRestoreGeneration[zone] || 0) + 1;
    eyeCanvasRestoreGeneration[zone] = generation;
    eyeCanvasRestoring[zone] = true;
    eyeCanvasRestoreError[zone] = null;
    function load(source) {
        if (!source) return Promise.resolve(null);
        return new Promise(function (resolve, reject) {
            var picture = new Image();
            picture.onload = function () { resolve(picture); };
            picture.onerror = function () { reject(new Error('The drawing history could not be restored.')); };
            picture.src = source;
        });
    }
    eyeCanvasReady[zone] = Promise.all([
        load(cPushArray[zone][step]), load((eyeCanvasInkHistory[zone] || [])[step])
    ]).then(function (pictures) {
        if (eyeCanvasRestoreGeneration[zone] !== generation) return;
        ctx[zone].clearRect(0, 0, ctx[zone].canvas.width, ctx[zone].canvas.height);
        ctx[zone].drawImage(pictures[0], 0, 0);
        var ink = eyeDrawingInkCanvas(zone);
        if (ink) {
            var inkContext = ink.getContext('2d');
            inkContext.clearRect(0, 0, ink.width, ink.height);
            if (pictures[1]) inkContext.drawImage(pictures[1], 0, 0);
        }
        eyeCanvasFlattened[zone] = !!(eyeCanvasFlattenedHistory[zone] || [])[step];
    }).catch(function (error) {
        if (eyeCanvasRestoreGeneration[zone] === generation) eyeCanvasRestoreError[zone] = error;
    }).finally(function () {
        if (eyeCanvasRestoreGeneration[zone] === generation) eyeCanvasRestoring[zone] = false;
    });
}
function cUndo(zone)
{
    if (eyeCanvasRestoring[zone]) return;
    if (cStep[zone] > 0) {
        markEyeCanvasDirty(zone);
        cStep[zone]--;
        restoreEyeDrawingStep(zone);
    }
}
function cRedo(zone)
{
    if (eyeCanvasRestoring[zone]) return;
    if (cStep[zone] < cPushArray[zone].length - 1) {
        markEyeCanvasDirty(zone);
        cStep[zone]++;
        restoreEyeDrawingStep(zone);
    }
}
function cReload(zone, preparedImage)
{
    if (preparedImage && (!preparedImage.complete || !(preparedImage.naturalWidth > 0) || !(preparedImage.naturalHeight > 0))) {
        return false;
    }
    markEyeCanvasDirty(zone);
    $('#url_' + zone).val($('#base_url_' + zone).val());
    return drawImage(zone, preparedImage);
}
function cReplace(zone)
{
    markEyeCanvasDirty(zone);
    $("#" + zone + "_olddrawing").addClass('nodisplay');
    $("#" + zone + "_canvas").show();
    canvasPic = new Image();
    canvasPic.src = $('#url_' + zone).val();
    ctx[zone].drawImage(canvasPic, 0, 0);
    cPush(zone);
    drawImage(zone);
}
function cBlank(zone)
{
    markEyeCanvasDirty(zone);
    $('#url_' + zone).val('../images/BLANK_BASE.png');
    drawImage(zone);
    cPush(zone);
}
    //each canvas on your page must be initialized
    // add this to your own forms/js files where HPI is replaced with your canvas Identifier
    // myCanvas_HPI --> myCanvas_YOURIDENTIFIER
InitThis('HPI');
InitThis('PMH');
InitThis('EXT');
InitThis('ANTSEG');
InitThis('RETINA');
InitThis('SDRETINA');
InitThis('NEURO');
InitThis('IMPPLAN');
InitThis('IMPPLAN');
