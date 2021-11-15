"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.position = exports.POSITION = void 0;
var POSITION;
(function (POSITION) {
    POSITION[POSITION["STATIC"] = 0] = "STATIC";
    POSITION[POSITION["RELATIVE"] = 1] = "RELATIVE";
    POSITION[POSITION["ABSOLUTE"] = 2] = "ABSOLUTE";
    POSITION[POSITION["FIXED"] = 3] = "FIXED";
    POSITION[POSITION["STICKY"] = 4] = "STICKY";
})(POSITION = exports.POSITION || (exports.POSITION = {}));
exports.position = {
    name: 'position',
    initialValue: 'static',
    prefix: false,
    type: 2 /* IDENT_VALUE */,
    parse: function (_context, position) {
        switch (position) {
            case 'relative':
                return POSITION.RELATIVE;
            case 'absolute':
                return POSITION.ABSOLUTE;
            case 'fixed':
                return POSITION.FIXED;
            case 'sticky':
                return POSITION.STICKY;
        }
        return POSITION.STATIC;
    }
};
//# sourceMappingURL=position.js.map