var Eyedropper, Tool, getPixel,
  extend = function(child, parent) { for (var key in parent) { if (hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
  hasProp = {}.hasOwnProperty;

Tool = require('./base').Tool;

getPixel = function(ctx, arg) {
  var pixel, x, y;
  x = arg.x, y = arg.y;
  pixel = ctx.getImageData(x, y, 1, 1).data;
  if (pixel[3]) {
    return "rgb(" + pixel[0] + ", " + pixel[1] + ", " + pixel[2] + ")";
  } else {
    return null;
  }
};

module.exports = Eyedropper = (function(superClass) {
  extend(Eyedropper, superClass);

  Eyedropper.prototype.name = 'Eyedropper';

  Eyedropper.prototype.iconName = 'eyedropper';

  Eyedropper.prototype.optionsStyle = 'stroke-or-fill';

  function Eyedropper(lc) {
    Eyedropper.__super__.constructor.call(this, lc);
    this.strokeOrFill = 'stroke';
  }

  Eyedropper.prototype.readColor = function(x, y, lc) {
    var canvas, color, newColor, offset;
    offset = lc.getDefaultImageRect();
    canvas = lc.getImage();
    newColor = getPixel(canvas.getContext('2d'), {
      x: x - offset.x,
      y: y - offset.y
    });
    color = newColor || lc.getColor('background');
    if (this.strokeOrFill === 'stroke') {
      return lc.setColor('primary', newColor);
    } else {
      return lc.setColor('secondary', newColor);
    }
  };

  Eyedropper.prototype.begin = function(x, y, lc) {
    return this.readColor(x, y, lc);
  };

  Eyedropper.prototype["continue"] = function(x, y, lc) {
    return this.readColor(x, y, lc);
  };

  return Eyedropper;

})(Tool);
