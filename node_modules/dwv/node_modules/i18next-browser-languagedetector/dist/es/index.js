var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

import * as utils from './utils.js';
import cookie from './browserLookups/cookie.js';
import querystring from './browserLookups/querystring.js';
import localStorage from './browserLookups/localStorage.js';
import navigator from './browserLookups/navigator.js';
import htmlTag from './browserLookups/htmlTag.js';
import path from './browserLookups/path.js';
import subdomain from './browserLookups/subdomain.js';

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
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

    _classCallCheck(this, Browser);

    this.type = 'languageDetector';
    this.detectors = {};

    this.init(services, options);
  }

  _createClass(Browser, [{
    key: 'init',
    value: function init(services) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var i18nOptions = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

      this.services = services;
      this.options = utils.defaults(options, this.options || {}, getDefaults());

      // backwards compatibility
      if (this.options.lookupFromUrlIndex) this.options.lookupFromPathIndex = this.options.lookupFromUrlIndex;

      this.i18nOptions = i18nOptions;

      this.addDetector(cookie);
      this.addDetector(querystring);
      this.addDetector(localStorage);
      this.addDetector(navigator);
      this.addDetector(htmlTag);
      this.addDetector(path);
      this.addDetector(subdomain);
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

      if (!found) {
        var fallbacks = this.i18nOptions.fallbackLng;
        if (typeof fallbacks === 'string') fallbacks = [fallbacks];
        if (!fallbacks) fallbacks = [];

        if (Object.prototype.toString.apply(fallbacks) === '[object Array]') {
          found = fallbacks[0];
        } else {
          found = fallbacks[0] || fallbacks.default && fallbacks.default[0];
        }
      };

      return found;
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

export default Browser;