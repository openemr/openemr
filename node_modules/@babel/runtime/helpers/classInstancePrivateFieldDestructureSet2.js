var classCheckPrivateInstanceAccess2 = require("./classCheckPrivateInstanceAccess2.js");

function _classInstancePrivateFieldDestructureSet2(receiver, privateMap) {
  classCheckPrivateInstanceAccess2(receiver, privateMap, "set");
  return {
    set _(value) {
      privateMap.set(receiver, value);
    }

  };
}

module.exports = _classInstancePrivateFieldDestructureSet2;
module.exports["default"] = module.exports, module.exports.__esModule = true;