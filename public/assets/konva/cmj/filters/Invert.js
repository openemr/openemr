"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Invert = void 0;
const Invert = function (imageData) {
    var data = imageData.data, len = data.length, i;
    for (i = 0; i < len; i += 4) {
        data[i] = 255 - data[i];
        data[i + 1] = 255 - data[i + 1];
        data[i + 2] = 255 - data[i + 2];
    }
};
exports.Invert = Invert;
