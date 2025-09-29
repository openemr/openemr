/**
 * Polyfills for Node.js environment
 * These are needed for jsdom and modern browser APIs
 */

// TextEncoder/TextDecoder polyfills for Node.js
const { TextEncoder, TextDecoder } = require('util');
global.TextEncoder = TextEncoder;
global.TextDecoder = TextDecoder;

// Polyfill for ArrayBuffer if needed
if (typeof global.ArrayBuffer === 'undefined') {
    global.ArrayBuffer = ArrayBuffer;
}

// Polyfill for Uint8Array if needed
if (typeof global.Uint8Array === 'undefined') {
    global.Uint8Array = Uint8Array;
}

// Polyfill for btoa/atob
if (typeof global.btoa === 'undefined') {
    global.btoa = function(str) {
        return Buffer.from(str, 'binary').toString('base64');
    };
}

if (typeof global.atob === 'undefined') {
    global.atob = function(str) {
        return Buffer.from(str, 'base64').toString('binary');
    };
}

// Add Node.js version compatibility
process.versions = process.versions || {};
process.versions.node = process.versions.node || '18.0.0';