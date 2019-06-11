/**
 * Init i18next library with all the translation of current languages
 * The json of translation is loaded from sessionStorage
 *
 * Example of usage in the js files -
 *
 * i18n.then(function(t) {
 *   document.getElementById('output').innerHTML = i18next.t('key');
 *  });
 *
 */

var translationObj = JSON.parse(sessionStorage.getItem('i18n'));

i18n = i18next.init({
    lng: 'selected',
    debug: false,
    resources: {
        selected: {
            translation: translationObj
        }
    }
});
