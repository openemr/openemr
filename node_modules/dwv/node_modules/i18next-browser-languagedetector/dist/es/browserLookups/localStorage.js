var hasLocalStorageSupport = void 0;
try {
  hasLocalStorageSupport = window !== 'undefined' && window.localStorage !== null;
  var testKey = 'i18next.translate.boo';
  window.localStorage.setItem(testKey, 'foo');
  window.localStorage.removeItem(testKey);
} catch (e) {
  hasLocalStorageSupport = false;
}

export default {
  name: 'localStorage',

  lookup: function lookup(options) {
    var found = void 0;

    if (options.lookupLocalStorage && hasLocalStorageSupport) {
      var lng = window.localStorage.getItem(options.lookupLocalStorage);
      if (lng) found = lng;
    }

    return found;
  },
  cacheUserLanguage: function cacheUserLanguage(lng, options) {
    if (options.lookupLocalStorage && hasLocalStorageSupport) {
      window.localStorage.setItem(options.lookupLocalStorage, lng);
    }
  }
};