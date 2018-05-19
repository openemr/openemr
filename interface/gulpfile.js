'use strict';

// modules
var browserSync = require('browser-sync');
var csso = require('gulp-csso');
var gap = require('gulp-append-prepend');
var gulp = require('gulp');
var argv = require('minimist')(process.argv.slice(2));
var gulpif = require('gulp-if');
var prefix = require('gulp-autoprefixer');
var reload = browserSync.reload;
var rename = require('gulp-rename');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');

// configuration
var config = {
    dev: argv['dev'],
    proxy: argv['p'], // todo add proxy for openemr instance
    src: {
        styles: {
            style_uni: 'themes/style_*.scss',
            style_color: 'themes/colors/*.scss',
            all: 'themes/**/style_*.scss'
        }
    },
    dest: {
        themes: 'themes'
    }
};

// clean - will use once css files are no longer committed
// gulp.task('clean', function (cb) {
//     del([config.dest], cb);
// });

gulp.task('browser-sync', ['styles'], function() {
    browserSync.init({
        proxy: "localhost:" + config.proxy
    });

    gulp.watch('themes/**/*.scss', ['styles'])
});

// styles
gulp.task('styles:style_uni', function () {
    gulp.src(config.src.styles.style_uni)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(prefix('last 1 version'))
        .pipe(gulpif(!config.dev, csso()))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(config.dest.themes))
        .pipe(gulpif(config.dev, reload({stream:true})));
});

gulp.task('styles:style_color', function () {
    gulp.src(config.src.styles.style_color)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(prefix('last 1 version'))
        .pipe(gulpif(!config.dev, csso()))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(config.dest.themes))
        .pipe(gulpif(config.dev, reload({stream:true})));
});

gulp.task('styles:rtl', function () {
    gulp.src(config.src.styles.all)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(prefix('last 1 version'))
        .pipe(gulpif(!config.dev, csso()))
        .pipe(sourcemaps.write())
        .pipe(gap.appendFile('themes/rtl.css'))
        .pipe(rename({
            dirname: "",
            prefix:"rtl_"
        }))
        .pipe(gulp.dest(config.dest.themes))
        .pipe(gulpif(config.dev, reload({stream:true})));
});

// gulp.task('styles:style_list', function () {
//     gulp.src(config.src.styles.all)
//         .pipe(require('gulp-filelist')('themeOptions.html', {flatten: true, removeExtensions: true, destRowTemplate: "<option value=\"@filePath@\">@filePath@</option>\n"}))
//         .pipe(gulp.dest('src/views/layouts/includes'));
// });

gulp.task('styles', ['styles:style_uni', 'styles:style_color', 'styles:rtl']);

gulp.task('default', [ 'browser-sync']);
