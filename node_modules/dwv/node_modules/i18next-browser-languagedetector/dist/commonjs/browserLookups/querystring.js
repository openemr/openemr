'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = {
  name: 'querystring',

  lookup: function lookup(options) {
    var found = void 0;

    if (typeof window !== 'undefined') {
      var query = window.location.search.substring(1);
      var params = query.split('&');
      for (var i = 0; i < params.length; i++) {
        var pos = params[i].indexOf('=');
        if (pos > 0) {
          var key = params[i].substring(0, pos);
          if (key === options.lookupQuerystring) {
            found = params[i].substring(pos + 1);
          }
        }
      }
    }

    return found;
  }
};