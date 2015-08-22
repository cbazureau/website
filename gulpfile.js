var gulp = require('gulp');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var minifyCss = require('gulp-minify-css');
var inject = require('gulp-inject');
var runSequence = require('run-sequence');
var del = require('del');
var imagemin = require('gulp-imagemin');
var pngquant = require('imagemin-pngquant');
var composer = require('gulp-composer');
var SRC_DIR = "./www/cedric/src";
var BUILD_DIR = "./www/cedric/dist";
var timeInMs = Date.now();


// Build CSS (concat, minify  & copy to build)
gulp.task('build-css', function() {
  return gulp.src(SRC_DIR+'/css/*.css')
    .pipe(concat('style.'+timeInMs+'.min.css'))
    .pipe(minifyCss())
    .pipe(gulp.dest(BUILD_DIR+'/css'));
});

gulp.task('build-img', function () {
    return gulp.src(SRC_DIR+'/img/*.*')
        .pipe(imagemin({
            progressive: true,
            svgoPlugins: [{removeViewBox: false}],
            use: [pngquant()]
        }))
        .pipe(gulp.dest(BUILD_DIR+'/img'));
});

// Build Fonts (Copy from src to build)
gulp.task('build-fonts', function() {
   gulp.src(SRC_DIR+'/fonts/*.*')
   .pipe(gulp.dest(BUILD_DIR+'/fonts'));
});

// Build Js (concat, uglify & copy to build)
gulp.task('build-js', function() {
  return gulp.src([SRC_DIR+'/js/jquery.min.js',
                  SRC_DIR+'/js/jquery.*.min.js',
                  SRC_DIR+'/js/skel.min.js',
                  SRC_DIR+'/js/skel-*.min.js',
                  SRC_DIR+'/js/init.js'
                ])
    .pipe(concat('script.'+timeInMs+'.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest(BUILD_DIR+'/js'));
});

// Build HTML (change css/js links & copy to build)
gulp.task('build-html', function() {
  var target = gulp.src(SRC_DIR+'/view/*.html');
  var sources = gulp.src([BUILD_DIR+'/**/*.js', BUILD_DIR+'/**/*.css'], {read: false});
 
  return target.pipe(inject(sources,{"ignorePath":"/www/cedric/"}))
    .pipe(gulp.dest(BUILD_DIR+'/view'));
});

// Clean
gulp.task('build-clean', function (cb) {
  del([
    BUILD_DIR+'/**/*',
    // we don't want to clean this file though so we negate the pattern
    '!'+BUILD_DIR+'/.gitignore'
  ], cb);
});

// Build
gulp.task('build', function(callback) {
  runSequence('build-clean',
              ['build-css', 'build-js','build-fonts','build-img'],
              'build-html',
              callback);
});

// Full-Build
gulp.task('full-build', function(callback) {
  runSequence('composer',
              'build',
              callback);
});

// Composer
gulp.task('composer', function () {
    return composer({bin:"php composer.phar"});
});
