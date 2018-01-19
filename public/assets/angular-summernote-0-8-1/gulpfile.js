var gulp = require('gulp'),
    jshint = require('gulp-jshint'),
    uglify = require('gulp-uglify'),
    rename = require("gulp-rename"),
    header = require("gulp-header"),
    Server = require('karma').Server,
    coveralls = require('gulp-coveralls'),
    del = require('del'),
    nugetpack = require('gulp-nuget-pack'),
    pkg = require('./package.json');

var banner = '/*  angular-summernote v<%=pkg.version%> | (c) 2016 JeongHoon Byun | MIT license */\n';
var isAngular12 = isAngular13 = isAngular14 = false;

gulp.task('lint', function() {
  return gulp.src(['./src/**/*.js', './test/**/*.test.js'])
    .pipe(jshint())
    .pipe(jshint.reporter('jshint-stylish'));
});

gulp.task('copy', function() {
  return gulp.src('./src/angular-summernote.js')
    .pipe(header(banner, {pkg: pkg}))
    .pipe(gulp.dest('dist'));
});

gulp.task('build', ['copy'], function() {
  return gulp.src('./src/angular-summernote.js')
    .pipe(uglify({mangle: false}))
    .pipe(rename({extname: '.min.js'}))
    .pipe(header(banner, {pkg: pkg}))
    .pipe(gulp.dest('dist'));
});

gulp.task('karma', function (done) {
  var configFile = '/test/karma.conf.js';
  if (isAngular12) { configFile = '/test/karma-angular-1-2-x.conf.js'; }
  if (isAngular13) { configFile = '/test/karma-angular-1-3-x.conf.js'; }
  if (isAngular14) { configFile = '/test/karma-angular-1-4-x.conf.js'; }

  if (!process.env.CI) {
    new Server({
      configFile: __dirname + configFile,
      autoWatch: true
    }, done).start();
  } else {
    new Server({
      configFile: __dirname + configFile,
      browsers: ['PhantomJS'],
      singleRun: true
    }, done).start();
  }
});

gulp.task('test', function() {
  gulp.start('karma');
});

gulp.task('test:angular12', function() {
  isAngular12 = true;
  gulp.start('karma');
});

gulp.task('test:angular13', function() {
  isAngular13 = true;
  gulp.start('karma');
});

gulp.task('test:angular14', function() {
  isAngular14 = true;
  gulp.start('karma');
});

gulp.task('test:coverage', function(done) {
  var configFile = '/test/karma.conf.js';
  new Server({
    configFile: __dirname + configFile,
    singleRun: true,
    browsers: ['PhantomJS'],
    reporters: ['progress', 'coverage'],
    preprocessors: { '../**/src/**/*.js': 'coverage' },
    coverageReporter: { type: 'lcov', dir: '../coverage/' },
    plugins: [ 'karma-*' ]
  }, done).start();
});

gulp.task('clean:coverage', function () {
  return del([
    'coverage'
  ]);
});

gulp.task('coveralls', ['clean:coverage', 'test:coverage'], function() {
  return gulp.src('./coverage/**/lcov.info')
    .pipe(coveralls());
});

gulp.task('travis', ['test', 'test:angular12', 'test:angular13', 'test:angular14'], function() {
});

gulp.task('nuget-pack', function(done) {
  nugetpack({
    id: "Angular.Summernote",
    version: pkg.version,
    authors: pkg.author.name,
    description: pkg.description,
    projectUrl: pkg.homepage,
    licenseUrl: "https://github.com/summernote/angular-summernote/blob/master/LICENSE-MIT",
    copyright: "MIT",
    tags: pkg.keywords.join(' '),
    outputDir: "out"
  }, ['dist/*.js', 'README.md' ], done);
});
