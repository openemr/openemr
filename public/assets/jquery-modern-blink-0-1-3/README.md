# Modern Blink

jQuery plugin utilizing CSS Animations. Because we all loved the &lt;blink&gt; tag!

Modern Blink will use CSS Animations in browsers supporting them and fallback to jQuery Animations in older browsers.

**Demo**: http://codepen.io/leonderijke/pen/bkgxi

## Installation

Include Modern Blink after the jQuery library:

```html
<script src="/path/to/jquery.modern-blink.js"></script>
```

Or, install via Bower:

```
bower install modern-blink
```

## Usage

Use Modern Blink like this on the desired elements:

```js
$('.js-blink').modernBlink();
```

### Options

The following options can be passed to Modern Blink (defaults are shown):

```js
$(element).modernBlink({
	// Duration specified in milliseconds (integer)
	duration: 1000,

	// Number of times the element should blink ("infinite" or integer)
	iterationCount: "infinite",

	// Whether to start automatically or not (boolean)
	auto: true
});
```

### Methods

Modern Blink provides the following public methods:

```js
$(element).modernBlink('start'); // Will start the animation
$(element).modernBlink('stop'); // Will stop the animation
```

### Events

Modern Blink will attach the following event listeners to the element:

```js
$(element).trigger('modernBlink.start'); // Will start the animation
$(element).trigger('modernBlink.stop'); // Will stop the animation
```

## Browser Support

Tested in:
* Chrome
* Safari
* Firefox
* Mobile Safari 5.1+
* Android browser 4.0+
* Internet Explorer 6+

## License

MIT License