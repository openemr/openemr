var LiterallyCanvasModel, LiterallyCanvasReactComponent, baseTools, canvasRenderer, conversion, defaultImageURLPrefix, defaultOptions, defaultTools, defineOptionsStyle, init, initReactDOM, initWithoutGUI, localize, registerJQueryPlugin, renderSnapshotToImage, renderSnapshotToSVG, setDefaultImageURLPrefix, shapes, svgRenderer, tools, util;

require('./ie_customevent');

require('./ie_setLineDash');

LiterallyCanvasModel = require('./core/LiterallyCanvas');

defaultOptions = require('./core/defaultOptions');

canvasRenderer = require('./core/canvasRenderer');

svgRenderer = require('./core/svgRenderer');

shapes = require('./core/shapes');

util = require('./core/util');

renderSnapshotToImage = require('./core/renderSnapshotToImage');

renderSnapshotToSVG = require('./core/renderSnapshotToSVG');

localize = require('./core/localization').localize;

LiterallyCanvasReactComponent = require('./reactGUI/LiterallyCanvas');

initReactDOM = require('./reactGUI/initDOM');

require('./optionsStyles/font');

require('./optionsStyles/stroke-width');

require('./optionsStyles/line-options-and-stroke-width');

require('./optionsStyles/polygon-and-stroke-width');

require('./optionsStyles/stroke-or-fill');

require('./optionsStyles/null');

defineOptionsStyle = require('./optionsStyles/optionsStyles').defineOptionsStyle;

conversion = {
  snapshotToShapes: function(snapshot) {
    var i, len, ref, results, shape;
    ref = snapshot.shapes;
    results = [];
    for (i = 0, len = ref.length; i < len; i++) {
      shape = ref[i];
      results.push(shapes.JSONToShape(shape));
    }
    return results;
  },
  snapshotJSONToShapes: function(json) {
    return conversion.snapshotToShapes(JSON.parse(json));
  }
};

baseTools = require('./tools/base');

tools = {
  Pencil: require('./tools/Pencil'),
  Eraser: require('./tools/Eraser'),
  Line: require('./tools/Line'),
  Rectangle: require('./tools/Rectangle'),
  Ellipse: require('./tools/Ellipse'),
  Text: require('./tools/Text'),
  Polygon: require('./tools/Polygon'),
  Pan: require('./tools/Pan'),
  Eyedropper: require('./tools/Eyedropper'),
  SelectShape: require('./tools/SelectShape'),
  Tool: baseTools.Tool,
  ToolWithStroke: baseTools.ToolWithStroke
};

defaultTools = defaultOptions.tools;

defaultImageURLPrefix = defaultOptions.imageURLPrefix;

setDefaultImageURLPrefix = function(newDefault) {
  defaultImageURLPrefix = newDefault;
  return defaultOptions.imageURLPrefix = newDefault;
};

init = function(el, opts) {
  var child, i, len, opt, ref;
  if (opts == null) {
    opts = {};
  }
  for (opt in defaultOptions) {
    if (!(opt in opts)) {
      opts[opt] = defaultOptions[opt];
    }
  }
  ref = el.children;
  for (i = 0, len = ref.length; i < len; i++) {
    child = ref[i];
    el.removeChild(child);
  }
  return require('./reactGUI/initDOM')(el, opts);
};

initWithoutGUI = function(el, opts) {
  var drawingViewElement, lc, originalClassName;
  originalClassName = el.className;
  if ([' ', ' '].join(el.className).indexOf(' literally ') === -1) {
    el.className = el.className + ' literally';
  }
  el.className = el.className + ' toolbar-hidden';
  drawingViewElement = document.createElement('div');
  drawingViewElement.className = 'lc-drawing';
  el.appendChild(drawingViewElement);
  lc = new LiterallyCanvasModel(drawingViewElement, opts);
  lc.teardown = function() {
    var child, i, len, ref;
    lc._teardown();
    ref = el.children;
    for (i = 0, len = ref.length; i < len; i++) {
      child = ref[i];
      el.removeChild(child);
    }
    return el.className = originalClassName;
  };
  if ('onInit' in opts) {
    opts.onInit(lc);
  }
  return lc;
};

registerJQueryPlugin = function(_$) {
  return _$.fn.literallycanvas = function(opts) {
    if (opts == null) {
      opts = {};
    }
    this.each((function(_this) {
      return function(ix, el) {
        return el.literallycanvas = init(el, opts);
      };
    })(this));
    return this;
  };
};

if (typeof window !== 'undefined') {
  window.LC = {
    init: init
  };
  if (window.$) {
    registerJQueryPlugin(window.$);
  }
}

module.exports = {
  init: init,
  registerJQueryPlugin: registerJQueryPlugin,
  util: util,
  tools: tools,
  setDefaultImageURLPrefix: setDefaultImageURLPrefix,
  defaultTools: defaultTools,
  defineOptionsStyle: defineOptionsStyle,
  LiterallyCanvasReactComponent: LiterallyCanvasReactComponent,
  defineShape: shapes.defineShape,
  createShape: shapes.createShape,
  JSONToShape: shapes.JSONToShape,
  shapeToJSON: shapes.shapeToJSON,
  defineCanvasRenderer: canvasRenderer.defineCanvasRenderer,
  renderShapeToContext: canvasRenderer.renderShapeToContext,
  renderShapeToCanvas: canvasRenderer.renderShapeToCanvas,
  renderShapesToCanvas: util.renderShapes,
  defineSVGRenderer: svgRenderer.defineSVGRenderer,
  renderShapeToSVG: svgRenderer.renderShapeToSVG,
  renderShapesToSVG: util.renderShapesToSVG,
  snapshotToShapes: conversion.snapshotToShapes,
  snapshotJSONToShapes: conversion.snapshotJSONToShapes,
  renderSnapshotToImage: renderSnapshotToImage,
  renderSnapshotToSVG: renderSnapshotToSVG,
  localize: localize
};
