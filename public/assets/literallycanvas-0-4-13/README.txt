Literally Canvas v0.4.11
========================

Complete documentation can be found at literallycanvas.com.

Literally Canvas is an extensible, open source (BSD-licensed), HTML5 drawing
widget. Its only dependency is [React.js](http://facebook.github.io/react/).

Get help on our mailing list: literallycanvas@librelist.com (just send it a
message to subscribe)

If you want to modify the source
--------------------------------

Please visit github.com/literallycanvas/literallycanvas and work from the
master branch. This distribution does not include the build sources.

Usage
-----

1. Add the files under `css/` and `img/` to your project, as well as the
appropriate file from `js/`.

2. Add some markup and some JavaScript:

<div class="literally with-jquery"></div>
<script>
  $('.literally.with-jquery').literallycanvas();
</script>

<div class="literally without-jquery"></div>
<script>
  LC.init(document.getElementsByClassName('literally without-jquery')[0]);
</script>

Developing
----------

Setup: `npm install`

Watching and serving: `gulp dev`

Browse to `localhost:8000/demo` and modify `demo/index.html` to test code
in progress.
