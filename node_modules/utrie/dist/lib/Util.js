"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.polyUint32Array = exports.polyUint16Array = exports.decode = void 0;
var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
// Use a lookup table to find the index.
var lookup = typeof Uint8Array === 'undefined' ? [] : new Uint8Array(256);
for (var i = 0; i < chars.length; i++) {
    lookup[chars.charCodeAt(i)] = i;
}
var decode = function (base64) {
    var bufferLength = base64.length * 0.75, len = base64.length, i, p = 0, encoded1, encoded2, encoded3, encoded4;
    if (base64[base64.length - 1] === '=') {
        bufferLength--;
        if (base64[base64.length - 2] === '=') {
            bufferLength--;
        }
    }
    var buffer = typeof ArrayBuffer !== 'undefined' &&
        typeof Uint8Array !== 'undefined' &&
        typeof Uint8Array.prototype.slice !== 'undefined'
        ? new ArrayBuffer(bufferLength)
        : new Array(bufferLength);
    var bytes = Array.isArray(buffer) ? buffer : new Uint8Array(buffer);
    for (i = 0; i < len; i += 4) {
        encoded1 = lookup[base64.charCodeAt(i)];
        encoded2 = lookup[base64.charCodeAt(i + 1)];
        encoded3 = lookup[base64.charCodeAt(i + 2)];
        encoded4 = lookup[base64.charCodeAt(i + 3)];
        bytes[p++] = (encoded1 << 2) | (encoded2 >> 4);
        bytes[p++] = ((encoded2 & 15) << 4) | (encoded3 >> 2);
        bytes[p++] = ((encoded3 & 3) << 6) | (encoded4 & 63);
    }
    return buffer;
};
exports.decode = decode;
var polyUint16Array = function (buffer) {
    var length = buffer.length;
    var bytes = [];
    for (var i = 0; i < length; i += 2) {
        bytes.push((buffer[i + 1] << 8) | buffer[i]);
    }
    return bytes;
};
exports.polyUint16Array = polyUint16Array;
var polyUint32Array = function (buffer) {
    var length = buffer.length;
    var bytes = [];
    for (var i = 0; i < length; i += 4) {
        bytes.push((buffer[i + 3] << 24) | (buffer[i + 2] << 16) | (buffer[i + 1] << 8) | buffer[i]);
    }
    return bytes;
};
exports.polyUint32Array = polyUint32Array;
//# sourceMappingURL=Util.js.map