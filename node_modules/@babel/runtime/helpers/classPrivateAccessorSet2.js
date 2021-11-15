var classCheckPrivateInstanceAccess2 = require("./classCheckPrivateInstanceAccess2.js");

function _classPrivateAccessorSet2(receiver, privateSet, fn, value) {
  classCheckPrivateInstanceAccess2(receiver, privateSet, "set");
  fn.call(receiver, value);
  return value;
}

module.exports = _classPrivateAccessorSet2;
module.exports["default"] = module.exports, module.exports.__esModule = true;