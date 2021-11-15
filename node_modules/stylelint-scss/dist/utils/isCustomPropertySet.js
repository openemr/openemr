"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = _default;

var _lodash = require("lodash");

var _hasBlock = _interopRequireDefault(require("./hasBlock"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

/**
 * Check whether a Node is a custom property set
 *
 * @param {import('postcss').Rule} node
 * @returns {boolean}
 */
function _default(node) {
  var selector = (0, _lodash.get)(node, "raws.selector.raw", node.selector);
  return node.type === "rule" && (0, _hasBlock["default"])(node) && selector.startsWith("--") && selector.endsWith(":");
}