// original file available at https://github.com/ivmartel/dwv/blob/e6ea132ba2bac98546a1b27476e7ebe27bfeb02d/src/utils/i18n.js

// namespaces
var dwv = dwv || {};
// external
var i18next = i18next || {};
var i18nextXHRBackend = i18nextXHRBackend || {};
var i18nextBrowserLanguageDetector = i18nextBrowserLanguageDetector || {};

// This is mainly a wrapper around the i18next object.
// see its API: http://i18next.com/docs/api/

// global locales path
dwv.i18nLocalesPath = null;

/**
 * Initialise i18n.
 * @param {String} language The language to translate to. Defaults to 'auto' and
 *   gets the language from the browser.
 * @param {String} localesPath Path to the locales directory.
 * @external i18next
 * @external i18nextXHRBackend
 * @external i18nextBrowserLanguageDetector
 */
dwv.i18nInitialise = function (language, localesPath)
{
    var lng = (typeof language === "undefined") ? "auto" : language;
    var lpath = (typeof localesPath === "undefined") ? "./../public/assets/dwv" : localesPath;
    // store as global
    dwv.i18nLocalesPath = lpath;
    // i18n options: default 'en' language and
    //  only load language, not specialised (for ex en-GB)
    var options = {
        fallbackLng: "en",
        load: "languageOnly",
        backend: { loadPath: lpath + "/locales/{{lng}}/{{ns}}.json" }
    };
    // use the XHR backend to get translation files
    var i18n = i18next.use(i18nextXHRBackend);
    // use browser language or the specified one
    if (lng === "auto") {
        i18n.use(i18nextBrowserLanguageDetector);
    }
    else {
        options.lng = lng;
    }
    // init i18n: will be ready when the 'loaded' event is fired
    i18n.init(options);
};

/**
 * Initialise i18n with recources as input.
 * @param {String} language The language to translate to. Defaults to 'auto' and
 *   gets the language from the browser.
 * @param {Object} resources Languages provided as object.
 * @external i18next
 * @external i18nextBrowserLanguageDetector
 */
dwv.i18nInitialiseWithResources = function (language, resources)
{
    var lng = (typeof language === "undefined") ? "auto" : language;
    // i18n options: default 'en' language and
    //  only load language, not specialised (for ex en-GB)
    var options = {
        fallbackLng: "en",
        load: "languageOnly",
        resources: resources
    };
    // use browser language or the specified one
    // init i18n: will be ready when the 'loaded' event is fired
    if (lng === "auto") {
        var i18n = i18next.use(i18nextBrowserLanguageDetector);
        i18n.init(options);
    }
    else {
        options.lng = lng;
        i18next.init(options);
    }
};

/**
 * Handle i18n 'initialized' event.
 * @param {Object} callback The callback function to call when i18n is initialised.
 *  It can take one argument that will be replaced with the i18n options.
 * @external i18next
 */
dwv.i18nOnInitialised = function (callback) {
    i18next.on('initialized', callback);
};

/**
 * Stop handling i18n load event.
 * @external i18next
 */
dwv.i18nOffInitialised = function () {
    i18next.off('initialized');
};

/**
 * Handle i18n failed load event.
 * @param {Object} callback The callback function to call when i18n is loaded.
 *  It can take three arguments: lng, ns and msg.
 * @external i18next
 */
dwv.i18nOnFailedLoad = function (callback) {
    i18next.on('failedLoading', callback);
};

/**
 * Stop handling i18n failed load event.
 * @external i18next
 */
dwv.i18nOffFailedLoad = function () {
    i18next.off('failedLoading');
};

/**
 * Get the translated text.
 * @param {String} key The key to the text entry.
 * @param {Object} options The translation options such as plural, context...
 * @external i18next
 */
dwv.i18n = function (key, options) {
    return i18next.t(key, options);
};

/**
 * Check the existence of a translation.
 * @param {String} key The key to the text entry.
 * @param {Object} options The translation options such as plural, context...
 * @external i18next
 */
dwv.i18nExists = function (key, options) {
    return i18next.exists(key, options);
};

/**
 * Translate all data-i18n tags in the current html page. If an html tag defines the
 * data-i18n attribute, its value will be used as key to find its corresponding text
 * and will replace the content of the html tag.
 */
dwv.i18nPage = function () {
    // get all elements
    var elements = document.getElementsByTagName("*");
    // if the element defines data-i18n, replace its content with the tranlation
    for (var i = 0; i < elements.length; ++i) {
        if (typeof elements[i].dataset.i18n !== "undefined") {
            elements[i].innerHTML = dwv.i18n(elements[i].dataset.i18n);
        }
    }
};

/**
 * Get the current locale resource path.
 * Warning: to be used once i18next is initialised.
 * @return {String} The path to the locale resource.
 */
dwv.i18nGetLocalePath = function (filename) {
    var lng = i18next.language.substr(0, 2);
    return dwv.i18nLocalesPath + "/locales/" + lng + "/" + filename;
};

/**
 * Get the current locale resource path.
 * Warning: to be used once i18next is initialised.
 * @return {String} The path to the locale resource.
 */
dwv.i18nGetFallbackLocalePath = function (filename) {
    var lng = i18next.languages[i18next.languages.length-1].substr(0, 2);
    return dwv.i18nLocalesPath + "/locales/" + lng + "/" + filename;
};
