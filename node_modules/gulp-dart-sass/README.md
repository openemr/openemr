# gulp-dart-sass [![Build Status](https://travis-ci.org/mattdsteele/gulp-dart-sass.svg?branch=master)](https://travis-ci.org/mattdsteele/gulp-dart-sass) [![Join the chat at https://gitter.im/mattdsteele/gulp-dart-sass](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/mattdsteele/gulp-dart-sass?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) [![npm version](https://badge.fury.io/js/gulp-dart-sass.svg)](http://badge.fury.io/js/gulp-dart-sass)

Sass plugin for [Gulp](https://github.com/gulpjs/gulp), using the [Dart Sass](https://github.com/sass/dart-sass) compiler.

# Support

Only [Active LTS and Current releases][1] are supported.

[1]: https://github.com/nodejs/Release#release-schedule

# Install

```
npm install gulp-dart-sass --save-dev
```

# Basic Usage

Something like this will compile your Sass files:

```javascript
'use strict';

var gulp = require('gulp');
var sass = require('gulp-dart-sass');

gulp.task('sass', function () {
  return gulp.src('./sass/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('./css'));
});

gulp.task('sass:watch', function () {
  gulp.watch('./sass/**/*.scss', ['sass']);
});
```

You can also compile synchronously, doing something like this:

```javascript
'use strict';

var gulp = require('gulp');
var sass = require('gulp-dart-sass');

gulp.task('sass', function () {
  return gulp.src('./sass/**/*.scss')
    .pipe(sass.sync().on('error', sass.logError))
    .pipe(gulp.dest('./css'));
});

gulp.task('sass:watch', function () {
  gulp.watch('./sass/**/*.scss', ['sass']);
});
```

## Options

Pass in options just like you would for [`dart-sass`](https://github.com/sass/node-sass#options); they will be passed along just as if you were using `dart-sass`. Except for the `data` option which is used by gulp-dart-sass internally. Using the `file` option is also unsupported and results in undefined behaviour that may change without notice.

For example:

```javascript
gulp.task('sass', function () {
 return gulp.src('./sass/**/*.scss')
   .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
   .pipe(gulp.dest('./css'));
});
```

## Source Maps

`gulp-dart-sass` can be used in tandem with [gulp-sourcemaps](https://github.com/floridoo/gulp-sourcemaps) to generate source maps for the Sass to CSS compilation. You will need to initialize [gulp-sourcemaps](https://github.com/floridoo/gulp-sourcemaps) prior to running `gulp-dart-sass` and write the source maps after.

```javascript
var sourcemaps = require('gulp-sourcemaps');

gulp.task('sass', function () {
 return gulp.src('./sass/**/*.scss')
  .pipe(sourcemaps.init())
  .pipe(sass().on('error', sass.logError))
  .pipe(sourcemaps.write())
  .pipe(gulp.dest('./css'));
});
```

By default, [gulp-sourcemaps](https://github.com/floridoo/gulp-sourcemaps) writes the source maps inline in the compiled CSS files. To write them to a separate file, specify a path relative to the `gulp.dest()` destination in the `sourcemaps.write()` function.

```javascript
var sourcemaps = require('gulp-sourcemaps');
gulp.task('sass', function () {
 return gulp.src('./sass/**/*.scss')
  .pipe(sourcemaps.init())
  .pipe(sass().on('error', sass.logError))
  .pipe(sourcemaps.write('./maps'))
  .pipe(gulp.dest('./css'));
});
```

# Issues

`gulp-dart-sass` is a very light-weight wrapper around [`dart-sass`](https://github.com/sass/dart-sass), which is a port of [`Sass`](https://github.com/sass/sass). Because of this, the issue you're having likely isn't a `gulp-dart-sass` issue, but an issue with one of those projects.

If you have a feature request/question how Sass works/concerns on how your Sass gets compiled/errors in your compiling, it's likely a `dart-sass` or `Sass` issue and you should file your issue with one of those projects.

If you're having problems with the options you're passing in, it's likely a `dart-sass` or `libsass` issue and you should file your issue with one of those projects.

We may, in the course of resolving issues, direct you to one of these other projects. If we do so, please follow up by searching that project's issue queue (both open and closed) for your problem and, if it doesn't exist, filing an issue with them.
