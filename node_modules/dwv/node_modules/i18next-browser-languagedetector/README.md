# Introduction

[![npm version](https://img.shields.io/npm/v/i18next-browser-languagedetector.svg?style=flat-square)](https://www.npmjs.com/package/i18next-browser-languagedetector)
[![Bower](https://img.shields.io/bower/v/i18next-browser-languagedetector.svg)]()
[![David](https://img.shields.io/david/i18next/i18next-browser-languagedetector.svg?style=flat-square)](https://david-dm.org/i18next/i18next-browser-languagedetector)

This is a i18next language detection plugin use to detect user language in the browser with support for:

- cookie
- localStorage
- navigator
- querystring (append `?lng=LANGUAGE` to URL)
- htmlTag
- path
- subdomain

# Getting started

Source can be loaded via [npm](https://www.npmjs.com/package/i18next-browser-languagedetector), bower or [downloaded](https://github.com/i18next/i18next-browser-languagedetector/blob/master/i18nextBrowserLanguageDetector.min.js) from this repo.

```
# npm package
$ npm install i18next-browser-languagedetector

# bower
$ bower install i18next-browser-languagedetector
```

- If you don't use a module loader it will be added to `window.i18nextBrowserLanguageDetector`

Wiring up:

```js
import i18next from 'i18next';
import LanguageDetector from 'i18next-browser-languagedetector';

i18next
  .use(LanguageDetector)
  .init(i18nextOptions);
```

As with all modules you can either pass the constructor function (class) to the i18next.use or a concrete instance.

## Detector Options

```js
{
  // order and from where user language should be detected
  order: ['querystring', 'cookie', 'localStorage', 'navigator', 'htmlTag', 'path', 'subdomain'],

  // keys or params to lookup language from
  lookupQuerystring: 'lng',
  lookupCookie: 'i18next',
  lookupLocalStorage: 'i18nextLng',
  lookupFromPathIndex: 0,
  lookupFromSubdomainIndex: 0,

  // cache user language on
  caches: ['localStorage', 'cookie'],
  excludeCacheFor: ['cimode'], // languages to not persist (cookie, localStorage)

  // optional expire and domain for set cookie
  cookieMinutes: 10,
  cookieDomain: 'myDomain',

  // optional htmlTag with lang attribute, the default is:
  htmlTag: document.documentElement
}
```

Options can be passed in:

**preferred** - by setting options.detection in i18next.init:

```js
import i18next from 'i18next';
import LanguageDetector from 'i18next-browser-languagedetector';

i18next
  .use(LanguageDetector)
  .init({
    detection: options
  });
```

on construction:

```js
  import LanguageDetector from 'i18next-browser-languagedetector';
  const languageDetector = new LanguageDetector(null, options);
```

via calling init:

```js
  import LanguageDetector from 'i18next-browser-languagedetector';
  const languageDetector = new LanguageDetector();
  lngDetector.init(options);
```

## Adding own detection functionality

### interface

```js
export default {
  name: 'myDetectorsName',

  lookup(options) {
    // options -> are passed in options
    return 'en';
  },

  cacheUserLanguage(lng, options) {
    // options -> are passed in options
    // lng -> current language, will be called after init and on changeLanguage

    // store it
  }
};
```


### adding it

```js
  import LanguageDetector from 'i18next-browser-languagedetector';
  const languageDetector = new LanguageDetector();
  languageDetector.addDetector(myDetector);

  i18next
    .use(languageDetector)
    .init({
      detection: options
    });
```

Don't forget: You have to add the name of your detector (`myDetectorsName` in this case) to the `order` array in your `options` object. Without that, your detector won't be used. See the [Detector Options section for more](#detector-options).

--------------

<h3 align="center">Gold Sponsors</h3>

<p align="center">
  <a href="https://locize.com/" target="_blank">
    <img src="https://raw.githubusercontent.com/i18next/i18next/master/assets/locize_sponsor_240.gif" width="240px">
  </a>
</p>
