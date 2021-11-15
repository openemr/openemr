# StackBlur.js

[![NPM Version](https://img.shields.io/npm/v/stackblur-canvas.svg)][pkg-npm]
[![License](https://img.shields.io/npm/l/stackblur-canvas.svg)](https://github.com/flozz/StackBlur/blob/master/COPYING)

StackBlur.js is a fast, almost Gaussian blur created by Mario Klingemann.

  * **More informations:** <http://incubator.quasimondo.com/processing/fast_blur_deluxe.php>
  * **Algorithm:** <https://medium.com/better-programming/blurring-image-algorithm-example-in-android-cec81911cd5e>
  * **Demo:** <http://www.quasimondo.com/StackBlurForCanvas/StackBlurDemo.html>

Original source:

  * <http://www.quasimondo.com/StackBlurForCanvas/StackBlur.js>

## Getting Started

### Standalone version

To use the standalone version,

download the [latest zip][dl-zip-master] from Github or clone the repository

```
git clone git@github.com:flozz/StackBlur.git
```

and include the `dist/stackblur.js` or `dist/stackblur.min.js` file in your HTML page:

```html
<script src="StackBlur/dist/stackblur.js"></script>
```

### Node

To use the [NPM package][pkg-npm],

install the package:

```
npm install --save stackblur-canvas
```

and require it where needed

```js
const StackBlur = require('stackblur-canvas');
```

### Browsers

If you are only supporting modern browsers, you may use ES6 Modules directly:

```js
import * as StackBlur from
  './node_modules/stackblur-canvas/dist/stackblur-es.min.js';
```

Or, if you are using Rollup in your own project, use the [node-resolve](https://github.com/rollup/rollup-plugin-node-resolve) plugin,
and import by just referencing the module:

```js
import * as StackBlur from 'stackblur-canvas';
```

## API

See also the docs in [docs/jsdoc](./docs/jsdoc/index.html).

**Image as source:**

```js
StackBlur.image(sourceImage, targetCanvas, radius, blurAlphaChannel);
```

  * `sourceImage`: the `HTMLImageElement` or its `id`.
  * `targetCanvas`: the `HTMLCanvasElement` or its `id`.
  * `radius`: the radius of the blur.
  * `blurAlphaChannel`: Set it to `true` if you want to blur a RGBA image (optional, default = `false`)

**RGBA Canvas as source:**

```js
StackBlur.canvasRGBA(targetCanvas, top_x, top_y, width, height, radius);
```

  * `targetCanvas`: the `HTMLCanvasElement`.
  * `top_x`: the horizontal coordinate of the top-left corner of the rectangle to blur.
  * `top_y`: the vertical coordinate of the top-left corner of the rectangle to blur.
  * `width`: the width of the rectangle to blur.
  * `height`: the height of the rectangle to blur.
  * `radius`: the radius of the blur.

**RGB Canvas as source:**

```js
StackBlur.canvasRGB(targetCanvas, top_x, top_y, width, height, radius);
```

  * `targetCanvas`: the `HTMLCanvasElement`.
  * `top_x`: the horizontal coordinate of the top-left corner of the rectangle to blur.
  * `top_y`: the vertical coordinate of the top-left corner of the rectangle to blur.
  * `width`: the width of the rectangle to blur.
  * `height`: the height of the rectangle to blur.
  * `radius`: the radius of the blur.

**RGBA ImageData as source:**

```js
StackBlur.imageDataRGBA(imageData, top_x, top_y, width, height, radius);
```

  * `imageData`: the canvas' `ImageData`.
  * `top_x`: the horizontal coordinate of the top-left corner of the rectangle to blur.
  * `top_y`: the vertical coordinate of the top-left corner of the rectangle to blur.
  * `width`: the width of the rectangle to blur.
  * `height`: the height of the rectangle to blur.
  * `radius`: the radius of the blur.

**RGB ImageData as source:**

```js
StackBlur.imageDataRGB(imageData, top_x, top_y, width, height, radius);
```

  * `imageData`: the canvas' `ImageData`.
  * `top_x`: the horizontal coordinate of the top-left corner of the rectangle to blur.
  * `top_y`: the vertical coordinate of the top-left corner of the rectangle to blur.
  * `width`: the width of the rectangle to blur.
  * `height`: the height of the rectangle to blur.
  * `radius`: the radius of the blur.


## Hacking

### Building

This library is built using [Rollup](https://rollupjs.org/guide/en).
If you change something in the `src/` folder, use the following command
to re-build the files in the `dist/` folder:

`npm run rollup`


[dl-zip-master]: https://github.com/flozz/StackBlur/archive/master.zip
[pkg-npm]: https://www.npmjs.com/package/stackblur-canvas
[grunt]: http://gruntjs.com/
