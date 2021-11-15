"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.visibility = exports.VISIBILITY = void 0;
var VISIBILITY;
(function (VISIBILITY) {
    VISIBILITY[VISIBILITY["VISIBLE"] = 0] = "VISIBLE";
    VISIBILITY[VISIBILITY["HIDDEN"] = 1] = "HIDDEN";
    VISIBILITY[VISIBILITY["COLLAPSE"] = 2] = "COLLAPSE";
})(VISIBILITY = exports.VISIBILITY || (exports.VISIBILITY = {}));
exports.visibility = {
    name: 'visible',
    initialValue: 'none',
    prefix: false,
    type: 2 /* IDENT_VALUE */,
    parse: function (_context, visibility) {
        switch (visibility) {
            case 'hidden':
                return VISIBILITY.HIDDEN;
            case 'collapse':
                return VISIBILITY.COLLAPSE;
            case 'visible':
            default:
                return VISIBILITY.VISIBLE;
        }
    }
};
//# sourceMappingURL=visibility.js.map