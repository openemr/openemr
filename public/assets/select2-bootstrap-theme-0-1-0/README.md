A [Select2](https://select2.github.io/) v4 [Theme](https://select2.github.io/examples.html#themes) for Bootstrap 3  
![select2-bootstrap-theme version](https://img.shields.io/badge/select2--bootstrap--theme-v0.1.0--beta.10-brightgreen.svg)
[![License](http://img.shields.io/badge/License-MIT-blue.svg)](http://opensource.org/licenses/MIT)

Demonstrations available at
[select2.github.io/select2-bootstrap-theme](http://select2.github.io/select2-bootstrap-theme/)

#### Compatibility

Built and tested with Bootstrap v3.3.7 and Select2 v4.0.3 in latest Chrome, Firefox and Safari (Mac) and Internet Explorer 11, 10 and 9 (should work in IE8).

#### Installation

You can [download select2-bootstrap-theme from this GitHub repo](https://github.com/select2/select2-bootstrap-theme/releases), install it using Bower or npm – required if you want to integrate the Sass or Less sources in your build process – or source the compiled CSS directly from CDNJS or jsDelivr.

##### Install using Bower or npm/yarn

You may install select2-bootstrap-theme with [Bower](https://bower.io/), [npm](https://www.npmjs.com/) or [Yarn](https://yarnpkg.com/):

```shell
// Bower
bower install select2-bootstrap-theme

// npm
npm install select2-bootstrap-theme

// yarn
yarn add select2-bootstrap-theme
```

##### Source select2-bootstrap-theme from CDNJS or jsDelivr

select2-bootstrap-theme [is also available on CDNJS](https://cdnjs.com/libraries/select2-bootstrap-theme/) and [jsDelivr](http://www.jsdelivr.com/projects/select2-bootstrap-theme).

#### Usage

select2-bootstrap-theme only works with Select2 v4.x. Applying the theme requires `select2-bootstrap.css` referenced after the default `select2.css` that comes with Select2:

```html
<link rel="stylesheet" href="select2.css">
<link rel="stylesheet" href="select2-bootstrap.css">
```

To apply the theme, tell Select2 to do so by passing `bootstrap` to the [`theme`](https://select2.github.io/examples.html#themes) option when initializing Select2:

```js
$( "#dropdown" ).select2({
    theme: "bootstrap"
});
```

You may also set it as the default theme for all Select2 widgets like so:

```js
$.fn.select2.defaults.set( "theme", "bootstrap" );
```

#### Changelog

##### 0.1.0-beta.10

* Compiled with grunt-sass v2.0.0 (was v1.2.1).
* Merged pull request [#54](https://github.com/select2/select2-bootstrap-theme/pull/54) by @odahcam (and fixed it: `:first-child/:not(:first-child)/:last-child` for `.select2-container--bootstrap` won’t play nice in our case; we have to take the original `<select>` element (with `.select2-hidden-accessible` applied) into account): Select2’s border-radii now are correctly styled in Bootstrap’s “Input Groups” without the need for helper classes (`.select2-bootstrap-append`, `.select2-bootstrap-prepend`).
* Fixed [#63](https://github.com/select2/select2-bootstrap-theme/issues/63): "Cursor position of multiselect in RTL state is in incorrect position"
* Fixed [#20](https://github.com/select2/select2-bootstrap-theme/issues/20): "Select2 inside inline form does not fall back to `display: block` when resizing window to 'extra small'"
* Added new Sass/Less variable `$s2bs-btn-default-color`/`@s2bs-btn-default-color` – defaults to Bootstrap’s `$btn-default-color`/`@btn-default-color`.
* Docs: Updated AnchorJS to v3.2.2 (was v3.2.1).
* Docs: Fixed layout for mobile devices (add `meta name="viewport" content="width=device-width, initial-scale=1"`).
* package.json: Fixed `license`-related npm 2.x “SPDX” warning: for npm 2.x and according to https://docs.npmjs.com/files/package.json#license.
* package.json: Added `bugs`.

##### 0.1.0-beta.9

* Built on Bootstrap 3 v3.3.7 and corresponding bootstrap-sass.
* Prefixed all Sass and Less variables with `s2bs` to avoid conflicts and improve customizability as select2-bootstrap-theme does not "force" you to use (specific) Bootstrap Sass/Less variables anymore; a nice side effect is that we now also provide (a raw) documentation for the inherited Bootstrap variables in one place.
* Added Sass and Less variables to override select2-bootstrap-themes default `font-size`, `color` and vertical `padding` for `.select2-results__group`. [[#19](https://github.com/select2/select2-bootstrap-theme/issues/19)]
* Added Sass and Less variables replacing hardcoded values in styles for `.select2-selection__clear`, `.select2-selection__choice__remove` and `.select2-selection__choice`.
* Added padding to root level `.select2-results__option` – fixes alignment of `.select2-results__message` and `.select2-results__option--load-more`. [[#28](https://github.com/select2/select2-bootstrap-theme/issues/28)]
* Removed `font-family` definition for `.select2-container--bootstrap .select2-selection`. [[#50](https://github.com/select2/select2-bootstrap-theme/issues/50)]
* Added Select2 and Bootstrap as dependencies in `bower.json` – fingers crossed, low hopes. [[#52](https://github.com/select2/select2-bootstrap-theme/issues/52)]
* Added "repository" to `bower.json`.
* Sass is now compiled using [LibSass](https://github.com/sass/libsass/)/[node-sass](https://github.com/sass/node-sass) instead of [Ruby Sass](https://github.com/sass/sass) (and [grunt-sass](https://github.com/sindresorhus/grunt-sass) instead of [grunt-contrib-sass](https://github.com/gruntjs/grunt-contrib-sass)).
* Decreased Sass number precision from 9 to 8 – now Sass numbers really matches its Less counterpart.
* Added Grunt task to compile Less and altered Less test (via [grunt-contrib-less](https://github.com/gruntjs/grunt-contrib-less)).
* Switched Sass and Less source tab size/indention from four to two spaces.
* Updated development dependencies: Autoprefixer to v6.4.0 (was v6.3.6), diff to v2.2.3 (was v2.2.2), grunt-gh-pages to v1.2.0 (was v1.1.0).
* Docs: Added "plain" (not nested in an `<optgroup>`) options to the "State" Select2.
* Docs: Updated AnchorJS to v3.2.1 (was v3.1.1), Bootstrap to v3.3.7 (was v3.3.6), jQuery to v1.12.4 (was v1.11.2).
* Docs: Enabled pagination for AJAX examples (in context of [#28](https://github.com/select2/select2-bootstrap-theme/issues/28)).
* Docs: Brought back demo pages for different Select2 releases (covering all of 4.0.x via [cdnjs](https://cdnjs.com/libraries/select2)).
* Docs: Removed empty `<option>` from the "Select2 multi append Radiobutton" demo (which resulted in problems tracked in [11](https://github.com/select2/select2-bootstrap-theme/issues/11)).

##### 0.1.0-beta.8

* Fixed bower.jsons "main" field. [[#45](https://github.com/select2/select2-bootstrap-theme/pull/45)]
* Do no re-assign the `$form-control-default-box-shadow`, `$form-control-focus-box-shadow`, and `$form-control-transition` Sass variables if they are already assigned. [[#45](https://github.com/select2/select2-bootstrap-theme/pull/45)]

##### 0.1.0-beta.7

* Fixed version number in distribution files.

##### 0.1.0-beta.6

* Fixed a bug where math would not compile correctly in Less v2.6.0. [[#36](https://github.com/select2/select2-bootstrap-theme/pull/36)]
* Fixed version number for Bower and NPM.
* Docs: Updated AnchorJS to latest version.

##### 0.1.0-beta.5

* Updated all development dependencies.
* Added Browsersync, Autoprefixer (as required by bootstrap-sass) and scss2less to the build process.
* Built on Bootstrap 3 v3.3.6 and corresponding bootstrap-sass.
* Rewrote the sizing class CSS to work with `containerCssClass` option available with the full Select2 build. [[#34](https://github.com/select2/select2-bootstrap-theme/issues/34)]
* Added copyright and license information. [[#43](https://github.com/select2/select2-bootstrap-theme/issues/43)]

##### 0.1.0-beta.4

 * Added missing styles for `.select2-container--focus`. [[#18](https://github.com/select2/select2-bootstrap-theme/issues/18)]
 * Added support for Bootstrap's [`.form-inline`](http://getbootstrap.com/css/#forms-inline). [[#13](https://github.com/select2/select2-bootstrap-theme/pull/13)]
 * Added basic styles for `.select2-selection__clear` in `.select2-selection--multiple`. [[#11](https://github.com/select2/select2-bootstrap-theme/issues/11)]
 * Brought Less source in line with the Sass version and fixed Less patch file and test. [[3e86f34](https://github.com/select2/select2-bootstrap-theme/commit/3e86f34f6c94302cd8b4d6c3d751c5fb70fe61f6)]

##### 0.1.0-beta.3

 * Fixed specifity problems with `.form-control.select2-hidden-accessible`.

##### 0.1.0-beta.2

 * Added Less version.

##### 0.1.0-beta.1

#### Contributing

The project offers [Less](http://lesscss.org/) and [Sass](http://sass-lang.com/) sources for building `select2-bootstrap.css`; both make use of variables from either [Bootstrap](https://github.com/twbs/bootstrap) (Less) or [Bootstrap for Sass](https://github.com/twbs/bootstrap-sass). The demo pages are built using [Jekyll](http://jekyllrb.com/) and there are a bunch of [Grunt](http://gruntjs.com/) tasks to ease development.

With [Jekyll](http://jekyllrb.com/), [node.js](http://nodejs.org/) and [Less](http://lesscss.org/) installed, run

```sh
npm install
```

to install all necessary development dependencies (Sass is compiled v[LibSass](https://github.com/sass/libsass/)/[node-sass](https://github.com/sass/node-sass)).

 * `grunt build` builds `docs`
 * `grunt serve` builds `docs` and serves them via Jekyll's `--watch` flag on http://localhost:3000

Develop in `src/select2-bootstrap.scss` and test your changes using `grunt serve`. Ideally, port your changes to `lib/select2-bootstrap.less` and make sure tests are passing to verify that both Less and Sass compile down to the target CSS via `npm test`.

`grunt scss2less` helps in converting the Sass source to its Less counterpart (and overwrites the existing `src/select2-bootstrap.less`), but doesn't do the full job – please review the changes to the Less source file and make the necessary adjustments.

#### Copyright and license

The license is available within the repository in the [LICENSE](LICENSE) file.
