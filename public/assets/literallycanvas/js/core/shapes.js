var JSONToShape, LinePath, TextRenderer, _createLinePathFromData, _doAllPointsShareStyle, _dual, _mid, _refine, bspline, createShape, defineCanvasRenderer, defineSVGRenderer, defineShape, lineEndCapShapes, linePathFuncs, ref, ref1, renderShapeToContext, renderShapeToSVG, shapeToJSON, shapes, util;

util = require('./util');

TextRenderer = require('./TextRenderer');

lineEndCapShapes = require('./lineEndCapShapes');

ref = require('./canvasRenderer'), defineCanvasRenderer = ref.defineCanvasRenderer, renderShapeToContext = ref.renderShapeToContext;

ref1 = require('./svgRenderer'), defineSVGRenderer = ref1.defineSVGRenderer, renderShapeToSVG = ref1.renderShapeToSVG;

shapes = {};

defineShape = function(name, props) {
  var Shape, drawFunc, drawLatestFunc, k, legacyDrawFunc, legacyDrawLatestFunc, legacySVGFunc, svgFunc;
  Shape = function(a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p) {
    props.constructor.call(this, a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p);
    return this;
  };
  Shape.prototype.className = name;
  Shape.fromJSON = props.fromJSON;
  if (props.draw) {
    legacyDrawFunc = props.draw;
    legacyDrawLatestFunc = props.draw || function(ctx, bufferCtx, retryCallback) {
      return this.draw(ctx, bufferCtx, retryCallback);
    };
    drawFunc = function(ctx, shape, retryCallback) {
      return legacyDrawFunc.call(shape, ctx, retryCallback);
    };
    drawLatestFunc = function(ctx, bufferCtx, shape, retryCallback) {
      return legacyDrawLatestFunc.call(shape, ctx, bufferCtx, retryCallback);
    };
    delete props.draw;
    if (props.drawLatest) {
      delete props.drawLatest;
    }
    defineCanvasRenderer(name, drawFunc, drawLatestFunc);
  }
  if (props.toSVG) {
    legacySVGFunc = props.toSVG;
    svgFunc = function(shape) {
      return legacySVGFunc.call(shape);
    };
    delete props.toSVG;
    defineSVGRenderer(name, svgFunc);
  }
  Shape.prototype.draw = function(ctx, retryCallback) {
    return renderShapeToContext(ctx, this, {
      retryCallback: retryCallback
    });
  };
  Shape.prototype.drawLatest = function(ctx, bufferCtx, retryCallback) {
    return renderShapeToContext(ctx, this, {
      retryCallback: retryCallback,
      bufferCtx: bufferCtx,
      shouldOnlyDrawLatest: true
    });
  };
  Shape.prototype.toSVG = function() {
    return renderShapeToSVG(this);
  };
  for (k in props) {
    if (k !== 'fromJSON') {
      Shape.prototype[k] = props[k];
    }
  }
  shapes[name] = Shape;
  return Shape;
};

createShape = function(name, a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p) {
  var s;
  s = new shapes[name](a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p);
  s.id = util.getGUID();
  return s;
};

JSONToShape = function(arg) {
  var className, data, id, shape;
  className = arg.className, data = arg.data, id = arg.id;
  if (className in shapes) {
    shape = shapes[className].fromJSON(data);
    if (shape) {
      if (id) {
        shape.id = id;
      }
      return shape;
    } else {
      console.log('Unreadable shape:', className, data);
      return null;
    }
  } else {
    console.log("Unknown shape:", className, data);
    return null;
  }
};

shapeToJSON = function(shape) {
  return {
    className: shape.className,
    data: shape.toJSON(),
    id: shape.id
  };
};

bspline = function(points, order) {
  if (!order) {
    return points;
  }
  return bspline(_dual(_dual(_refine(points))), order - 1);
};

_refine = function(points) {
  var index, len, point, q, refined;
  points = [points[0]].concat(points).concat(util.last(points));
  refined = [];
  index = 0;
  for (q = 0, len = points.length; q < len; q++) {
    point = points[q];
    refined[index * 2] = point;
    if (points[index + 1]) {
      refined[index * 2 + 1] = _mid(point, points[index + 1]);
    }
    index += 1;
  }
  return refined;
};

_dual = function(points) {
  var dualed, index, len, point, q;
  dualed = [];
  index = 0;
  for (q = 0, len = points.length; q < len; q++) {
    point = points[q];
    if (points[index + 1]) {
      dualed[index] = _mid(point, points[index + 1]);
    }
    index += 1;
  }
  return dualed;
};

_mid = function(a, b) {
  return createShape('Point', {
    x: a.x + ((b.x - a.x) / 2),
    y: a.y + ((b.y - a.y) / 2),
    size: a.size + ((b.size - a.size) / 2),
    color: a.color
  });
};

defineShape('Image', {
  constructor: function(args) {
    if (args == null) {
      args = {};
    }
    this.x = args.x || 0;
    this.y = args.y || 0;
    this.scale = args.scale || 1;
    return this.image = args.image || null;
  },
  getBoundingRect: function() {
    return {
      x: this.x,
      y: this.y,
      width: this.image.width * this.scale,
      height: this.image.height * this.scale
    };
  },
  toJSON: function() {
    return {
      x: this.x,
      y: this.y,
      imageSrc: this.image.src,
      imageObject: this.image,
      scale: this.scale
    };
  },
  fromJSON: function(data) {
    var img, ref2;
    img = null;
    if ((ref2 = data.imageObject) != null ? ref2.width : void 0) {
      img = data.imageObject;
    } else {
      img = new Image();
      img.src = data.imageSrc;
    }
    return createShape('Image', {
      x: data.x,
      y: data.y,
      image: img,
      scale: data.scale
    });
  },
  move: function(moveInfo) {
    if (moveInfo == null) {
      moveInfo = {};
    }
    this.x = this.x - moveInfo.xDiff;
    return this.y = this.y - moveInfo.yDiff;
  },
  setUpperLeft: function(upperLeft) {
    if (upperLeft == null) {
      upperLeft = {};
    }
    this.x = upperLeft.x;
    return this.y = upperLeft.y;
  }
});

defineShape('Rectangle', {
  constructor: function(args) {
    if (args == null) {
      args = {};
    }
    this.x = args.x || 0;
    this.y = args.y || 0;
    this.width = args.width || 0;
    this.height = args.height || 0;
    this.strokeWidth = args.strokeWidth || 1;
    this.strokeColor = args.strokeColor || 'black';
    return this.fillColor = args.fillColor || 'transparent';
  },
  getBoundingRect: function() {
    return {
      x: this.x - this.strokeWidth / 2,
      y: this.y - this.strokeWidth / 2,
      width: this.width + this.strokeWidth,
      height: this.height + this.strokeWidth
    };
  },
  toJSON: function() {
    return {
      x: this.x,
      y: this.y,
      width: this.width,
      height: this.height,
      strokeWidth: this.strokeWidth,
      strokeColor: this.strokeColor,
      fillColor: this.fillColor
    };
  },
  fromJSON: function(data) {
    return createShape('Rectangle', data);
  },
  move: function(moveInfo) {
    if (moveInfo == null) {
      moveInfo = {};
    }
    this.x = this.x - moveInfo.xDiff;
    return this.y = this.y - moveInfo.yDiff;
  },
  setUpperLeft: function(upperLeft) {
    if (upperLeft == null) {
      upperLeft = {};
    }
    this.x = upperLeft.x;
    return this.y = upperLeft.y;
  }
});

defineShape('Ellipse', {
  constructor: function(args) {
    if (args == null) {
      args = {};
    }
    this.x = args.x || 0;
    this.y = args.y || 0;
    this.width = args.width || 0;
    this.height = args.height || 0;
    this.strokeWidth = args.strokeWidth || 1;
    this.strokeColor = args.strokeColor || 'black';
    return this.fillColor = args.fillColor || 'transparent';
  },
  getBoundingRect: function() {
    return {
      x: this.x - this.strokeWidth / 2,
      y: this.y - this.strokeWidth / 2,
      width: this.width + this.strokeWidth,
      height: this.height + this.strokeWidth
    };
  },
  toJSON: function() {
    return {
      x: this.x,
      y: this.y,
      width: this.width,
      height: this.height,
      strokeWidth: this.strokeWidth,
      strokeColor: this.strokeColor,
      fillColor: this.fillColor
    };
  },
  fromJSON: function(data) {
    return createShape('Ellipse', data);
  },
  move: function(moveInfo) {
    if (moveInfo == null) {
      moveInfo = {};
    }
    this.x = this.x - moveInfo.xDiff;
    return this.y = this.y - moveInfo.yDiff;
  },
  setUpperLeft: function(upperLeft) {
    if (upperLeft == null) {
      upperLeft = {};
    }
    this.x = upperLeft.x;
    return this.y = upperLeft.y;
  }
});

defineShape('Line', {
  constructor: function(args) {
    if (args == null) {
      args = {};
    }
    this.x1 = args.x1 || 0;
    this.y1 = args.y1 || 0;
    this.x2 = args.x2 || 0;
    this.y2 = args.y2 || 0;
    this.strokeWidth = args.strokeWidth || 1;
    this.color = args.color || 'black';
    this.capStyle = args.capStyle || 'round';
    this.endCapShapes = args.endCapShapes || [null, null];
    return this.dash = args.dash || null;
  },
  getBoundingRect: function() {
    return {
      x: Math.min(this.x1, this.x2) - this.strokeWidth / 2,
      y: Math.min(this.y1, this.y2) - this.strokeWidth / 2,
      width: Math.abs(this.x2 - this.x1) + this.strokeWidth / 2,
      height: Math.abs(this.y2 - this.y1) + this.strokeWidth / 2
    };
  },
  toJSON: function() {
    return {
      x1: this.x1,
      y1: this.y1,
      x2: this.x2,
      y2: this.y2,
      strokeWidth: this.strokeWidth,
      color: this.color,
      capStyle: this.capStyle,
      dash: this.dash,
      endCapShapes: this.endCapShapes
    };
  },
  fromJSON: function(data) {
    return createShape('Line', data);
  },
  move: function(moveInfo) {
    if (moveInfo == null) {
      moveInfo = {};
    }
    this.x1 = this.x1 - moveInfo.xDiff;
    this.y1 = this.y1 - moveInfo.yDiff;
    this.x2 = this.x2 - moveInfo.xDiff;
    return this.y2 = this.y2 - moveInfo.yDiff;
  },
  setUpperLeft: function(upperLeft) {
    var br, xDiff, yDiff;
    if (upperLeft == null) {
      upperLeft = {};
    }
    br = this.getBoundingRect();
    xDiff = br.x - upperLeft.x;
    yDiff = br.y - upperLeft.y;
    return this.move({
      xDiff: xDiff,
      yDiff: yDiff
    });
  }
});

_doAllPointsShareStyle = function(points) {
  var color, len, point, q, size;
  if (!points.length) {
    return false;
  }
  size = points[0].size;
  color = points[0].color;
  for (q = 0, len = points.length; q < len; q++) {
    point = points[q];
    if (!(point.size === size && point.color === color)) {
      console.log(size, color, point.size, point.color);
    }
    if (!(point.size === size && point.color === color)) {
      return false;
    }
  }
  return true;
};

_createLinePathFromData = function(shapeName, data) {
  var pointData, points, smoothedPoints, x, y;
  points = null;
  if (data.points) {
    points = (function() {
      var len, q, ref2, results;
      ref2 = data.points;
      results = [];
      for (q = 0, len = ref2.length; q < len; q++) {
        pointData = ref2[q];
        results.push(JSONToShape(pointData));
      }
      return results;
    })();
  } else if (data.pointCoordinatePairs) {
    points = (function() {
      var len, q, ref2, ref3, results;
      ref2 = data.pointCoordinatePairs;
      results = [];
      for (q = 0, len = ref2.length; q < len; q++) {
        ref3 = ref2[q], x = ref3[0], y = ref3[1];
        results.push(JSONToShape({
          className: 'Point',
          data: {
            x: x,
            y: y,
            size: data.pointSize,
            color: data.pointColor,
            smooth: data.smooth
          }
        }));
      }
      return results;
    })();
  }
  smoothedPoints = null;
  if (data.smoothedPointCoordinatePairs) {
    smoothedPoints = (function() {
      var len, q, ref2, ref3, results;
      ref2 = data.smoothedPointCoordinatePairs;
      results = [];
      for (q = 0, len = ref2.length; q < len; q++) {
        ref3 = ref2[q], x = ref3[0], y = ref3[1];
        results.push(JSONToShape({
          className: 'Point',
          data: {
            x: x,
            y: y,
            size: data.pointSize,
            color: data.pointColor,
            smooth: data.smooth
          }
        }));
      }
      return results;
    })();
  }
  if (!points[0]) {
    return null;
  }
  return createShape(shapeName, {
    points: points,
    smoothedPoints: smoothedPoints,
    order: data.order,
    tailSize: data.tailSize,
    smooth: data.smooth
  });
};

linePathFuncs = {
  constructor: function(args) {
    var len, point, points, q, results;
    if (args == null) {
      args = {};
    }
    points = args.points || [];
    this.order = args.order || 3;
    this.tailSize = args.tailSize || 3;
    this.smooth = 'smooth' in args ? args.smooth : true;
    this.segmentSize = Math.pow(2, this.order);
    this.sampleSize = this.tailSize + 1;
    if (args.smoothedPoints) {
      this.points = args.points;
      return this.smoothedPoints = args.smoothedPoints;
    } else {
      this.points = [];
      results = [];
      for (q = 0, len = points.length; q < len; q++) {
        point = points[q];
        results.push(this.addPoint(point));
      }
      return results;
    }
  },
  getBoundingRect: function() {
    return util.getBoundingRect(this.points.map(function(p) {
      return {
        x: p.x - p.size / 2,
        y: p.y - p.size / 2,
        width: p.size,
        height: p.size
      };
    }));
  },
  toJSON: function() {
    var p, point;
    if (_doAllPointsShareStyle(this.points)) {
      return {
        order: this.order,
        tailSize: this.tailSize,
        smooth: this.smooth,
        pointCoordinatePairs: (function() {
          var len, q, ref2, results;
          ref2 = this.points;
          results = [];
          for (q = 0, len = ref2.length; q < len; q++) {
            point = ref2[q];
            results.push([point.x, point.y]);
          }
          return results;
        }).call(this),
        smoothedPointCoordinatePairs: (function() {
          var len, q, ref2, results;
          ref2 = this.smoothedPoints;
          results = [];
          for (q = 0, len = ref2.length; q < len; q++) {
            point = ref2[q];
            results.push([point.x, point.y]);
          }
          return results;
        }).call(this),
        pointSize: this.points[0].size,
        pointColor: this.points[0].color
      };
    } else {
      return {
        order: this.order,
        tailSize: this.tailSize,
        smooth: this.smooth,
        points: (function() {
          var len, q, ref2, results;
          ref2 = this.points;
          results = [];
          for (q = 0, len = ref2.length; q < len; q++) {
            p = ref2[q];
            results.push(shapeToJSON(p));
          }
          return results;
        }).call(this)
      };
    }
  },
  fromJSON: function(data) {
    return _createLinePathFromData('LinePath', data);
  },
  addPoint: function(point) {
    this.points.push(point);
    if (!this.smooth) {
      this.smoothedPoints = this.points;
      return;
    }
    if (!this.smoothedPoints || this.points.length < this.sampleSize) {
      return this.smoothedPoints = bspline(this.points, this.order);
    } else {
      this.tail = util.last(bspline(util.last(this.points, this.sampleSize), this.order), this.segmentSize * this.tailSize);
      return this.smoothedPoints = this.smoothedPoints.slice(0, this.smoothedPoints.length - this.segmentSize * (this.tailSize - 1)).concat(this.tail);
    }
  },
  move: function(moveInfo) {
    var len, pt, pts, q;
    if (moveInfo == null) {
      moveInfo = {};
    }
    if (!this.smooth) {
      pts = this.points;
    } else {
      pts = this.smoothedPoints;
    }
    for (q = 0, len = pts.length; q < len; q++) {
      pt = pts[q];
      pt.move(moveInfo);
    }
    return this.points = this.smoothedPoints;
  },
  setUpperLeft: function(upperLeft) {
    var br, xDiff, yDiff;
    if (upperLeft == null) {
      upperLeft = {};
    }
    br = this.getBoundingRect();
    xDiff = br.x - upperLeft.x;
    yDiff = br.y - upperLeft.y;
    return this.move({
      xDiff: xDiff,
      yDiff: yDiff
    });
  }
};

LinePath = defineShape('LinePath', linePathFuncs);

defineShape('ErasedLinePath', {
  constructor: linePathFuncs.constructor,
  toJSON: linePathFuncs.toJSON,
  addPoint: linePathFuncs.addPoint,
  getBoundingRect: linePathFuncs.getBoundingRect,
  fromJSON: function(data) {
    return _createLinePathFromData('ErasedLinePath', data);
  }
});

defineShape('Point', {
  constructor: function(args) {
    if (args == null) {
      args = {};
    }
    this.x = args.x || 0;
    this.y = args.y || 0;
    this.size = args.size || 0;
    return this.color = args.color || '';
  },
  getBoundingRect: function() {
    return {
      x: this.x - this.size / 2,
      y: this.y - this.size / 2,
      width: this.size,
      height: this.size
    };
  },
  toJSON: function() {
    return {
      x: this.x,
      y: this.y,
      size: this.size,
      color: this.color
    };
  },
  fromJSON: function(data) {
    return createShape('Point', data);
  },
  move: function(moveInfo) {
    if (moveInfo == null) {
      moveInfo = {};
    }
    this.x = this.x - moveInfo.xDiff;
    return this.y = this.y - moveInfo.yDiff;
  },
  setUpperLeft: function(upperLeft) {
    if (upperLeft == null) {
      upperLeft = {};
    }
    this.x = upperLeft.x;
    return this.y = upperLeft.y;
  }
});

defineShape('Polygon', {
  constructor: function(args) {
    var len, point, q, ref2, results;
    if (args == null) {
      args = {};
    }
    this.points = args.points;
    this.fillColor = args.fillColor || 'white';
    this.strokeColor = args.strokeColor || 'black';
    this.strokeWidth = args.strokeWidth;
    this.dash = args.dash || null;
    if (args.isClosed == null) {
      args.isClosed = true;
    }
    this.isClosed = args.isClosed;
    ref2 = this.points;
    results = [];
    for (q = 0, len = ref2.length; q < len; q++) {
      point = ref2[q];
      point.color = this.strokeColor;
      results.push(point.size = this.strokeWidth);
    }
    return results;
  },
  addPoint: function(x, y) {
    return this.points.push(LC.createShape('Point', {
      x: x,
      y: y
    }));
  },
  getBoundingRect: function() {
    return util.getBoundingRect(this.points.map(function(p) {
      return p.getBoundingRect();
    }));
  },
  toJSON: function() {
    return {
      strokeWidth: this.strokeWidth,
      fillColor: this.fillColor,
      strokeColor: this.strokeColor,
      dash: this.dash,
      isClosed: this.isClosed,
      pointCoordinatePairs: this.points.map(function(p) {
        return [p.x, p.y];
      })
    };
  },
  fromJSON: function(data) {
    data.points = data.pointCoordinatePairs.map(function(arg) {
      var x, y;
      x = arg[0], y = arg[1];
      return createShape('Point', {
        x: x,
        y: y,
        size: data.strokeWidth,
        color: data.strokeColor
      });
    });
    return createShape('Polygon', data);
  },
  move: function(moveInfo) {
    var len, pt, q, ref2, results;
    if (moveInfo == null) {
      moveInfo = {};
    }
    ref2 = this.points;
    results = [];
    for (q = 0, len = ref2.length; q < len; q++) {
      pt = ref2[q];
      results.push(pt.move(moveInfo));
    }
    return results;
  },
  setUpperLeft: function(upperLeft) {
    var br, xDiff, yDiff;
    if (upperLeft == null) {
      upperLeft = {};
    }
    br = this.getBoundingRect();
    xDiff = br.x - upperLeft.x;
    yDiff = br.y - upperLeft.y;
    return this.move({
      xDiff: xDiff,
      yDiff: yDiff
    });
  }
});

defineShape('Text', {
  constructor: function(args) {
    if (args == null) {
      args = {};
    }
    this.x = args.x || 0;
    this.y = args.y || 0;
    this.v = args.v || 0;
    this.text = args.text || '';
    this.color = args.color || 'black';
    this.font = args.font || '18px sans-serif';
    this.forcedWidth = args.forcedWidth || null;
    return this.forcedHeight = args.forcedHeight || null;
  },
  _makeRenderer: function(ctx) {
    ctx.lineHeight = 1.2;
    this.renderer = new TextRenderer(ctx, this.text, this.font, this.forcedWidth, this.forcedHeight);
    if (this.v < 1) {
      console.log('repairing baseline');
      this.v = 1;
      this.x -= this.renderer.metrics.bounds.minx;
      return this.y -= this.renderer.metrics.leading - this.renderer.metrics.descent;
    }
  },
  setText: function(text) {
    this.text = text;
    return this.renderer = null;
  },
  setFont: function(font) {
    this.font = font;
    return this.renderer = null;
  },
  setPosition: function(x, y) {
    this.x = x;
    return this.y = y;
  },
  setSize: function(forcedWidth, forcedHeight) {
    this.forcedWidth = Math.max(forcedWidth, 0);
    this.forcedHeight = Math.max(forcedHeight, 0);
    return this.renderer = null;
  },
  enforceMaxBoundingRect: function(lc) {
    var br, dx, lcBoundingRect;
    br = this.getBoundingRect(lc.ctx);
    lcBoundingRect = {
      x: -lc.position.x / lc.scale,
      y: -lc.position.y / lc.scale,
      width: lc.canvas.width / lc.scale,
      height: lc.canvas.height / lc.scale
    };
    if (br.x + br.width > lcBoundingRect.x + lcBoundingRect.width) {
      dx = br.x - lcBoundingRect.x;
      this.forcedWidth = lcBoundingRect.width - dx - 10;
      return this.renderer = null;
    }
  },
  getBoundingRect: function(ctx, isEditing) {
    if (isEditing == null) {
      isEditing = false;
    }
    if (!this.renderer) {
      if (ctx) {
        this._makeRenderer(ctx);
      } else {
        throw "Must pass ctx if text hasn't been rendered yet";
      }
    }
    return {
      x: Math.floor(this.x),
      y: Math.floor(this.y),
      width: Math.ceil(this.renderer.getWidth(true)),
      height: Math.ceil(this.renderer.getHeight())
    };
  },
  toJSON: function() {
    return {
      x: this.x,
      y: this.y,
      text: this.text,
      color: this.color,
      font: this.font,
      forcedWidth: this.forcedWidth,
      forcedHeight: this.forcedHeight,
      v: this.v
    };
  },
  fromJSON: function(data) {
    return createShape('Text', data);
  },
  move: function(moveInfo) {
    if (moveInfo == null) {
      moveInfo = {};
    }
    this.x = this.x - moveInfo.xDiff;
    return this.y = this.y - moveInfo.yDiff;
  },
  setUpperLeft: function(upperLeft) {
    if (upperLeft == null) {
      upperLeft = {};
    }
    this.x = upperLeft.x;
    return this.y = upperLeft.y;
  }
});

defineShape('SelectionBox', {
  constructor: function(args) {
    if (args == null) {
      args = {};
    }
    this.shape = args.shape;
    if (args.handleSize != null) {
      this.handleSize = args.handleSize;
    } else {
      this.handleSize = 10;
    }
    this.margin = 4;
    this.backgroundColor = args.backgroundColor || null;
    return this._br = this.shape.getBoundingRect(args.ctx);
  },
  toJSON: function() {
    return {
      shape: shapeToJSON(this.shape),
      backgroundColor: this.backgroundColor
    };
  },
  fromJSON: function(arg) {
    var backgroundColor, handleSize, margin, shape;
    shape = arg.shape, handleSize = arg.handleSize, margin = arg.margin, backgroundColor = arg.backgroundColor;
    return createShape('SelectionBox', {
      shape: JSONToShape(shape),
      backgroundColor: backgroundColor
    });
  },
  getTopLeftHandleRect: function() {
    return {
      x: this._br.x - this.handleSize - this.margin,
      y: this._br.y - this.handleSize - this.margin,
      width: this.handleSize,
      height: this.handleSize
    };
  },
  getBottomLeftHandleRect: function() {
    return {
      x: this._br.x - this.handleSize - this.margin,
      y: this._br.y + this._br.height + this.margin,
      width: this.handleSize,
      height: this.handleSize
    };
  },
  getTopRightHandleRect: function() {
    return {
      x: this._br.x + this._br.width + this.margin,
      y: this._br.y - this.handleSize - this.margin,
      width: this.handleSize,
      height: this.handleSize
    };
  },
  getBottomRightHandleRect: function() {
    return {
      x: this._br.x + this._br.width + this.margin,
      y: this._br.y + this._br.height + this.margin,
      width: this.handleSize,
      height: this.handleSize
    };
  },
  getBoundingRect: function() {
    return {
      x: this._br.x - this.margin,
      y: this._br.y - this.margin,
      width: this._br.width + this.margin * 2,
      height: this._br.height + this.margin * 2
    };
  }
});

module.exports = {
  defineShape: defineShape,
  createShape: createShape,
  JSONToShape: JSONToShape,
  shapeToJSON: shapeToJSON
};
