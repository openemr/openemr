"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.isVector = exports.Vector = void 0;
var path_1 = require("./path");
var Vector = /** @class */ (function () {
    function Vector(x, y) {
        this.type = path_1.PathType.VECTOR;
        this.x = x;
        this.y = y;
    }
    Vector.prototype.add = function (deltaX, deltaY) {
        return new Vector(this.x + deltaX, this.y + deltaY);
    };
    return Vector;
}());
exports.Vector = Vector;
var isVector = function (path) { return path.type === path_1.PathType.VECTOR; };
exports.isVector = isVector;
//# sourceMappingURL=vector.js.map