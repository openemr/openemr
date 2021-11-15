# Introduction

[![Travis](https://img.shields.io/travis/i18next/i18next-xhr-backend/master.svg?style=flat-square)](https://travis-ci.org/i18next/i18next-xhr-backend)
[![Coveralls](https://img.shields.io/coveralls/i18next/i18next-xhr-backend/master.svg?style=flat-square)](https://coveralls.io/github/i18next/i18next-xhr-backend)
[![npm version](https://img.shields.io/npm/v/i18next-xhr-backend.svg?style=flat-square)](https://www.npmjs.com/package/i18next-xhr-backend)
[![Bower](https://img.shields.io/bower/v/i18next-xhr-backend.svg)]()
[![David](https://img.shields.io/david/i18next/i18next-xhr-backend.svg?style=flat-square)](https://david-dm.org/i18next/i18next-xhr-backend)

This is a simple i18next backend to be used in the browser. It will load resources from a backend server using the xhr API.

# Getting started

Source can be loaded via [npm](https://www.npmjs.com/package/i18next-xhr-backend), bower or [downloaded](https://github.com/i18next/i18next-xhr-backend/blob/master/i18nextXHRBackend.min.js) from this repo.

```
# npm package
$ npm install i18next-xhr-backend

# bower
$ bower install i18next-xhr-backend
```

Wiring up:

```js
import i18next from 'i18next';
import XHR from 'i18next-xhr-backend';

i18next.use(XHR).init(i18nextOptions);
```

- As with all modules you can either pass the constructor function (class) to the i18next.use or a concrete instance.
- If you don't use a module loader it will be added to `window.i18nextXHRBackend`

## Backend Options

```js
{
  // path where resources get loaded from, or a function
  // returning a path:
  // function(lngs, namespaces) { return customPath; }
  // the returned path will interpolate lng, ns if provided like giving a static path
  loadPath: '/locales/{{lng}}/{{ns}}.json',

  // path to post missing resources
  addPath: 'locales/add/{{lng}}/{{ns}}',

  // your backend server supports multiloading
  // /locales/resources.json?lng=de+en&ns=ns1+ns2
  // Adapter is needed to enable MultiLoading https://github.com/i18next/i18next-multiload-backend-adapter
  // Returned JSON structure in this case is
  // {
  //  lang : {
  //   namespaceA: {},
  //   namespaceB: {},
  //   ...etc
  //  }
  // }
  allowMultiLoading: false, // set loadPath: '/locales/resources.json?lng={{lng}}&ns={{ns}}' to adapt to multiLoading

  // parse data after it has been fetched
  // in example use https://www.npmjs.com/package/json5
  // here it removes the letter a from the json (bad idea)
  parse: function(data) { return data.replace(/a/g, ''); },

  //parse data before it has been sent by addPath
  parsePayload: function(namespace, key, fallbackValue) { return { key } },

  // allow cross domain requests
  crossDomain: false,

  // allow credentials on cross domain requests
  withCredentials: false,

  // overrideMimeType sets request.overrideMimeType("application/json")
  overrideMimeType: false,

  // custom request headers sets request.setRequestHeader(key, value)
  customHeaders: {
    authorization: 'foo',
    // ...
  },

  // define a custom xhr function
  // can be used to support XDomainRequest in IE 8 and 9
  //
  // 'url' will be passed the value of 'loadPath'
  // 'options' will be this entire options object
  // 'callback' is a function that takes two parameters, 'data' and 'xhr'.
  //            'data' should be the key:value translation pairs for the
  //            requested language and namespace, or null in case of an error.
  //            'xhr' should be a status object, e.g. { status: 200 }
  // 'data' will be a key:value object used when saving missing translations
  ajax: function (url, options, callback, data) {},

  // adds parameters to resource URL. 'example.com' -> 'example.com?v=1.3.5'
  queryStringParams: { v: '1.3.5' }
}
```

Options can be passed in:

**preferred** - by setting options.backend in i18next.init:

```js
import i18next from 'i18next';
import XHR from 'i18next-xhr-backend';

i18next.use(XHR).init({
  backend: options,
});
```

on construction:

```js
import XHR from 'i18next-xhr-backend';
const xhr = new XHR(null, options);
```

via calling init:

```js
import XHR from 'i18next-xhr-backend';
const xhr = new XHR();
xhr.init(null, options);
```

## Misc

#### TypeScript definitions

- Install from `@types` (for TypeScript v2 and later):

        npm install --save-dev @types/i18next-xhr-backend

- Install from `typings`:

        typings install --save --global dt~i18next-xhr-backend

---

<h3 align="center">Gold Sponsors</h3>

<p align="center">
  <a href="https://locize.com/" target="_blank">
    <img src="https://raw.githubusercontent.com/i18next/i18next/master/assets/locize_sponsor_240.gif" width="240px">
  </a>
</p>
