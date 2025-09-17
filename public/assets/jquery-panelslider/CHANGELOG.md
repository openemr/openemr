# 1.0.0 (2015-09-10)

## Bug Fixes

- Allow plugin to be loaded inside `<head>`

## Features

- New option `bodyClass` (defaults to 'ps-active') to define a class to be added to body when panel is opened

## Breaking changes

Animation and positioning are no longer implemented by the plugin. The
animation should be implemented by user's CSS. This enables **CSS transform**
to be used instead of **jQuery's animate** which significatively boosts
performance and makes animations smoother on mobile devices. Checkout
*example.html* to see a CSS implementation of left and right panels.

The following options are not supported anymore (They can all be easily
implemented by user's own CSS):

- `side`
- `duration`
- `easingOpen`
- `easingClose`
