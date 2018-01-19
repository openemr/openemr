var Line, ToolWithStroke, createShape,
  extend = function(child, parent) { for (var key in parent) { if (hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
  hasProp = {}.hasOwnProperty;

ToolWithStroke = require('./base').ToolWithStroke;

createShape = require('../core/shapes').createShape;

module.exports = Line = (function(superClass) {
  extend(Line, superClass);

  function Line() {
    return Line.__super__.constructor.apply(this, arguments);
  }

  Line.prototype.name = 'Line';

  Line.prototype.iconName = 'line';

  Line.prototype.optionsStyle = 'line-options-and-stroke-width';

  Line.prototype.begin = function(x, y, lc) {
    return this.currentShape = createShape('Line', {
      x1: x,
      y1: y,
      x2: x,
      y2: y,
      strokeWidth: this.strokeWidth,
      dash: (function() {
        switch (false) {
          case !this.isDashed:
            return [this.strokeWidth * 2, this.strokeWidth * 4];
          default:
            return null;
        }
      }).call(this),
      endCapShapes: this.hasEndArrow ? [null, 'arrow'] : null,
      color: lc.getColor('primary')
    });
  };

  Line.prototype["continue"] = function(x, y, lc) {
    this.currentShape.x2 = x;
    this.currentShape.y2 = y;
    return lc.drawShapeInProgress(this.currentShape);
  };

  Line.prototype.end = function(x, y, lc) {
    return lc.saveShape(this.currentShape);
  };

  return Line;

})(ToolWithStroke);
