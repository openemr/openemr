# angular-summernote - [AngularJS](http://angularjs.org/) directive to [Summernote](http://summernote.org/)

***

[![Build Status](https://travis-ci.org/summernote/angular-summernote.png?branch=master)](https://travis-ci.org/summernote/angular-summernote)
[![Dependency Status](https://gemnasium.com/summernote/angular-summernote.png)](https://gemnasium.com/summernote/angular-summernote)
[![Coverage Status](https://coveralls.io/repos/summernote/angular-summernote/badge.png)](https://coveralls.io/r/summernote/angular-summernote)

angular-summernote is just a directive to bind summmernote's all features.
You can use summernote with angular way.

**Since v0.7.x, the version of angular-summernote follows the version of summernote.
So, angular-summernote v0.7.x are compatible with summernote v0.7.x and
and angular-summernote v0.8.x will be compatible with summernote v0.8.x.
Angular-summernote will match only `major.minor` with summernote.
Therefore, angular-summernote v0.7.0 will be compatible with summernote v0.7.0, v0.7.1 and
v0.7.2. Angular-summernote will release patch update, such as v0.7.1, if only angular-summernote has changed.**

## Table of Contents

- [Demo](#demo)
- [Installation](#Installation)
- [How To Use](#how-to-use)
    - [summernote directive](summernote-directive)
    - [Options](#options)
    - [ngModel](#ngmodel)
    - [Event Listeners](#event-listeners)
    - [i18n Support](#i18n-support)
- [FAQ](#faq)
- [Change Logs](#change-logs)

## Demo

See at [JSFiddle](http://jsfiddle.net/outsider/n8dt4/322/embedded/result%2Chtml%2Cjs%2Ccss/)
or run example in projects(need to run `bower install` before run)

## Installation

angular-summernote requires all include files of [Summernote](http://summernote.org/).
see [Summernote's installation](http://summernote.org/#/features#installation).

Project files are also available through your favourite package manager:

* Bower: `bower install angular-summernote`

## How To Use

When you are done downloading all the dependencies and project files the only remaining part is to add dependencies on the ui.bootstrap AngularJS module:

When you've inclued all js and css files you need to inject `a` into your angular application:

```javascript
angular.module('myApp', ['summernote']);
```

### `summernote` Directive

You can use `summernote` directive where you want to use summernote editor.
And when the scope is destroyed the directive will be destroyed.

#### As element:

```html
<summernote></summernote>
```
#### As attribute:

```html
<div summernote></div>
```

It will be initialized automatically.

If you put markups in the directive, the markups used as initial text.

```html
<summernote><span style="font-weight: bold;">This is initial text.</span></summernote>
```

### Options

summernote's options can be specified as attributes.

#### height

```html
<summernote height="300"></summernote>
```

#### focus

```html
<summernote focus></summernote>
```

#### airmode
```html
<summernote airMode></summernote>
```

If you use the `removeMedia` button in popover, like below:

```
<summernote airMode config="options" on-media-delete="mediaDelete(target)"></summernote>
```

```
function DemoController($scope) {
  $scope.options = {
    popover: {
      image: [['remove', ['removeMedia']] ],
      air: [['insert', ['picture']]]
    }
  };
  $scope.mediaDelete = function(target) {
    console.log('media is delted:', target);
  }
}
```

You can use the 'onMediaDelete` callback. The `target` object has information of the DOM that is removed like:

```
{
  tagName: "IMG",
  attrs: {
    data-filename: "image-name.jpg",
    src: "http://path/to/image",
    style: "width: 100px;"
  }
}
```

#### options object

You can specify all options using ngModel in `config` attribute.

```html
<summernote config="options"></summernote>
```

```javascript
function DemoController($scope) {
  $scope.options = {
    height: 300,
    focus: true,
    airMode: true,
    toolbar: [
            ['edit',['undo','redo']],
            ['headline', ['style']],
            ['style', ['bold', 'italic', 'underline', 'superscript', 'subscript', 'strikethrough', 'clear']],
            ['fontface', ['fontname']],
            ['textsize', ['fontsize']],
            ['fontclr', ['color']],
            ['alignment', ['ul', 'ol', 'paragraph', 'lineheight']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link','picture','video','hr']],
            ['view', ['fullscreen', 'codeview']],
            ['help', ['help']]
        ]
  };
}
```

NOTE: `height` and `focus` attributes have high priority than options object.

NOTE: custom toolbar can be set by options object.

### ngModel

summernote's `code`, that is HTML string in summernote.
If you specify ngModel it will be 2-ways binding
to HTML string in summernote. Otherwise `angular-summernote` simply ignore it.

```html
<summernote ng-model="text"></summernote>
```

```javascript
function DemoController($scope) {
  $scope.text = "Hello World";
}
```

And you can use [ngModelOptions](https://docs.angularjs.org/api/ng/directive/ngModelOptions)
with Angular v1.3+. So, you can update ngModel when blur event emitted or with a debouncing delay
if you want.

### Event Listeners

event listeners can be registered as attribute as you want.

```javascript
function DemoController($scope) {
  $scope.init = function() { console.log('Summernote is launched'); }
  $scope.enter = function() { console.log('Enter/Return key pressed'); }
  $scope.focus = function(e) { console.log('Editable area is focused'); }
  $scope.blur = function(e) { console.log('Editable area loses focus'); }
  $scope.paste = function(e) { console.log('Called event paste'); }
  $scope.change = function(contents) {
    console.log('contents are changed:', contents, $scope.editable);
  };
  $scope.keyup = function(e) { console.log('Key is released:', e.keyCode); }
  $scope.keydown = function(e) { console.log('Key is pressed:', e.keyCode); }
  $scope.imageUpload = function(files) {
    console.log('image upload:', files);
    console.log('image upload\'s editable:', $scope.editable);
  }
}
```

```html
<summernote on-init="init()" on-enter="enter()" on-focus="focus(evt)"
            on-blur="blur(evt)" on-paste="paste()" on-keyup="keyup(evt)"
            on-keydown="keydown(evt)" on-change="change(contents)"
            on-image-upload="imageUpload(files)" editable="editable" editor="editor">
</summernote>
```

If you use `$editable` object in `onImageUpload` or `onChange`
(see [summernote's callback](http://summernote.org/#/features#callbacks)),
you should define `editable` attribute and use it in `$scope`.
(Because [AngularJS 1.3.x restricts access to DOM nodes from within expressions](https://docs.angularjs.org/error/$parse/isecdom))

Since summernote v0.6.4, APIs have been changed. So, If you use the verions,
`onImageUpload` is not return `editor` object anymore. If you want to user
`editor` object, you should define `editor` attribute and use it in `$scope`.
Futhermore, you can use summernote's APIs via the `editor` object.

### i18n Support

If you use i18n, you have to include language files.
See [summernote's document](http://summernote.org/#/features#i18n)
for more details.
And then you can specify language like:

```html
<summernote lang="ko-KR"></summernote>
```

## FAQ

- __How to solve compatibility problem with AngularUI Bootstrap?__

[AngularUI Bootstrap](http://angular-ui.github.io/bootstrap/) module is
written to replace the JavaScript file for bootstrap with its own
implementation (`ui-bootstrap-tpls.min.js`).

Summernote was intended to work with Bootstrap, so the coder implemented
features that rely on the `bootstrap.js` file being present.

* If you do not include `bootstrap.js`, summernote throws exceptions.
* If you do not include `ui-bootstrap-tpls.min.js`, your angular directives
  for bootstrap will not work.
* If you include both, then both JavaScript files try to listen on various
  events, and otherwise may have incompatibility issues.

If you have a drop down in the navbar, and use `data-dropdown` directive
as bootstrap says to, then two clicks are required to open
the drop down (menu) instead of the expected one click.

The solution is to not use `data-dropdown` directive. However, the
real solution is for summernote to be agnostic about which of
`bootstrap.js` or `ui-bootstrap-tpls.min.js` are loaded and make the right calls.
(see [#21](https://github.com/summernote/angular-summernote/issues/21))

## Change Logs

See [here](https://github.com/summernote/angular-summernote/blob/master/CHANGELOG.md).
