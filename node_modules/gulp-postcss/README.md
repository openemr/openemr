# gulp-postcss

![Build Status](https://github.com/postcss/gulp-postcss/actions/workflows/test.yml/badge.svg?branch=main)
[![Coverage Status](https://img.shields.io/coveralls/postcss/gulp-postcss.svg)](https://coveralls.io/r/postcss/gulp-postcss)

[PostCSS](https://github.com/postcss/postcss) gulp plugin to pipe CSS through
several plugins, but parse CSS only once.

## Install

    $ npm install --save-dev postcss gulp-postcss

Install required [postcss plugins](https://www.npmjs.com/browse/keyword/postcss-plugin) separately. E.g. for autoprefixer, you need to install [autoprefixer](https://github.com/postcss/autoprefixer) package.

## Basic usage

The configuration is loaded automatically from `postcss.config.js`
as [described here](https://www.npmjs.com/package/postcss-load-config),
so you don't have to specify any options.

```js
var postcss = require('gulp-postcss');
var gulp = require('gulp');

gulp.task('css', function () {
    return gulp.src('./src/*.css')
        .pipe(postcss())
        .pipe(gulp.dest('./dest'));
});
```

## Passing plugins directly

```js
var postcss = require('gulp-postcss');
var gulp = require('gulp');
var autoprefixer = require('autoprefixer');
var cssnano = require('cssnano');

gulp.task('css', function () {
    var plugins = [
        autoprefixer({browsers: ['last 1 version']}),
        cssnano()
    ];
    return gulp.src('./src/*.css')
        .pipe(postcss(plugins))
        .pipe(gulp.dest('./dest'));
});
```

## Using with .pcss extension

For using gulp-postcss to have input files in .pcss format and get .css output need additional library like gulp-rename.

```js
var postcss = require('gulp-postcss');
var gulp = require('gulp');
const rename = require('gulp-rename');

gulp.task('css', function () {
    return gulp.src('./src/*.pcss')
        .pipe(postcss())
        .pipe(rename({
          extname: '.css'
        }))
        .pipe(gulp.dest('./dest'));
});
```

This is done for more explicit transformation. According to [gulp plugin guidelines](https://github.com/gulpjs/gulp/blob/master/docs/writing-a-plugin/guidelines.md#guidelines)

> Your plugin should only do one thing, and do it well.


## Passing additional options to PostCSS

The second optional argument to gulp-postcss is passed to PostCSS.

This, for instance, may be used to enable custom parser:

```js
var gulp = require('gulp');
var postcss = require('gulp-postcss');
var nested = require('postcss-nested');
var sugarss = require('sugarss');

gulp.task('default', function () {
    var plugins = [nested];
    return gulp.src('in.sss')
        .pipe(postcss(plugins, { parser: sugarss }))
        .pipe(gulp.dest('out'));
});
```

## Using a custom processor

```js
var postcss = require('gulp-postcss');
var cssnext = require('postcss-cssnext');
var opacity = function (css, opts) {
    css.walkDecls(function(decl) {
        if (decl.prop === 'opacity') {
            decl.parent.insertAfter(decl, {
                prop: '-ms-filter',
                value: '"progid:DXImageTransform.Microsoft.Alpha(Opacity=' + (parseFloat(decl.value) * 100) + ')"'
            });
        }
    });
};

gulp.task('css', function () {
    var plugins = [
        cssnext({browsers: ['last 1 version']}),
        opacity
    ];
    return gulp.src('./src/*.css')
        .pipe(postcss(plugins))
        .pipe(gulp.dest('./dest'));
});
```

## Source map support

Source map is disabled by default, to extract map use together
with [gulp-sourcemaps](https://github.com/floridoo/gulp-sourcemaps).

```js
return gulp.src('./src/*.css')
    .pipe(sourcemaps.init())
    .pipe(postcss(plugins))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./dest'));
```

## Advanced usage

If you want to configure postcss on per-file-basis, you can pass a callback
that receives [vinyl file object](https://github.com/gulpjs/vinyl) and returns
`{ plugins: plugins, options: options }`. For example, when you need to
parse different extensions differntly:

```js
var gulp = require('gulp');
var postcss = require('gulp-postcss');

gulp.task('css', function () {
    function callback(file) {
        return {
            plugins: [
                require('postcss-import')({ root: file.dirname }),
                require('postcss-modules')
            ],
            options: {
                parser: file.extname === '.sss' ? require('sugarss') : false
            }
        }
    }
    return gulp.src('./src/*.css')
        .pipe(postcss(callback))
        .pipe(gulp.dest('./dest'));
});
```

The same result may be achieved with
[`postcss-load-config`](https://www.npmjs.com/package/postcss-load-config),
because it receives `ctx` with the context options and the vinyl file.

```js
var gulp = require('gulp');
var postcss = require('gulp-postcss');

gulp.task('css', function () {
    var contextOptions = { modules: true };
    return gulp.src('./src/*.css')
        .pipe(postcss(contextOptions))
        .pipe(gulp.dest('./dest'));
});
```

```js
module.exports = function (ctx) {
    var file = ctx.file;
    var options = ctx.options;
    return {
        parser: file.extname === '.sss' ? : 'sugarss' : false,
        plugins: {
           'postcss-import': { root: file.dirname }
           'postcss-modules': options.modules ? {} : false
        }
    }
})
```

## Changelog

* 9.0.1
  * Bump postcss-load-config to ^3.0.0

* 9.0.0
  * Bump PostCSS to 8.0
  * Drop Node 6 support
  * PostCSS is now a peer dependency

* 8.0.0
  * Bump PostCSS to 7.0
  * Drop Node 4 support

* 7.0.1
  * Drop dependency on gulp-util

* 7.0.0
  * Bump PostCSS to 6.0
  * Smaller module size
  * Use eslint instead of jshint

* 6.4.0
  * Add more details to `PluginError` object

* 6.3.0
  * Integrated with postcss-load-config
  * Added a callback to configure postcss on per-file-basis
  * Dropped node 0.10 support

* 6.2.0
  * Fix syntax error message for PostCSS 5.2 compatibility

* 6.1.1
  * Fixed the error output

* 6.1.0
  * Support for `null` files
  * Updated dependencies

* 6.0.1
  * Added an example and a test to pass options to PostCSS (e.g. `syntax` option)
  * Updated vinyl-sourcemaps-apply to 0.2.0

* 6.0.0
  * Updated PostCSS to version 5.0.0

* 5.1.10
  * Use autoprefixer in README

* 5.1.9
  * Prevent unhandled exception of the following pipes from being suppressed by Promise

* 5.1.8
  * Prevent stream’s unhandled exception from being suppressed by Promise

* 5.1.7
  * Updated direct dependencies

* 5.1.6
  * Updated `CssSyntaxError` check

* 5.1.4
  * Simplified error handling
  * Simplified postcss execution with object plugins

* 5.1.3 Updated travis banner

* 5.1.2 Transferred repo into postcss org on github

* 5.1.1
  * Allow override of `to` option

* 5.1.0 PostCSS Runner Guidelines
  * Set `from` and `to` processing options
  * Don't output js stack trace for `CssSyntaxError`
  * Display `result.warnings()` content

* 5.0.1
  * Fix to support object plugins

* 5.0.0
  * Use async API

* 4.0.3
  * Fixed bug with relative source map

* 4.0.2
  * Made PostCSS a simple dependency, because peer dependency is deprecated

* 4.0.1
  * Made PostCSS 4.x a peer dependency

* 4.0.0
  * Updated PostCSS to 4.0

* 3.0.0
  * Updated PostCSS to 3.0 and fixed tests

* 2.0.1
  * Added Changelog
  * Added example for a custom processor in README

* 2.0.0
  * Disable source map by default
  * Test source map
  * Added Travis support
  * Use autoprefixer-core in README

* 1.0.2
  * Improved README

* 1.0.1
  * Don't add source map comment if used with gulp-sourcemaps

* 1.0.0
  * Initial release
