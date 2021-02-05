# Demo

Side panel slider plugin (jQuery) that also slides page (inspired on medium).

[Live demo](http://eduardomb.github.io/jquery-panelslider).

# Features

* slide page and panel together (inspired on medium.com)
* support multiple panels on same page
* support closing when clicking outside panel or pressing ESC.
* play well with angularJS directives (Example comming soon)


# Installation

Include [jQuery](http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js) and `jquery.panelslider.min.js` scripts:
```html
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="jquery.panelslider.min.js"></script>
```

[Bower](https://github.com/bower/bower) users can get the source with:

```sh
bower install jquery-panelslider
```

# Usage

**Opening**

Create an element with the panel content and a link to it.
```html
<div id="my-panel">
  <p>Hello, world</p>
</div>

<a id="my-link" href="#my-panel">Open panel</a>
```

and then invoke `panelslider()` on the element.
```javascript
$('#my-panel').panelslider();
```

You can also invoke `panelslider()` directly
```javascript
$.panelslider($('#my-panel'));
```

**Closing**

The panel will close when ESC is pressed or any part of the page outside of panel is clicked (if options.clickClose is true).

Alternativaly, it's possible to close it manually. Because there can be only one panel active at a single time, there's no need to select which panel to close:
```javascript
$.panelslider.close(callback);
```


# Options

These are the supported options and their default values:
```javascript
defaults = {
  side: 'left',      // Panel side: left or right
  duration: 200,     // Transition duration in miliseconds
  clickClose: true,  // If true closes panel when clicking outside it
  onOpen: null       // When supplied, function is called after the panel opens
}
```

A call with no arguments is equivalent to:
```javascript
$('#my-panel').panelslider({side: 'left', duration: 200, clickClose: true, onOpen: null });
```
