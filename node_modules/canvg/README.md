# canvg

[![NPM version][npm]][npm-url]
[![Dependencies status][deps]][deps-url]
[![Build status][build]][build-url]
[![Coverage status][coverage]][coverage-url]
[![Dependabot badge][dependabot]][dependabot-url]
[![Documentation badge][documentation]][documentation-url]

[npm]: https://img.shields.io/npm/v/canvg.svg
[npm-url]: https://npmjs.com/package/canvg

[deps]: https://david-dm.org/canvg/canvg.svg
[deps-url]: https://david-dm.org/canvg/canvg

[build]: https://img.shields.io/github/workflow/status/canvg/canvg/CI.svg
[build-url]: https://github.com/canvg/canvg/actions

[coverage]: https://img.shields.io/coveralls/canvg/canvg.svg
[coverage-url]: https://coveralls.io/r/canvg/canvg

[dependabot]: https://api.dependabot.com/badges/status?host=github&repo=canvg/canvg
[dependabot-url]: https://dependabot.com/

[documentation]: https://img.shields.io/badge/API-Documentation-2b7489.svg
[documentation-url]: https://canvg.github.io/canvg

JavaScript SVG parser and renderer on Canvas. It takes the URL to the SVG file or the text of the SVG file, parses it in JavaScript and renders the result on Canvas.

[Demo](https://canvg.github.io/canvg/demo/index.html)

[Playground](https://jsfiddle.net/0q1vrjxk/)

## Install

```sh
npm i canvg
# or
yarn add canvg
```

## Usage

Basic module exports:

```js
export default Canvg;
export {
    presets
};
```

[Description of all exports you can find in Documentation.](https://canvg.github.io/canvg/index.html)

### Example

```js
import Canvg from 'canvg';

let v = null;

window.onload = async () => {
    const canvas = document.querySelector('canvas');
    const ctx = canvas.getContext('2d');
    
    v = await Canvg.from(ctx, './svgs/1.svg');

    // Start SVG rendering with animations and mouse handling.
    v.start();
};

window.onbeforeunload = () => {
    v.stop();
};
```

<details>
    <summary>
        <b>OffscreenCanvas</b>
    </summary>

```js
import Canvg, {
    presets
} from 'canvg';

self.onmessage = async (event) => {
    const {
        width,
        height,
        svg
    } = event.data;
    const canvas = new OffscreenCanvas(width, height);
    const ctx = canvas.getContext('2d');
    const v = await Canvg.from(ctx, svg, presets.offscreen());

    // Render only first frame, ignoring animations and mouse.
    await v.render();

    const blob = await canvas.convertToBlob();
    const pngUrl = URL.createObjectURL(blob);

    self.postMessage({
        pngUrl
    });
};
```

[`OffscreenCanvas` browsers compatibility.](https://caniuse.com/offscreencanvas)

</details>

<details>
    <summary>
        <b>NodeJS</b>
    </summary>

```js
import {
    promises as fs
} from 'fs';
import {
    DOMParser
} from 'xmldom';
import * as canvas from 'canvas';
import fetch from 'node-fetch';
import Canvg, {
    presets
} from 'canvg';

const preset = presets.node({
    DOMParser,
    canvas,
    fetch
});

(async (output, input) => {
    const svg = await fs.readFile(input, 'utf8');
    const canvas = preset.createCanvas(800, 600);
    const ctx = canvas.getContext('2d');
    const v = Canvg.fromString(ctx, svg, preset);

    // Render only first frame, ignoring animations.
    await v.render();

    const png = canvas.toBuffer();

    await fs.writeFile(output, png);

})(
    process.argv.pop(),
    process.argv.pop()
);
```

</details>

<details>
    <summary>
        <b>Resize</b>
    </summary>

```js
import Canvg, {
    presets
} from 'canvg';

self.onmessage = async (event) => {
    const {
        width,
        height,
        svg
    } = event.data;
    const canvas = new OffscreenCanvas(width, height);
    const ctx = canvas.getContext('2d');
    const v = await Canvg.from(ctx, svg, presets.offscreen());

    /**
     * Resize SVG to fit in given size.
     * @param width
     * @param height
     * @param preserveAspectRatio
     */
    v.resize(width, height, 'xMidYMid meet');

    // Render only first frame, ignoring animations and mouse.
    await v.render();

    const blob = await canvas.convertToBlob();
    const pngUrl = URL.createObjectURL(blob);

    self.postMessage({
        pngUrl
    });
};
```

</details>

<details>
    <summary>
        <b>Browser</b>
    </summary>

```html
<script type="text/javascript" src="https://unpkg.com/canvg@3.0.4/lib/umd.js"></script>
<script type="text/javascript">
window.onload = () => {
    const canvas = document.querySelector('canvas');
    const ctx = canvas.getContext('2d');
    
    v = canvg.Canvg.fromString(ctx, '<svg width="600" height="600"><text x="50" y="50">Hello World!</text></svg>');

    // Start SVG rendering with animations and mouse handling.
    v.start();

};
</script>
<canvas />
```

</details>

### Options

The third parameter of `new Canvg(...)`, `Canvg.from(...)` and `Canvg.fromString(...)` is options:

```ts
interface IOptions {
    /**
     * WHATWG-compatible `fetch` function.
     */
    fetch?: typeof fetch;
    /**
     * XML/HTML parser from string into DOM Document.
     */
    DOMParser?: typeof DOMParser;
    /**
     * Window object.
     */
    window?: Window;
    /**
     * Whether enable the redraw.
     */
    enableRedraw?: boolean;
    /**
     * Ignore mouse events.
     */
    ignoreMouse?: boolean;
    /**
     * Ignore animations.
     */
    ignoreAnimation?: boolean;
    /**
     * Does not try to resize canvas.
     */
    ignoreDimensions?: boolean;
    /**
     * Does not clear canvas.
     */
    ignoreClear?: boolean;
    /**
     * Scales horizontally to width.
     */
    scaleWidth?: number;
    /**
     * Scales vertically to height.
     */
    scaleHeight?: number;
    /**
     * Draws at a x offset.
     */
    offsetX?: number;
    /**
     * Draws at a y offset.
     */
    offsetY?: number;
    /**
     * Will call the function on every frame, if it returns true, will redraw.
     */
    forceRedraw?(): boolean;
    /**
     * Default `rem` size.
     */
    rootEmSize?: number;
    /**
     * Default `em` size.
     */
    emSize?: number;
    /**
     * Function to create new canvas.
     */
    createCanvas?: (width: number, height: number) => HTMLCanvasElement | OffscreenCanvas;
    /**
     * Function to create new image.
     */
    createImage?: (src: string, anonymousCrossOrigin?: boolean) => Promise<CanvasImageSource>;
    /**
     * Load images anonymously.
     */
    anonymousCrossOrigin?: boolean;
}
```

#### Options presets

There are two options presets:

- `presets.offscreen()`: options for [`OffscreenCanvas`](https://developer.mozilla.org/en-US/docs/Web/API/OffscreenCanvas);
- `presets.node({ DOMParser, canvas, fetch })`: options for NodeJS with [`node-canvas`](https://github.com/Automattic/node-canvas).

## What's implemented?

The end goal is everything from the [SVG spec](http://www.w3.org/TR/SVG/). The majority of the rendering and animation is working. If you would like to see a feature implemented, don't hesitate to add it to the issues list, or better is to create pull request 😎
