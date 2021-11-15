# [gulp](https://github.com/gulpjs/gulp)-csso [![Build Status](https://travis-ci.org/ben-eb/gulp-csso.svg?branch=master)](https://travis-ci.org/ben-eb/gulp-csso) [![NPM version](https://badge.fury.io/js/gulp-csso.svg)](http://badge.fury.io/js/gulp-csso) [![Dependency Status](https://gemnasium.com/ben-eb/gulp-csso.svg)](https://gemnasium.com/ben-eb/gulp-csso)

> Minify CSS with [CSSO](https://www.npmjs.com/package/csso).

*If you have any difficulties with the output of this plugin, please use
the [CSSO tracker](https://github.com/css/csso/issues).*

## Install

With [npm](https://www.npmjs.com/package/gulp-csso) do:

```
npm install gulp-csso --save-dev
```

## Example

```js
var gulp = require('gulp');
var csso = require('gulp-csso');

gulp.task('default', function () {
    return gulp.src('./main.css')
        .pipe(csso())
        .pipe(gulp.dest('./out'));
});

gulp.task('development', function () {
    return gulp.src('./main.css')
        .pipe(csso({
            restructure: false,
            sourceMap: true,
            debug: true
        }))
        .pipe(gulp.dest('./out'));
});
```

## API

### csso([options])

#### options

For backwards compatibility it can also be a `boolean`. In this case, the
inverted value is set to `options.restructure`
(e.g. `true` becomes `{restructure: false}`).

##### restructure

Type: `boolean`  
Default: `true`

The default is to use structure minimization for maximum compression.
Pass `false` instead if you want to disable this feature.

##### sourceMap

Type: `boolean`  
Default: depends on input file has a source map or not

Specify this to generate source map; by default a source map is generated only
if the input file has a source map. Pass `true` to ensure that the source map
is generated or `false` to disable this.

Alternatively, you can enable source maps support using [gulp-sourcemaps]:

[gulp-sourcemaps]: https://github.com/floridoo/gulp-sourcemaps

```js
var gulp = require('gulp');
var csso = require('gulp-csso');
var sourcemaps = require('gulp-sourcemaps');

gulp.task('default', function () {
    return gulp.src('main.css')
        .pipe(sourcemaps.init())
        .pipe(csso())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./out'));
});
```

##### debug

Type: `boolean`  
Default: `false`

Pass `true` or a positive number (greater number for more details) to get some
debugging information about the minification process.

##### usage

Type: `object`
Default: `null`

Usage data for advanced optimisations (read more in the [CSSO documentation](https://github.com/css/csso#usage-data)).

## Contributing

Pull requests are welcome. If you add functionality, then please add unit tests
to cover it.

## License

MIT © [Ben Briggs](http://beneb.info)
