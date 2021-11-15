# jscolor - JavaScript Color picker with Opacity (alpha)

**jscolor.js** is a web color picker with [opacity (alpha channel) and customizable palette](https://jscolor.com/examples/).

- Single file of plain JS with **no dependencies**
- Supports CSS colors such as **rgba()** and **hex**, including **#rrggbbaa** notation.
- [Download](https://jscolor.com/release/latest.zip) includes minified **jscolor.min.js**
- Mobile friendly



## Installation in two lines

```html
<script src="jscolor.js"></script>

Color: <input value="#3399FF80" data-jscolor="{}">
```

[Run example >](https://jscolor.com/#installation)



## Configuration & Custom palette (swatch)

```html
<script>
// These options apply to all color pickers on the page
jscolor.presets.default = {
	width: 201,
	height: 81,
	position: 'right',
	previewPosition: 'right',
	backgroundColor: '#f3f3f3',
	borderColor: '#bbbbbb',
	controlBorderColor: '#bbbbbb',
	palette: [
		'#000000', '#7d7d7d', '#870014', '#ec1c23', '#ff7e26',
		'#fef100', '#22b14b', '#00a1e7', '#3f47cc', '#a349a4',
		'#ffffff', '#c3c3c3', '#b87957', '#feaec9', '#ffc80d',
		'#eee3af', '#b5e61d', '#99d9ea', '#7092be', '#c8bfe7',
	],
	paletteCols: 10,
	hideOnPaletteClick: true,
}
</script>
```



## Screenshot

[<img src="https://jscolor.com/hosted/gui/jscolor-2.4.5.png" alt="Screenshots of jscolor">](https://jscolor.com/examples/)



## Links

- [Online Configurator tool](https://jscolor.com/configure/)
- [Sandbox](https://jscolor.com/sandbox/)
- [Examples](https://jscolor.com/examples/)
- [Download](https://jscolor.com/download/) including minified **jscolor.min.js**



## Features


* **No framework needed** \
  jscolor.js is a completely self-sufficient JavaScript library consisting of only one file of vanilla JavaScript.
  It doesn't need any frameworks (jQuery, Dojo, MooTools etc.) But it can certainly coexist alongside them.


* **Cross-browser** \
  All modern browsers are supported, including:
  Edge, Firefox, Chrome, Safari, Opera, Internet Explorer 10 and above, and others...


* **Highly customizable** \
  jscolor provides many [configuration options](https://jscolor.com/docs/#doc-api-options). Whether you need to change color picker's size or colors, or attach a function to its onchange event, the configuration can be fine-tuned for your web project.


* **Mobile friendly** \
  With a built-in support for touch events, jscolor is designed to be easy to use on touch devices such as tablets and smartphones.



## License

* [GNU GPL v3](http://www.gnu.org/licenses/gpl-3.0.txt) for open source use
* [Commercial license](https://jscolor.com/download/#licenses) for commercial use



## Website

For more info on jscolor project, see [jscolor website](https://jscolor.com)
