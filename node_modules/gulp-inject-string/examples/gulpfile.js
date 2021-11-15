var gulp = require('gulp'),
    rename = require('gulp-rename'),
    inject = require('../');

gulp.task('inject:append', function(){
    gulp.src('src/example.html')
        .pipe(inject.append('\n<!-- Created: ' + Date() + ' -->'))
        .pipe(rename('append.html'))
        .pipe(gulp.dest('build'));
});

gulp.task('inject:prepend', function(){
    gulp.src('src/example.html')
        .pipe(inject.prepend('<!-- Created: ' + Date() + ' -->\n'))
        .pipe(rename('prepend.html'))
        .pipe(gulp.dest('build'));
});

gulp.task('inject:wrap', function(){
    gulp.src('src/example.html')
        .pipe(inject.wrap('<!-- Created: ' + Date() + ' -->\n', '<!-- Author: Mike Hazell -->'))
        .pipe(rename('wrap.html'))
        .pipe(gulp.dest('build'));
});

gulp.task('inject:before', function(){
    gulp.src('src/example.html')
        .pipe(inject.before('<script', '<script src="http://code.jquery.com/jquery-2.1.1.min.js"></script>\n'))
        .pipe(rename('before.html'))
        .pipe(gulp.dest('build'));
});

gulp.task('inject:after', function(){
    gulp.src('src/example.html')
        .pipe(inject.after('</title>', '\n<link rel="stylesheet" href="test.css">\n'))
        .pipe(rename('after.html'))
        .pipe(gulp.dest('build'));
});

gulp.task('inject:beforeEach', function(){
    gulp.src('src/example.html')
        .pipe(inject.beforeEach('</p', ' Finis.'))
        .pipe(rename('beforeEach.html'))
        .pipe(gulp.dest('build'));
});

gulp.task('inject:afterEach', function(){
    gulp.src('src/example.html')
        .pipe(inject.afterEach('<p', ' class="bold"'))
        .pipe(rename('afterEach.html'))
        .pipe(gulp.dest('build'));
});

gulp.task('inject:replace', function(){
    gulp.src('src/example.html')
        .pipe(inject.replace('test.js', 'test.min.js'))
        .pipe(rename('replace.html'))
        .pipe(gulp.dest('build'));
});

gulp.task('default', [
    'inject:append',
    'inject:prepend',
    'inject:wrap',
    'inject:before',
    'inject:after',
    'inject:beforeEach',
    'inject:afterEach',
    'inject:replace'
]);
