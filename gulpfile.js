/* jshint esversion: 6, node: true, strict: true */
(function () {
  'use strict';

  const gulp = require('gulp');
  const del = require('del');

  const wwwPath = 'D:/www/bingo/';

  function clean() {
    return del(['dist/*', wwwPath + '*'], { dot: true, force: true });
  }

  function css() {
    return gulp.src('src/css/*.css')
      .pipe(gulp.dest('dist/css/'));
  }

  function js() {
    return gulp.src('src/js/*.js')
      .pipe(gulp.dest('dist/js/'));
  }

  function php() {
    return gulp.src('src/**/*.php')
      .pipe(gulp.dest('dist/'));
  }

  function misc() {
    return gulp.src('src/**/.htaccess')
      .pipe(gulp.dest('dist/'));
  }

  function sync() {
    return gulp.src('dist/**/*', { dot: true })
      .pipe(gulp.dest(wwwPath));
  }

  function watch(cb) {
    gulp.watch('src/css/*.css', css);
    gulp.watch('src/js/*.js', js);
    gulp.watch('src/**/*.php', php);
    gulp.watch('src/.htaccess', misc);
    gulp.watch('dist/**/*', { dot: true }, sync);
    cb();
  }

  exports.clean = clean;

  exports.default = gulp.series(clean, gulp.parallel(css, js, php, misc), sync, watch);
})();
