var INFINITE, JSONToShape, LiterallyCanvas, Pencil, actions, bindEvents, createShape, math, ref, renderShapeToContext, renderShapeToSVG, renderSnapshotToImage, renderSnapshotToSVG, shapeToJSON, util,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
  slice = [].slice,
  indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

actions = require('./actions');

bindEvents = require('./bindEvents');

math = require('./math');

ref = require('./shapes'), createShape = ref.createShape, shapeToJSON = ref.shapeToJSON, JSONToShape = ref.JSONToShape;

renderShapeToContext = require('./canvasRenderer').renderShapeToContext;

renderShapeToSVG = require('./svgRenderer').renderShapeToSVG;

renderSnapshotToImage = require('./renderSnapshotToImage');

renderSnapshotToSVG = require('./renderSnapshotToSVG');

Pencil = require('../tools/Pencil');

util = require('./util');

INFINITE = 'infinite';

module.exports = LiterallyCanvas = (function() {
  function LiterallyCanvas(arg1, arg2) {
    this.setImageSize = bind(this.setImageSize, this);
    var containerEl, opts;
    opts = null;
    containerEl = null;
    if (arg1 instanceof HTMLElement) {
      containerEl = arg1;
      opts = arg2;
    } else {
      opts = arg1;
    }
    this.opts = opts || {};
    this.config = {
      zoomMin: opts.zoomMin || 0.2,
      zoomMax: opts.zoomMax || 4.0,
      zoomStep: opts.zoomStep || 0.2
    };
    this.colors = {
      primary: opts.primaryColor || '#000',
      secondary: opts.secondaryColor || '#fff',
      background: opts.backgroundColor || 'transparent'
    };
    this.watermarkImage = opts.watermarkImage;
    this.watermarkScale = opts.watermarkScale || 1;
    this.backgroundCanvas = document.createElement('canvas');
    this.backgroundCtx = this.backgroundCanvas.getContext('2d');
    this.canvas = document.createElement('canvas');
    this.canvas.style['background-color'] = 'transparent';
    this.buffer = document.createElement('canvas');
    this.buffer.style['background-color'] = 'transparent';
    this.ctx = this.canvas.getContext('2d');
    this.bufferCtx = this.buffer.getContext('2d');
    this.backingScale = util.getBackingScale(this.ctx);
    this.backgroundShapes = opts.backgroundShapes || [];
    this._shapesInProgress = [];
    this.shapes = [];
    this.undoStack = [];
    this.redoStack = [];
    this.isDragging = false;
    this.position = {
      x: 0,
      y: 0
    };
    this.scale = 1.0;
    this.setTool(new this.opts.tools[0](this));
    this.width = opts.imageSize.width || INFINITE;
    this.height = opts.imageSize.height || INFINITE;
    this.setZoom(this.scale);
    if (opts.snapshot) {
      this.loadSnapshot(opts.snapshot);
    }
    this.isBound = false;
    if (containerEl) {
      this.bindToElement(containerEl);
    }
  }

  LiterallyCanvas.prototype.bindToElement = function(containerEl) {
    var ref1, repaintAll;
    if (this.containerEl) {
      console.warn("Trying to bind Literally Canvas to a DOM element more than once is unsupported.");
      return;
    }
    this.containerEl = containerEl;
    this._unsubscribeEvents = bindEvents(this, this.containerEl, this.opts.keyboardShortcuts);
    this.containerEl.style['background-color'] = this.colors.background;
    this.containerEl.appendChild(this.backgroundCanvas);
    this.containerEl.appendChild(this.canvas);
    this.isBound = true;
    repaintAll = (function(_this) {
      return function() {
        _this.keepPanInImageBounds();
        return _this.repaintAllLayers();
      };
    })(this);
    util.matchElementSize(this.containerEl, [this.backgroundCanvas, this.canvas], this.backingScale, repaintAll);
    if (this.watermarkImage) {
      this.watermarkImage.onload = (function(_this) {
        return function() {
          return _this.repaintLayer('background');
        };
      })(this);
    }
    if ((ref1 = this.tool) != null) {
      ref1.didBecomeActive(this);
    }
    return repaintAll();
  };

  LiterallyCanvas.prototype._teardown = function() {
    this.tool.willBecomeInactive(this);
    if (typeof this._unsubscribeEvents === "function") {
      this._unsubscribeEvents();
    }
    this.tool = null;
    this.containerEl = null;
    return this.isBound = false;
  };

  LiterallyCanvas.prototype.trigger = function(name, data) {
    this.canvas.dispatchEvent(new CustomEvent(name, {
      detail: data
    }));
    return null;
  };

  LiterallyCanvas.prototype.on = function(name, fn) {
    var wrapper;
    wrapper = function(e) {
      return fn(e.detail);
    };
    this.canvas.addEventListener(name, wrapper);
    return (function(_this) {
      return function() {
        return _this.canvas.removeEventListener(name, wrapper);
      };
    })(this);
  };

  LiterallyCanvas.prototype.getRenderScale = function() {
    return this.scale * this.backingScale;
  };

  LiterallyCanvas.prototype.clientCoordsToDrawingCoords = function(x, y) {
    return {
      x: (x * this.backingScale - this.position.x) / this.getRenderScale(),
      y: (y * this.backingScale - this.position.y) / this.getRenderScale()
    };
  };

  LiterallyCanvas.prototype.drawingCoordsToClientCoords = function(x, y) {
    return {
      x: x * this.getRenderScale() + this.position.x,
      y: y * this.getRenderScale() + this.position.y
    };
  };

  LiterallyCanvas.prototype.setImageSize = function(width, height) {
    this.width = width || INFINITE;
    this.height = height || INFINITE;
    this.keepPanInImageBounds();
    this.repaintAllLayers();
    return this.trigger('imageSizeChange', {
      width: this.width,
      height: this.height
    });
  };

  LiterallyCanvas.prototype.setTool = function(tool) {
    var ref1;
    if (this.isBound) {
      if ((ref1 = this.tool) != null) {
        ref1.willBecomeInactive(this);
      }
    }
    this.tool = tool;
    this.trigger('toolChange', {
      tool: tool
    });
    if (this.isBound) {
      return this.tool.didBecomeActive(this);
    }
  };

  LiterallyCanvas.prototype.setShapesInProgress = function(newVal) {
    return this._shapesInProgress = newVal;
  };

  LiterallyCanvas.prototype.pointerDown = function(x, y) {
    var p;
    p = this.clientCoordsToDrawingCoords(x, y);
    if (this.tool.usesSimpleAPI) {
      this.tool.begin(p.x, p.y, this);
      this.isDragging = true;
      return this.trigger("drawStart", {
        tool: this.tool
      });
    } else {
      this.isDragging = true;
      return this.trigger("lc-pointerdown", {
        tool: this.tool,
        x: p.x,
        y: p.y,
        rawX: x,
        rawY: y
      });
    }
  };

  LiterallyCanvas.prototype.pointerMove = function(x, y) {
    return util.requestAnimationFrame((function(_this) {
      return function() {
        var p, ref1;
        p = _this.clientCoordsToDrawingCoords(x, y);
        if ((ref1 = _this.tool) != null ? ref1.usesSimpleAPI : void 0) {
          if (_this.isDragging) {
            _this.tool["continue"](p.x, p.y, _this);
            return _this.trigger("drawContinue", {
              tool: _this.tool
            });
          }
        } else {
          if (_this.isDragging) {
            return _this.trigger("lc-pointerdrag", {
              tool: _this.tool,
              x: p.x,
              y: p.y,
              rawX: x,
              rawY: y
            });
          } else {
            return _this.trigger("lc-pointermove", {
              tool: _this.tool,
              x: p.x,
              y: p.y,
              rawX: x,
              rawY: y
            });
          }
        }
      };
    })(this));
  };

  LiterallyCanvas.prototype.pointerUp = function(x, y) {
    var p;
    p = this.clientCoordsToDrawingCoords(x, y);
    if (this.tool.usesSimpleAPI) {
      if (this.isDragging) {
        this.tool.end(p.x, p.y, this);
        this.isDragging = false;
        return this.trigger("drawEnd", {
          tool: this.tool
        });
      }
    } else {
      this.isDragging = false;
      return this.trigger("lc-pointerup", {
        tool: this.tool,
        x: p.x,
        y: p.y,
        rawX: x,
        rawY: y
      });
    }
  };

  LiterallyCanvas.prototype.setColor = function(name, color) {
    this.colors[name] = color;
    if (!this.isBound) {
      return;
    }
    switch (name) {
      case 'background':
        this.containerEl.style.backgroundColor = this.colors.background;
        this.repaintLayer('background');
        break;
      case 'primary':
        this.repaintLayer('main');
        break;
      case 'secondary':
        this.repaintLayer('main');
    }
    this.trigger(name + "ColorChange", this.colors[name]);
    if (name === 'background') {
      return this.trigger("drawingChange");
    }
  };

  LiterallyCanvas.prototype.getColor = function(name) {
    return this.colors[name];
  };

  LiterallyCanvas.prototype.saveShape = function(shape, triggerShapeSaveEvent, previousShapeId) {
    if (triggerShapeSaveEvent == null) {
      triggerShapeSaveEvent = true;
    }
    if (previousShapeId == null) {
      previousShapeId = null;
    }
    if (!previousShapeId) {
      previousShapeId = this.shapes.length ? this.shapes[this.shapes.length - 1].id : null;
    }
    this.execute(new actions.AddShapeAction(this, shape, previousShapeId));
    if (triggerShapeSaveEvent) {
      this.trigger('shapeSave', {
        shape: shape,
        previousShapeId: previousShapeId
      });
    }
    return this.trigger('drawingChange');
  };

  LiterallyCanvas.prototype.pan = function(x, y) {
    return this.setPan(this.position.x - x, this.position.y - y);
  };

  LiterallyCanvas.prototype.keepPanInImageBounds = function() {
    var ref1, renderScale, x, y;
    renderScale = this.getRenderScale();
    ref1 = this.position, x = ref1.x, y = ref1.y;
    if (this.width !== INFINITE) {
      if (this.canvas.width > this.width * renderScale) {
        x = (this.canvas.width - this.width * renderScale) / 2;
      } else {
        x = Math.max(Math.min(0, x), this.canvas.width - this.width * renderScale);
      }
    }
    if (this.height !== INFINITE) {
      if (this.canvas.height > this.height * renderScale) {
        y = (this.canvas.height - this.height * renderScale) / 2;
      } else {
        y = Math.max(Math.min(0, y), this.canvas.height - this.height * renderScale);
      }
    }
    return this.position = {
      x: x,
      y: y
    };
  };

  LiterallyCanvas.prototype.setPan = function(x, y) {
    this.position = {
      x: x,
      y: y
    };
    this.keepPanInImageBounds();
    this.repaintAllLayers();
    return this.trigger('pan', {
      x: this.position.x,
      y: this.position.y
    });
  };

  LiterallyCanvas.prototype.zoom = function(factor) {
    var newScale;
    newScale = this.scale + factor;
    newScale = Math.max(newScale, this.config.zoomMin);
    newScale = Math.min(newScale, this.config.zoomMax);
    newScale = Math.round(newScale * 100) / 100;
    return this.setZoom(newScale);
  };

  LiterallyCanvas.prototype.setZoom = function(scale) {
    var oldScale;
    oldScale = this.scale;
    this.scale = scale;
    this.position.x = math.scalePositionScalar(this.position.x, this.canvas.width, oldScale, this.scale);
    this.position.y = math.scalePositionScalar(this.position.y, this.canvas.height, oldScale, this.scale);
    this.keepPanInImageBounds();
    this.repaintAllLayers();
    return this.trigger('zoom', {
      oldScale: oldScale,
      newScale: this.scale
    });
  };

  LiterallyCanvas.prototype.setWatermarkImage = function(newImage) {
    this.watermarkImage = newImage;
    util.addImageOnload(newImage, (function(_this) {
      return function() {
        return _this.repaintLayer('background');
      };
    })(this));
    if (newImage.width) {
      return this.repaintLayer('background');
    }
  };

  LiterallyCanvas.prototype.repaintAllLayers = function() {
    var i, key, len, ref1;
    ref1 = ['background', 'main'];
    for (i = 0, len = ref1.length; i < len; i++) {
      key = ref1[i];
      this.repaintLayer(key);
    }
    return null;
  };

  LiterallyCanvas.prototype.repaintLayer = function(repaintLayerKey, dirty) {
    var retryCallback;
    if (dirty == null) {
      dirty = repaintLayerKey === 'main';
    }
    if (!this.isBound) {
      return;
    }
    switch (repaintLayerKey) {
      case 'background':
        this.backgroundCtx.clearRect(0, 0, this.backgroundCanvas.width, this.backgroundCanvas.height);
        retryCallback = (function(_this) {
          return function() {
            return _this.repaintLayer('background');
          };
        })(this);
        if (this.watermarkImage) {
          this._renderWatermark(this.backgroundCtx, true, retryCallback);
        }
        this.draw(this.backgroundShapes, this.backgroundCtx, retryCallback);
        break;
      case 'main':
        retryCallback = (function(_this) {
          return function() {
            return _this.repaintLayer('main', true);
          };
        })(this);
        if (dirty) {
          this.buffer.width = this.canvas.width;
          this.buffer.height = this.canvas.height;
          this.bufferCtx.clearRect(0, 0, this.buffer.width, this.buffer.height);
          this.draw(this.shapes, this.bufferCtx, retryCallback);
        }
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        if (this.canvas.width > 0 && this.canvas.height > 0) {
          this.ctx.fillStyle = '#ccc';
          this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
          this.clipped(((function(_this) {
            return function() {
              _this.ctx.clearRect(0, 0, _this.canvas.width, _this.canvas.height);
              return _this.ctx.drawImage(_this.buffer, 0, 0);
            };
          })(this)), this.ctx);
          this.clipped(((function(_this) {
            return function() {
              return _this.transformed((function() {
                var i, len, ref1, results, shape;
                ref1 = _this._shapesInProgress;
                results = [];
                for (i = 0, len = ref1.length; i < len; i++) {
                  shape = ref1[i];
                  results.push(renderShapeToContext(_this.ctx, shape, {
                    bufferCtx: _this.bufferCtx,
                    shouldOnlyDrawLatest: true
                  }));
                }
                return results;
              }), _this.ctx, _this.bufferCtx);
            };
          })(this)), this.ctx, this.bufferCtx);
        }
    }
    return this.trigger('repaint', {
      layerKey: repaintLayerKey
    });
  };

  LiterallyCanvas.prototype._renderWatermark = function(ctx, worryAboutRetina, retryCallback) {
    if (worryAboutRetina == null) {
      worryAboutRetina = true;
    }
    if (!this.watermarkImage.width) {
      this.watermarkImage.onload = retryCallback;
      return;
    }
    ctx.save();
    ctx.translate(ctx.canvas.width / 2, ctx.canvas.height / 2);
    ctx.scale(this.watermarkScale, this.watermarkScale);
    if (worryAboutRetina) {
      ctx.scale(this.backingScale, this.backingScale);
    }
    ctx.drawImage(this.watermarkImage, -this.watermarkImage.width / 2, -this.watermarkImage.height / 2);
    return ctx.restore();
  };

  LiterallyCanvas.prototype.drawShapeInProgress = function(shape) {
    this.repaintLayer('main', false);
    return this.clipped(((function(_this) {
      return function() {
        return _this.transformed((function() {
          return renderShapeToContext(_this.ctx, shape, {
            bufferCtx: _this.bufferCtx,
            shouldOnlyDrawLatest: true
          });
        }), _this.ctx, _this.bufferCtx);
      };
    })(this)), this.ctx, this.bufferCtx);
  };

  LiterallyCanvas.prototype.draw = function(shapes, ctx, retryCallback) {
    var drawShapes;
    if (!shapes.length) {
      return;
    }
    drawShapes = (function(_this) {
      return function() {
        var i, len, results, shape;
        results = [];
        for (i = 0, len = shapes.length; i < len; i++) {
          shape = shapes[i];
          results.push(renderShapeToContext(ctx, shape, {
            retryCallback: retryCallback
          }));
        }
        return results;
      };
    })(this);
    return this.clipped(((function(_this) {
      return function() {
        return _this.transformed(drawShapes, ctx);
      };
    })(this)), ctx);
  };

  LiterallyCanvas.prototype.clipped = function() {
    var contexts, ctx, fn, height, i, j, len, len1, results, width, x, y;
    fn = arguments[0], contexts = 2 <= arguments.length ? slice.call(arguments, 1) : [];
    x = this.width === INFINITE ? 0 : this.position.x;
    y = this.height === INFINITE ? 0 : this.position.y;
    width = (function() {
      switch (this.width) {
        case INFINITE:
          return this.canvas.width;
        default:
          return this.width * this.getRenderScale();
      }
    }).call(this);
    height = (function() {
      switch (this.height) {
        case INFINITE:
          return this.canvas.height;
        default:
          return this.height * this.getRenderScale();
      }
    }).call(this);
    for (i = 0, len = contexts.length; i < len; i++) {
      ctx = contexts[i];
      ctx.save();
      ctx.beginPath();
      ctx.rect(x, y, width, height);
      ctx.clip();
    }
    fn();
    results = [];
    for (j = 0, len1 = contexts.length; j < len1; j++) {
      ctx = contexts[j];
      results.push(ctx.restore());
    }
    return results;
  };

  LiterallyCanvas.prototype.transformed = function() {
    var contexts, ctx, fn, i, j, len, len1, results, scale;
    fn = arguments[0], contexts = 2 <= arguments.length ? slice.call(arguments, 1) : [];
    for (i = 0, len = contexts.length; i < len; i++) {
      ctx = contexts[i];
      ctx.save();
      ctx.translate(Math.floor(this.position.x), Math.floor(this.position.y));
      scale = this.getRenderScale();
      ctx.scale(scale, scale);
    }
    fn();
    results = [];
    for (j = 0, len1 = contexts.length; j < len1; j++) {
      ctx = contexts[j];
      results.push(ctx.restore());
    }
    return results;
  };

  LiterallyCanvas.prototype.clear = function(triggerClearEvent) {
    var newShapes, oldShapes;
    if (triggerClearEvent == null) {
      triggerClearEvent = true;
    }
    oldShapes = this.shapes;
    newShapes = [];
    this.setShapesInProgress([]);
    this.execute(new actions.ClearAction(this, oldShapes, newShapes));
    this.repaintLayer('main');
    if (triggerClearEvent) {
      this.trigger('clear', null);
    }
    return this.trigger('drawingChange', {});
  };

  LiterallyCanvas.prototype.execute = function(action) {
    this.undoStack.push(action);
    action["do"]();
    return this.redoStack = [];
  };

  LiterallyCanvas.prototype.undo = function() {
    var action;
    if (!this.undoStack.length) {
      return;
    }
    action = this.undoStack.pop();
    action.undo();
    this.redoStack.push(action);
    this.trigger('undo', {
      action: action
    });
    return this.trigger('drawingChange', {});
  };

  LiterallyCanvas.prototype.redo = function() {
    var action;
    if (!this.redoStack.length) {
      return;
    }
    action = this.redoStack.pop();
    this.undoStack.push(action);
    action["do"]();
    this.trigger('redo', {
      action: action
    });
    return this.trigger('drawingChange', {});
  };

  LiterallyCanvas.prototype.canUndo = function() {
    return !!this.undoStack.length;
  };

  LiterallyCanvas.prototype.canRedo = function() {
    return !!this.redoStack.length;
  };

  LiterallyCanvas.prototype.getPixel = function(x, y) {
    var p, pixel;
    p = this.drawingCoordsToClientCoords(x, y);
    pixel = this.ctx.getImageData(p.x, p.y, 1, 1).data;
    if (pixel[3]) {
      return "rgb(" + pixel[0] + ", " + pixel[1] + ", " + pixel[2] + ")";
    } else {
      return null;
    }
  };

  LiterallyCanvas.prototype.getContentBounds = function() {
    return util.getBoundingRect((this.shapes.concat(this.backgroundShapes)).map(function(s) {
      return s.getBoundingRect();
    }), this.width === INFINITE ? 0 : this.width, this.height === INFINITE ? 0 : this.height);
  };

  LiterallyCanvas.prototype.getDefaultImageRect = function(explicitSize, margin) {
    var s;
    if (explicitSize == null) {
      explicitSize = {
        width: 0,
        height: 0
      };
    }
    if (margin == null) {
      margin = {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      };
    }
    return util.getDefaultImageRect((function() {
      var i, len, ref1, results;
      ref1 = this.shapes.concat(this.backgroundShapes);
      results = [];
      for (i = 0, len = ref1.length; i < len; i++) {
        s = ref1[i];
        results.push(s.getBoundingRect(this.ctx));
      }
      return results;
    }).call(this), explicitSize, margin);
  };

  LiterallyCanvas.prototype.getImage = function(opts) {
    if (opts == null) {
      opts = {};
    }
    if (opts.includeWatermark == null) {
      opts.includeWatermark = true;
    }
    if (opts.scaleDownRetina == null) {
      opts.scaleDownRetina = true;
    }
    if (opts.scale == null) {
      opts.scale = 1;
    }
    if (!opts.scaleDownRetina) {
      opts.scale *= this.backingScale;
    }
    if (opts.includeWatermark) {
      opts.watermarkImage = this.watermarkImage;
      opts.watermarkScale = this.watermarkScale;
      if (!opts.scaleDownRetina) {
        opts.watermarkScale *= this.backingScale;
      }
    }
    return renderSnapshotToImage(this.getSnapshot(), opts);
  };

  LiterallyCanvas.prototype.canvasForExport = function() {
    this.repaintAllLayers();
    return util.combineCanvases(this.backgroundCanvas, this.canvas);
  };

  LiterallyCanvas.prototype.canvasWithBackground = function(backgroundImageOrCanvas) {
    return util.combineCanvases(backgroundImageOrCanvas, this.canvasForExport());
  };

  LiterallyCanvas.prototype.getSnapshot = function(keys) {
    var i, k, len, ref1, shape, snapshot;
    if (keys == null) {
      keys = null;
    }
    if (keys == null) {
      keys = ['shapes', 'imageSize', 'colors', 'position', 'scale', 'backgroundShapes'];
    }
    snapshot = {};
    ref1 = ['colors', 'position', 'scale'];
    for (i = 0, len = ref1.length; i < len; i++) {
      k = ref1[i];
      if (indexOf.call(keys, k) >= 0) {
        snapshot[k] = this[k];
      }
    }
    if (indexOf.call(keys, 'shapes') >= 0) {
      snapshot.shapes = (function() {
        var j, len1, ref2, results;
        ref2 = this.shapes;
        results = [];
        for (j = 0, len1 = ref2.length; j < len1; j++) {
          shape = ref2[j];
          results.push(shapeToJSON(shape));
        }
        return results;
      }).call(this);
    }
    if (indexOf.call(keys, 'backgroundShapes') >= 0) {
      snapshot.backgroundShapes = (function() {
        var j, len1, ref2, results;
        ref2 = this.backgroundShapes;
        results = [];
        for (j = 0, len1 = ref2.length; j < len1; j++) {
          shape = ref2[j];
          results.push(shapeToJSON(shape));
        }
        return results;
      }).call(this);
    }
    if (indexOf.call(keys, 'imageSize') >= 0) {
      snapshot.imageSize = {
        width: this.width,
        height: this.height
      };
    }
    return snapshot;
  };

  LiterallyCanvas.prototype.getSnapshotJSON = function() {
    console.warn("lc.getSnapshotJSON() is deprecated. use JSON.stringify(lc.getSnapshot()) instead.");
    return JSON.stringify(this.getSnapshot());
  };

  LiterallyCanvas.prototype.getSVGString = function(opts) {
    if (opts == null) {
      opts = {};
    }
    return renderSnapshotToSVG(this.getSnapshot(), opts);
  };

  LiterallyCanvas.prototype.loadSnapshot = function(snapshot) {
    var i, j, k, len, len1, ref1, ref2, s, shape, shapeRepr;
    if (!snapshot) {
      return;
    }
    if (snapshot.colors) {
      ref1 = ['primary', 'secondary', 'background'];
      for (i = 0, len = ref1.length; i < len; i++) {
        k = ref1[i];
        this.setColor(k, snapshot.colors[k]);
      }
    }
    if (snapshot.shapes) {
      this.shapes = [];
      ref2 = snapshot.shapes;
      for (j = 0, len1 = ref2.length; j < len1; j++) {
        shapeRepr = ref2[j];
        shape = JSONToShape(shapeRepr);
        if (shape) {
          this.execute(new actions.AddShapeAction(this, shape));
        }
      }
    }
    if (snapshot.backgroundShapes) {
      this.backgroundShapes = (function() {
        var l, len2, ref3, results;
        ref3 = snapshot.backgroundShapes;
        results = [];
        for (l = 0, len2 = ref3.length; l < len2; l++) {
          s = ref3[l];
          results.push(JSONToShape(s));
        }
        return results;
      })();
    }
    if (snapshot.imageSize) {
      this.width = snapshot.imageSize.width;
      this.height = snapshot.imageSize.height;
    }
    if (snapshot.position) {
      this.position = snapshot.position;
    }
    if (snapshot.scale) {
      this.scale = snapshot.scale;
    }
    this.repaintAllLayers();
    this.trigger('snapshotLoad');
    return this.trigger('drawingChange', {});
  };

  LiterallyCanvas.prototype.loadSnapshotJSON = function(str) {
    console.warn("lc.loadSnapshotJSON() is deprecated. use lc.loadSnapshot(JSON.parse(snapshot)) instead.");
    return this.loadSnapshot(JSON.parse(str));
  };

  return LiterallyCanvas;

})();
