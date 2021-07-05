/**
 * Gulpfile.
 *
 * A simple implementation of Gulp.
 *
 * Implements:
 *            1. CSS concatenation and minification
 *            2. JS concatenation
 *            3. Watch files
 *
 * @since 1.0.0
 * @author Guillermo Figueroa Mesa (@gfirem)
 */

/**
 * Configuration.
 */
const styleDir = './assets/css/*.css';
const sassDir = './assets/sass/**/*.scss';
const sassTargetDir = './assets/css';
const styleDestination = './assets/css/';
const jsDir = './assets/js/*.js';
const jsDestination = './assets/js/';
/*
 * Load modules
 */
const gulp = require('gulp');
const sass = require('gulp-sass');
sass.compiler = require('node-sass');
var autoprefixer = require('gulp-autoprefixer')
var minCss = require('gulp-minify-css')
var rename = require('gulp-rename')

const minifycss = require('gulp-uglifycss');
var uglify = require('gulp-uglify-es').default;
const notify = require('gulp-notify');
const clean = require('gulp-clean');
const stripDebug = require('gulp-strip-debug');
const eslint = require('gulp-eslint');

gulp.task('lint', () => {
    return gulp.src([jsDir, '!node_modules/**'])
        .pipe(eslint())
        .pipe(eslint.format())
        .pipe(eslint.failAfterError());
});

gulp.task('clean-min-styles', function() {
    return gulp.src(styleDestination + '*.min.css', {read: false})
        .pipe(clean({force: true}));
});

gulp.task('clean-min-js', function() {
    return gulp.src(jsDestination + '*.min.js', {read: false})
        .pipe(clean({force: true}));
});

gulp.task('sass', function () {
	return gulp.src(sassDir)
	// output non-minified CSS file
	.pipe(sass({outputStyle : 'expanded'}).on('error', sass.logError))
	.pipe(autoprefixer())
	.pipe(gulp.dest(sassTargetDir))
	// output the minified version
	.pipe(minifycss())
	.pipe(rename({ extname: '.min.css' }))
	.pipe(gulp.dest(sassTargetDir))
	.pipe(notify({message: 'TASK: "SASS" Completed!', onLast: true}));
});

gulp.task('sass:watch', function () {
	gulp.watch(sassDir, ['sass']);
});

gulp.task('js:watch', ['clean-min-js'], function () {
    gulp.watch(jsDir, ['js']);
});

gulp.task('styles', ['clean-min-styles'], function() {
    gulp.src(styleDir)
        .pipe(rename({suffix: '.min'}))
        .pipe(minifycss({
            maxLineLen: 10
        }))
        .pipe(gulp.dest(styleDestination))
        .pipe(notify({message: 'TASK: "CSS" Completed!', onLast: true}))
});

gulp.task('js', ['clean-min-js'], function() {
    gulp.src(jsDir)
        .pipe(rename({suffix: '.min'}))
        .pipe(uglify())
        //.pipe(stripDebug())
        .pipe(gulp.dest(jsDestination))
        .pipe(notify({message: 'TASK: "JS" Completed!', onLast: true}));
});

gulp.task('default', [], function() {
    gulp.run('styles');
    gulp.run('js');
    gulp.run('sass:watch');
    gulp.run('js:watch');
});
