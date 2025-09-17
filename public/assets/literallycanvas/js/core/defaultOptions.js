'use strict';

module.exports = {
  imageURLPrefix: 'lib/img',
  primaryColor: 'hsla(0, 0%, 0%, 1)',
  secondaryColor: 'hsla(0, 0%, 100%, 1)',
  backgroundColor: 'transparent',
  strokeWidths: [1, 2, 5, 10, 20, 30],
  defaultStrokeWidth: 5,
  toolbarPosition: 'top',
  keyboardShortcuts: false,
  imageSize: { width: 'infinite', height: 'infinite' },
  backgroundShapes: [],
  watermarkImage: null,
  watermarkScale: 1,
  zoomMin: 0.2,
  zoomMax: 4.0,
  zoomStep: 0.2,
  snapshot: null,
  onInit: function onInit() {},
  tools: [require('../tools/Pencil'), require('../tools/Eraser'), require('../tools/Line'), require('../tools/Rectangle'), require('../tools/Ellipse'), require('../tools/Text'), require('../tools/Polygon'), require('../tools/Pan'), require('../tools/Eyedropper')]
};