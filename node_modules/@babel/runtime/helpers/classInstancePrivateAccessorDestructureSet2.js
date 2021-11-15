var classCheckPrivateInstanceAccess2 = require("./classCheckPrivateInstanceAccess2.js");

function _classInstancePrivateAccessorDestructureSet2(receiver, privateSet, setter) {
  classCheckPrivateInstanceAccess2(receiver, privateSet, "set");
  return {
    set _(value) {
      setter.call(receiver, value);
    }

  };
}

module.exports = _classInstancePrivateAccessorDestructureSet2;
module.exports["default"] = module.exports, module.exports.__esModule = true;