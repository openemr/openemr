var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

import * as utils from './utils.js';
import baseLogger from './logger.js';

var Interpolator = function () {
  function Interpolator() {
    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

    _classCallCheck(this, Interpolator);

    this.logger = baseLogger.create('interpolator');

    this.init(options, true);
  }

  /* eslint no-param-reassign: 0 */


  Interpolator.prototype.init = function init() {
    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    var reset = arguments[1];

    if (reset) {
      this.options = options;
      this.format = options.interpolation && options.interpolation.format || function (value) {
        return value;
      };
    }
    if (!options.interpolation) options.interpolation = { escapeValue: true };

    var iOpts = options.interpolation;

    this.escape = iOpts.escape !== undefined ? iOpts.escape : utils.escape;
    this.escapeValue = iOpts.escapeValue !== undefined ? iOpts.escapeValue : true;
    this.useRawValueToEscape = iOpts.useRawValueToEscape !== undefined ? iOpts.useRawValueToEscape : false;

    this.prefix = iOpts.prefix ? utils.regexEscape(iOpts.prefix) : iOpts.prefixEscaped || '{{';
    this.suffix = iOpts.suffix ? utils.regexEscape(iOpts.suffix) : iOpts.suffixEscaped || '}}';

    this.formatSeparator = iOpts.formatSeparator ? iOpts.formatSeparator : iOpts.formatSeparator || ',';

    this.unescapePrefix = iOpts.unescapeSuffix ? '' : iOpts.unescapePrefix || '-';
    this.unescapeSuffix = this.unescapePrefix ? '' : iOpts.unescapeSuffix || '';

    this.nestingPrefix = iOpts.nestingPrefix ? utils.regexEscape(iOpts.nestingPrefix) : iOpts.nestingPrefixEscaped || utils.regexEscape('$t(');
    this.nestingSuffix = iOpts.nestingSuffix ? utils.regexEscape(iOpts.nestingSuffix) : iOpts.nestingSuffixEscaped || utils.regexEscape(')');

    this.maxReplaces = iOpts.maxReplaces ? iOpts.maxReplaces : 1000;

    // the regexp
    this.resetRegExp();
  };

  Interpolator.prototype.reset = function reset() {
    if (this.options) this.init(this.options);
  };

  Interpolator.prototype.resetRegExp = function resetRegExp() {
    // the regexp
    var regexpStr = this.prefix + '(.+?)' + this.suffix;
    this.regexp = new RegExp(regexpStr, 'g');

    var regexpUnescapeStr = '' + this.prefix + this.unescapePrefix + '(.+?)' + this.unescapeSuffix + this.suffix;
    this.regexpUnescape = new RegExp(regexpUnescapeStr, 'g');

    var nestingRegexpStr = this.nestingPrefix + '(.+?)' + this.nestingSuffix;
    this.nestingRegexp = new RegExp(nestingRegexpStr, 'g');
  };

  Interpolator.prototype.interpolate = function interpolate(str, data, lng, options) {
    var _this = this;

    var match = void 0;
    var value = void 0;
    var replaces = void 0;

    function regexSafe(val) {
      return val.replace(/\$/g, '$$$$');
    }

    var handleFormat = function handleFormat(key) {
      if (key.indexOf(_this.formatSeparator) < 0) return utils.getPath(data, key);

      var p = key.split(_this.formatSeparator);
      var k = p.shift().trim();
      var f = p.join(_this.formatSeparator).trim();

      return _this.format(utils.getPath(data, k), f, lng);
    };

    this.resetRegExp();

    var missingInterpolationHandler = options && options.missingInterpolationHandler || this.options.missingInterpolationHandler;

    replaces = 0;
    // unescape if has unescapePrefix/Suffix
    /* eslint no-cond-assign: 0 */
    while (match = this.regexpUnescape.exec(str)) {
      value = handleFormat(match[1].trim());
      str = str.replace(match[0], value);
      this.regexpUnescape.lastIndex = 0;
      replaces++;
      if (replaces >= this.maxReplaces) {
        break;
      }
    }

    replaces = 0;
    // regular escape on demand
    while (match = this.regexp.exec(str)) {
      value = handleFormat(match[1].trim());
      if (value === undefined) {
        if (typeof missingInterpolationHandler === 'function') {
          var temp = missingInterpolationHandler(str, match);
          value = typeof temp === 'string' ? temp : '';
        } else {
          this.logger.warn('missed to pass in variable ' + match[1] + ' for interpolating ' + str);
          value = '';
        }
      } else if (typeof value !== 'string' && !this.useRawValueToEscape) {
        value = utils.makeString(value);
      }
      value = this.escapeValue ? regexSafe(this.escape(value)) : regexSafe(value);
      str = str.replace(match[0], value);
      this.regexp.lastIndex = 0;
      replaces++;
      if (replaces >= this.maxReplaces) {
        break;
      }
    }
    return str;
  };

  Interpolator.prototype.nest = function nest(str, fc) {
    var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

    var match = void 0;
    var value = void 0;

    var clonedOptions = _extends({}, options);
    clonedOptions.applyPostProcessor = false; // avoid post processing on nested lookup

    // if value is something like "myKey": "lorem $(anotherKey, { "count": {{aValueInOptions}} })"
    function handleHasOptions(key, inheritedOptions) {
      if (key.indexOf(',') < 0) return key;

      var p = key.split(',');
      key = p.shift();
      var optionsString = p.join(',');
      optionsString = this.interpolate(optionsString, clonedOptions);
      optionsString = optionsString.replace(/'/g, '"');

      try {
        clonedOptions = JSON.parse(optionsString);

        if (inheritedOptions) clonedOptions = _extends({}, inheritedOptions, clonedOptions);
      } catch (e) {
        this.logger.error('failed parsing options string in nesting for key ' + key, e);
      }

      return key;
    }

    // regular escape on demand
    while (match = this.nestingRegexp.exec(str)) {
      value = fc(handleHasOptions.call(this, match[1].trim(), clonedOptions), clonedOptions);

      // is only the nesting key (key1 = '$(key2)') return the value without stringify
      if (value && match[0] === str && typeof value !== 'string') return value;

      // no string to include or empty
      if (typeof value !== 'string') value = utils.makeString(value);
      if (!value) {
        this.logger.warn('missed to resolve ' + match[1] + ' for nesting ' + str);
        value = '';
      }
      // Nested keys should not be escaped by default #854
      // value = this.escapeValue ? regexSafe(utils.escape(value)) : regexSafe(value);
      str = str.replace(match[0], value);
      this.regexp.lastIndex = 0;
    }
    return str;
  };

  return Interpolator;
}();

export default Interpolator;