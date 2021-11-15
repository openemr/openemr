"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.listStylePosition = exports.LIST_STYLE_POSITION = void 0;
var LIST_STYLE_POSITION;
(function (LIST_STYLE_POSITION) {
    LIST_STYLE_POSITION[LIST_STYLE_POSITION["INSIDE"] = 0] = "INSIDE";
    LIST_STYLE_POSITION[LIST_STYLE_POSITION["OUTSIDE"] = 1] = "OUTSIDE";
})(LIST_STYLE_POSITION = exports.LIST_STYLE_POSITION || (exports.LIST_STYLE_POSITION = {}));
exports.listStylePosition = {
    name: 'list-style-position',
    initialValue: 'outside',
    prefix: false,
    type: 2 /* IDENT_VALUE */,
    parse: function (_context, position) {
        switch (position) {
            case 'inside':
                return LIST_STYLE_POSITION.INSIDE;
            case 'outside':
            default:
                return LIST_STYLE_POSITION.OUTSIDE;
        }
    }
};
//# sourceMappingURL=list-style-position.js.map