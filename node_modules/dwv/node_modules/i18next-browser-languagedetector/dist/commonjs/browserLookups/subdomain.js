'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = {
  name: 'subdomain',

  lookup: function lookup(options) {
    var found = void 0;
    if (typeof window !== 'undefined') {
      var language = window.location.href.match(/(?:http[s]*\:\/\/)*(.*?)\.(?=[^\/]*\..{2,5})/gi);
      if (language instanceof Array) {
        if (typeof options.lookupFromSubdomainIndex === 'number') {
          found = language[options.lookupFromSubdomainIndex].replace('http://', '').replace('https://', '').replace('.', '');
        } else {
          found = language[0].replace('http://', '').replace('https://', '').replace('.', '');
        }
      }
    }
    return found;
  }
};