/**
 * JPEG Lossless decoder worker.
 */
// Do not warn if these variables were not defined before.
/* global importScripts, self, jpeg */

importScripts('lossless-min.js');

self.addEventListener('message', function (event) {

    // bytes per element
    var bpe = event.data.meta.bitsAllocated / 8;
    // decode DICOM buffer
    var buf = new Uint8Array(event.data.buffer);
    var decoder = new jpeg.lossless.Decoder();
    var decoded = decoder.decode(buf.buffer, 0, buf.buffer.byteLength, bpe);
    // post decoded data
    var res = null;
    if (event.data.meta.bitsAllocated === 8) {
        if (event.data.meta.isSigned) {
            res = new Int8Array(decoded.buffer);
        } else {
            res = new Uint8Array(decoded.buffer);
        }
    } else if (event.data.meta.bitsAllocated === 16) {
        if (event.data.meta.isSigned) {
            res = new Int16Array(decoded.buffer);
        } else {
            res = new Uint16Array(decoded.buffer);
        }
    }
    self.postMessage([res]);

}, false);
