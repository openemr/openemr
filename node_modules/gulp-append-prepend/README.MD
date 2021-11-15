# gulp-append-prepend

Simple Gulp plugin.

[![License:MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://github.com/JamesHemery/gulp-append-prepend/blob/master/LICENCE)
[![npm](https://img.shields.io/npm/v/gulp-append-prepend.svg)](https://www.npmjs.com/package/gulp-append-prepend)
[![Build](https://travis-ci.org/JamesHemery/gulp-append-prepend.svg)](https://travis-ci.org/JamesHemery/gulp-append-prepend)

## Usage

First, install gulp-append-prepend as a development dependency:
```
npm install gulp-append-prepend --save-dev
```

Then, add it to your gulpfile.js:

```javascript
const gap = require('gulp-append-prepend');

gulp.task('myawesometask', function(){
    gulp.src('index.html')
        .pipe(gap.prependFile('header.html'))
        .pipe(gap.prependText('<!-- HEADER -->'))
        .pipe(gap.appendText('<!-- FOOTER -->'))
        .pipe(gap.appendFile('footer.html'))
        .pipe(gulp.dest('www/'));
});
```

This example works with html but you can use any type of file.

## Methods
- ``appendFile(filepath, separator)`` The filepath can be an array. The separator is optional by default is "\n".
- ``prependFile(filepath, separator)`` The filepath can be an array. The separator is optional by default is "\n".
- ``appendText(text, separator)`` The text can be an array. The separator is optional by default is "\n".
- ``prependText(text, separator)`` The text can be an array. The separator is optional by default is "\n".

## Licence
This plugin is released under the [MIT licence](./LICENCE).

## Disclaimer
I am sorry for my poor English. Feel free to make changes in this README.
