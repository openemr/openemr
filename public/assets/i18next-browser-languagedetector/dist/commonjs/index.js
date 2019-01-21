'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _utils = require('./utils');

var utils = _interopRequireWildcard(_utils);

var _cookie = require('./browserLookups/cookie');

var _cookie2 = _interopRequireDefault(_cookie);

var _querystring = require('./browserLookups/querystring');

var _querystring2 = _interopRequireDefault(_querystring);

var _localStorage = require('./browserLookups/localStorage');

var _localStorage2 = _interopRequireDefault(_localStorage);

var _navigator = require('./browserLookups/navigator');

var _navigator2 = _interopRequireDefault(_navigator);

var _htmlTag = require('./browserLookups/htmlTag');

var _htmlTag2 = _interopRequireDefault(_htmlTag);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key]; } } newObj.default = obj; return newObj; } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function getDefaults() {
  return {
    order: ['querystring', 'cookie', 'localStorage', 'navigator', 'htmlTag'],
    lookupQuerystring: 'lng',
    lookupCookie: 'i18next',
    lookupLocalStorage: 'i18nextLng',

    // cache user language
    caches: ['localStorage'],
    excludeCacheFor: ['cimode']
    //cookieMinutes: 10,
    //cookieDomain: 'myDomain'
  };
}

var Browser = function () {
  function Browser(services) {
    var options = arguments.length <= 1 || arguments[1] === undefined ? {} : arguments[1];

    _classCallCheck(this, Browser);

    this.type = 'languageDetector';
    this.detectors = {};

    this.init(services, options);
  }

  _createClass(Browser, [{
    key: 'init',
    value: function init(services) {
      var options = arguments.length <= 1 || arguments[1] === undefined ? {} : arguments[1];
      var i18nOptions = arguments.length <= 2 || arguments[2] === undefined ? {} : arguments[2];

      this.services = services;
      this.options = utils.defaults(options, this.options || {}, getDefaults());
      this.i18nOptions = i18nOptions;

      this.addDetector(_cookie2.default);
      this.addDetector(_querystring2.default);
      this.addDetector(_localStorage2.default);
      this.addDetector(_navigator2.default);
      this.addDetector(_htmlTag2.default);
    }
  }, {
    key: 'addDetector',
    value: function addDetector(detector) {
      this.detectors[detector.name] = detector;
    }
  }, {
    key: 'detect',
    value: function detect(detectionOrder) {
      var _this = this;

      if (!detectionOrder) detectionOrder = this.options.order;

      var detected = [];
      detectionOrder.forEach(function (detectorName) {
        if (_this.detectors[detectorName]) {
          var lookup = _this.detectors[detectorName].lookup(_this.options);
          if (lookup && typeof lookup === 'string') lookup = [lookup];
          if (lookup) detected = detected.concat(lookup);
        }
      });

      var found = void 0;
      detected.forEach(function (lng) {
        if (found) return;
        var cleanedLng = _this.services.languageUtils.formatLanguageCode(lng);
        if (_this.services.languageUtils.isWhitelisted(cleanedLng)) found = cleanedLng;
      });

      return found || this.i18nOptions.fallbackLng[0];
    }
  }, {
    key: 'cacheUserLanguage',
    value: function cacheUserLanguage(lng, caches) {
      var _this2 = this;

      if (!caches) caches = this.options.caches;
      if (!caches) return;
      if (this.options.excludeCacheFor && this.options.excludeCacheFor.indexOf(lng) > -1) return;
      caches.forEach(function (cacheName) {
        if (_this2.detectors[cacheName]) _this2.detectors[cacheName].cacheUserLanguage(lng, _this2.options);
      });
    }
  }]);

  return Browser;
}();

Browser.type = 'languageDetector';

exports.default = Browser;