btoa
===

| [atob](https://git.coolaj86.com/coolaj86/atob.js)
| **btoa**
| [unibabel.js](https://git.coolaj86.com/coolaj86/unibabel.js)
| Sponsored by [ppl](https://ppl.family)

A port of the browser's `btoa` function.

Uses `Buffer` to emulate the exact functionality of the browser's btoa
(except that it supports some unicode that the browser may not).

It turns <strong>b</strong>inary data __to__ base64-encoded <strong>a</strong>scii.

```js
(function () {
  "use strict";

  var btoa = require('btoa');
  var bin = "Hello, 世界";
  var b64 = btoa(bin);

  console.log(b64); // "SGVsbG8sIBZM"
}());
```

**Note**: Unicode may or may not be handled incorrectly.
This module is intended to provide exact compatibility with the browser.

Copyright and License
===

Code copyright 2012-2018 AJ ONeal

Dual-licensed MIT and Apache-2.0

Docs copyright 2012-2018 AJ ONeal

Docs released under [Creative Commons](https://git.coolaj86.com/coolaj86/btoa.js/blob/master/LICENSE.DOCS).
