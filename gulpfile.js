(function() {
  'use strict';

  const autoprefixer = require('autoprefixer');
  const cssnano = require('cssnano');
  const del = require('del');
  const eslint = require('gulp-eslint');
  const gulp = require('gulp');
  const postcss = require('gulp-postcss');
  const rename = require('gulp-rename');
  const uglify = require('gulp-uglify');

  const wwwPath = 'D:/www/bingo/';

  let production = false;

  function setProd(cb) {
    production = true;
    cb();
  }

  function clean() {
    return del(['dist/*', wwwPath + '*'], {dot: true, force: true});
  }

  function css() {
    return gulp.src('src/css/*.css', {sourcemaps: !production})
      .pipe(postcss([autoprefixer(), cssnano()]))
      .pipe(rename({extname: '.min.css'}))
      .pipe(gulp.dest('dist/css/', {sourcemaps: '.'}));
  }

  function js() {
    return gulp.src('src/js/*.js', {sourcemaps: !production})
      .pipe(eslint())
      .pipe(eslint.format())
      .pipe(uglify())
      .pipe(rename({extname: '.min.js'}))
      .pipe(gulp.dest('dist/js/', {sourcemaps: '.'}));
  }

  function php() {
    return gulp.src('src/**/*.php')
      .pipe(gulp.dest('dist/'));
  }

  function misc() {
    return gulp.src('src/**/.htaccess')
      .pipe(gulp.dest('dist/'));
  }

  function audio() {
    return gulp.src('src/audio/**/*')
      .pipe(gulp.dest('dist/audio/'));
  }

  function assets() {
    return gulp.src('src/assets/**/*')
      .pipe(gulp.dest('dist/'));
  }

  function sync() {
    return gulp.src('dist/**/*', {dot: true})
      .pipe(gulp.dest(wwwPath));
  }

  function watch(cb) {
    gulp.watch('src/css/*.css', css);
    gulp.watch('src/js/*.js', js);
    gulp.watch('src/**/*.php', php);
    gulp.watch('src/.htaccess', misc);
    gulp.watch('src/audio/**/*', audio);
    gulp.watch('src/assets/**/*', assets);
    gulp.watch('dist/**/*', {dot: true}, sync);
    cb();
  }

  exports.clean = clean;
  exports.prod = gulp.series(setProd, clean, gulp.parallel(css, js, php, misc, audio, assets));

  exports.default = gulp.series(clean, gulp.parallel(css, js, php, misc, audio, assets), sync, watch);
})();
