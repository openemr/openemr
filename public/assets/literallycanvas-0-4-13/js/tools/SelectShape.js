var SelectShape, Tool, createShape,
  extend = function(child, parent) { for (var key in parent) { if (hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
  hasProp = {}.hasOwnProperty;

Tool = require('./base').Tool;

createShape = require('../core/shapes').createShape;

module.exports = SelectShape = (function(superClass) {
  extend(SelectShape, superClass);

  SelectShape.prototype.name = 'SelectShape';

  SelectShape.prototype.usesSimpleAPI = false;

  function SelectShape(lc) {
    this.selectCanvas = document.createElement('canvas');
    this.selectCanvas.style['background-color'] = 'transparent';
    this.selectCtx = this.selectCanvas.getContext('2d');
  }

  SelectShape.prototype.didBecomeActive = function(lc) {
    var onDown, onDrag, onUp, selectShapeUnsubscribeFuncs;
    selectShapeUnsubscribeFuncs = [];
    this._selectShapeUnsubscribe = (function(_this) {
      return function() {
        var func, j, len, results;
        results = [];
        for (j = 0, len = selectShapeUnsubscribeFuncs.length; j < len; j++) {
          func = selectShapeUnsubscribeFuncs[j];
          results.push(func());
        }
        return results;
      };
    })(this);
    onDown = (function(_this) {
      return function(arg) {
        var br, shapeIndex, x, y;
        x = arg.x, y = arg.y;
        _this.didDrag = false;
        shapeIndex = _this._getPixel(x, y, lc, _this.selectCtx);
        _this.selectedShape = lc.shapes[shapeIndex];
        if (_this.selectedShape != null) {
          lc.trigger('shapeSelected', {
            selectedShape: _this.selectedShape
          });
          lc.setShapesInProgress([
            _this.selectedShape, createShape('SelectionBox', {
              shape: _this.selectedShape,
              handleSize: 0
            })
          ]);
          lc.repaintLayer('main');
          br = _this.selectedShape.getBoundingRect();
          return _this.dragOffset = {
            x: x - br.x,
            y: y - br.y
          };
        }
      };
    })(this);
    onDrag = (function(_this) {
      return function(arg) {
        var x, y;
        x = arg.x, y = arg.y;
        if (_this.selectedShape != null) {
          _this.didDrag = true;
          _this.selectedShape.setUpperLeft({
            x: x - _this.dragOffset.x,
            y: y - _this.dragOffset.y
          });
          lc.setShapesInProgress([
            _this.selectedShape, createShape('SelectionBox', {
              shape: _this.selectedShape,
              handleSize: 0
            })
          ]);
          return lc.repaintLayer('main');
        }
      };
    })(this);
    onUp = (function(_this) {
      return function(arg) {
        var x, y;
        x = arg.x, y = arg.y;
        if (_this.didDrag) {
          _this.didDrag = false;
          lc.trigger('shapeMoved', {
            shape: _this.selectedShape
          });
          lc.trigger('drawingChange', {});
          lc.repaintLayer('main');
          return _this._drawSelectCanvas(lc);
        }
      };
    })(this);
    selectShapeUnsubscribeFuncs.push(lc.on('lc-pointerdown', onDown));
    selectShapeUnsubscribeFuncs.push(lc.on('lc-pointerdrag', onDrag));
    selectShapeUnsubscribeFuncs.push(lc.on('lc-pointerup', onUp));
    return this._drawSelectCanvas(lc);
  };

  SelectShape.prototype.willBecomeInactive = function(lc) {
    this._selectShapeUnsubscribe();
    return lc.setShapesInProgress([]);
  };

  SelectShape.prototype._drawSelectCanvas = function(lc) {
    var shapes;
    this.selectCanvas.width = lc.canvas.width;
    this.selectCanvas.height = lc.canvas.height;
    this.selectCtx.clearRect(0, 0, this.selectCanvas.width, this.selectCanvas.height);
    shapes = lc.shapes.map((function(_this) {
      return function(shape, index) {
        return createShape('SelectionBox', {
          shape: shape,
          handleSize: 0,
          backgroundColor: "#" + (_this._intToHex(index))
        });
      };
    })(this));
    return lc.draw(shapes, this.selectCtx);
  };

  SelectShape.prototype._intToHex = function(i) {
    return ("000000" + (i.toString(16))).slice(-6);
  };

  SelectShape.prototype._getPixel = function(x, y, lc, ctx) {
    var p, pixel;
    p = lc.drawingCoordsToClientCoords(x, y);
    pixel = ctx.getImageData(p.x, p.y, 1, 1).data;
    if (pixel[3]) {
      return parseInt(this._rgbToHex(pixel[0], pixel[1], pixel[2]), 16);
    } else {
      return null;
    }
  };

  SelectShape.prototype._componentToHex = function(c) {
    var hex;
    hex = c.toString(16);
    return ("0" + hex).slice(-2);
  };

  SelectShape.prototype._rgbToHex = function(r, g, b) {
    return "" + (this._componentToHex(r)) + (this._componentToHex(g)) + (this._componentToHex(b));
  };

  return SelectShape;

})(Tool);
