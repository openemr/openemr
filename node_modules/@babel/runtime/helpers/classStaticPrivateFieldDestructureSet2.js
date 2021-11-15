var classCheckPrivateStaticAccess = require("./classCheckPrivateStaticAccess.js");

function _classStaticPrivateFieldDestructureSet2(receiver, classConstructor, value, setter) {
  classCheckPrivateStaticAccess(receiver, classConstructor, value);
  return Object.defineProperty({}, "_", {
    set: setter
  });
}

module.exports = _classStaticPrivateFieldDestructureSet2;
module.exports["default"] = module.exports, module.exports.__esModule = true;