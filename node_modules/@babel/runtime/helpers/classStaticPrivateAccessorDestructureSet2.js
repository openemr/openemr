var classCheckPrivateStaticAccess = require("./classCheckPrivateStaticAccess.js");

function _classStaticPrivateAccessorDestructureSet2(receiver, classConstructor, setter) {
  classCheckPrivateStaticAccess(receiver, classConstructor);
  return {
    set _(value) {
      setter.call(receiver, value);
    }

  };
}

module.exports = _classStaticPrivateAccessorDestructureSet2;
module.exports["default"] = module.exports, module.exports.__esModule = true;