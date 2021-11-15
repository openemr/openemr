"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.paintOrder = exports.PAINT_ORDER_LAYER = void 0;
var parser_1 = require("../syntax/parser");
var PAINT_ORDER_LAYER;
(function (PAINT_ORDER_LAYER) {
    PAINT_ORDER_LAYER[PAINT_ORDER_LAYER["FILL"] = 0] = "FILL";
    PAINT_ORDER_LAYER[PAINT_ORDER_LAYER["STROKE"] = 1] = "STROKE";
    PAINT_ORDER_LAYER[PAINT_ORDER_LAYER["MARKERS"] = 2] = "MARKERS";
})(PAINT_ORDER_LAYER = exports.PAINT_ORDER_LAYER || (exports.PAINT_ORDER_LAYER = {}));
exports.paintOrder = {
    name: 'paint-order',
    initialValue: 'normal',
    prefix: false,
    type: 1 /* LIST */,
    parse: function (_context, tokens) {
        var DEFAULT_VALUE = [PAINT_ORDER_LAYER.FILL, PAINT_ORDER_LAYER.STROKE, PAINT_ORDER_LAYER.MARKERS];
        var layers = [];
        tokens.filter(parser_1.isIdentToken).forEach(function (token) {
            switch (token.value) {
                case 'stroke':
                    layers.push(PAINT_ORDER_LAYER.STROKE);
                    break;
                case 'fill':
                    layers.push(PAINT_ORDER_LAYER.FILL);
                    break;
                case 'markers':
                    layers.push(PAINT_ORDER_LAYER.MARKERS);
                    break;
            }
        });
        DEFAULT_VALUE.forEach(function (value) {
            if (layers.indexOf(value) === -1) {
                layers.push(value);
            }
        });
        return layers;
    }
};
//# sourceMappingURL=paint-order.js.map