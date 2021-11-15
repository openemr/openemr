### v3.0.3

- Remove clutter from npm package [181](https://github.com/i18next/i18next-browser-languageDetector/pull/181)

### v3.0.2

- typescript: Fix types for `use()` module [180](https://github.com/i18next/i18next-browser-languageDetector/pull/180)

### v3.0.1

- typescript: fix types [165](https://github.com/i18next/i18next-browser-languageDetector/pull/165)

### v3.0.0

- typescript: add types [164](https://github.com/i18next/i18next-browser-languageDetector/pull/164)

### v2.2.4

- fix [157](https://github.com/i18next/i18next-browser-languageDetector/issues/157)

### v2.2.3

- fix [159](https://github.com/i18next/i18next-browser-languageDetector/pull/159)

### v2.2.2

- Lang by path: skip if language not found [159](https://github.com/i18next/i18next-browser-languageDetector/pull/159)

### v2.2.1

- fixes option validation in path lookup [158](https://github.com/i18next/i18next-browser-languageDetector/issues/158)
- fixes lookup from href for subdomain [157](https://github.com/i18next/i18next-browser-languageDetector/issues/157)

### v2.2.0

- add detector for path and subdomain [PR153](https://github.com/i18next/i18next-browser-languageDetector/pull/153) and [PR152](https://github.com/i18next/i18next-browser-languageDetector/pull/152)

### v2.1.1

- support for fallback language in form of object [151](https://github.com/i18next/i18next-browser-languageDetector/issues/151)

### v2.1.0

- add .js for browser import implementation [PR147](https://github.com/i18next/i18next-browser-languageDetector/pull/147)

### v2.0.0

- [BREAKING] options.excludeCacheFor (array of language codes; default ['cimode']): if a language maps a value in that list the language will not be written to cache (eg. localStorage, cookie). If you use lng cimode in your tests and require it to be cached set the option to false or empty array
