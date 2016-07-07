"use strict";

var hasWarned = false;
if (!CanvasRenderingContext2D.prototype.setLineDash) {
  CanvasRenderingContext2D.prototype.setLineDash = function () {
    // no-op
    if (!hasWarned) {
      console.warn("context2D.setLineDash is a no-op in this browser.");
      hasWarned = true;
    }
  };
}
module.exports = null;