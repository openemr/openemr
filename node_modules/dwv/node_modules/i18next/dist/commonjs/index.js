'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.use = exports.t = exports.setDefaultNamespace = exports.on = exports.off = exports.loadResources = exports.loadNamespaces = exports.loadLanguages = exports.init = exports.getFixedT = exports.exists = exports.dir = exports.createInstance = exports.cloneInstance = exports.changeLanguage = undefined;

var _i18next = require('./i18next.js');

var _i18next2 = _interopRequireDefault(_i18next);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = _i18next2.default;
var changeLanguage = exports.changeLanguage = _i18next2.default.changeLanguage.bind(_i18next2.default);
var cloneInstance = exports.cloneInstance = _i18next2.default.cloneInstance.bind(_i18next2.default);
var createInstance = exports.createInstance = _i18next2.default.createInstance.bind(_i18next2.default);
var dir = exports.dir = _i18next2.default.dir.bind(_i18next2.default);
var exists = exports.exists = _i18next2.default.exists.bind(_i18next2.default);
var getFixedT = exports.getFixedT = _i18next2.default.getFixedT.bind(_i18next2.default);
var init = exports.init = _i18next2.default.init.bind(_i18next2.default);
var loadLanguages = exports.loadLanguages = _i18next2.default.loadLanguages.bind(_i18next2.default);
var loadNamespaces = exports.loadNamespaces = _i18next2.default.loadNamespaces.bind(_i18next2.default);
var loadResources = exports.loadResources = _i18next2.default.loadResources.bind(_i18next2.default);
var off = exports.off = _i18next2.default.off.bind(_i18next2.default);
var on = exports.on = _i18next2.default.on.bind(_i18next2.default);
var setDefaultNamespace = exports.setDefaultNamespace = _i18next2.default.setDefaultNamespace.bind(_i18next2.default);
var t = exports.t = _i18next2.default.t.bind(_i18next2.default);
var use = exports.use = _i18next2.default.use.bind(_i18next2.default);