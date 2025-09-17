Literally Canvas v0.4.14
========================

Literally Canvas is an extensible, open source (BSD-licensed), HTML5 drawing
widget. Its only dependency is [React.js](http://facebook.github.io/react/).

Get help on our mailing list:
[mailto:literallycanvas@librelist.com](literallycanvas@librelist.com) (just
send it a message to subscribe)

### [Full documentation](http://literallycanvas.com)

### [Examples](http://github.com/literallycanvas/literallycanvas-demos)

Along with the CSS, JS, and image assets, this is all it takes:

```javascript
<div class="my-drawing"></div>
<script>
  LC.init(document.getElementsByClassName('my-drawing')[0]);
</script>
```

Developing
----------

Setup: `npm install --dev`

Watching and serving: `gulp dev`

Browse to `localhost:8080/demo` and modify `demo/index.html` to test code
in progress.

To generate a production-ready `.js` file, run `gulp` and pull out either
`lib/js/literallycanvas.js` or `lib/js/literallycanvas.min.js`.
