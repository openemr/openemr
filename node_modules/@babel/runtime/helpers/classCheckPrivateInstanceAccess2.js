function _classCheckPrivateInstanceAccess2(receiver, privateMapOrSet, action) {
  if (!privateMapOrSet.has(receiver)) {
    throw new TypeError("attempted to " + action + " private field on non-instance");
  }
}

module.exports = _classCheckPrivateInstanceAccess2;
module.exports["default"] = module.exports, module.exports.__esModule = true;