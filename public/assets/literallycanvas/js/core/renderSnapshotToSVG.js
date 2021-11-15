var INFINITE, JSONToShape, util;

util = require('./util');

JSONToShape = require('./shapes').JSONToShape;

INFINITE = 'infinite';

module.exports = function(snapshot, opts) {
  var allShapes, backgroundShapes, colors, ctx, dummyCanvas, imageSize, s, shapes;
  if (opts == null) {
    opts = {};
  }
  shapes = (function() {
    var i, len, ref, results;
    ref = snapshot.shapes;
    results = [];
    for (i = 0, len = ref.length; i < len; i++) {
      s = ref[i];
      results.push(JSONToShape(s));
    }
    return results;
  })();
  backgroundShapes = [];
  if (snapshot.backgroundShapes) {
    backgroundShapes = (function() {
      var i, len, ref, results;
      ref = snapshot.backgroundShapes;
      results = [];
      for (i = 0, len = ref.length; i < len; i++) {
        s = ref[i];
        results.push(JSONToShape(s));
      }
      return results;
    })();
  }
  if (opts.margin == null) {
    opts.margin = {
      top: 0,
      right: 0,
      bottom: 0,
      left: 0
    };
  }
  imageSize = snapshot.imageSize || {
    width: INFINITE,
    height: INFINITE
  };
  colors = snapshot.colors || {
    background: 'transparent'
  };
  allShapes = shapes.concat(backgroundShapes);
  dummyCanvas = document.createElement('canvas');
  ctx = dummyCanvas.getContext('2d');
  if (opts.rect) {
    opts.rect.x -= opts.margin.left;
    opts.rect.y -= opts.margin.top;
    opts.rect.width += opts.margin.left + opts.margin.right;
    opts.rect.height += opts.margin.top + opts.margin.bottom;
  } else {
    opts.rect = util.getDefaultImageRect((function() {
      var i, len, results;
      results = [];
      for (i = 0, len = allShapes.length; i < len; i++) {
        s = allShapes[i];
        results.push(s.getBoundingRect(ctx));
      }
      return results;
    })(), imageSize, opts.margin);
  }
  return LC.renderShapesToSVG(backgroundShapes.concat(shapes), opts.rect, colors.background);
};
